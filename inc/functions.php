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

if ( ! function_exists( 'maybe_switch_url' ) ) :
    function maybe_switch_url( WP_User $user ) {
        $old_user = get_old_user();

        if ( $old_user && ( $old_user->ID === $user->ID ) ) {
            return switch_back_url( $old_user );
        } elseif ( current_user_can( 'switch_to_user', $user->ID ) ) {
            return switch_to_url( $user );
        } else {
            return false;
        }
    }
endif;

 /**
 * Validates the old user cookie and returns its user data.
 *
 * @return false|WP_User False if there's no old user cookie or it's invalid, WP_User object if it's present and valid.
 */

if ( ! function_exists( 'get_old_user' ) ) {
  
    function get_old_user() {
        $cookie = user_switching_get_olduser_cookie();
        if ( ! empty( $cookie ) ) {
            $old_user_id = wp_validate_auth_cookie( $cookie, 'logged_in' );

            if ( $old_user_id ) {
                return get_userdata( $old_user_id );
            }
        }
        return false;
    }
}

 /**
 * Returns the nonce-secured URL needed to switch to a given user ID.
 *
 * @param  WP_User $user The user to be switched to.
 * @return string The required URL.
 */
if ( ! function_exists( 'switch_to_url' ) ) {
    function switch_to_url( WP_User $user ) {
        return wp_nonce_url( add_query_arg( [
            'action' => 'switch_to_user',
            'user_id' => $user->ID,
            'nr' => 1,
        ], wp_login_url() ), "switch_to_user_{$user->ID}" );
    }
}

if ( ! function_exists( 'user_switching_get_olduser_cookie' ) ) {
    /**
     * Gets the value of the cookie containing the originating user.
     *
     * @return string|false The old user cookie, or boolean false if there isn't one.
     */
    function user_switching_get_olduser_cookie() {
        if ( isset( $_COOKIE[ USER_SWITCHER_OLDUSER_COOKIE ] ) ) {
            return wp_unslash( $_COOKIE[ USER_SWITCHER_OLDUSER_COOKIE ] );
        } else {
            return false;
        }
    }
}
