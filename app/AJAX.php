<?php
/**
 * All AJAX related functions
 */
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

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
	}

	function search_users() {
	    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'] ) ) {
	        wp_send_json_error( [ 'message' => __( 'Unauthorized', 'User Switcher' ) ], 401 );
	    }

	    $keyword = isset( $_GET['keyword'] ) ? sanitize_text_field( $_GET['keyword'] ) : '';

	    $args = [
	        'search'         => '*' . esc_attr( $keyword ) . '*',
	        'search_columns' => ['user_login', 'user_nicename', 'display_name', 'user_email'],
	    ];

	    $user_query = new \WP_User_Query( $args );
	    $users 		= $user_query->get_results();
	    if ( ! empty( $users ) ) {
	        $user_array = [];
	        foreach ( $users as $user ) {
	            $user_array[$user->ID] = $user->user_login;
	        }
	        wp_send_json_success( $user_array );
	    } else {
	        wp_send_json_error( [ 'message' => __( 'No users found', 'User Switcher' ) ] );
	    }
	}

	public function switch_user() {
	    if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'] ) ) {
	        wp_send_json_error( [ 'message' => __( 'Unauthorized', 'User Switcher' ) ], 401 );
	    }

	    $user_id = intval($_POST['user_id']);

	    ob_start();
		ob_get_clean();

		set_cookie( 'user_switcher', $user_id, time() + DAY_IN_SECONDS );
	
	    $response = array(
	        'success' => true,
	        'message' => 'User switched successfully!',
	    );
	    
	    wp_send_json_success( $response );
	    
	}


}