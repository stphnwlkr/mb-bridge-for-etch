# MB Bridge for Etch

A dedicated MB Bridge panel in Etch, opened with the supplied Meta Box logo in the lower section of the left toolbar. The layout follows Etch Toolkit's sidebar and workspace. Etch Toolkit is not a dependency.

## What it does

- Page fields renders the current item's applicable Meta Box field groups. When editing a template with an original post context, it uses that original post.
- Save field values uses Meta Box's renderers, nonces, sanitizers, storage, and save hooks. It does not update the post's title or content.
- Field groups, post types, taxonomies, relationships, settings pages, custom models, and views open their existing Meta Box admin editors inside the panel. Only installed tools the user can access appear.
- The left Etch toolbar stays usable. Unsaved Meta Box edits prompt before switching tools or closing the panel; continuing keeps the edits in the cached editor. The back arrow always returns to the canvas.
- Each tool retains its embedded editor when switching sections or closing the panel. Page fields refreshes when reopened if there are no unsaved field changes.
- Embedded screens stay hidden until the iframe layout adjustments load. Meta Box and WordPress provide the content styling.
- Refresh and Open in WordPress provide recovery if a screen cannot load. Meta Box management screens use their default styling both inside Etch and in WordPress.
- Back to list returns from a management editor to its listing. Clicking the active management section also returns to the list. Unsaved edits prompt before leaving.
- The outer Etch panel uses 14px text. Embedded controls retain Meta Box's native fonts, colors, and layout.

## Requirements and installation

WordPress 7.1+, PHP 8.3+, Etch, and Meta Box or Meta Box AIO. Visual management tools require their respective Meta Box extensions. This plugin does not bundle or replace Meta Box.

Upload `dist/mb-bridge-for-etch-0.1.0.zip` through Plugins → Add Plugin → Upload Plugin, then activate. Refresh the Etch builder and click the Meta Box logo in the lower toolbar. An inactive Meta Box installation produces an explanatory empty state.

## Validation

Update tests cover metadata validation, caching, plugin isolation, compatibility requirements, and handling unavailable or invalid releases.

Release checks ran on WordPress 7.1.3 and PHP 8.4 with Etch 1.7.0 and Meta Box AIO 3.12.0. PHP 8.3 is the declared minimum; a separate PHP 8.3 runtime was not available for these checks. Etch Toolkit is optional.

- Created and saved a field group using Meta Box's editor inside the panel.
- Rendered the assigned field and saved a value through Page fields. Database readback matched the entered value. The page content's SHA-256 hash was identical before and after.
- Integration checks cover permitted management tools, unauthenticated access, applicable post/term groups, invalid group nonces, text, checkbox and repeatable values, clearing values, and preservation of page content.
- Verified embedded screens load with native WordPress and Meta Box styles.
- Checked the separate Single Plugin template opens its Etch structure with the plugin active.

Run on a disposable/local Studio site with Meta Box active:

```sh
studio wp eval-file /absolute/path/to/mb-for-etch/tests/integration.php
```

The test creates a temporary draft page and moves it to Trash in a `finally` block. It does not change existing content. The test field group used for browser checks was also moved to Trash and its temporary page meta removed.

## Scope

Page fields supports post-based Meta Box groups, including custom post types. Term, user, comment, and option values remain in their native WordPress screens. Management editors use same-origin iframes so Meta Box keeps ownership of its functionality. Custom field extensions that assume a particular WordPress editor layout may need additional compatibility work. Test their advanced workflows before production use.

After changing values, Etch's existing dynamic preview may need a refresh. This plugin does not force a builder reload or modify an unsaved layout. Removing the plugin leaves all Meta Box definitions and values intact.

The bridge attempts to refresh saved post lists and existing templates through Etch's public navigation API when change tokens differ. The bridge checks on frame navigation, panel close, focus, and every 15 seconds while the tab is visible. Etch 1.7.0 does not expose invalidation of its cached post-type/taxonomy definitions; those changes display an explanation and still need a user-initiated reload. This is a partial implementation of live refresh, not full schema synchronization.

Accessibility is assessed against WCAG 2.2 AA, but full conformance is not claimed. See [the audit](ACCESSIBILITY.md) for checked screens and remaining native Meta Box blockers.

## GitHub distribution and updates

Releases: https://github.com/stphnwlkr/mb-bridge-for-etch/releases

Install the attached `mb-bridge-for-etch-0.1.0.zip`, not GitHub's automatically generated source archive. Future published releases appear in WordPress's Plugins and Updates screens. WordPress controls whether automatic updates are enabled. The bridge uses the native Update URI hook and fetches public metadata from GitHub; it sends no site content or credentials. GitHub access failures leave the installed plugin unchanged.

### Build a release

Update the plugin version, readme stable tag, requirements, and changelog together. Then run:

```sh
python3 scripts/package-release.py --repository https://github.com/stphnwlkr/mb-bridge-for-etch
```

The ZIP has one `mb-bridge-for-etch` folder and excludes tests, screenshots, reports, and development scripts. Upload the versioned ZIP, `dist/update.json`, and `dist/SHA256SUMS` to a GitHub release tagged `v<version>`. Publish only after all assets are uploaded. Keep preview releases marked prerelease so they do not become the latest stable update. Metadata includes the new release's PHP and WordPress requirements; update checks only accept version-matched ZIP URLs from this repository.

The initial release changelog is: Initial release.

The toolbar uses Etch's documented [Settings Bar Controls API](https://docs.etchwp.com/integrations/controls). Field discovery uses Meta Box's [registry API](https://docs.metabox.io/functions/rwmb-get-registry/).

Detected saved changes show a reload notice inside the Meta Box panel. It disappears with the panel and remains available when the panel is reopened. No warning floats over the Etch editor. The Reload Etch button asks users to save their Etch layout and Meta Box changes before confirming a normal browser reload. The bridge never reloads Etch automatically.
