<?php
/**
 * Fired when the Flipbook Catalog plugin is uninstalled.
 *
 * Removes plugin options only. Posts and post meta are intentionally preserved
 * so that content survives plugin removal.
 *
 * @package Flipbook_Catalog
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'flipbook_catalog_singular' );
delete_option( 'flipbook_catalog_plural' );
delete_option( 'flipbook_catalog_slug' );

flush_rewrite_rules();
