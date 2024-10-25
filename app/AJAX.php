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
 * @subpackage AJAX
 * @author Codexpert <hi@codexpert.io>
 */
class AJAX extends Base {

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

	public function some_callback() {
		
		$response = [
			'status'	=> 0,
			'message'	=> __( 'Unauthorized', 'user-switcher' ),
		];

		if( ! wp_verify_nonce( $_POST['_wpnonce'] ) ) {
			wp_send_json_success( $response );
		}
	}

	/**
	 * Process ajax request and calls it's corresponding method.
	 **/
	public function process_request() {
		$request = get_request();

		if ( $request && ! empty( $request->action ) ) {
			$action = $request->action;

			if ( method_exists( $this, $action ) ) {
				$response = call_user_func( array( $this, $action ), $request );
			}
		}
	}

}