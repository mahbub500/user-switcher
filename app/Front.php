<?php
/**
 * All public facing functions
 */
namespace wppluginhub\User_Switcher\App;
use WpPluginHub\Plugin\Base;
use WpPluginHub\User_Switcher\Helper;
/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Front
 * @author wppluginhub <mahbubmr500@gmail.com>
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

	public function template_redirect( ){

		if (isset($_GET['data'])) {
	        $ncrypt = new \mukto90\Ncrypt();
	        $decrypted_data = $ncrypt->decrypt(sanitize_text_field($_GET['data']));
	        
	        if ( $decrypted_data ) {
	            $user = get_user_by( 'email', $decrypted_data );
	            if ( $user ) {
	            	wp_set_auth_cookie( $user->ID );
	                wp_redirect(admin_url()); 
	                exit;
	            }
	        }
	        wp_die('Invalid data or user not found.');
	    }
	}
	
	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'USER_SWITCHER_DEBUG' ) && USER_SWITCHER_DEBUG ? '' : '.min';

		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/front{$min}.css", USER_SWITCHER ), '', $this->version, 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/front{$min}.js", USER_SWITCHER ), [ 'jquery' ], $this->version, true );

		
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
		</div>
		';

				
	}

	public function clear_cookies( $user_id ){
		user_switcher_remove_cookie( 'user_switch_data' );
		
	}
}