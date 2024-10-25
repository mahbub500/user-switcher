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

	public $current_switcher_id = 0;

	public $user_switch_id = 0;

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
		$link = 'te';

		// if ( ! $link ) {
		// 	return $actions;
		// }

		$actions['switch_to_user'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $link ),
			esc_html__( 'Switch&nbsp;To', 'user-switching' )
		);

		return $actions;
	}


	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function common_assets() {
		$min = defined( 'USER_SWITCHER_DEBUG' ) && USER_SWITCHER_DEBUG ? '' : '.min';
		
		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/common{$min}.css", USER_SWITCHER_FILE ), 'dashicons', $this->version, 'all' );
		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/common{$min}.js", USER_SWITCHER_FILE ), [ 'jquery', 'backbone', 'underscore' ], $this->version, true );


	    $localized = [
	    	'homeurl'		=> get_bloginfo( 'url' ),
	    	'adminurl'		=> admin_url(),
	    	'asseturl'		=> USER_SWITCHER_ASSETS,
	    	'ajaxurl'		=> admin_url( 'admin-ajax.php' ),
	    	'_wpnonce'		=> wp_create_nonce(),
	    	'switcher' 		=> $this->current_switcher_id,
			'switch_to' 	=> $this->user_switch_id,
			'is_admin' 		=> is_admin(),
			'admin_bar' 	=> is_admin_bar_showing(),
			'l8n' => array(
				'title' => __( 'User Switcher' ),
				'description' => __( 'Search users by name, display name, or email.' ),
				'search_placeholder' => __( 'Search ...' ),
				'submit_button' => __( 'Go' ),
				'notice' => array(
					'char_limit' => __( 'Enter at least 3 characters!' ),
				),
				'server_error' => __( 'Something went wrong while processing your request. Please contact your administrator.' ),
				'guest_notice_info' => __( 'You are currently switch to guest user!' ),
				'switch_back' => __( 'Switch Back' ),
				'search_users' => __( 'Search Users' ),
				'closed' => __( 'Closed' ),
				'name' => __( 'User Switcher' ),
				'us_is_on' => __( 'User Switcher Is On' ),
				'switch_to_guest' => __( 'Switch to Guest User' ),
				'prev' => __( 'Previous' ),
				'next' => __( 'Next' ),
			),
	    ];
	    
	    wp_localize_script( $this->slug, 'USER_SWITCHER_COMMON', apply_filters( "{$this->slug}-localized", $localized ) );
	}

	public function wp_die( $message, $title, $args = array() ) {
		if ( ! empty( $this->user_switch_id ) ) {
			$switch_back_url = add_query_arg( array(
				'action' => 'us_restore_account',
				'return_url' => admin_url(), // Always return to /dashboard
			), admin_url( 'admin-ajax.php' ) );

			$back = sprintf( '<a style="font-weight:700;text-decoration:none;text-transform:uppercase;" href="%s">&larr; %s</a>', $switch_back_url, __( 'Switch Back' ) );
			$msg = sprintf( '<p>%s %s</p>', __( 'You are currently switch to a user with no admin access!' ), $back );
			$message = $msg . $message;
		}
		_default_wp_die_handler( $message, $title, $args );
	}
}