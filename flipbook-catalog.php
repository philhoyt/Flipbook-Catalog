<?php
/**
 * Plugin Name:       Flipbook Catalog
 * Plugin URI:        https://github.com/philhoyt/flipbook-catalog
 * Description:       A configurable PDF catalog content type with block binding support for the Flipbook Block plugin.
 * Version:           1.0.0
 * Author:            philhoyt
 * Author URI:        https://philhoyt.com
 * License:           GPL-2.0-or-later
 * Text Domain:       flipbook-catalog
 * Requires at least: 6.5
 * Requires PHP:      8.1
 *
 * @package Flipbook_Catalog
 */

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

defined( 'ABSPATH' ) || exit;

define( 'FLIPBOOK_CATALOG_VERSION', '1.0.0' );
define( 'FLIPBOOK_CATALOG_PATH', plugin_dir_path( __FILE__ ) );
define( 'FLIPBOOK_CATALOG_URL', plugin_dir_url( __FILE__ ) );

// Updates are delivered from GitHub releases via Plugin Update Checker.
require_once FLIPBOOK_CATALOG_PATH . 'lib/plugin-update-checker/plugin-update-checker.php';

$flipbook_catalog_update_checker     = PucFactory::buildUpdateChecker(
	'https://github.com/philhoyt/Flipbook-Catalog/',
	__FILE__,
	'flipbook-catalog'
);
$flipbook_catalog_update_checker_api = $flipbook_catalog_update_checker->getVcsApi();
if ( method_exists( $flipbook_catalog_update_checker_api, 'enableReleaseAssets' ) ) {
	// Only a .zip asset counts, and a release without one is skipped. The
	// default PREFER_RELEASE_ASSETS falls back to GitHub's source archive,
	// which installs as <Repo>-<tag>/ and breaks the plugin directory.
	$flipbook_catalog_update_checker_api->enableReleaseAssets( '/\.zip$/', $flipbook_catalog_update_checker_api::REQUIRE_RELEASE_ASSETS );
}

require_once FLIPBOOK_CATALOG_PATH . 'includes/class-dependencies.php';
require_once FLIPBOOK_CATALOG_PATH . 'includes/class-settings.php';
require_once FLIPBOOK_CATALOG_PATH . 'includes/class-post-type.php';
require_once FLIPBOOK_CATALOG_PATH . 'includes/class-meta.php';

add_action(
	'plugins_loaded',
	function () {
		( new Flipbook_Catalog_Dependencies() )->register();
		( new Flipbook_Catalog_Settings() )->register();
		( new Flipbook_Catalog_Post_Type() )->register();
		( new Flipbook_Catalog_Meta() )->register();
	}
);
