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
}
