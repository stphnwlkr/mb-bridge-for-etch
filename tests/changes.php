<?php
// Run through studio wp eval-file on the local test site.
function mbfe_assert_change( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
	echo 'PASS: ' . $message . "\n";
}
$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'MB Bridge refresh test' ) );
try {
	$first = mbfe_changes()['page'] ?? '';
	mbfe_assert_change( ! empty( $first ), 'Creation records a change token.' );
	wp_update_post( array( 'ID' => $id, 'post_title' => 'MB Bridge refresh test updated' ) );
	$second = mbfe_changes()['page'] ?? '';
	mbfe_assert_change( $first !== $second, 'Update changes the token.' );
	wp_trash_post( $id );
	mbfe_assert_change( $second !== ( mbfe_changes()['page'] ?? '' ), 'Trash changes the token.' );
	wp_set_current_user( 0 );
	$request = new WP_REST_Request( 'GET', '/mb-bridge-for-etch/v1/changes' );
	mbfe_assert_change( rest_do_request( $request )->get_status() === 401, 'Anonymous change-token requests are denied.' );
} finally {
	wp_trash_post( $id );
}
