<?php
// Run through studio wp eval-file on a disposable/local site.
function mbfe_update_assert( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
	echo 'PASS: ' . $message . "\n";
}
$fixture = json_decode( file_get_contents( __DIR__ . '/../dist/update.json' ), true );
$calls = 0;
$reply = $fixture;
$mock = function ( $pre, $args, $url ) use ( &$calls, &$reply ) {
	if ( 'https://github.com/stphnwlkr/mb-bridge-for-etch/releases/latest/download/update.json' !== $url ) { return $pre; }
	++$calls;
	return is_wp_error( $reply ) ? $reply : array( 'response' => array( 'code' => 200 ), 'body' => wp_json_encode( $reply ) );
};
add_filter( 'pre_http_request', $mock, 10, 3 );
try {
	delete_site_transient( 'mbfe_release_metadata' );
	$data = mbfe_release_metadata();
	mbfe_update_assert( '0.1.0' === $data['version'], 'Valid release metadata accepted.' );
	mbfe_release_metadata();
	mbfe_update_assert( 1 === $calls, 'Successful checks are cached.' );
	$uri = array( 'UpdateURI' => 'https://github.com/stphnwlkr/mb-bridge-for-etch' );
	$file = plugin_basename( MBFE_DIR . 'mb-bridge-for-etch.php' );
	$update = apply_filters( 'update_plugins_github.com', false, $uri, $file, array() );
	mbfe_update_assert( '7.1' === $update['requires'] && '8.3' === $update['requires_php'] && '7.1.3' === $update['tested'], 'Update response includes compatibility requirements.' );
	mbfe_update_assert( false === apply_filters( 'update_plugins_github.com', false, $uri, 'other/plugin.php', array() ), 'Other GitHub plugins are untouched.' );
	$info = apply_filters( 'plugins_api', false, 'plugin_information', (object) array( 'slug' => 'mb-bridge-for-etch' ) );
	mbfe_update_assert( $info->download_link === $fixture['download_url'], 'Plugin details use the release ZIP.' );
	foreach ( array(
		array_replace( $fixture, array( 'download_url' => 'https://example.org/unsafe.zip' ) ),
		array_replace( $fixture, array( 'slug' => 'other' ) ),
		array_replace( $fixture, array( 'version' => '0.2.0-beta' ) ),
		array_replace( $fixture, array( 'requires_php' => array() ) ),
		new WP_Error( 'offline', 'Offline' ),
	) as $invalid ) {
		$reply = $invalid;
		delete_site_transient( 'mbfe_release_metadata' );
		mbfe_update_assert( array() === mbfe_release_metadata(), 'Invalid or unavailable release fails closed.' );
		$count = $calls;
		mbfe_release_metadata();
		mbfe_update_assert( $calls === $count, 'Failure is cached to avoid repeated requests.' );
	}
} finally {
	remove_filter( 'pre_http_request', $mock, 10 );
	delete_site_transient( 'mbfe_release_metadata' );
}
