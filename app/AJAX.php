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
	    $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

	    $args = [
	        'search' => '*' . esc_attr( $search_term ) . '*',
	        'search_columns' => ['user_login', 'user_email', 'display_name'],
	        'fields' => ['ID', 'display_name'],
	        'number' => -1, 
	    ];

	    $users = get_users( $args );

	    if ( $users ) {
		    foreach ( $users as $user ) {
		        $switch_url = wp_nonce_url(add_query_arg('user_id', $user->ID, admin_url('index.php')), 'switch_to_user_' . $user->ID);
		        
		        echo 'Name: ' . esc_html($user->display_name) . ' ID: ' . esc_html($user->ID) . ' <a href="' . esc_url($switch_url) . '">Go</a><br>';
		    }
		} else {
		    echo __('No users found.');
		}

	    wp_die(); 
	}

}