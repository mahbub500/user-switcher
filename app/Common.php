<?php
namespace Codexpert\User_Switcher\App;

use Codexpert\Plugin\Base;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Common
 * @author Codexpert <hi@codexpert.io>
 */
class Common extends Base {

	public $plugin;
	
	public $slug;

	public $name;

	public $version;

	/**
	 * Constructor function
	 */
	public function __construct() {
		$this->plugin	= USER_SWITCHER;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
	}

	public function filter_user_row_actions( $actions, $user ){
		$link = maybe_switch_url( $user );

		if ( ! $link ) {
			return $actions;
		}

		$actions['switch_to_user'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $link ),
			esc_html__( 'Switch&nbsp;To', 'user-switching' )
		);

		return $actions;
	}


	/**
	 * Defines the names of the cookies used by User Switching.
	 *
	 * @return void
	 */
	public function action_plugins_loaded() {
		// User Switching's auth_cookie
		if ( ! defined( 'USER_SWITCHER_COOKIE' ) ) {
			define( 'USER_SWITCHER_COOKIE', 'wordpress_user_sw_' . COOKIEHASH );
		}

		// User Switching's secure_auth_cookie
		if ( ! defined( 'USER_SWITCHER_SECURE_COOKIE' ) ) {
			define( 'USER_SWITCHER_SECURE_COOKIE', 'wordpress_user_sw_secure_' . COOKIEHASH );
		}

		// User Switching's logged_in_cookie
		if ( ! defined( 'USER_SWITCHER_OLDUSER_COOKIE' ) ) {
			define( 'USER_SWITCHER_OLDUSER_COOKIE', 'wordpress_user_sw_olduser_' . COOKIEHASH );
		}
	}


  	public function filter_user_has_cap( $user_caps, $required_caps, $args, $user ) {
        if ( 'switch_to_user' === $args[0] ) {
            if ( empty( $args[2] ) ) {
                $user_caps['switch_to_user'] = false;
                return $user_caps;
            }
            if ( array_key_exists( 'switch_users', $user_caps ) ) {
                $user_caps['switch_to_user'] = $user_caps['switch_users'];
                return $user_caps;
            }

            $user_caps['switch_to_user'] = ( user_can( $user->ID, 'edit_user', $args[2] ) && ( $args[2] !== $user->ID ) );
        } elseif ( 'switch_off' === $args[0] ) {
            if ( array_key_exists( 'switch_users', $user_caps ) ) {
                $user_caps['switch_off'] = $user_caps['switch_users'];
                return $user_caps;
            }

            $user_caps['switch_off'] = user_can( $user->ID, 'edit_users' );
        }

        return $user_caps;
    }

    public function filter_map_meta_cap( array $required_caps, $cap, $user_id, array $args ) {
		if ( 'switch_to_user' === $cap ) {
			if ( empty( $args[0] ) || $args[0] === $user_id ) {
				$required_caps[] = 'do_not_allow';
			}
		}
		return $required_caps;
	}

	/**
	 * Loads localisation files and routes actions depending on the 'action' query var.
	 *
	 * @return void
	 */
	public function action_init() {

		if ( ! isset( $_REQUEST['action'] ) ) {
			return;
		}

		$current_user = ( is_user_logged_in() ) ? wp_get_current_user() : null;

		switch ( $_REQUEST['action'] ) {

			// We're attempting to switch to another user:
			case 'switch_to_user':
				$user_id = absint( $_REQUEST['user_id'] ?? 0 );

				// Check authentication:
				if ( ! current_user_can( 'switch_to_user', $user_id ) ) {
					wp_die( esc_html__( 'Could not switch users.', 'user-switching' ), 403 );
				}

				// Check intent:
				check_admin_referer( "switch_to_user_{$user_id}" );

				// Switch user:
				$user = switch_to_user( $user_id, remember() );
				if ( $user ) {
					$redirect_to = get_redirect( $user, $current_user );

					// Redirect to the dashboard or the home URL depending on capabilities:
					$args = [
						'user_switched' => 'true',
					];

					if ( $redirect_to ) {
						wp_safe_redirect( add_query_arg( $args, $redirect_to ), 302, $application );
					} elseif ( ! current_user_can( 'read' ) ) {
						wp_safe_redirect( add_query_arg( $args, home_url() ), 302, $application );
					} else {
						wp_safe_redirect( add_query_arg( $args, admin_url() ), 302, $application );
					}
					exit;
				} else {
					wp_die( esc_html__( 'Could not switch users.', 'user-switching' ), 404 );
				}
				break;

			// We're attempting to switch back to the originating user:
			case 'switch_to_olduser':
				// Fetch the originating user data:
				$old_user = get_old_user();
				if ( ! $old_user ) {
					wp_die( esc_html__( 'Could not switch users.', 'user-switching' ), 400 );
				}

				// Check authentication:
				if ( ! authenticate_old_user( $old_user ) ) {
					wp_die( esc_html__( 'Could not switch users.', 'user-switching' ), 403 );
				}

				// Check intent:
				check_admin_referer( "switch_to_olduser_{$old_user->ID}" );

				// Switch user:
				if ( switch_to_user( $old_user->ID, remember(), false ) ) {

					if ( ! empty( $_REQUEST['interim-login'] ) && function_exists( 'login_header' ) ) {
						$GLOBALS['interim_login'] = 'success'; // @codingStandardsIgnoreLine
						login_header( '', '' );
						exit;
					}

					$redirect_to = get_redirect( $old_user, $current_user );
					$args = [
						'user_switched' => 'true',
						'switched_back' => 'true',
					];

					if ( $redirect_to ) {
						wp_safe_redirect( add_query_arg( $args, $redirect_to ), 302, $application );
					} else {
						wp_safe_redirect( add_query_arg( $args, admin_url( 'users.php' ) ), 302, $application );
					}
					exit;
				} else {
					wp_die( esc_html__( 'Could not switch users.', 'user-switching' ), 404 );
				}
				break;

			// We're attempting to switch off the current user:
			case 'switch_off':
				// Check authentication:
				if ( ! $current_user || ! current_user_can( 'switch_off' ) ) {
					/* Translators: "switch off" means to temporarily log out */
					wp_die( esc_html__( 'Could not switch off.', 'user-switching' ), 403 );
				}

				// Check intent:
				check_admin_referer( "switch_off_{$current_user->ID}" );

				// Switch off:
				if ( switch_off_user() ) {
					$redirect_to = get_redirect( null, $current_user );
					$args = [
						'switched_off' => 'true',
					];

					if ( $redirect_to ) {
						wp_safe_redirect( add_query_arg( $args, $redirect_to ), 302, $application );
					} else {
						wp_safe_redirect( add_query_arg( $args, home_url() ), 302, $application );
					}
					exit;
				} else {
					/* Translators: "switch off" means to temporarily log out */
					wp_die( esc_html__( 'Could not switch off.', 'user-switching' ), 403 );
				}
				break;

		}
	}

	/**
	 * Displays the 'Switched to {user}' and 'Switch back to {user}' messages in the admin area.
	 *
	 * @return void
	 */
	public function action_admin_notices() {
		$user = wp_get_current_user();
		$old_user = get_old_user();

		if ( $old_user ) {
			$switched_locale = false;
			$lang_attr = '';
			$locale = get_user_locale( $old_user );
			$switched_locale = switch_to_locale( $locale );
			$lang_attr = str_replace( '_', '-', $locale );

			?>
			<div id="user_switching" class="updated notice notice-success is-dismissible">
				<?php
				if ( $lang_attr ) {
					printf(
						'<p lang="%s">',
						esc_attr( $lang_attr )
					);
				} else {
					echo '<p>';
				}
				?>
				<span class="dashicons dashicons-admin-users" style="color:#56c234" aria-hidden="true"></span>
				<?php
				$message = '';
				$just_switched = isset( $_GET['user_switched'] );
				if ( $just_switched ) {
					$message = esc_html( switched_to_message( $user ) );
				}
				$switch_back_url = add_query_arg( [
					'redirect_to' => rawurlencode( current_url() ),
				], switch_back_url( $old_user ) );

				$message .= sprintf(
					' <a href="%s">%s</a>.',
					esc_url( $switch_back_url ),
					esc_html( switch_back_message( $old_user ) )
				);

				/**
				 * Filters the contents of the message that's displayed to switched users in the admin area.
				 *
				 * @since 1.1.0
				 *
				 * @param string  $message         The message displayed to the switched user.
				 * @param WP_User $user            The current user object.
				 * @param WP_User $old_user        The old user object.
				 * @param string  $switch_back_url The switch back URL.
				 * @param bool    $just_switched   Whether the user made the switch on this page request.
				 */
				$message = apply_filters( 'user_switching_switched_message', $message, $user, $old_user, $switch_back_url, $just_switched );

				echo wp_kses( $message, [
					'a' => [
						'href' => [],
					],
				] );
				?>
				</p>
			</div>
			<?php
			if ( $switched_locale ) {
				restore_previous_locale();
			}
		} elseif ( isset( $_GET['user_switched'] ) ) {
			?>
			<div id="user_switching" class="updated notice notice-success is-dismissible">
				<p>
				<?php
				if ( isset( $_GET['switched_back'] ) ) {
					echo esc_html( switched_back_message( $user ) );
				} else {
					echo esc_html( switched_to_message( $user ) );
				}
				?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Clears the cookies containing the originating user, or pops the latest item off the end if there's more than one.
	 *
	 * @param bool $clear_all Optional. Whether to clear the cookies (as opposed to just popping the last user off the end). Default true.
	 * @return void
	 */
	function user_switching_clear_olduser_cookie( $clear_all = true ) {
		$auth_cookie = user_switching_get_auth_cookie();
		if ( ! empty( $auth_cookie ) ) {
			array_pop( $auth_cookie );
		}
		if ( $clear_all || empty( $auth_cookie ) ) {
			/**
			 * Fires just before the user switching cookies are cleared.
			 *
			 * @since 1.4.0
			 */
			do_action( 'clear_olduser_cookie' );

			/** This filter is documented in user-switching.php */
			if ( ! apply_filters( 'user_switching_send_auth_cookies', true ) ) {
				return;
			}

			$expire = time() - 31536000;
			setcookie( USER_SWITCHER_COOKIE,         ' ', $expire, SITECOOKIEPATH, COOKIE_DOMAIN );
			setcookie( USER_SWITCHER_SECURE_COOKIE,  ' ', $expire, SITECOOKIEPATH, COOKIE_DOMAIN );
			setcookie( USER_SWITCHER_OLDUSER_COOKIE, ' ', $expire, COOKIEPATH, COOKIE_DOMAIN );
		} else {
			if ( secure_auth_cookie() ) {
				$scheme = 'secure_auth';
			} else {
				$scheme = 'auth';
			}

			$old_cookie = end( $auth_cookie );

			$old_user_id = wp_validate_auth_cookie( $old_cookie, $scheme );
			if ( $old_user_id ) {
				$parts = wp_parse_auth_cookie( $old_cookie, $scheme );

				if ( false !== $parts ) {
					user_switching_set_olduser_cookie( $old_user_id, true, $parts['token'] );
				}
			}
		}
	}
}
