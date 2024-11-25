<?php
/**
 * All admin facing functions
 */
namespace wppluginhub\User_Switcher\App;
use WpPluginHub\Plugin\Base;
use WpPluginHub\Plugin\Metabox;

use WpPluginHub\User_Switcher\Helper;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Admin
 * @author wppluginhub <mahbubmr500@gmail.com>
 */
class Admin extends Base {

	public $plugin;

	// private $user_switch_id = 0;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
		$this->user_switch_id	= $this->plugin['user_switch_id'];
	}

	/**
	 * Internationalization
	 */
	public function i18n() {
		load_plugin_textdomain( 'user-switcher', false, USER_SWITCHER_DIR . '/languages/' );

		$cookie_name = 'user_switcher_' . COOKIEHASH;

		if ( ! empty( $_COOKIE[ $cookie_name ] ) ) {
			$this->user_switch_id = $cookie[ $cookie_name ];
		}
	}


	/**
	 * Installer. Runs once when the plugin in activated.
	 *
	 * @since 1.0
	 */
	public function install() {

		if( ! get_option( 'user-switcher_version' ) ){
			update_option( 'user-switcher_version', $this->version );
		}
		
		if( ! get_option( 'user-switcher_install_time' ) ){
			update_option( 'user-switcher_install_time', time() );
		}
	}

	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'USER_SWITCHER_DEBUG' ) && USER_SWITCHER_DEBUG ? '' : '.min';
		
		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/admin{$min}.css", USER_SWITCHER ), '', $this->version, 'all' );

		wp_enqueue_style( 'select2', plugins_url( "/assets/css/select2.css", USER_SWITCHER ), '', $this->version, 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/admin{$min}.js", USER_SWITCHER ), [ 'jquery' ], $this->version, true );

		wp_enqueue_script( 'select2', plugins_url( "/assets/js/select2.min.js", USER_SWITCHER ), [ 'jquery' ], $this->version, true );

		$localized = [
			'ajaxurl'	=> admin_url( 'admin-ajax.php' ),
			'_wpnonce'	=> wp_create_nonce(),
		];
		wp_localize_script( $this->slug, 'USER_SWITCHER', apply_filters( "{$this->slug}-localized", $localized ) );	
	}

	public function modal() {
		echo '
		<div id="user-switcher-modal" style="display: none">
			<img id="user-switcher-modal-loader" src="' . esc_attr( USER_SWITCHER_ASSET . '/img/loader.gif' ) . '" />
		</div>';

		echo Helper::get_template( 'modal', 'views' );
		
	}
	/**
	 * Add menus to admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance, passed by reference.
	 */
	public function admin_bar_menu( $wp_admin_bar ) {
		
		if ( ! is_admin_bar_showing() || ! current_user_can( 'manage_options' ) ) {
		    return;
		}

	    $wp_admin_bar->add_menu( array(
	        'id'    => 'us-switcher-menu',
	        'title' => '<span class="us-icon us-main-menu">' . __( 'User Switch', 'user-switcher' ) . '</span>',
	    ) );

	    $wp_admin_bar->add_menu(array(
	        'parent' => 'us-switcher-menu',
	        'id'     => 'us-to-user',
	        'title'  => '<button id="switch-to-user-button"><span class="us-icon us-user">' . __('Switch to User', 'user-switcher') . '</span></button>',
	        'meta'   => array(
	            'html' => '',
	        ),
	    ));
	}

	/**
	 * Clear switcher cookies whenever the user login.
	 **/
	public function clear_cookies() {
		us_remove_cookie( 'user_switch_data' );
	}

}