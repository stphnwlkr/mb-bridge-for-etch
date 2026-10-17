<?php
/** GitHub releases delivered through WordPress's native plugin update flow. */
defined( 'ABSPATH' ) || exit;

/** Read only release metadata from our public repository; cache failures briefly. */
function mbfe_release_metadata() {
	$cached = get_site_transient( 'mbfe_release_metadata' );
	if ( false !== $cached ) {
		return $cached;
	}
	$repository = 'https://github.com/stphnwlkr/mb-bridge-for-etch';
	$response = wp_safe_remote_get( $repository . '/releases/latest/download/update.json', array(
		'timeout' => 10,
		'user-agent' => 'MB-Bridge-for-Etch/' . MBFE_VERSION,
		'headers' => array( 'Accept' => 'application/json' ),
		'limit_response_size' => 65536,
	) );
	$data = ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response )
		? json_decode( wp_remote_retrieve_body( $response ), true ) : null;
	$valid = is_array( $data ) && 'mb-bridge-for-etch' === ( $data['slug'] ?? '' );
	foreach ( array( 'version', 'requires', 'requires_php', 'tested' ) as $key ) {
		$valid = $valid && isset( $data[ $key ] ) && is_string( $data[ $key ] )
			&& preg_match( '/^\d+\.\d+(?:\.\d+)?$/D', $data[ $key ] );
	}
	if ( $valid ) {
		$expected = $repository . '/releases/download/v' . $data['version'] . '/mb-bridge-for-etch-' . $data['version'] . '.zip';
		$valid = $expected === ( $data['download_url'] ?? '' );
	}
	if ( ! $valid ) {
		set_site_transient( 'mbfe_release_metadata', array(), 5 * MINUTE_IN_SECONDS );
		return array();
	}
	set_site_transient( 'mbfe_release_metadata', $data, HOUR_IN_SECONDS );
	return $data;
}

add_filter( 'update_plugins_github.com', function ( $update, $plugin_data, $plugin_file ) {
	if ( plugin_basename( MBFE_DIR . 'mb-bridge-for-etch.php' ) !== $plugin_file ) {
		return $update;
	}
	$data = mbfe_release_metadata();
	if ( empty( $data ) ) {
		return $update;
	}
	return array(
		'id' => $plugin_data['UpdateURI'],
		'slug' => 'mb-bridge-for-etch',
		'version' => $data['version'],
		'url' => 'https://github.com/stphnwlkr/mb-bridge-for-etch',
		'package' => $data['download_url'],
		'requires' => $data['requires'],
		'requires_php' => $data['requires_php'],
		'tested' => $data['tested'],
	);
}, 10, 3 );

add_filter( 'plugins_api', function ( $result, $action, $args ) {
	if ( 'plugin_information' !== $action || 'mb-bridge-for-etch' !== ( $args->slug ?? '' ) ) {
		return $result;
	}
	$data = mbfe_release_metadata();
	if ( empty( $data ) ) {
		return $result;
	}
	$sections = array();
	foreach ( array( 'description', 'changelog' ) as $key ) {
		$sections[ $key ] = wpautop( esc_html( is_string( $data['sections'][ $key ] ?? null ) ? $data['sections'][ $key ] : '' ) );
	}
	return (object) array(
		'name' => 'MB Bridge for Etch', 'slug' => 'mb-bridge-for-etch',
		'version' => $data['version'], 'author' => 'Stephen Walker',
		'homepage' => 'https://github.com/stphnwlkr/mb-bridge-for-etch',
		'requires' => $data['requires'], 'requires_php' => $data['requires_php'],
		'tested' => $data['tested'], 'download_link' => $data['download_url'],
		'sections' => $sections,
	);
}, 10, 3 );

// Let Dashboard > Updates > Check again bypass the bridge's metadata cache too.
add_action( 'load-update-core.php', function () {
	if ( current_user_can( 'update_plugins' ) ) {
		delete_site_transient( 'mbfe_release_metadata' );
	}
} );
