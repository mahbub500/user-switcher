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
        $secure_auth_cookie = secure_auth_cookie();
        $secure_olduser_cookie = secure_olduser_cookie();
        $expiration = time() + 172800; // 48 hours
        $auth_cookie = user_switching_get_auth_cookie();
        $olduser_cookie = wp_generate_auth_cookie( $old_user_id, $expiration, 'logged_in', $token );

        if ( $secure_auth_cookie ) {
            $auth_cookie_name = USER_SWITCHER_SECURE_COOKIE;
            $scheme = 'secure_auth';
        } else {
            $auth_cookie_name = USER_SWITCHER_COOKIE;
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
        setcookie( USER_SWITCHER_OLDUSER_COOKIE, $olduser_cookie, $expiration, COOKIEPATH, COOKIE_DOMAIN, $secure_olduser_cookie, true );
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

/**
 * Returns the nonce-secured URL needed to switch back to the originating user.
 *
 * @param  WP_User $user The old user.
 * @return string        The required URL.
 */
function switch_back_url( WP_User $user ) {
    return wp_nonce_url( add_query_arg( [
        'action' => 'switch_to_olduser',
        'nr' => 1,
    ], wp_login_url() ), "switch_to_olduser_{$user->ID}" );
}

if ( ! function_exists( 'switch_to_user' ) ) {
    /**
     * Switches the current logged in user to the specified user.
     *
     * @param  int  $user_id      The ID of the user to switch to.
     * @param  bool $remember     Optional. Whether to 'remember' the user in the form of a persistent browser cookie. Default false.
     * @param  bool $set_old_user Optional. Whether to set the old user cookie. Default true.
     * @return false|WP_User WP_User object on success, false on failure.
     */
    function switch_to_user( $user_id, $remember = false, $set_old_user = true ) {
        $user = get_userdata( $user_id );

        if ( ! $user ) {
            return false;
        }

        $old_user_id = ( is_user_logged_in() ) ? get_current_user_id() : false;
        $old_token = wp_get_session_token();
        $auth_cookies = user_switching_get_auth_cookie();
        $auth_cookie = end( $auth_cookies );
        $cookie_parts = $auth_cookie ? wp_parse_auth_cookie( $auth_cookie ) : false;

        if ( $set_old_user && $old_user_id ) {
            // Switching to another user
            $new_token = '';
            user_switching_set_olduser_cookie( $old_user_id, false, $old_token );
        } else {
            // Switching back, either after being switched off or after being switched to another user
            $new_token = $cookie_parts['token'] ?? '';
            user_switching_clear_olduser_cookie( false );
        }

        /**
         * Attaches the original user ID and session token to the new session when a user switches to another user.
         *
         * @param array<string, mixed> $session Array of extra data.
         * @return array<string, mixed> Array of extra data.
         */
        $session_filter = function ( array $session ) use ( $old_user_id, $old_token ) {
            $session['switched_from_id'] = $old_user_id;
            $session['switched_from_session'] = $old_token;
            return $session;
        };

        add_filter( 'attach_session_information', $session_filter, 99 );

        wp_clear_auth_cookie();
        wp_set_auth_cookie( $user_id, $remember, '', $new_token );
        wp_set_current_user( $user_id );

        remove_filter( 'attach_session_information', $session_filter, 99 );

        if ( $set_old_user && $old_user_id ) {
            /**
             * Fires when a user switches to another user account.
             *
             * @since 0.6.0
             * @since 1.4.0 The `$new_token` and `$old_token` parameters were added.
             *
             * @param int    $user_id     The ID of the user being switched to.
             * @param int    $old_user_id The ID of the user being switched from.
             * @param string $new_token   The token of the session of the user being switched to. Can be an empty string
             *                            or a token for a session that may or may not still be valid.
             * @param string $old_token   The token of the session of the user being switched from.
             */
            do_action( 'switch_to_user', $user_id, $old_user_id, $new_token, $old_token );
        } else {
            /**
             * Fires when a user switches back to their originating account.
             *
             * @since 0.6.0
             * @since 1.4.0 The `$new_token` and `$old_token` parameters were added.
             *
             * @param int       $user_id     The ID of the user being switched back to.
             * @param int|false $old_user_id The ID of the user being switched from, or false if the user is switching back
             *                               after having been switched off.
             * @param string    $new_token   The token of the session of the user being switched to. Can be an empty string
             *                               or a token for a session that may or may not still be valid.
             * @param string    $old_token   The token of the session of the user being switched from.
             */
            do_action( 'switch_back_user', $user_id, $old_user_id, $new_token, $old_token );
        }

        if ( $old_token && $old_user_id && ! $set_old_user ) {
            // When switching back, destroy the session for the old user
            $manager = WP_Session_Tokens::get_instance( $old_user_id );
            $manager->destroy( $old_token );
        }

        return $user;
    }
}

if ( ! function_exists( 'user_switching_get_auth_cookie' ) ) {
    /**
     * Gets the value of the auth cookie containing the list of originating users.
     *
     * @return array<int,string> Array of originating user authentication cookie values. Empty array if there are none.
     */
    function user_switching_get_auth_cookie() {
        if ( secure_auth_cookie() ) {
            $auth_cookie_name = USER_SWITCHER_SECURE_COOKIE;
        } else {
            $auth_cookie_name = USER_SWITCHER_COOKIE;
        }

        if ( isset( $_COOKIE[ $auth_cookie_name ] ) && is_string( $_COOKIE[ $auth_cookie_name ] ) ) {
            $cookie = json_decode( wp_unslash( $_COOKIE[ $auth_cookie_name ] ) );
        }
        if ( ! isset( $cookie ) || ! is_array( $cookie ) ) {
            $cookie = [];
        }
        return $cookie;
    }
}

/**
 * Returns whether User Switching's equivalent of the 'auth' cookie should be secure.
 *
 * This is used to determine whether to set a secure auth cookie.
 *
 * @return bool Whether the auth cookie should be secure.
 */
function secure_auth_cookie() {
    return ( is_ssl() && ( 'https' === wp_parse_url( wp_login_url(), PHP_URL_SCHEME ) ) );
}

/**
 * Returns whether User Switching's equivalent of the 'logged_in' cookie should be secure.
 *
 * This is used to set the 'secure' flag on the old user cookie, for enhanced security.
 *
 * @link https://core.trac.wordpress.org/ticket/15330
 *
 * @return bool Should the old user cookie be secure?
 */
function secure_olduser_cookie() {
    return ( is_ssl() && ( 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME ) ) );
}