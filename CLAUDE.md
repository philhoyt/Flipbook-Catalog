# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Flipbook Catalog is a WordPress plugin that registers a configurable PDF-based custom post type (`flipbook_catalog`). It has no block of its own — it depends on the [flipbook-block](https://github.com/philhoyt/flipbook-block) plugin and wires up `ph/flipbook-block` via a block template using `core/post-meta` block binding. The PDF URL is stored in post meta and resolved at render time — never hardcoded in the template.

**Requirements:** WordPress 6.5+, PHP 8.1+, `ph/flipbook-block` plugin installed and active.

**Block binding note:** `core/post-meta` block binding requires WordPress 6.9+ (when the `block_bindings_supported_attributes` filter was added). On 6.5–6.8 the template renders but the PDF URL will be empty. `ph/flipbook-block` must also declare `"usesContext": ["postId", "postType"]` in its `block.json` and register `pdfUrl` via the `block_bindings_supported_attributes_ph/flipbook-block` filter — both are required for the binding to resolve.

## Development

This is a pure PHP plugin. There is no build step, no `package.json`, and no `node_modules`. Install directly into a WordPress `wp-content/plugins/` directory and activate.

**Linting:**
```bash
composer install          # first time only
./vendor/bin/phpcs        # check all files
./vendor/bin/phpcbf       # auto-fix violations
```

WordPress Coding Standards are enforced via `phpcs.xml`. The `InvalidClassFileName` rule is suppressed — short file names (`class-settings.php` etc.) are intentional.

Testing is done manually against a WordPress installation. There is no automated test suite.

## File Structure

```
flipbook-catalog/
├── flipbook-catalog.php          # Main plugin file, constants, bootstrap
├── uninstall.php                 # Deletes options only (not posts/meta)
├── readme.txt                    # WordPress.org readme
├── composer.json                 # Dev dependencies (WPCS)
├── phpcs.xml                     # PHP_CodeSniffer configuration
├── templates/
│   └── single-flipbook_catalog.html  # Block theme template (ignored by classic themes)
└── includes/
    ├── class-post-type.php       # CPT registration + classic editor enforcement
    ├── class-meta.php            # Meta registration, meta box UI, save hook
    ├── class-settings.php        # Settings page + options (Settings API only)
    └── class-dependencies.php    # Checks for ph/flipbook-block; shows admin notice if missing
```

## Architecture: Key Decisions (Do Not Reverse Without Discussion)

1. **Zero block ownership.** This plugin uses `ph/flipbook-block` from the flipbook-block plugin — it does not bundle, fork, or register any block itself. If the dependency is missing, show a notice; never add a fallback block here.

2. **Block template uses `core/post-meta` binding.** `templates/single-flipbook_catalog.html` contains no hardcoded PDF URL. The URL is always resolved at render time from post meta. Do not replace this with a static URL.

3. **`show_in_rest: true` is mandatory.** Required on the post type registration AND both meta keys for `core/post-meta` block binding to function. Do not remove it from either.

4. **Meta keys are fixed constants.** `flipbook_catalog_pdf_url` and `flipbook_catalog_pdf_id` are not user-configurable. The block template and external integrations depend on these being stable.

5. **Settings page is plain WordPress Settings API.** No React, no custom REST endpoints. Standard `register_setting()` / `settings_fields()` / `do_settings_sections()` pattern only.

6. **No build tooling.** Do not introduce `@wordpress/scripts`, webpack, or any JS compilation. Any JS needed (meta box media picker) is output via `wp_add_inline_script` or `wp_print_inline_script_tag`.

## PHP Conventions

- PHP prefix: `flipbook_catalog_`
- Text domain: `flipbook-catalog`
- Constants defined in main file: `FLIPBOOK_CATALOG_VERSION`, `FLIPBOOK_CATALOG_PATH`, `FLIPBOOK_CATALOG_URL`
- Classes instantiated and wired on `plugins_loaded`
- CPT key is always `flipbook_catalog` — never expose this as configurable

## What This Plugin Does NOT Do

- No shortcodes, no jQuery, no custom REST endpoints
- No block editor for the CPT (classic editor enforced via `use_block_editor_for_post_type` filter)
- No automatic deactivation when flipbook-block is missing (content must survive)
- No deletion of posts or postmeta on uninstall (only options are deleted)
- No archive template (handled by the active theme)
- No custom taxonomies (may be added later)
