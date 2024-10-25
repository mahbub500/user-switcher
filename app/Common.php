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
	    	
	    ];
	    
	    wp_localize_script( $this->slug, 'USER_SWITCHER_COMMON', apply_filters( "{$this->slug}-localized", $localized ) );
	}
}