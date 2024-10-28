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
	    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'user_switcher_nonce' ) ) {
	        wp_send_json_error( [ 'message' => __( 'Unauthorized', 'User Switcher' ) ], 401 );
	    }

	    $keyword = isset( $_GET['keyword'] ) ? sanitize_text_field( $_GET['keyword'] ) : '';

	    $args = [
	        'search'         => '*' . esc_attr( $keyword ) . '*',
	        'search_columns' => ['user_login', 'user_nicename', 'display_name', 'user_email'],
	    ];

	    $user_query = new WP_User_Query( $args );
	    $users = $user_query->get_results();

	    if ( ! empty( $users ) ) {
	        $user_data = [];
	        foreach ( $users as $user ) {
	            $user_data[] = [
	                'ID'            => $user->ID,
	                'user_login'    => $user->user_login,
	                'user_nicename' => $user->user_nicename,
	                'display_name'  => $user->display_name,
	                'user_email'    => $user->user_email,
	            ];
	        }
	        wp_send_json_success( $user_data );
	    } else {
	        wp_send_json_error( [ 'message' => __( 'No users found', 'User Switcher' ) ] );
	    }
	}


}