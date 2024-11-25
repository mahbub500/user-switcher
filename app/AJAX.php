<?php
/**
 * All AJAX related functions
 */
namespace wppluginhub\User_Switcher\App;
use WpPluginHub\Plugin\Base;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage AJAX
 * @author wppluginhub <mahbubmr500@gmail.com>
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
	    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_GET['_wpnonce'] ) ) ) {
	        wp_send_json_error( [ 'message' => __( 'Unauthorized', 'switch-to-user' ) ], 401 );
	    }

	    $current_user_id = get_current_user_id();

	    $keyword = isset( $_GET['keyword']  ) ? sanitize_text_field( $_GET['keyword'] ) : '';

	    $args = [
	        'search'         => '*' . esc_attr( $keyword ) . '*',
	        'search_columns' => ['user_login', 'user_nicename', 'display_name', 'user_email'],
	        'exclude'        => [ $current_user_id ],
	    ];

	    $user_query = new \WP_User_Query( $args );
	    $users 		= $user_query->get_results();
	    if ( ! empty( $users ) ) {
	        $user_array = [];
	        foreach ( $users as $user ) {
	            $user_array[ $user->ID ] = $user->user_login;
	        }
	        wp_send_json_success( $user_array );
	    } else {
	        wp_send_json_error( [ 'message' => __( 'No users found', 'switch-to-user' ) ] );
	    }
	}

	public function switch_user() {
	    if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'] ) ) {
	        wp_send_json_error( [ 'message' => __( 'Unauthorized', 'switch-to-user' ) ], 401 );
	    }

	    $switch_from 	= get_current_user_id();
	    $switch_to_user = intval( sanitize_text_field( $_POST['user_id'] ));

	    $switch_data = [
		    'switch_from' 		=> $switch_from,
		    'switch_to_user' 	=> $switch_to_user,
		];

		$switch_data_json = json_encode( $switch_data );


		stu_us_set_cookie( 'user_switch_data', $switch_data_json, time() + DAY_IN_SECONDS );

		$login_url 	= stu_encrypted_login_url( $switch_to_user );
	
	    $response = array(
	        'success' => true,
	        'url' => $login_url,
	        'message' => __( 'User switched successfully!', 'switch-to-user' ) ,
	    );
	    
	    wp_send_json_success( $response );
	    
	}

	public function remove_cookie(){
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'] ) ) {
	        wp_send_json_error( [ 'message' => __( 'Unauthorized', 'switch-to-user' ) ], 401 );
	    }

	    stu_remove_cookie( 'user_switch_data' );

	    $response = array(
	        'success' => true,
	        'message' => __( 'Cookie Removed', 'switch-to-user' ) ,
	    );
	    
	    wp_send_json_success( $response );

	}


}