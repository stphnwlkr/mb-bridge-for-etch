=== MB Bridge for Etch ===
Contributors: stphnwlkr
Tags: custom fields, meta box, etch
Requires at least: 7.1
Tested up to: 7.1.3
Requires PHP: 8.3
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Access Meta Box field values and management screens from a dedicated panel inside the Etch builder.

== Description ==

MB Bridge for Etch adds an MB Bridge button to the lower section of Etch's left toolbar.

Edit the current page's field values, or open the installed Meta Box management tools without leaving Etch. Management screens use same-site iframes, keeping Meta Box's existing editors, permissions, and saving behavior.

Features:

* Edit applicable post-based Meta Box fields, including custom post types.
* Save field values separately from the Etch page layout.
* Open field groups, post types, taxonomies, relationships, settings pages, custom models, and views when the corresponding Meta Box extensions are active.
* Return from an editor with Back to list, or click its active sidebar section.
* Keep Meta Box and WordPress default styling inside the panel.
* Load embedded screens behind a themed loading state to avoid a white flash.
* Refresh saved post lists and existing templates using Etch's public API.

Requires Etch and Meta Box or Meta Box AIO, installed separately. Visual management requires the relevant Meta Box extensions. This plugin does not include Etch, Meta Box, or paid extensions.

This is an independent integration, not an official Meta Box or Etch product. Meta Box and Etch names and the Meta Box logo identify the products this plugin integrates with.

The bridge sends no telemetry. It contacts GitHub to check public release metadata and download plugin updates through WordPress. No credentials or site content are sent to GitHub. Embedded screens run on your own WordPress site. Etch, Meta Box, and their extensions retain their own service behavior.

== Installation ==

1. Install and activate Etch and Meta Box or Meta Box AIO.
2. Upload the plugin ZIP through Plugins > Add Plugin > Upload Plugin, or copy the mb-bridge-for-etch folder into wp-content/plugins.
3. Activate MB Bridge for Etch.
4. Refresh the Etch builder and click MB Bridge with the Meta Box logo in the lower left toolbar.

If upgrading from the development plugin named Meta Box for Etch, deactivate that development copy before activating MB Bridge for Etch. Existing Meta Box data is retained.

== Updates ==

Download the installable ZIP from https://github.com/stphnwlkr/mb-bridge-for-etch/releases. After installation, newer published releases appear in WordPress Plugins and Dashboard > Updates. Automatic updates follow your WordPress settings; the bridge does not enable them for you. Update checks require outbound HTTPS access to GitHub.

== Frequently Asked Questions ==

= Does this replace Meta Box? =

No. Meta Box and the relevant extensions must remain installed and active. The bridge exposes their existing tools inside Etch.

= Are these native Etch editors? =

Management screens are Meta Box's WordPress screens embedded in same-site iframes. Page fields is a fields-only form rendered and saved by Meta Box, also embedded in an iframe.

= Does saving fields overwrite my Etch layout? =

No. The Page fields save action updates field values through Meta Box without updating the post title or content. Refresh Etch's preview when needed to see changed dynamic values.

= Which field values can I edit? =

Page fields supports applicable post-based field groups. Term, user, comment, and option values remain in their native WordPress screens. Third-party field extensions may need additional compatibility testing.

= What happens if I deactivate or delete the bridge? =

Meta Box definitions and values remain intact. The bridge does not create a separate field database or remove Meta Box data.

= Do new content types appear in Etch immediately? =

Not yet. The bridge attempts to refresh saved lists, but this does not reliably update every Etch view. An in-panel notice offers a Reload Etch button to see all changes. A confirmation warns that unsaved Etch and Meta Box changes will be lost. Save your work first; the button performs a normal browser reload. Etch's current public API does not refresh cached post-type and taxonomy definitions. Those changes still need a user-initiated reload after saving your work.

= Does this meet WCAG 2.2 AA? =

Full conformance is not claimed. The outer panel keeps the Etch toolbar available and provides modal confirmations and responsive navigation. Embedded screens retain Meta Box and WordPress default styles and controls. Native Meta Box controls still include accessibility blockers, including a nested interactive control in the field group editor. Further keyboard, screen-reader, and field-extension testing is required.

== Changelog ==

= 0.1.0 =
* Initial release.
