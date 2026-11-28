#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
source_image=riyadh-decor/assets/majlis.webp
output=riyadh-decor/assets/majlis-desktop-opt.webp
if ! command -v cwebp >/dev/null; then
    echo "cwebp is required (Ubuntu: sudo apt-get install webp)" >&2
    exit 1
fi
tmp=$(mktemp --suffix=.webp)
trap 'rm -f "$tmp"' EXIT
for quality in 76 70 64; do
    cwebp -quiet -q "$quality" -m 6 -resize 1280 0 "$source_image" -o "$tmp"
    if [[ $(wc -c < "$tmp") -le 100000 ]]; then break; fi
done
[[ $(wc -c < "$tmp") -lt $(wc -c < "$source_image") ]]
cp "$tmp" "$output"
printf 'Hero original=%s bytes; optimized=%s bytes\n' "$(wc -c < "$source_image")" "$(wc -c < "$output")"
