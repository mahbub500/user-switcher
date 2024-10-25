<?php
if( ! function_exists( 'get_plugin_data' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

/**
 * Gets the site's base URL
 * 
 * @uses get_bloginfo()
 * 
 * @return string $url the site URL
 */
if( ! function_exists( 'us_site_url' ) ) :
function us_site_url() {
	$url = get_bloginfo( 'url' );

	return $url;
}
endif;

/**
	 * Validate and retrieve server $_REQUEST
	 *
	 * @return (array) $array					An array of request if successful otherwise false.
	 **/
	function us_get_request() {
		$request = $_REQUEST;

		if ( ! empty( $request['nonce'] ) && wp_verify_nonce( $request['nonce'], 'user_switcher_nonce' ) ) {

			$request = json_decode( file_get_contents( 'php://input' ) );
			return $request;
		}
		return false;
	}