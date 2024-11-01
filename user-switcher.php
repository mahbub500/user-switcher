<?php
/**
 * Plugin Name: User Switcher
 * Description: Using this plugin you can switch to another user.
 * Plugin URI: https://codexpert.io
 * Author: Codexpert, Inc
 * Author URI: https://codexpert.io
 * Version: 0.9
 * Text Domain: user-switcher
 * Domain Path: /languages
 */

namespace Codexpert\User_Switcher;
use Codexpert\Plugin\Notice;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for the plugin
 * @package Plugin
 * @author Codexpert <hi@codexpert.io>
 */
final class Plugin {
	
	/**
	 * Plugin instance
	 * 
	 * @access private
	 * 
	 * @var Plugin
	 */
	private static $_instance;

	/**
	 * The constructor method
	 * 
	 * @access private
	 * 
	 * @since 0.9
	 */
	private function __construct() {
		/**
		 * Includes required files
		 */
		$this->include();

		/**
		 * Defines contants
		 */
		$this->define();

		/**
		 * Runs actual hooks
		 */
		$this->hook();
	}

	/**
	 * Includes files
	 * 
	 * @access private
	 * 
	 * @uses composer
	 * @uses psr-4
	 */
	private function include() {
		require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );
		require_once( dirname( __FILE__ ) . '/inc/functions.php' );
	}

	/**
	 * Define variables and constants
	 * 
	 * @access private
	 * 
	 * @uses get_plugin_data
	 * @uses plugin_basename
	 */
	private function define() {

		/**
		 * Define some constants
		 * 
		 * @since 0.9
		 */
		define( 'USER_SWITCHER', __FILE__ );
		define( 'USER_SWITCHER_DIR', dirname( USER_SWITCHER ) );
		define( 'USER_SWITCHER_ASSET', plugins_url( 'assets', USER_SWITCHER ) );
		define( 'USER_SWITCHER_DEBUG', apply_filters( 'user-switcher_debug', true ) );

		/**
		 * The plugin data
		 * 
		 * @since 0.9
		 * @var $plugin
		 */
		$this->plugin					= get_plugin_data( USER_SWITCHER );
		$this->plugin['basename']		= plugin_basename( USER_SWITCHER );
		$this->plugin['file']			= USER_SWITCHER;
		$this->plugin['min_php']		= '5.6';
		$this->plugin['min_wp']			= '4.0';
		$this->plugin['icon']			= USER_SWITCHER_ASSET . '/img/icon.png';
		$this->plugin['server']			= apply_filters( 'user-switcher_server', 'https://codexpert.io/dashboard' );
		$this->plugin['user_switch_id']	= 0;
		
	}

	/**
	 * Hooks
	 * 
	 * @access private
	 * 
	 * Executes main plugin features
	 *
	 * To add an action, use $instance->action()
	 * To apply a filter, use $instance->filter()
	 * To register a shortcode, use $instance->register()
	 * To add a hook for logged in users, use $instance->priv()
	 * To add a hook for non-logged in users, use $instance->nopriv()
	 * 
	 * @return void
	 */
	private function hook() {

		if( is_admin() ) :

			/**
			 * Admin facing hooks
			 */
			$admin = new App\Admin( $this->plugin );
			$admin->activate( 'install' );
			$admin->action( 'admin_footer', 'modal' );
			$admin->action( 'plugins_loaded', 'i18n' );
			$admin->action( 'admin_enqueue_scripts', 'enqueue_scripts' );
			$admin->action( 'admin_footer_text', 'footer_text' );
			$admin->action( 'admin_bar_menu', 'admin_bar_menu', 100 );
			$admin->action( 'wp_login', 'clear_cookies' );
			

		else : // ! is_admin() ?

			/**
			 * Front facing hooks
			 */
			$front = new App\Front( $this->plugin );
			$front->action( 'wp_head', 'head' );
			$front->action( 'wp_footer', 'modal' );
			$front->action( 'wp_enqueue_scripts', 'enqueue_scripts' );
			// $front->action( 'wp_login', 'clear_cookies' );

		

		endif;


		/**
		 * AJAX related hooks
		 */
		$ajax = new App\AJAX( $this->plugin );
		$ajax->priv( 'search_users', 'search_users' );
		$ajax->priv( 'switch_user', 'switch_user' );
	}

	/**
	 * Cloning is forbidden.
	 * 
	 * @access public
	 */
	public function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 * 
	 * @access public
	 */
	public function __wakeup() { }

	/**
	 * Instantiate the plugin
	 * 
	 * @access public
	 * 
	 * @return $_instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

Plugin::instance();