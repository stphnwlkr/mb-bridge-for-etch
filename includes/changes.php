<?php
/** Change tokens only. Content remains in WordPress and Meta Box. */
defined( 'ABSPATH' ) || exit;

function mbfe_changes(): array {
	return (array) get_option( 'mbfe_changes', array() );
}

function mbfe_record_change( int $post_id, ?WP_Post $post = null ): void {
	$post = $post ?? get_post( $post_id );
	if ( ! $post || wp_is_post_revision( $post_id ) || 'auto-draft' === $post->post_status ) {
		return;
	}
	$type = get_post_type_object( $post->post_type );
	if ( ! $type || ! $type->show_ui ) {
		return;
	}
	$key = str_starts_with( $post->post_type, 'mb-' ) || 'meta-box' === $post->post_type ? 'schema' : $post->post_type;
	$changes = mbfe_changes();
	$changes[ $key ] = wp_generate_uuid4();
	update_option( 'mbfe_changes', $changes, false );
}

add_action( 'save_post', 'mbfe_record_change', 100, 2 );
add_action( 'deleted_post', 'mbfe_record_change', 10, 2 );
add_action( 'rwmb_after_save_post', 'mbfe_record_change' );

add_action( 'rest_api_init', function () {
	register_rest_route( 'mb-bridge-for-etch/v1', '/changes', array(
		'methods' => 'GET',
		'permission_callback' => static function () { return current_user_can( 'edit_posts' ); },
		'callback' => static function () {
			$response = new WP_REST_Response( mbfe_changes() );
			$response->header( 'Cache-Control', 'no-store' );
			return $response;
		},
	) );
} );
