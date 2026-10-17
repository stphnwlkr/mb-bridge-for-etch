Current navigation: the outer panel is nonmodal so Etch toolbar controls remain available. Unsaved-change confirmations are modal. The back arrow returns directly to the canvas.

# Accessibility review, 0.0.1

**Historical audit:** The shared admin theme and its accessibility attribute patches were subsequently removed at the user's request. Embedded screens now use native Meta Box/WordPress styling. Results and fixes below describe the earlier themed build and must not be treated as validation of the current embedded screens. The outer panel's modal focus management remains.

Target: [WCAG 2.2 levels A and AA](https://www.w3.org/WAI/WCAG22/quickref/). Review date: 2026-10-09. **Not a statement of full conformance.**

Environment: WordPress 7.1.3, Etch 1.7.0, Meta Box AIO 3.12.0, the site's installed Admin Columns controls, and the dark Etch theme at localhost:8881.

## Changes made

- Native modal dialog keeps focus out of the covered Etch canvas. Escape closes the panel and returns focus to its toolbar button.
- Visible focus indicators and a non-color marker for the selected section.
- Minimum 28px height for bridge buttons, with 24px minimum width for themed native buttons.
- Shared readable text, notices, filter counts, helper text, and stronger borders. Input boundaries use the brighter muted foreground.
- Missing accessible names supplied from existing visible field labels; shortcode, tooltip, group-toggle, and Views category controls named.
- Stacked bridge navigation and Meta Box editor layout at narrow widths. The editor's scroll container measured 294px client and scroll width at a 320px viewport after correction.
- The page-fields save bar is not sticky, avoiding covered fields when zoomed or text spacing increases.

## Checks performed

Axe-core WCAG 2 A/AA, 2.1 A/AA, and 2.2 AA rules were run using a temporary local harness excluded from the release. Automated results are limited to the selected rendered screens and do not establish conformance.

| Screen | Automated outcome after fixes |
| --- | --- |
| Bridge controls | No violations in the tested desktop state |
| Post Types list | No violations; table-header manual review flag |
| Field Groups list | No violations; shortcode contrast and table-header manual review flags |
| Taxonomies, Relationships, Settings Pages, Custom Models lists | No violations; table-header manual review flags |
| Views list | No violations after category select was named; table-header manual review flag |
| Page fields, Sample Page empty state | No violations after eyebrow contrast correction |
| Website Fields editor | One native nested-interactive violation remains; checked contrast and labels passed |

Manual checks: opening with Enter; closing with Escape; focus returning to the toolbar; Back to list navigation; 320px bridge and field-editor reflow measurements. The shared theme was visually inspected in standalone WordPress and the Etch iframe.

## Remaining blockers and review

- Meta Box renders a checkbox inside the "Save data in a custom table" button. This remains an axe nested-interactive violation affecting semantic/keyboard access. Moving React-owned controls in a theme shim risks breaking Meta Box. Fix belongs upstream or needs a separately tested compatibility implementation.
- Native field sorting and other dragging operations need a verified keyboard and single-pointer alternative, including WCAG 2.5.7.
- A complete screen-reader review has not been performed. All editor tabs, tooltips, media dialogs, validation errors, custom field extensions, authentication flows, and translations need review.
- Light theme, custom Etch palettes, 200% text enlargement, text-spacing overrides, and all 400% zoom workflows have not been exhaustively tested. The 320px viewport check covers only the bridge and the inspected editor state.
- Automated manual-review flags are unresolved, not passes. The Sample Page had no assigned groups, so its empty-state check does not cover all page-field types.

Do not market 0.0.1 as WCAG 2.2 AA conformant while these issues remain.
