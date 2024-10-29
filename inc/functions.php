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
 * Get all users with their names and IDs.
 *
 * @return array Array of users with 'ID' and 'display_name'.
 */
public function get_all_users_with_names_and_ids() {
    
    $users 		= get_users();
    $user_data 	= array();
    
    foreach ($users as $user) {
        $user_data[] = array(
            'ID' => $user->ID,
            'display_name' => $user->display_name,
        );
    }
    
    return $user_data;
}

/**
 * Set or unset cookie.
 *
 * @param (string) $cookie_name             The name of the cookie. Cookiehash will be appended to the name.
 * @param (string) $value                   The value to store.
 * @param (mixed) $time                     The duraction the cookie will remain.
 * @return null
 **/

if ( ! function_exists( 'us_set_cookie' ) ) {
    function us_set_cookie( $cookie_name, $value, $time ) {
        $cookie_name .= '_' . COOKIEHASH;
        $secure = ( 'https' === parse_url( home_url(), PHP_URL_SCHEME ) );
        setcookie( $cookie_name, $value, $time, COOKIEPATH, COOKIE_DOMAIN, $secure );
    }
}
