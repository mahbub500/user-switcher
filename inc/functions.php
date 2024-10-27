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
function get_all_users_with_names_and_ids() {
    
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