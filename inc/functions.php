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

/**
 * Restore current user.
 *
 * @param (object) $input			The request/post object.
 **/
function us_restore_account( $input ) {
	// Let's make sure we can switch back without problem
	ob_start();
	ob_get_clean();

	us_set_cookie( 'user_switcher', null, -1 );

	if ( ! empty( $input->ajax ) ) {
		wp_send_json_success( array( 'ok' => true ) );
	} else {
		if ( ! empty( $input->return_url ) ) {
			wp_safe_redirect( $input->return_url );
		}
	}
}

/**
	 * Set or unset cookie.
	 *
	 * @param (string) $cookie_name				The name of the cookie. Cookiehash will be appended to the name.
	 * @param (string) $value					The value to store.
	 * @param (mixed) $time						The duraction the cookie will remain.
	 * @return null
	 **/
	function us_set_cookie( $cookie_name, $value, $time ) {
		$cookie_name .= '_' . COOKIEHASH;
		$secure = ( 'https' === parse_url( home_url(), PHP_URL_SCHEME ) );
		setcookie( $cookie_name, $value, $time, COOKIEPATH, COOKIE_DOMAIN, $secure );
	}