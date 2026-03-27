=== Flipbook Catalog ===
Contributors: philhoyt
Tags: flipbook, pdf, catalog, magazine, post-type
Requires at least: 6.5
Tested up to: 7.0
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A configurable PDF catalog content type with block binding support for the Flipbook Block plugin.

== Description ==

Flipbook Catalog registers a custom post type for PDF-based content — magazines, catalogs, brochures, or whatever label you choose. It integrates with the [Flipbook Block](https://github.com/philhoyt/flipbook-block) plugin to render PDF flipbooks on the front end.

**How it works:**

1. Each catalog post stores a PDF URL in post meta (`flipbook_catalog_pdf_url`).
2. A pre-configured block theme template (`single-flipbook_catalog.html`) uses WordPress's `core/post-meta` block binding to inject the PDF URL into the Flipbook Block at render time.
3. No URL is ever hardcoded in the template — changing a post's PDF automatically updates the front end.

**Configurable labels and URL slug:**

The content type label (e.g. "Catalog", "Magazine", "Brochure") and its URL slug are configurable from the Settings page without touching code.

== Requirements ==

* WordPress 6.5 or higher (6.9+ required for block binding to function)
* PHP 8.1 or higher
* [Flipbook Block](https://github.com/philhoyt/flipbook-block) plugin version 1.0.4 or higher

== Block Binding Compatibility ==

The block template uses `core/post-meta` block binding to inject the PDF URL into the Flipbook Block at render time. For this to work, three conditions must be met:

**1. WordPress 6.9 or higher**

WordPress 6.9 introduced the `block_bindings_supported_attributes` filter, which allows custom blocks to opt into binding support. On WordPress 6.5–6.8, the binding is silently skipped and the flipbook renders without a PDF.

**2. Flipbook Block 1.0.4 or higher**

Version 1.0.4 adds the two changes required for binding to work:

* `"usesContext": ["postId", "postType"]` in `block.json` — tells WordPress to pass the current post's ID and type to the block, which `core/post-meta` uses to look up the right meta value.
* Registration of `pdfUrl` via the `block_bindings_supported_attributes_ph/flipbook-block` filter — WordPress 6.9+ maintains an opt-in allowlist of bindable attributes per block; without this registration the binding processor exits before calling the meta source.

**3. A plain string `pdfUrl` attribute**

The `pdfUrl` attribute in `block.json` must have no `source`, `selector`, or `attribute` properties — just `"type": "string"`. This has been the case since Flipbook Block 1.0.3.

== Installation ==

1. Install and activate [Flipbook Block](https://github.com/philhoyt/flipbook-block) version 1.0.4 or higher first.
2. Go to the [Flipbook Catalog releases page](https://github.com/philhoyt/flipbook-catalog/releases/latest) on GitHub and download the latest `flipbook-catalog.zip` file.
3. In your WordPress admin, go to **Plugins → Add New Plugin → Upload Plugin**.
4. Choose the downloaded zip file and click **Install Now**.
5. Click **Activate Plugin**.
6. Navigate to the new post type in the admin menu and configure labels and slug under Settings.

== Frequently Asked Questions ==

= Why isn't the flipbook rendering on the front end? =

Work through these checks in order:

1. **Is Flipbook Block installed and active?** Flipbook Catalog requires it. You will see an admin notice if it is missing.
2. **Is WordPress 6.9 or higher?** Block binding is silently skipped on earlier versions. The flipbook renders but receives an empty PDF URL.
3. **Is Flipbook Block 1.0.4 or higher?** Earlier versions do not register `pdfUrl` as a bindable attribute or declare the required post context. Update the plugin.
4. **Does the post have a PDF set?** Open the post in the admin and confirm a PDF has been selected in the PDF File meta box and the post has been saved.

= Will my content be deleted if I uninstall the plugin? =

No. Uninstalling removes only the plugin's settings (labels and slug). All posts and their PDF metadata are preserved in the database.

= Can I use this with a classic (non-block) theme? =

The block template (`single-flipbook_catalog.html`) is only used by block themes. With a classic theme, WordPress falls back to your theme's `single.php` or `index.php`. You would need to add the Flipbook Block to your template manually.

= Can I change the internal post type key? =

No. The post type key `flipbook_catalog` is fixed and not configurable. It is referenced by the block template and by any external integrations. The visible label and URL slug are configurable from the Settings page.

== Changelog ==

= 1.0.0 =
* Initial release.
* Registers the `flipbook_catalog` custom post type with configurable labels and URL slug.
* Classic meta box with media library picker for selecting PDF files.
* Block theme template using `core/post-meta` block binding to inject the PDF URL into Flipbook Block at render time.
* Settings page for singular label, plural label, and URL slug with automatic permalink flush on slug change.
* Admin notice when the Flipbook Block dependency is missing.
* Options-only cleanup on uninstall — posts and meta are preserved.
