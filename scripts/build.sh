#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."
build_dir="$(mktemp -d)"
server_pid=''
cleanup() {
    if [[ -n "$server_pid" ]]; then
        kill "$server_pid" 2>/dev/null || true
    fi
    rm -rf "$build_dir"
}
trap cleanup EXIT

port="${RD_EXPORT_PORT:-9471}"
local_url="http://127.0.0.1:$port"
public_url="${1:-$local_url}"
if [[ -n "${RD_WORDPRESS_ZIP:-}" ]]; then
    wp_zip="$RD_WORDPRESS_ZIP"
else
    wp_zip="$build_dir/wordpress.zip"
    curl --fail --silent --show-error --location --retry 3 --max-time 120 \
        --output "$wp_zip" https://wordpress.org/wordpress-6.9.4.zip
fi
unzip -q "$wp_zip" -d "$build_dir"
node_modules/.bin/wp-playground-cli server --port "$port" --php 8.3 --workers 1 \
    --site-url "$local_url" --wordpress-install-mode install-from-existing-files \
    --mount-dir-before-install "$build_dir/wordpress" /wordpress \
    --mount-dir "$PWD/riyadh-decor" /wordpress/wp-content/themes/riyadh-decor \
    --blueprint "$PWD/scripts/blueprint.json" >"$build_dir/server.log" 2>&1 &
server_pid=$!

started=false
for ((attempt=0; attempt<120; attempt++)); do
    if curl --fail --silent --max-time 3 "$local_url/" -o "$build_dir/home.html" &&
        grep -q 'id="rd-hero-title"' "$build_dir/home.html"; then
        started=true
        break
    fi
    if ! kill -0 "$server_pid" 2>/dev/null; then break; fi
    sleep 1
done
if [[ "$started" != true ]]; then
    cat "$build_dir/server.log"
    exit 1
fi
mkdir -p qa-report
if ! node scripts/export.mjs "$local_url" "$public_url" 2>&1 | tee qa-report/export.log; then
    cp "$build_dir/server.log" qa-report/playground-server.log
    tail -n 100 "$build_dir/server.log" >&2
    exit 1
fi
