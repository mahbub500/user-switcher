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

/**
 * Returns whether the current logged in user is being remembered in the form of a persistent browser cookie
 * (ie. they checked the 'Remember Me' check box when they logged in). This is used to persist the 'remember me'
 * value when the user switches to another user.
 *
 * @return bool Whether the current user is being 'remembered'.
 */
if ( ! function_exists( 'remember' ) ) {


    function remember() {
        /** This filter is documented in wp-includes/pluggable.php */
        $cookie_life = apply_filters( 'auth_cookie_expiration', 172800, get_current_user_id(), false );
        $current = wp_parse_auth_cookie( '', 'logged_in' );

        if ( ! $current ) {
            return false;
        }

        // Here we calculate the expiration length of the current auth cookie and compare it to the default expiration.
        // If it's greater than this, then we know the user checked 'Remember Me' when they logged in.
        return ( intval( $current['expiration'] ) - time() > $cookie_life );
    }
}

/**
 * Returns whether the current logged in user is being remembered in the form of a persistent browser cookie
 * (ie. they checked the 'Remember Me' check box when they logged in). This is used to persist the 'remember me'
 * value when the user switches to another user.
 *
 * @return bool Whether the current user is being 'remembered'.
 */
if ( ! function_exists( 'get_redirect' ) ) {


    /**
     * Fetches the URL to redirect to for a given user (used after switching).
     *
     * @param  WP_User $new_user Optional. The new user's WP_User object.
     * @param  WP_User $old_user Optional. The old user's WP_User object.
     * @return string The URL to redirect to.
     */
    function get_redirect( ?WP_User $new_user = null, ?WP_User $old_user = null ) {
        $redirect_to = '';
        $requested_redirect_to = '';
        $redirect_type = REDIRECT_TYPE_NONE;

        if ( ! empty( $_REQUEST['redirect_to'] ) ) {
            // URL
            $redirect_to = remove_query_args( wp_unslash( $_REQUEST['redirect_to'] ) );
            $requested_redirect_to = wp_unslash( $_REQUEST['redirect_to'] );
            $redirect_type = REDIRECT_TYPE_URL;
        } elseif ( ! empty( $_GET['redirect_to_post'] ) ) {
            // Post
            $post_id = absint( $_GET['redirect_to_post'] );
            $redirect_type = REDIRECT_TYPE_POST;

            if ( is_post_publicly_viewable( $post_id ) ) {
                $link = get_permalink( $post_id );

                if ( is_string( $link ) ) {
                    $redirect_to = $link;
                    $requested_redirect_to = $link;
                }
            }
        } elseif ( ! empty( $_GET['redirect_to_term'] ) ) {
            // Term
            $term = get_term( absint( $_GET['redirect_to_term'] ) );
            $redirect_type = REDIRECT_TYPE_TERM;

            if ( ( $term instanceof WP_Term ) && is_taxonomy_viewable( $term->taxonomy ) ) {
                $link = get_term_link( $term );

                if ( is_string( $link ) ) {
                    $redirect_to = $link;
                    $requested_redirect_to = $link;
                }
            }
        } elseif ( ! empty( $_GET['redirect_to_user'] ) ) {
            // User
            $user = get_userdata( absint( $_GET['redirect_to_user'] ) );
            $redirect_type = REDIRECT_TYPE_USER;

            if ( $user instanceof WP_User ) {
                $link = get_author_posts_url( $user->ID );

                if ( is_string( $link ) ) {
                    $redirect_to = $link;
                    $requested_redirect_to = $link;
                }
            }
        } elseif ( ! empty( $_GET['redirect_to_comment'] ) ) {
            // Comment
            $comment = get_comment( absint( $_GET['redirect_to_comment'] ) );
            $redirect_type = REDIRECT_TYPE_COMMENT;

            if ( $comment instanceof WP_Comment ) {
                if ( 'approved' === wp_get_comment_status( $comment ) ) {
                    $link = get_comment_link( $comment );

                    if ( is_string( $link ) ) {
                        $redirect_to = $link;
                        $requested_redirect_to = $link;
                    }
                } elseif ( is_post_publicly_viewable( (int) $comment->comment_post_ID ) ) {
                    $link = get_permalink( (int) $comment->comment_post_ID );

                    if ( is_string( $link ) ) {
                        $redirect_to = $link;
                        $requested_redirect_to = $link;
                    }
                }
            }
        }

        if ( ! $new_user ) {
            /** This filter is documented in wp-login.php */
            $redirect_to = apply_filters( 'logout_redirect', $redirect_to, $requested_redirect_to, $old_user );
        } else {
            /** This filter is documented in wp-login.php */
            $redirect_to = apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $new_user );
        }

        /**
         * Filters the redirect location after a user switches to another account or switches off.
         *
         * @since 1.7.0
         *
         * @param string       $redirect_to   The target redirect location, or an empty string if none is specified.
         * @param string|null  $redirect_type The redirect type, see the `user_switching::REDIRECT_*` constants.
         * @param WP_User|null $new_user      The user being switched to, or null if there is none.
         * @param WP_User|null $old_user      The user being switched from, or null if there is none.
         */
        return apply_filters( 'user_switching_redirect_to', $redirect_to, $redirect_type, $new_user, $old_user );
    }
}

