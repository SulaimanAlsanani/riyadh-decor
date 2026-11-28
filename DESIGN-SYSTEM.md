# Riyadh Decor Design System v1.4.0

RTL-first Arabic theme. Tokens are defined in `riyadh-decor/style.css`; shared icons are rendered by `rd_icon()` in `inc/components.php`.

| Token | Value |
|---|---|
| Brand dark | #092B27 |
| Brand deep | #103E38 |
| Accent green | #086E64 |
| Primary CTA | #F89922 |
| Page | #FAF8F3 |
| Surface | #FFFFFF |
| Spacing | 4, 8, 12, 16, 20, 24px |
| Controls | 44px minimum, 9px radius |
| Cards | 14px radius |
| Motion | 180ms ease; reduced-motion respected |
| Font | DIN Next Arabic if installed, local Almarai 400/700/800 fallback |

Use shared button classes and semantic color tokens. Icons are decorative with adjacent text or accessible labels. Preserve original image files; use object-fit for thumbnails and contain in the lightbox. Test widths 390, 768 and 1440 pixels. Do not add unlicensed fonts.

V1.3.0 adds editorial category/gallery sequencing, room feature banners, detail section navigation, gallery thumbnail navigation and a quote review flow. Existing brand tokens remain unchanged.

Stable baseline: `stable/v1.3.1` (4fee10d). V1.4.0 introduces an accessible quick quote selector, mobile quote shortcut and low priority hints for lazy-loaded images. No original assets modified. Performance improvement must be measured, not assumed.
