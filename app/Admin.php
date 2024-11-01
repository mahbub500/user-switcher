<?php
/**
 * All admin facing functions
 */
namespace Codexpert\User_Switcher\App;
use Codexpert\Plugin\Base;
use Codexpert\Plugin\Metabox;

use Codexpert\User_Switcher\Helper;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Admin
 * @author Codexpert <hi@codexpert.io>
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

		$cookie = $_COOKIE;
		$cookie_name = 'user_switcher_' . COOKIEHASH;

		if ( ! empty( $cookie[ $cookie_name ] ) ) {
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

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/admin{$min}.js", USER_SWITCHER ), [ 'jquery' ], $this->version, true );

		wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js', [ 'jquery' ], null, true );
		wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css' );

		$localized = [
			'ajaxurl'	=> admin_url( 'admin-ajax.php' ),
			'_wpnonce'	=> wp_create_nonce(),
		];
		wp_localize_script( $this->slug, 'USER_SWITCHER', apply_filters( "{$this->slug}-localized", $localized ) );	
	}

	public function footer_text( $text ) {
		if( get_current_screen()->parent_base != $this->slug ) return $text;

		return sprintf( __( 'Built with %1$s by the folks at <a href="%2$s" target="_blank">Codexpert, Inc</a>.' ), '&hearts;', 'https://codexpert.io' );
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
		if ( ! is_admin_bar_showing() ) {
			return;
		}

		$switch_to_user_id 	= get_user_switch_data( 'switch_from' );
		$switch_to_user 	= get_username_by_id( $switch_to_user_id );
		$login_url 			= get_encrypted_login_url( $switch_to_user_id );

		$wp_admin_bar->add_node( [
			'id' => 'switch-back',
			'title' => 'Switch back '. $switch_to_user,
			'href' => $login_url,
		] );

	    $wp_admin_bar->add_menu( array(
	        'id'    => 'us-switcher-menu',
	        'title' => '<span class="us-icon us-main-menu">' . __( 'User Switcher' ) . '</span>',
	    ) );

	    $wp_admin_bar->add_menu(array(
	        'parent' => 'us-switcher-menu',
	        'id'     => 'us-to-user',
	        'title'  => '<button id="switch-to-user-button"><span class="us-icon us-user">' . __('Switch to User') . '</span></button>',
	        'meta'   => array(
	            'html' => '',
	        ),
	    ));
	}

	/**
	 * Clear switcher cookies whenever the user login.
	 **/
	public function clear_cookies() {
		$cookie_name = 'user_switcher_' . COOKIEHASH;
		$secure = ( 'https' === parse_url( home_url(), PHP_URL_SCHEME ) );
		setcookie( $cookie_name, null, -1, COOKIEPATH, COOKIE_DOMAIN, $secure );
	}

}