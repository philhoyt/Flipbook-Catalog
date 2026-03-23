<?php
/**
 * Post meta registration, meta box UI, and save handler for Flipbook Catalog.
 *
 * @package Flipbook_Catalog
 */

// phpcs:disable WordPress.Files.FileName.InvalidClassFileName

defined( 'ABSPATH' ) || exit;

/**
 * Handles PDF meta fields: registration, classic meta box, and save hook.
 */
class Flipbook_Catalog_Meta {

	/**
	 * Whether publish was blocked due to a missing PDF.
	 *
	 * @var bool
	 */
	private bool $publish_prevented = false;

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_meta_fields' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box_ui' ) );
		add_action( 'save_post_flipbook_catalog', array( $this, 'save_meta' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_meta_box_assets' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'prevent_publish_without_pdf' ) );
		add_filter( 'redirect_post_location', array( $this, 'add_no_pdf_query_arg' ) );
		add_action( 'admin_notices', array( $this, 'show_no_pdf_notice' ) );
	}

	/**
	 * Register post meta keys for the flipbook_catalog post type.
	 *
	 * Both keys require show_in_rest: true for core/post-meta block binding to resolve
	 * the PDF URL at render time.
	 */
	public function register_meta_fields(): void {
		register_post_meta(
			'flipbook_catalog',
			'flipbook_catalog_pdf_url',
			array(
				'type'          => 'string',
				'single'        => true,
				'show_in_rest'  => true, // Required for core/post-meta block binding.
				'auth_callback' => fn() => current_user_can( 'edit_posts' ),
			)
		);

		register_post_meta(
			'flipbook_catalog',
			'flipbook_catalog_pdf_id',
			array(
				'type'          => 'integer',
				'single'        => true,
				'show_in_rest'  => true,
				'auth_callback' => fn() => current_user_can( 'edit_posts' ),
			)
		);
	}

	/**
	 * Register the PDF meta box on the flipbook_catalog edit screen.
	 */
	public function add_meta_box_ui(): void {
		add_meta_box(
			'flipbook_catalog_pdf',
			__( 'PDF File', 'flipbook-catalog' ),
			array( $this, 'render_meta_box' ),
			'flipbook_catalog',
			'normal',
			'high'
		);
	}

	/**
	 * Render the PDF meta box HTML.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function render_meta_box( WP_Post $post ): void {
		$pdf_url = get_post_meta( $post->ID, 'flipbook_catalog_pdf_url', true );
		$pdf_id  = get_post_meta( $post->ID, 'flipbook_catalog_pdf_id', true );

		wp_nonce_field( 'flipbook_catalog_save_pdf', 'flipbook_catalog_pdf_nonce' );
		?>
		<div style="padding: 8px 0;">
			<div id="flipbook-catalog-pdf-display" style="margin-bottom: 8px; font-style: <?php echo $pdf_url ? 'normal' : 'italic'; ?>; color: <?php echo $pdf_url ? 'inherit' : '#757575'; ?>;">
				<?php
				if ( $pdf_url ) {
					echo esc_html( basename( $pdf_url ) );
				} else {
					esc_html_e( 'No PDF selected.', 'flipbook-catalog' );
				}
				?>
			</div>
			<p style="margin: 0 0 8px; color: #646970; font-size: 12px;">
				<span style="color: #b32d2e;">*</span>
				<?php esc_html_e( 'Required before publishing.', 'flipbook-catalog' ); ?>
			</p>

			<input type="hidden" id="flipbook_catalog_pdf_url" name="flipbook_catalog_pdf_url"
				value="<?php echo esc_attr( $pdf_url ? $pdf_url : '' ); ?>" />
			<input type="hidden" id="flipbook_catalog_pdf_id" name="flipbook_catalog_pdf_id"
				value="<?php echo esc_attr( $pdf_id ? $pdf_id : '' ); ?>" />

			<button type="button" id="flipbook-catalog-upload-btn" class="button">
				<?php esc_html_e( 'Upload / Select PDF', 'flipbook-catalog' ); ?>
			</button>

			<a href="#" id="flipbook-catalog-remove-pdf"
				style="margin-left: 8px; color: #b32d2e;<?php echo $pdf_url ? '' : ' display:none;'; ?>">
				<?php esc_html_e( 'Remove', 'flipbook-catalog' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Enqueue the media library and inline JS for the meta box on the edit screen.
	 */
	public function enqueue_meta_box_assets(): void {
		$screen = get_current_screen();

		if ( ! $screen || 'flipbook_catalog' !== $screen->post_type || 'post' !== $screen->base ) {
			return;
		}

		wp_enqueue_media();

		$script = <<<'JS'
( function() {
	var uploadBtn   = document.getElementById( 'flipbook-catalog-upload-btn' );
	var removeLink  = document.getElementById( 'flipbook-catalog-remove-pdf' );
	var urlInput    = document.getElementById( 'flipbook_catalog_pdf_url' );
	var idInput     = document.getElementById( 'flipbook_catalog_pdf_id' );
	var displayArea = document.getElementById( 'flipbook-catalog-pdf-display' );

	if ( ! uploadBtn ) {
		return;
	}

	var frame;

	uploadBtn.addEventListener( 'click', function( e ) {
		e.preventDefault();

		if ( frame ) {
			frame.open();
			return;
		}

		frame = wp.media( {
			title:    'Select PDF',
			library:  { type: 'application/pdf' },
			multiple: false,
			button:   { text: 'Use this PDF' }
		} );

		frame.on( 'select', function() {
			var attachment = frame.state().get( 'selection' ).first().toJSON();

			urlInput.value    = attachment.url;
			idInput.value     = attachment.id;
			displayArea.textContent = attachment.filename || attachment.url.split( '/' ).pop();
			displayArea.style.fontStyle = 'normal';
			displayArea.style.color     = 'inherit';

			if ( removeLink ) {
				removeLink.style.display = '';
			}
		} );

		frame.open();
	} );

	if ( removeLink ) {
		removeLink.addEventListener( 'click', function( e ) {
			e.preventDefault();

			urlInput.value          = '';
			idInput.value           = '';
			displayArea.textContent = 'No PDF selected.';
			displayArea.style.fontStyle = 'italic';
			displayArea.style.color     = '#757575';
			removeLink.style.display    = 'none';
		} );
	}
} )();
JS;

		wp_add_inline_script( 'media-editor', $script );
	}

	/**
	 * Revert post status to draft when publishing without a PDF file.
	 *
	 * @param array $data Sanitized post data about to be inserted.
	 * @return array
	 */
	public function prevent_publish_without_pdf( array $data ): array {
		if ( 'flipbook_catalog' !== $data['post_type'] || 'publish' !== $data['post_status'] ) {
			return $data;
		}

		// Only act when our meta box form was submitted.
		if (
			! isset( $_POST['flipbook_catalog_pdf_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['flipbook_catalog_pdf_nonce'] ), 'flipbook_catalog_save_pdf' )
		) {
			return $data;
		}

		$pdf_url = isset( $_POST['flipbook_catalog_pdf_url'] )
			? esc_url_raw( wp_unslash( $_POST['flipbook_catalog_pdf_url'] ) )
			: '';

		if ( empty( $pdf_url ) ) {
			$data['post_status']     = 'draft';
			$this->publish_prevented = true;
		}

		return $data;
	}

	/**
	 * Append an error query arg to the redirect URL when publish was blocked.
	 *
	 * @param string $location The redirect URL after saving.
	 * @return string
	 */
	public function add_no_pdf_query_arg( string $location ): string {
		if ( $this->publish_prevented ) {
			$location = add_query_arg( 'flipbook_catalog_error', 'no_pdf', $location );
		}
		return $location;
	}

	/**
	 * Show an admin error notice when publishing was blocked due to a missing PDF.
	 */
	public function show_no_pdf_notice(): void {
		$screen = get_current_screen();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display query arg set by this plugin.
		$error = isset( $_GET['flipbook_catalog_error'] ) ? sanitize_key( $_GET['flipbook_catalog_error'] ) : '';

		if ( ! $screen || 'flipbook_catalog' !== $screen->post_type || 'no_pdf' !== $error ) {
			return;
		}

		echo '<div class="notice notice-error"><p>';
		esc_html_e( 'A PDF file is required before publishing. Please upload or select a PDF.', 'flipbook-catalog' );
		echo '</p></div>';
	}

	/**
	 * Save the PDF meta values when the post is saved.
	 *
	 * @param int $post_id The post ID being saved.
	 */
	public function save_meta( int $post_id ): void {
		// Use constant check — more reliable than wp_doing_autosave() during auto-draft creation.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if (
			! isset( $_POST['flipbook_catalog_pdf_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['flipbook_catalog_pdf_nonce'] ), 'flipbook_catalog_save_pdf' )
		) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$pdf_url = isset( $_POST['flipbook_catalog_pdf_url'] )
			? esc_url_raw( wp_unslash( $_POST['flipbook_catalog_pdf_url'] ) )
			: '';

		$pdf_id = isset( $_POST['flipbook_catalog_pdf_id'] )
			? absint( $_POST['flipbook_catalog_pdf_id'] )
			: 0;

		if ( ! empty( $pdf_url ) ) {
			update_post_meta( $post_id, 'flipbook_catalog_pdf_url', $pdf_url );
			update_post_meta( $post_id, 'flipbook_catalog_pdf_id', $pdf_id );
		} else {
			delete_post_meta( $post_id, 'flipbook_catalog_pdf_url' );
			delete_post_meta( $post_id, 'flipbook_catalog_pdf_id' );
		}
	}
}
