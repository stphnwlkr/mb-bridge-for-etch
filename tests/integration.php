<?php
/** Run with studio wp eval-file /absolute/path/tests/integration.php on a test site. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) { exit; }
function mbfe_assert( $condition, string $message ): void {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
	WP_CLI::log( 'PASS: ' . $message );
}
$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
wp_set_current_user( (int) $admins[0] );
mbfe_assert( count( mbfe_sections() ) >= 3, 'Administrators can access installed management tools.' );
wp_set_current_user( 0 );
mbfe_assert( array() === mbfe_sections(), 'Unauthenticated users receive no management tools.' );
wp_set_current_user( (int) $admins[0] );
$post_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'Meta Box panel verification', 'post_content' => '<!-- wp:paragraph --><p>Preserve this layout.</p><!-- /wp:paragraph -->' ), true );
if ( is_wp_error( $post_id ) ) { throw new RuntimeException( $post_id->get_error_message() ); }
$post = get_post( $post_id );
$original = $post->post_content;
$registry = rwmb_get_registry( 'meta_box' );
$box = $registry->make( array( 'id' => 'mbfe-integration-test', 'title' => 'Integration test', 'post_types' => array( 'page' ), 'fields' => array(
	array( 'id' => 'mbfe_test_text', 'type' => 'text', 'name' => 'Test text' ),
	array( 'id' => 'mbfe_test_checkbox', 'type' => 'checkbox', 'name' => 'Test checkbox' ),
	array( 'id' => 'mbfe_test_clones', 'type' => 'text', 'name' => 'Test clones', 'clone' => true ),
) ) );
try {
	$eligible = mbfe_post_boxes( $post );
	mbfe_assert( isset( $eligible['mbfe-integration-test'] ), 'Page field groups are eligible for a page.' );
	mbfe_assert( ! isset( $eligible['media-fields'] ), 'Other content types are excluded.' );
	mbfe_assert( ! isset( $eligible['branch-of-service-fields'] ), 'Term field groups are excluded.' );
	rwmb_request()->set_post_data( array( 'nonce_mbfe-integration-test' => 'invalid', 'mbfe_test_text' => 'Should not save' ) );
	$box->save_post( $post_id );
	mbfe_assert( '' === get_post_meta( $post_id, 'mbfe_test_text', true ), 'Meta Box rejects an invalid group nonce.' );
	rwmb_request()->set_post_data( array( 'nonce_mbfe-integration-test' => wp_create_nonce( 'rwmb-save-mbfe-integration-test' ), 'mbfe_test_text' => 'Round trip', 'mbfe_test_checkbox' => 1, 'mbfe_test_clones' => array( 'First', 'Second' ) ) );
	$box->save_post( $post_id );
	mbfe_assert( 'Round trip' === get_post_meta( $post_id, 'mbfe_test_text', true ), 'Text values persist through native Meta Box saving.' );
	mbfe_assert( '1' === get_post_meta( $post_id, 'mbfe_test_checkbox', true ), 'Checkbox values persist.' );
	mbfe_assert( array( 'First', 'Second' ) === get_post_meta( $post_id, 'mbfe_test_clones', true ), 'Repeatable values persist.' );
	$box->saved = false;
	rwmb_request()->set_post_data( array( 'nonce_mbfe-integration-test' => wp_create_nonce( 'rwmb-save-mbfe-integration-test' ), 'mbfe_test_text' => '', 'mbfe_test_checkbox' => null, 'mbfe_test_clones' => null ) );
	$box->save_post( $post_id );
	mbfe_assert( ! get_post_meta( $post_id, 'mbfe_test_checkbox', true ), 'Unchecking a checkbox clears its saved value.' );
	mbfe_assert( '' === get_post_meta( $post_id, 'mbfe_test_text', true ), 'Clearing text persists.' );
	mbfe_assert( $original === get_post( $post_id )->post_content, 'Saving fields leaves page content unchanged.' );
} finally {
	wp_trash_post( $post_id );
}
WP_CLI::success( 'Meta Box integration checks passed; temporary page moved to Trash.' );
