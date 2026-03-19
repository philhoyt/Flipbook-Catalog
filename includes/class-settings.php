<?php
/**
 * Settings page for the Flipbook Catalog plugin.
 *
 * @package Flipbook_Catalog
 */

// phpcs:disable WordPress.Files.FileName.InvalidClassFileName

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the Flipbook Catalog settings page.
 */
class Flipbook_Catalog_Settings {

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_options' ) );
		add_action( 'update_option_flipbook_catalog_slug', array( $this, 'on_slug_change' ) );
		add_action( 'admin_notices', array( $this, 'maybe_show_slug_updated_notice' ) );
	}

	/**
	 * Register the settings submenu page under the CPT menu.
	 */
	public function add_settings_page(): void {
		add_submenu_page(
			'edit.php?post_type=flipbook_catalog',
			__( 'Flipbook Catalog Settings', 'flipbook-catalog' ),
			__( 'Settings', 'flipbook-catalog' ),
			'manage_options',
			'flipbook-catalog-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register all plugin options with their defaults and sanitization callbacks.
	 */
	public function register_options(): void {
		register_setting(
			'flipbook_catalog_settings',
			'flipbook_catalog_singular',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'Catalog',
			)
		);

		register_setting(
			'flipbook_catalog_settings',
			'flipbook_catalog_plural',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'Catalogs',
			)
		);

		register_setting(
			'flipbook_catalog_settings',
			'flipbook_catalog_slug',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'catalogs',
			)
		);

		add_settings_section(
			'flipbook_catalog_main',
			__( 'Content Type Labels', 'flipbook-catalog' ),
			'__return_empty_string',
			'flipbook-catalog-settings'
		);

		add_settings_field(
			'flipbook_catalog_singular',
			__( 'Singular Label', 'flipbook-catalog' ),
			array( $this, 'render_singular_field' ),
			'flipbook-catalog-settings',
			'flipbook_catalog_main'
		);

		add_settings_field(
			'flipbook_catalog_plural',
			__( 'Plural Label', 'flipbook-catalog' ),
			array( $this, 'render_plural_field' ),
			'flipbook-catalog-settings',
			'flipbook_catalog_main'
		);

		add_settings_field(
			'flipbook_catalog_slug',
			__( 'URL Slug', 'flipbook-catalog' ),
			array( $this, 'render_slug_field' ),
			'flipbook-catalog-settings',
			'flipbook_catalog_main'
		);
	}

	/**
	 * Render the singular label field.
	 */
	public function render_singular_field(): void {
		$value = get_option( 'flipbook_catalog_singular', 'Catalog' );
		?>
		<input type="text" name="flipbook_catalog_singular" id="flipbook_catalog_singular"
			value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<p class="description"><?php esc_html_e( 'Used in admin UI labels, e.g. "Add New Magazine".', 'flipbook-catalog' ); ?></p>
		<?php
	}

	/**
	 * Render the plural label field.
	 */
	public function render_plural_field(): void {
		$value = get_option( 'flipbook_catalog_plural', 'Catalogs' );
		?>
		<input type="text" name="flipbook_catalog_plural" id="flipbook_catalog_plural"
			value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<p class="description"><?php esc_html_e( 'Used in admin UI labels, e.g. "All Magazines".', 'flipbook-catalog' ); ?></p>
		<?php
	}

	/**
	 * Render the URL slug field.
	 */
	public function render_slug_field(): void {
		$value = get_option( 'flipbook_catalog_slug', 'catalogs' );
		?>
		<input type="text" name="flipbook_catalog_slug" id="flipbook_catalog_slug"
			value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<p class="description"><?php esc_html_e( 'URL base for single posts and archives, e.g. /magazines/my-catalog/. Changing this will flush permalink settings automatically.', 'flipbook-catalog' ); ?></p>
		<?php
	}

	/**
	 * Flush rewrite rules when the slug option changes and set a notice transient.
	 */
	public function on_slug_change(): void {
		flush_rewrite_rules();
		set_transient( 'flipbook_catalog_slug_updated', true, 30 );
	}

	/**
	 * Show a one-time admin notice after a slug change.
	 */
	public function maybe_show_slug_updated_notice(): void {
		if ( ! get_transient( 'flipbook_catalog_slug_updated' ) ) {
			return;
		}

		delete_transient( 'flipbook_catalog_slug_updated' );
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Flipbook Catalog: URL slug updated and permalink settings flushed automatically.', 'flipbook-catalog' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'flipbook_catalog_settings' );
				do_settings_sections( 'flipbook-catalog-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
