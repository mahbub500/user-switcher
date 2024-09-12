<?php
namespace Codexpert\Plugin;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Base
 * @author Codexpert <hi@codexpert.io>
 */
abstract class Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Checks if a callback exists, adds a add to display notices
	 * 
	 * @return bool
	 */
	public function method_exists( $callback ) {

		if( ! method_exists( $this, $callback ) ) {
			
			add_action( 'plugins_loaded', function() use( $callback ) {
				if( current_user_can( 'manage_options' ) ) {
					add_action( ( is_admin() ? 'admin_head' : 'wp_head' ), function() use ( $callback ) {
						printf(
							'<div class="notice notice-error cx-notice cx-shadow"><p>%s</p></div>',
							sprintf( __( 'Hey Dev, it looks like you forgot to define the <code>%1$s()</code> method in the <code>%2$s</code> class!', 'cx-plugin' ), $callback, get_called_class() )
						);
					} );
				}
			} );

			return false;
		}

		return true;
	}
	
	/**
	 * @see register_activation_hook
	 */
	public function activate( $callback ) {

		if( ! $this->method_exists( $callback ) ) return;

		register_activation_hook( $this->plugin['file'], [ $this, $callback ] );
	}
	
	/**
	 * @see register_activation_hook
	 */
	public function deactivate( $callback ) {

		if( ! $this->method_exists( $callback ) ) return;

		register_deactivation_hook( $this->plugin['file'], [ $this, $callback ] );
	}
	
	/**
	 * @see add_action
	 */
	public function action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {

		if( ! $this->method_exists( $callback ) ) return;

		add_action( $tag, [ $this, $callback ], $priority, $accepted_args );
	}

	/**
	 * @see add_filter
	 */
	public function filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {

		if( ! $this->method_exists( $callback ) ) return;

		add_filter( $tag, [ $this, $callback ], $priority, $accepted_args );
	}

	/**
	 * @see add_shortcode
	 */
	public function register( $tag, $callback ) {

		if( ! $this->method_exists( $callback ) ) return;

		add_shortcode( $tag, [ $this, $callback ] );
	}

	/**
	 * @see add_action( 'wp_ajax_..' )
	 */
	public function priv( $handle, $callback ) {

		if( ! $this->method_exists( $callback ) ) return;

		$this->action( "wp_ajax_{$handle}", $callback );
	}

	/**
	 * @see add_action( 'wp_ajax_nopriv_..' )
	 */
	public function nopriv( $handle, $callback ) {

		if( ! $this->method_exists( $callback ) ) return;

		$this->action( "wp_ajax_nopriv_{$handle}", $callback );
	}

	/**
	 * @see add_action( 'wp_ajax_..' )
	 * @see add_action( 'wp_ajax_nopriv_..' )
	 */
	public function all( $handle, $callback ) {

		if( ! $this->method_exists( $callback ) ) return;

		$this->priv( $handle, $callback );
		$this->nopriv( $handle, $callback );
	}

	/**
	 * @return true
	 */
	public function __return_true() {
		return __return_true();
	}

	/**
	 * @return false
	 */
	public function __return_false() {
		return __return_false();
	}

	/**
	 * @return 0
	 */
	public function __return_zero() {
		return __return_zero();
	}

	/**
	 * @return []
	 */
	public function __return_empty_array() {
		return __return_empty_array();
	}

	/**
	 * @return null
	 */
	public function __return_null() {
		return __return_null();
	}

	/**
	 * @return ''
	 */
	public function __return_empty_string() {
		return __return_empty_string();
	}

	/**
	 * Sanitize data
	 * 
	 * @param mix $input The input
	 * @param string $type The data type
	 * 
	 * @return mix
	 */
	public function sanitize( $input, $type = 'text' ) {

		if( 'array' == $type ) {
			$sanitized = [];

			foreach ( $input as $key => $value ) {
				if( is_array( $value ) ) {
					$sanitized[ $key ] = $this->sanitize( $value, $type );
				}
				else {
					// identify textarea to fix possible linebreak issues
					$_type = count( explode( PHP_EOL, $value ) ) > 1 ? 'textarea' : 'text';

					$sanitized[ $key ] = $this->sanitize( $value, $_type );
				}
			}

			return $sanitized;
		}

		if( ! in_array( $type, [ 'textarea', 'email', 'file', 'class', 'key', 'title', 'user', 'option', 'meta' ] ) ) {
			$type = 'text';
		}

		if( array_key_exists( $type,
			$maps = [
				'text'      => 'text_field',
				'textarea'  => 'textarea_field',
				'file'      => 'file_name',
				'class'     => 'html_class',
			]
		) ) {
			$type = $maps[ $type ];
		}

		$fn = "sanitize_{$type}";

		return $fn( $input );
	}
}