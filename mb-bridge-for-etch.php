<?php
/**
 * Plugin Name: MB Bridge for Etch
 * Description: Edit Meta Box field values and manage field groups, post types, taxonomies, relationships, and more from an integrated panel in the Etch builder. Requires Etch and Meta Box; management tools depend on installed Meta Box extensions.
 * Plugin URI: https://github.com/stphnwlkr/mb-bridge-for-etch
 * Update URI: https://github.com/stphnwlkr/mb-bridge-for-etch
 * Version: 0.1.0
 * Requires at least: 7.1
 * Requires PHP: 8.3
 * Author: Stephen Walker
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mb-bridge-for-etch
 */

defined( 'ABSPATH' ) || exit;

define( 'MBFE_VERSION', '0.1.0' );
define( 'MBFE_DIR', plugin_dir_path( __FILE__ ) );
define( 'MBFE_URL', plugin_dir_url( __FILE__ ) );

require_once MBFE_DIR . 'includes/page-fields.php';
require_once MBFE_DIR . 'includes/changes.php';
require_once MBFE_DIR . 'includes/updates.php';

/** Only expose tools that the installed extensions and current role provide. */
function mbfe_sections(): array {
	$sections = array();
	$types = array(
		'meta-box'         => array( __( 'Field groups', 'mb-bridge-for-etch' ), __( 'Create and manage your custom fields.', 'mb-bridge-for-etch' ), 'fields' ),
		'mb-post-type'     => array( __( 'Post types', 'mb-bridge-for-etch' ), __( 'Manage the content types on your site.', 'mb-bridge-for-etch' ), 'posts' ),
		'mb-taxonomy'      => array( __( 'Taxonomies', 'mb-bridge-for-etch' ), __( 'Organize content with custom taxonomies.', 'mb-bridge-for-etch' ), 'taxonomy' ),
		'mb-relationship'  => array( __( 'Relationships', 'mb-bridge-for-etch' ), __( 'Connect posts, terms, and users.', 'mb-bridge-for-etch' ), 'relationships' ),
		'mb-settings-page' => array( __( 'Settings pages', 'mb-bridge-for-etch' ), __( 'Manage custom settings pages.', 'mb-bridge-for-etch' ), 'settings' ),
		'mb-model'         => array( __( 'Custom models', 'mb-bridge-for-etch' ), __( 'Manage models stored in custom tables.', 'mb-bridge-for-etch' ), 'models' ),
		'mb-views'         => array( __( 'Views', 'mb-bridge-for-etch' ), __( 'Manage Meta Box views and templates.', 'mb-bridge-for-etch' ), 'views' ),
	);
	foreach ( $types as $type => $details ) {
		$object = get_post_type_object( $type );
		if ( ! $object || ! $object->show_ui || ! current_user_can( $object->cap->edit_posts ) ) {
			continue;
		}
		$sections[] = array(
			'id' => $type, 'label' => $details[0], 'description' => $details[1], 'icon' => $details[2],
			'url' => admin_url( 'edit.php?post_type=' . $type ),
		);
	}
	return $sections;
}

add_action( 'wp_enqueue_scripts', function () {
	// Read-only builder context, not an action or authorization check.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only asset selection; no state change.
	if ( is_admin() || ! isset( $_GET['etch'] ) || 'magic' !== $_GET['etch'] || ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	wp_enqueue_style( 'mbfe-panel', MBFE_URL . 'assets/panel.css', array(), (string) filemtime( MBFE_DIR . 'assets/panel.css' ) );
	wp_enqueue_script( 'mbfe-panel', MBFE_URL . 'assets/panel.js', array(), (string) filemtime( MBFE_DIR . 'assets/panel.js' ), true );
	wp_enqueue_script( 'mbfe-sync', MBFE_URL . 'assets/sync.js', array( 'mbfe-panel' ), (string) filemtime( MBFE_DIR . 'assets/sync.js' ), true );
	wp_add_inline_script( 'mbfe-panel', 'window.mbfeConfig = ' . wp_json_encode( array(
		'available' => function_exists( 'rwmb_get_registry' ),
		'changes' => mbfe_changes(),
		'changesUrl' => rest_url( 'mb-bridge-for-etch/v1/changes' ),
		'restNonce' => wp_create_nonce( 'wp_rest' ),
		'sections' => mbfe_sections(),
		'fieldsUrl' => admin_url( 'admin.php?page=mbfe-page-fields' ),
		'frameCss' => MBFE_URL . 'assets/frame.css?v=' . filemtime( MBFE_DIR . 'assets/frame.css' ),
		'pluginsUrl' => current_user_can( 'activate_plugins' ) ? admin_url( 'plugins.php' ) : '',
	) ) . ';', 'before' );
} );
