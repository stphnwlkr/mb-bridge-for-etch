<?php
/** A fields-only editor. Rendering, validation, storage and hooks belong to Meta Box. */
defined( 'ABSPATH' ) || exit;

function mbfe_post_boxes( WP_Post $post ): array {
	if ( ! function_exists( 'rwmb_get_registry' ) ) {
		return array();
	}
	return array_filter( rwmb_get_registry( 'meta_box' )->all(), static function ( $box ) use ( $post ) {
		return 'post' === $box->get_object_type()
			&& empty( $box->meta_box['type'] ) // Exclude block field groups.
			&& in_array( $post->post_type, (array) $box->post_types, true )
			&& $box->is_shown();
	} );
}

add_action( 'admin_menu', function () {
	$hook = add_submenu_page( '', __( 'Page fields', 'mb-bridge-for-etch' ), __( 'Page fields', 'mb-bridge-for-etch' ), 'edit_posts', 'mbfe-page-fields', 'mbfe_render_fields' );
	add_action( 'load-' . $hook, 'mbfe_load_fields' );
} );

function mbfe_load_fields(): void {
	// The URL identifies the post on both GET and POST. Never accept a different form target.
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	$post = get_post( $post_id );
	if ( ! $post || ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( esc_html__( 'You cannot edit fields for this item.', 'mb-bridge-for-etch' ), '', array( 'response' => 403 ) );
	}
	$GLOBALS['post'] = $post;
	$GLOBALS['post_ID'] = $post_id;
	$GLOBALS['post_type'] = $post->post_type;
	// Meta Box uses the standard post screen to enqueue field and extension assets.
	$screen = get_current_screen();
	$screen->base = 'post';
	$screen->post_type = $post->post_type;
	$boxes = mbfe_post_boxes( $post );
	$GLOBALS['mbfe_boxes'] = $boxes;
	foreach ( $boxes as $box ) {
		$box->set_object_id( $post_id );
	}
	if ( 'POST' === sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
		check_admin_referer( 'mbfe-save-' . $post_id, 'mbfe_nonce' );
		if ( ! function_exists( 'rwmb_request' ) ) {
			wp_die( esc_html__( 'Meta Box is not available.', 'mb-bridge-for-etch' ), '', array( 'response' => 409 ) );
		}
		// Validate the submitted set before any save. Groups added in another tab must not be cleared.
		$submitted = isset( $_POST['mbfe_groups'] ) && is_array( $_POST['mbfe_groups'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['mbfe_groups'] ) ) : array();
		$to_save = array();
		foreach ( $submitted as $id ) {
			$box = $boxes[ $id ] ?? null;
			if ( ! $box || ! $box->validate() ) {
				wp_die( esc_html__( 'Your field groups changed or this form expired. Reload the panel and try again.', 'mb-bridge-for-etch' ), '', array( 'response' => 409 ) );
			}
			$to_save[ $id ] = $box;
		}
		foreach ( $to_save as $box ) {
			$box->save_post( $post_id );
		}
		// Deliberately do not call wp_update_post: Etch owns the page content.
		wp_safe_redirect( add_query_arg( array( 'page' => 'mbfe-page-fields', 'post' => $post_id, 'saved' => '1' ), admin_url( 'admin.php' ) ) );
		exit;
	}
	add_action( 'admin_enqueue_scripts', function () use ( $boxes, $post_id ) {
		wp_enqueue_media( array( 'post' => $post_id ) );
		foreach ( $boxes as $box ) {
			$box->enqueue();
		}
		wp_enqueue_style( 'mbfe-fields', MBFE_URL . 'assets/fields.css', array(), MBFE_VERSION );
		wp_enqueue_script( 'mbfe-fields', MBFE_URL . 'assets/fields.js', array( 'jquery' ), MBFE_VERSION, true );
	}, 30 );
}

function mbfe_render_fields(): void {
	$post = $GLOBALS['post'];
	$boxes = $GLOBALS['mbfe_boxes'] ?? array();
	?>
	<div class="wrap mbfe-fields">
		<header class="mbfe-fields-heading">
			<span class="mbfe-eyebrow"><?php echo esc_html( get_post_type_object( $post->post_type )->labels->singular_name ); ?></span>
			<h1><?php echo esc_html( get_the_title( $post ) ?: __( 'Untitled', 'mb-bridge-for-etch' ) ); ?></h1>
			<p><?php esc_html_e( 'Update this item\'s Meta Box fields. Field values save separately from your Etch layout.', 'mb-bridge-for-etch' ); ?></p>
		</header>
		<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only success notice after nonce-verified POST redirect.
		if ( isset( $_GET['saved'] ) ) : ?>
			<div class="notice notice-success mbfe-saved" role="status"><p><?php esc_html_e( 'Field values saved.', 'mb-bridge-for-etch' ); ?></p></div>
		<?php endif; ?>
		<?php if ( ! $boxes ) : ?>
			<div class="mbfe-empty"><h2><?php esc_html_e( 'No field groups assigned', 'mb-bridge-for-etch' ); ?></h2><p><?php esc_html_e( 'Assign a Meta Box field group to this content type, then reopen Page fields.', 'mb-bridge-for-etch' ); ?></p>
			<?php if ( post_type_exists( 'meta-box' ) && current_user_can( get_post_type_object( 'meta-box' )->cap->edit_posts ) ) : ?>
				<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=meta-box' ) ); ?>"><?php esc_html_e( 'Manage field groups', 'mb-bridge-for-etch' ); ?></a>
			<?php endif; ?></div>
		<?php else : ?>
			<form id="mbfe-fields-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( add_query_arg( array( 'page' => 'mbfe-page-fields', 'post' => $post->ID ), admin_url( 'admin.php' ) ) ); ?>">
				<?php wp_nonce_field( 'mbfe-save-' . $post->ID, 'mbfe_nonce' ); ?>
				<input type="hidden" id="post_ID" name="post_ID" value="<?php echo esc_attr( $post->ID ); ?>">
				<input type="hidden" id="post_type" name="post_type" value="<?php echo esc_attr( $post->post_type ); ?>">
				<?php foreach ( $boxes as $box ) : ?>
					<section class="postbox mbfe-field-group" id="<?php echo esc_attr( $box->id ); ?>">
						<h2><?php echo esc_html( $box->title ); ?></h2>
						<div class="inside"><?php $box->show(); ?></div>
						<input type="hidden" name="mbfe_groups[]" value="<?php echo esc_attr( $box->id ); ?>">
					</section>
				<?php endforeach; ?>
				<div class="mbfe-save-row"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save field values', 'mb-bridge-for-etch' ); ?></button><span id="mbfe-save-status" role="status"><?php esc_html_e( 'All changes saved', 'mb-bridge-for-etch' ); ?></span></div>
			</form>
		<?php endif; ?>
	</div>
	<?php
}
