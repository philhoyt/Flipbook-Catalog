=== Flipbook Catalog ===
Contributors: philhoyt
Tags: flipbook, pdf, catalog, magazine, post-type
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.0.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A PDF catalog post type that feeds each post's PDF into the Flipbook Block through block binding, with configurable labels and URL slug

== Description ==

Flipbook Catalog registers a `flipbook_catalog` post type for PDF publications: catalogs, magazines, brochures, or whatever you call them. It has no block of its own. It depends on the [Flipbook Block](https://github.com/philhoyt/flipbook-block) plugin to show the PDF on the front end.

How the pieces fit:

1. Each post stores a PDF from the media library in the `flipbook_catalog_pdf_url` and `flipbook_catalog_pdf_id` meta keys. The edit screen is the classic editor with a PDF File meta box; the block editor is turned off for this post type.
2. The plugin registers a block template, `single-flipbook_catalog`, that places a Flipbook Block with its `pdfUrl` attribute bound to the post meta through `core/post-meta`.
3. At render time WordPress reads the PDF URL from the post, so changing a post's PDF changes the front end. No URL is stored in the template.

A post cannot be published without a PDF. Publishing with the PDF field empty keeps the post as a draft and shows an error notice.

The singular label, plural label, and URL slug are set on the post type's Settings page. Changing the slug flushes permalinks. The post type key, `flipbook_catalog`, and the meta keys are fixed.

Uninstalling removes the three options. Posts and their meta stay in the database.

== Requirements ==

* WordPress 6.5 or newer to activate. The block template needs 6.7, and the `core/post-meta` binding needs 6.9, so on anything older than 6.9 the flipbook renders without a PDF.
* PHP 8.1 or newer.
* A block theme. Classic themes ignore the block template.
* [Flipbook Block](https://github.com/philhoyt/flipbook-block) 1.0.4 or newer, installed and active. The plugin shows an admin notice when the `ph/flipbook-block` block is not registered.

== Block Binding Compatibility ==

Three things have to be true for the PDF to reach the block:

1. WordPress 6.9 or newer. 6.9 added the `block_bindings_supported_attributes` filter that lets a non-core block opt in to binding. On 6.5 to 6.8 the binding is skipped and the block gets an empty URL.
2. Flipbook Block 1.0.4 or newer. That version declares `"usesContext": ["postId", "postType"]` in its `block.json`, which `core/post-meta` uses to find the right post, and registers `pdfUrl` through the `block_bindings_supported_attributes_ph/flipbook-block` filter.
3. A plain string `pdfUrl` attribute in the block's `block.json`, with no `source`, `selector`, or `attribute` keys. That has been the case since Flipbook Block 1.0.3.

== Installation ==

1. Install and activate Flipbook Block 1.0.4 or newer.
2. Download `flipbook-catalog.zip` from the [latest release](https://github.com/philhoyt/flipbook-catalog/releases/latest) on GitHub.
3. Go to Plugins > Add New Plugin > Upload Plugin, choose the zip, and click Install Now.
4. Activate the plugin.
5. A Catalogs menu appears in wp-admin. Add a post, pick a PDF in the PDF File box, and publish. Labels and the slug are under Catalogs > Settings.

Updates come from GitHub releases through the bundled Plugin Update Checker.

== Frequently Asked Questions ==

= The flipbook is empty on the front end. What should I check? =

In this order:

1. Is Flipbook Block installed and active? An admin notice appears when it is not.
2. Is WordPress 6.9 or newer? Earlier versions skip the binding, so the block renders with no PDF.
3. Is Flipbook Block 1.0.4 or newer? Earlier versions do not register `pdfUrl` as bindable or declare the post context.
4. Does the post have a PDF? Open it in wp-admin and check the PDF File box.

= What happens to my posts when I uninstall? =

They stay. Uninstall deletes the three label and slug options and flushes permalinks; posts and post meta are untouched.

= Does it work with a classic theme? =

The block template only applies to block themes. A classic theme falls back to its own `single.php` or `index.php`, where you would need to add the Flipbook Block yourself.

= Can I change the post type key? =

No. `flipbook_catalog` is fixed because the template and any outside integration refer to it. The visible labels and the URL slug are the configurable parts.

== Changelog ==

= 1.0.1 =
* Fixed a catalog post being publishable without a PDF. Publishing without one now keeps the post as a draft and shows an error notice.
* Changed updates to be delivered through GitHub releases.
* Tested up to WordPress 7.1.

= 1.0.0 =
* Initial release.
* Added the `flipbook_catalog` post type with configurable labels and URL slug.
* Added a classic meta box with a media library picker for the PDF.
* Added a block theme template that binds the PDF URL into the Flipbook Block through `core/post-meta`.
* Added a Settings page for the singular label, plural label, and URL slug, with a permalink flush on slug change.
* Added an admin notice when the Flipbook Block dependency is missing.
* Added an uninstall routine that removes options only; posts and meta are kept.
