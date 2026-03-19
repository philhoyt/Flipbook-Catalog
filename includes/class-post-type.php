<?php
/**
 * Custom post type registration for the Flipbook Catalog plugin.
 *
 * @package Flipbook_Catalog
 */

// phpcs:disable WordPress.Files.FileName.InvalidClassFileName

defined( 'ABSPATH' ) || exit;

/**
 * Registers the flipbook_catalog custom post type and its block theme template.
 */
class Flipbook_Catalog_Post_Type {

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_cpt' ) );
		add_action( 'init', array( $this, 'register_block_template' ), 11 ); // Priority 11 ensures CPT is registered first.
		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_block_editor' ), 10, 2 );
	}

	/**
	 * Register the flipbook_catalog custom post type.
	 */
	public function register_cpt(): void {
		$singular = get_option( 'flipbook_catalog_singular', 'Catalog' );
		$plural   = get_option( 'flipbook_catalog_plural', 'Catalogs' );
		$slug     = get_option( 'flipbook_catalog_slug', 'catalogs' );

		register_post_type(
			'flipbook_catalog',
			array(
				'labels'       => array(
					'name'               => $plural,
					'singular_name'      => $singular,
					'add_new_item'       => sprintf(
						/* translators: %s: singular label */
						__( 'Add New %s', 'flipbook-catalog' ),
						$singular
					),
					'edit_item'          => sprintf(
						/* translators: %s: singular label */
						__( 'Edit %s', 'flipbook-catalog' ),
						$singular
					),
					'new_item'           => sprintf(
						/* translators: %s: singular label */
						__( 'New %s', 'flipbook-catalog' ),
						$singular
					),
					'view_item'          => sprintf(
						/* translators: %s: singular label */
						__( 'View %s', 'flipbook-catalog' ),
						$singular
					),
					'search_items'       => sprintf(
						/* translators: %s: plural label */
						__( 'Search %s', 'flipbook-catalog' ),
						$plural
					),
					'not_found'          => sprintf(
						/* translators: %s: plural label */
						__( 'No %s found', 'flipbook-catalog' ),
						$plural
					),
					'not_found_in_trash' => sprintf(
						/* translators: %s: plural label */
						__( 'No %s found in Trash', 'flipbook-catalog' ),
						$plural
					),
					'all_items'          => sprintf(
						/* translators: %s: plural label */
						__( 'All %s', 'flipbook-catalog' ),
						$plural
					),
					'menu_name'          => $plural,
				),
				'public'       => true,
				'show_in_rest' => true, // Required for core/post-meta block binding.
				'supports'     => array( 'title', 'thumbnail', 'excerpt' ), // No 'editor' — classic meta box handles PDF input.
				'menu_icon'    => 'dashicons-media-document',
				'has_archive'  => true,
				'rewrite'      => array( 'slug' => $slug ),
			)
		);
	}

	/**
	 * Register the block theme template for single flipbook_catalog posts.
	 *
	 * Requires WordPress 6.7+. On earlier versions the template will not be
	 * registered — users should add a template directly in their theme instead.
	 */
	public function register_block_template(): void {
		if ( ! function_exists( 'register_block_template' ) ) {
			return;
		}

		$template_path = FLIPBOOK_CATALOG_PATH . 'templates/single-flipbook_catalog.html';

		if ( ! file_exists( $template_path ) ) {
			return;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents( $template_path );

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
		register_block_template(
			'flipbook-catalog//single-flipbook_catalog',
			array(
				'title'       => __( 'Single Flipbook Catalog', 'flipbook-catalog' ),
				'description' => __( 'Template for single flipbook catalog posts.', 'flipbook-catalog' ),
				'content'     => $content,
			)
		);
	}

	/**
	 * Disable the block editor for the flipbook_catalog post type.
	 *
	 * @param bool   $enabled   Whether to use the block editor.
	 * @param string $post_type Post type slug.
	 * @return bool
	 */
	public function disable_block_editor( bool $enabled, string $post_type ): bool {
		if ( 'flipbook_catalog' === $post_type ) {
			return false;
		}

		return $enabled;
	}
}
