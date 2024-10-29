<?php
/**
 * All public facing functions
 */
namespace Codexpert\User_Switcher\App;
use Codexpert\Plugin\Base;
use Codexpert\User_Switcher\Helper;
/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Front
 * @author Codexpert <hi@codexpert.io>
 */
class Front extends Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
	}

	public function head() {}
	
	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'USER_SWITCHER_DEBUG' ) && USER_SWITCHER_DEBUG ? '' : '.min';

		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/front{$min}.css", USER_SWITCHER ), '', $this->version, 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/front{$min}.js", USER_SWITCHER ), [ 'jquery' ], $this->version, true );

		wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js', [ 'jquery' ], null, true );
		wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css' );
		
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
	    $wp_admin_bar->add_menu( array(
	        'id'    => 'us-switcher-menu',
	        'title' => '<span class="us-icon us-main-menu">' . __( 'User Switcher' ) . '</span>',
	    ) );

	    $wp_admin_bar->add_menu(array(
	        'parent' => 'us-switcher-menu',
	        'id'     => 'us-to-guest',
	        'title'  => '<button id="switch-to-guest-button"><span class="us-icon us-guest-user">' . __('Switch to User') . '</span></button>',
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