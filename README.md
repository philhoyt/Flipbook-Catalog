# Flipbook Catalog

[![CI](https://github.com/philhoyt/Flipbook-Catalog/actions/workflows/ci.yml/badge.svg)](https://github.com/philhoyt/Flipbook-Catalog/actions/workflows/ci.yml)

A PDF catalog post type that feeds each post's PDF into the Flipbook Block through block binding, with configurable labels and URL slug.

The plugin registers the `flipbook_catalog` post type, a PDF File meta box, a Settings page, and one block template. It has no block of its own: the front end is rendered by `ph/flipbook-block` from the [Flipbook Block](https://github.com/philhoyt/flipbook-block) plugin, which must be installed separately.

## Requirements

- WordPress 6.5 or newer to activate. The block template is registered with `register_block_template()`, which exists from 6.7, and the `core/post-meta` binding on a non-core block only resolves from 6.9. In practice you want 6.9 or newer.
- PHP 8.1 or newer.
- A block theme. Classic themes ignore the template.
- Flipbook Block 1.0.4 or newer, active. The plugin checks for the `ph/flipbook-block` block type on `admin_init` and shows a notice when it is missing. It does not deactivate itself.

## Installation

1. Install and activate Flipbook Block 1.0.4 or newer.
2. Download `flipbook-catalog.zip` from the [latest release](https://github.com/philhoyt/flipbook-catalog/releases/latest).
3. Go to Plugins > Add New Plugin > Upload Plugin, choose the zip, install, and activate.

Updates are delivered from GitHub releases through the bundled Plugin Update Checker. Only releases that carry a `.zip` asset are offered; a release without one is skipped rather than falling back to the source archive.

## Usage

1. Go to Catalogs > Add New. The screen is the classic editor with title, featured image, and excerpt; the block editor is switched off for this post type.
2. In the PDF File box, click Upload / Select PDF and choose a file from the media library.
3. Publish. Publishing with no PDF keeps the post as a draft and shows an error notice.

Catalogs > Settings holds the singular label (default `Catalog`), plural label (default `Catalogs`), and URL slug. Saving a new slug flushes the rewrite rules. The post type key and the meta keys are not configurable.

## How it works

- `includes/class-post-type.php` registers `flipbook_catalog` with `public`, `has_archive`, `show_in_rest`, and `supports` of title, thumbnail, and excerpt. On `init` at priority 11 it reads `templates/single-flipbook_catalog.html` and registers it as `flipbook-catalog//single-flipbook_catalog`. It also filters `use_block_editor_for_post_type` to return false for this type.
- `includes/class-meta.php` registers `flipbook_catalog_pdf_url` (string) and `flipbook_catalog_pdf_id` (integer) with `show_in_rest`, which the binding requires. It renders the meta box, saves on `save_post_flipbook_catalog`, and uses `wp_insert_post_data` plus a redirect query arg to block publishing without a PDF. The media picker is a `wp.media` frame added with `wp_add_inline_script` on `media-editor`.
- The template places a `ph/flipbook-block` with `"metadata":{"bindings":{"pdfUrl":{"source":"core/post-meta","args":{"key":"flipbook_catalog_pdf_url"}}}}`. No URL lives in the template.
- `includes/class-settings.php` is plain Settings API: three `register_setting()` calls, one section, and a `flush_rewrite_rules()` on `update_option_flipbook_catalog_slug`.
- `uninstall.php` deletes the three options and flushes rewrite rules. Posts and meta stay.

### Why the binding needs WordPress 6.9 and Flipbook Block 1.0.4

WordPress keeps an allowlist of bindable attributes per block. Core blocks are on it; a third-party block has to opt in through the `block_bindings_supported_attributes_{block}` filter, which was added in 6.9. Flipbook Block 1.0.4 registers `pdfUrl` through that filter and declares `"usesContext": ["postId", "postType"]` so `core/post-meta` can find the current post. The attribute also has to be a plain `"type": "string"` with no `source`, which it has been since 1.0.3. If any of these is missing the binding is skipped and the block renders with an empty URL.

## Development

PHP only. No `package.json`, no build step.

```bash
composer install        # WPCS, phpcs; also copies Plugin Update Checker into lib/
vendor/bin/phpcs        # lint
vendor/bin/phpcbf       # fix what phpcs can fix
composer plugin-zip     # bin/plugin-zip.sh, writes flipbook-catalog.zip
```

There is no automated test suite. Test against a WordPress install with Flipbook Block active.

CI (`.github/workflows/ci.yml`) runs `composer audit` and `vendor/bin/phpcs` on PHP 8.1 through 8.4 for pull requests and pushes to `main`.

## Releases

Bump the version in `flipbook-catalog.php` (header and `FLIPBOOK_CATALOG_VERSION`) and `Stable tag` in `readme.txt`, add a changelog entry, then push a lowercase `v` tag:

```bash
git tag v1.0.1 && git push origin v1.0.1
```

The tag must be lowercase. Plugin Update Checker strips the prefix with a case-sensitive `ltrim()`, so an uppercase `V1.0.1` is never offered as an update, and the release workflow only matches `v[0-9]+.[0-9]+.[0-9]+`. The existing `V1.0.0` tag predates that rule.

The workflow (`.github/workflows/release.yml`) runs `bin/plugin-zip.sh` (an rsync that excludes `vendor/`, `bin/`, `docs/`, `CLAUDE.md`, `README.md`, and the composer and phpcs files, then zips with a top-level `flipbook-catalog/` folder), pulls the matching `= X.Y.Z =` block from `readme.txt` for the release notes, and attaches `flipbook-catalog.zip` and `readme.txt` to the GitHub release.

## Limitations

- No block of its own, and no fallback when Flipbook Block is missing: the notice is the only signal.
- No archive template. The archive at the configured slug uses the theme's archive template.
- No custom taxonomies and no REST endpoints beyond what `show_in_rest` exposes.
- The block editor is disabled for this post type, so the single template cannot be edited per post.
- One PDF per post.

## License

GPL-2.0-or-later.
