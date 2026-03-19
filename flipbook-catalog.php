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
 * Domain Path:       /languages
 * Requires at least: 6.5
 * Requires PHP:      8.1
 *
 * @package Flipbook_Catalog
 */

defined( 'ABSPATH' ) || exit;

define( 'FLIPBOOK_CATALOG_VERSION', '1.0.0' );
define( 'FLIPBOOK_CATALOG_PATH', plugin_dir_path( __FILE__ ) );
define( 'FLIPBOOK_CATALOG_URL', plugin_dir_url( __FILE__ ) );

require_once FLIPBOOK_CATALOG_PATH . 'includes/class-dependencies.php';
require_once FLIPBOOK_CATALOG_PATH . 'includes/class-settings.php';
require_once FLIPBOOK_CATALOG_PATH . 'includes/class-post-type.php';
require_once FLIPBOOK_CATALOG_PATH . 'includes/class-meta.php';

add_action(
	'plugins_loaded',
	function () {
		load_plugin_textdomain(
			'flipbook-catalog',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);

		( new Flipbook_Catalog_Dependencies() )->register();
		( new Flipbook_Catalog_Settings() )->register();
		( new Flipbook_Catalog_Post_Type() )->register();
		( new Flipbook_Catalog_Meta() )->register();
	}
);
