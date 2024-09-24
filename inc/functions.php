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

if ( ! function_exists( 'user_switching_set_olduser_cookie' ) ) {
    /**
     * Sets authorisation cookies containing the originating user information.
     *
     * @since 1.4.0 The `$token` parameter was added.
     *
     * @param int    $old_user_id The ID of the originating user, usually the current logged in user.
     * @param bool   $pop         Optional. Pop the latest user off the auth cookie, instead of appending the new one. Default false.
     * @param string $token       Optional. The old user's session token to store for later reuse. Default empty string.
     * @return void
     */
    function user_switching_set_olduser_cookie( $old_user_id, $pop = false, $token = '' ) {
        $secure_auth_cookie = user_switching::secure_auth_cookie();
        $secure_olduser_cookie = user_switching::secure_olduser_cookie();
        $expiration = time() + 172800; // 48 hours
        $auth_cookie = user_switching_get_auth_cookie();
        $olduser_cookie = wp_generate_auth_cookie( $old_user_id, $expiration, 'logged_in', $token );

        if ( $secure_auth_cookie ) {
            $auth_cookie_name = USER_SWITCHING_SECURE_COOKIE;
            $scheme = 'secure_auth';
        } else {
            $auth_cookie_name = USER_SWITCHING_COOKIE;
            $scheme = 'auth';
        }

        if ( $pop ) {
            array_pop( $auth_cookie );
        } else {
            array_push( $auth_cookie, wp_generate_auth_cookie( $old_user_id, $expiration, $scheme, $token ) );
        }

        $auth_cookie = wp_json_encode( $auth_cookie );

        if ( false === $auth_cookie ) {
            return;
        }

        /**
         * Fires immediately before the User Switching authentication cookie is set.
         *
         * @since 1.4.0
         *
         * @param string $auth_cookie JSON-encoded array of authentication cookie values.
         * @param int    $expiration  The time when the authentication cookie expires as a UNIX timestamp.
         * @param int    $old_user_id User ID.
         * @param string $scheme      Authentication scheme. Values include 'auth' or 'secure_auth'.
         * @param string $token       User's session token to use for the latest cookie.
         */
        do_action( 'set_user_switching_cookie', $auth_cookie, $expiration, $old_user_id, $scheme, $token );

        $scheme = 'logged_in';

        /**
         * Fires immediately before the User Switching old user cookie is set.
         *
         * @since 1.4.0
         *
         * @param string $olduser_cookie The old user cookie value.
         * @param int    $expiration     The time when the logged-in authentication cookie expires as a UNIX timestamp.
         * @param int    $old_user_id    User ID.
         * @param string $scheme         Authentication scheme. Values include 'auth' or 'secure_auth'.
         * @param string $token          User's session token to use for this cookie.
         */
        do_action( 'set_olduser_cookie', $olduser_cookie, $expiration, $old_user_id, $scheme, $token );

        /**
         * Allows preventing auth cookies from actually being sent to the client.
         *
         * @since 1.5.4
         *
         * @param bool $send Whether to send auth cookies to the client.
         */
        if ( ! apply_filters( 'user_switching_send_auth_cookies', true ) ) {
            return;
        }

        setcookie( $auth_cookie_name, $auth_cookie, $expiration, SITECOOKIEPATH, COOKIE_DOMAIN, $secure_auth_cookie, true );
        setcookie( USER_SWITCHING_OLDUSER_COOKIE, $olduser_cookie, $expiration, COOKIEPATH, COOKIE_DOMAIN, $secure_olduser_cookie, true );
    }
}

