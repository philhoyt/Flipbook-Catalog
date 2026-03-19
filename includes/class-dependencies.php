<?php
/**
 * Dependency checker for the Flipbook Catalog plugin.
 *
 * @package Flipbook_Catalog
 */

// phpcs:disable WordPress.Files.FileName.InvalidClassFileName

defined( 'ABSPATH' ) || exit;

/**
 * Checks that the ph/flipbook-block block is installed and active.
 * Shows a persistent admin notice when the dependency is missing.
 */
class Flipbook_Catalog_Dependencies {

	/**
	 * Whether the flipbook-block dependency is missing.
	 *
	 * @var bool
	 */
	private bool $dependency_missing = false;

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'admin_init', array( $this, 'check_dependency' ) );
		add_action( 'admin_notices', array( $this, 'maybe_show_notice' ) );
	}

	/**
	 * Check whether the ph/flipbook-block block is registered.
	 */
	public function check_dependency(): void {
		$block = WP_Block_Type_Registry::get_instance()->get_registered( 'ph/flipbook-block' );

		if ( null === $block ) {
			$this->dependency_missing = true;
		}
	}

	/**
	 * Output an admin notice when the dependency is missing.
	 */
	public function maybe_show_notice(): void {
		// Run the check on admin_notices too, since check_dependency runs on admin_init
		// which fires before admin_notices on the same request.
		if ( ! $this->dependency_missing ) {
			$block = WP_Block_Type_Registry::get_instance()->get_registered( 'ph/flipbook-block' );
			if ( null !== $block ) {
				return;
			}
		}

		?>
		<div class="notice notice-error">
			<p>
				<?php
				printf(
					/* translators: %s: link to the Flipbook Block GitHub repository */
					esc_html__( 'Flipbook Catalog requires the Flipbook Block plugin to be installed and active. %s', 'flipbook-catalog' ),
					'<a href="https://github.com/philhoyt/flipbook-block" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Install Flipbook Block', 'flipbook-catalog' ) . '</a>'
				);
				?>
			</p>
		</div>
		<?php
	}
}
