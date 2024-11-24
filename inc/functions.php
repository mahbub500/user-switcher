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

if( ! function_exists( 'get_all_users_with_names_and_ids' ) ) :
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
endif;

/**
 * Set or unset cookie.
 *
 * @param (string) $cookie_name             The name of the cookie. Cookiehash will be appended to the name.
 * @param (string) $value                   The value to store.
 * @param (mixed) $time                     The duraction the cookie will remain.
 * @return null
 **/

if ( ! function_exists( 'stu_us_set_cookie' ) ) {
    function stu_us_set_cookie( $cookie_name, $value, $time ) {
        $secure = ( 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME ) );
        setcookie( $cookie_name, $value, $time, COOKIEPATH, COOKIE_DOMAIN, $secure );
    }
}

/**
 * Set or unset cookie.
 *
 * @param (string) $cookie_name             The name of the cookie. Cookiehash will be appended to the name.
 * @param (string) $value                   The value to store.
 * @param (mixed) $time                     The duraction the cookie will remain.
 * @return null
 **/
if ( ! function_exists( 'stu_remove_cookie' ) ) {
    function stu_remove_cookie( $cookie_name ) {
        if ( empty( $cookie_name ) ) {
            return; // Exit if no cookie name is provided
        }

        $secure = ( 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME ) );
        
        // Clear the specified cookie by setting it to an empty value and a past expiration time
        setcookie( $cookie_name, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, $secure, true );
    }
}


/**
 * Get user id of new user & old user
 */

if ( ! function_exists( 'stu_user_switch_data' ) ) {
    function stu_user_switch_data( $type ) {
        if ( isset( $_COOKIE['user_switch_data'] ) ) {
            $switch_data_json   = sanitize_text_field ( wp_unslash( $_COOKIE['user_switch_data'] ));
            $switch_data        = json_decode( $switch_data_json, true );
            
            $switch_from = $switch_data['switch_from'] ?? null;
            $switch_to_user = $switch_data['switch_to_user'] ?? null;

            if ( $type === 'switch_from' ) {
                return $switch_from; 
            } elseif ( $type === 'switch_to_user' ) {
                return $switch_to_user; 
            }
        }

        return null;
    }
}

/**
 * Get user name by user id
 */

if ( ! function_exists( 'get_username_by_id' ) ) {
    function get_username_by_id( $user_id ) {
        $user_data = get_userdata($user_id);
        
        if ($user_data) {
            return $user_data->user_login; 
        }

        return null;
    }
}

/**
 * Get encrypted log in url
 */

if ( ! function_exists( 'get_encrypted_login_url' ) ) {
    function get_encrypted_login_url( $user_id ) {
        $user_data = get_userdata( $user_id );

        if ( !$user_data ) {
            error_log('User data not found for user ID: ' . $user_id);
            return null;
        }

        
        $data_to_encrypt = $user_data->user_email;
        $ncrypt = new \mukto90\Ncrypt;
        $encrypted_data = $ncrypt->encrypt($data_to_encrypt);

        $login_url = add_query_arg(['data' => $encrypted_data], home_url());
       

        return $login_url;

       
    }
}

