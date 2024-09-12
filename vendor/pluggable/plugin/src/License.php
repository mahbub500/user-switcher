<?php
namespace Pluggable\Plugin;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @package Plugin
 * @subpackage License
 * @author Pluggable <hi@pluggable.io>
 */
class License {
	
	/**
	 * @var the plugin data array
	 */
	public $plugin;
	
	/**
	 * @var plugin slug
	 */
	public $slug;
	
	/**
	 * @var plugin name
	 */
	public $name;
	
	/**
	 * @var the server URL
	 */
	public $server;
	
	/**
	 * @var where we should redirect after activation
	 */
	public $redirect;

	/**
	 * Is it in the validating state?
	 */
	public $validating = false;

	public $file;

	public $args;

	public $item_id;

	/**
	 * @param string $plugin the plugin __FILE__
	 * 
	 * @since 0.93
	 * @param array $args[
	 * 		string $redirect where it should take after activating a license
	 * 		string $server the API server
	 * ]
	 */
	public function __construct( $file, $args = [] ) {

		if( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		$this->file 	= $file;
		$this->plugin 	= get_plugin_data( $file );
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];

		$this->args = wp_parse_args( $args, [
			'redirect'	=> admin_url( "admin.php?page={$this->slug}" ),
			'server'	=> 'https://my.pluggable.io',
			'item_id'	=> 0,
		] );

		$this->server 		= untrailingslashit( $this->args['server'] );
		$this->redirect 	= $this->args['redirect'];
		$this->item_id		= $this->args['item_id'];
		
		$this->plugin['license']	= $this;
		$this->plugin['basename']	= plugin_basename( $file );
		$update	= new Update( $this->plugin, $this->server );

		$this->hooks();
	}

	public function hooks() {
		register_activation_hook( $this->file, [ $this, 'install' ] );
		register_deactivation_hook( $this->file, [ $this, 'uninstall' ] );
		add_action( 'pluggable-daily', [ $this, 'validate' ] );
		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( 'admin_notices', [ $this, 'show_notices' ] );
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}

	/**
	 * Installer. Runs once when the plugin in activated.
	 *
	 * @since 1.0
	 */
	public function install() {
		if ( ! wp_next_scheduled ( 'pluggable-daily' )) {
		    wp_schedule_event( time(), 'daily', 'pluggable-daily' );
		}
	}

	/**
	 * Uninstaller. Runs once when the plugin is deactivated.
	 *
	 * @since 1.0
	 */
	public function uninstall() {
		wp_clear_scheduled_hook( 'pluggable-daily' );
	}

	public function validate() {
		if( $this->_is_activated() ) {

			/**
			 * It's in the validating state
			 */
			$this->validating = true;

			$validation = $this->do( 'check', $this->get_license_key(), $this->name ) ;
			if( $validation['status'] != true ) {
				update_option( $this->get_license_status_name(), 'invalid' );
			}
			else {
				update_option( $this->get_license_status_name(), 'valid' );
				update_option( $this->get_license_expiry_name(), ( $validation['data']->expires == 'lifetime' ? 4765132799 : strtotime( $validation['data']->expires ) ) );
			}
		}
	}

	public function init() {
		
		if( ! isset( $_GET['pl-license'] ) ) return;

		if( $_GET['pl-license'] == 'deactivate' ) {
			if( ! wp_verify_nonce( $_GET['pl-nonce'], 'pluggable' ) ) {
				// print an error message. maybe store in a temporary session and print later?
			}
			else {
				$this->do( 'deactivate', $this->get_license_key(), $this->name );
			}
		}

		elseif( $_GET['pl-license'] == 'activate' ) {
			if( ! wp_verify_nonce( $_GET['pl-nonce'], 'pluggable' ) || $_GET['key'] == '' ) {
				// print an error message. maybe store in a temporary session and print later?
			}
			else {
				$this->do( 'activate', $_GET['key'], $this->name );
			}
		}

		$query = isset( $_GET ) ? $_GET : [];
		foreach ( [ 'item_id', 'item_slug', 'pl-license', 'pl-nonce', 'key' ] as $key ) {
			if( isset( $query[ $key ] ) ) {
				unset( $query[ $key ] );
			}
		}

		wp_redirect( add_query_arg( $query, admin_url( 'admin.php' ) ) );
	}

	public function show_notices() {

		if( apply_filters( 'pluggable_hide-notices', false, $this->plugin ) || ( isset( $this->args['hide_notice'] ) && $this->args['hide_notice'] ) ) return;

		if( did_action( "_license_{$this->slug}_notice" ) ) return;
		do_action( "_license_{$this->slug}_notice" );

		global $pl_notices;

		// not activated
		if( ! $this->_is_activated() ) {

			$notice = '';
			$notice .= '<p>' . sprintf( __( '<strong>ALERT:</strong> In order to enjoy the features of <strong>%1$s</strong>, you need to activate the license first. Sorry, but the plugin won\'t work without activation! Please <a href="%2$s">activate it now</a>.', 'pluggable' ), $this->name, $this->get_activation_url() ) . '</p>';

			echo '<div class="notice notice-error cx-notice cx-shadow">' . $notice . '</div>';
		}

		// about to expire?
		if( $this->_is_activated() && ( time() + apply_filters( 'pluggable-expiry-notice-time', MONTH_IN_SECONDS, $this ) ) > ( $expiry = get_option( $this->get_license_expiry_name() ) ) && time() < $expiry ) {

			$notice = '';
			$notice .= '<p>' . sprintf( __( '<strong>ALERT:</strong> Your license for <strong>%1$s</strong> is about to expire in <strong>%2$s</strong>. The plugin will stop working without a valid license key. <a href="%3$s">Renew your license</a> now and get a special <strong>%4$s discount</strong>!', 'pluggable' ), $this->name, human_time_diff( $expiry, time() ), $this->get_renewal_url(), '20%' ) . '</p>';

			echo '<div class="notice notice-error cx-notice cx-shadow">' . $notice . '</div>';
		}

		// expired to invalid license?
		if( $this->_is_activated() && ( $this->_is_invalid() || $this->_is_expired() ) && apply_filters( 'pluggable-show_validation_notice', false, $this->plugin ) ) {

			$notice = '';
			$notice .= '<p>' . sprintf( __( '<strong>WARNING:</strong> It looks like <strong>%1$s</strong> can\'t connect to our server and is unable to receive updates! The plugin might stop working if it\'s not connected. <a href="%2$s">Reconnect Now</a>.', 'pluggable' ), $this->name, $this->get_deactivation_url() ) . '</p>';

			echo '<div class="notice notice-warning cx-notice cx-shadow">' . $notice . '</div>';
		}
	}

	public function activation_form() {
		$html = '';

		if( ! $this->_is_activated() ) {
			$activation_url = $this->get_activation_url();
			$activate_label	= apply_filters( "{$this->slug}_activate_label", __( 'Activate', 'pluggable' ), $this->plugin );

			$html .= '<p class="pl-desc">' . sprintf( __( 'Thanks for installing <strong>%1$s</strong> 👋', 'pluggable' ), $this->name ) . '</p>';
			$html .= '<p class="pl-desc">' . __( 'In order to make the plugin work, you need to activate the license by clicking the button below. Please reach out to us if you need any help.', 'pluggable' ) . '</p>';
			$html .= "<a id='pl-activate' class='pl-button button button-primary' href='{$activation_url}'>" . $activate_label . "</a>";
		}

		else {
			$deactivation_url	= $this->get_deactivation_url();
			$deactivate_label	= apply_filters( "{$this->slug}_deactivate_label", __( 'Deactivate', 'pluggable' ), $this->plugin );
			$license_meta		= $this->get_license_meta();
			
			$html .= '<p class="pl-desc">' . sprintf( __( 'Congratulations! Your license for <strong>%s</strong> is activated. 🎉', 'pluggable' ), $this->name ) . '</p>';
			
			
			if( isset( $license_meta->customer_name ) ) {
				$html .= '<p class="pl-info">' . sprintf( __( 'Name: %s', 'pluggable' ), $license_meta->customer_name ) . '</p>';
			}

			if( isset( $license_meta->customer_email ) ) {
				$html .= '<p class="pl-info">' . sprintf( __( 'Email: %s', 'pluggable' ), $license_meta->customer_email ) . '</p>';
			}

			if( isset( $license_meta->payment_id ) ) {
				$html .= '<p class="pl-info">' . sprintf( __( 'Order ID: %s', 'pluggable' ), $license_meta->payment_id ) . '</p>';
			}

			$html .= '<p class="pl-info">' . sprintf( __( 'Expiry: %s', 'pluggable' ), $this->get_license_expiry() ) . '</p>';

			$html .= '<p class="pl-info">' . __( 'You can deactivate the license by clicking the button below.', 'pluggable' ) . '</p>';
			$html .= "<a id='pl-deactivate' class='pl-button button button-secondary' href='{$deactivation_url}'>" . $deactivate_label . "</a>";
		}

		return apply_filters( "{$this->slug}_activation_form", $html, $this->plugin );
	}

	// backward compatibility
	public function activator_form() {
		return $this->activation_form();
	}

	public function register_endpoints() {
		register_rest_route( 'pluggable', 'license', [
			'methods'				=> 'POST',
			'callback'				=> [ $this, 'callback_action' ],
			'permission_callback'	=> '__return_true'
		] );
	}

	public function callback_action( $request ) {
		
		add_filter( 'pluggable-is_forced', '__return_true' );
		
		$parameters = $request->get_params();
		return $this->do( $parameters['action'], $parameters['license_key'], $parameters['item_name'] );
	}

	/**
	 * Perform an action
	 *
	 * @param string $action activate|deactivate|check
	 * @param string $item_name the plugin name
	 */
	public function do( $action, $license, $item_name ) {
		if( did_action( "_{$this->slug}_did_license_action" ) && $this->validating !== true ) return;
		do_action( "_{$this->slug}_did_license_action" );

		// for activate and deactivate, if slug doesn't match, abort
		// if( in_array( $action, [ 'activate', 'deactivate' ] ) && ( ! isset( $_GET['item_slug'] ) || $_GET['item_slug'] != $this->slug ) ) return;
		if( ! in_array( $action, [ 'activate', 'deactivate' ] ) ) return;

		$_response = [
			'status'	=> false,
			'message'	=> __( 'Something is wrong', 'pluggable' ),
			'data'		=> []
		];

		// data to send in our API request
		$api_params = [
			'edd_action'	=> "{$action}_license",
			'license'		=> $license,
			'item_name'		=> urlencode( $item_name ),
			'url'			=> home_url()
		];

		$response		= wp_remote_get( $this->server, [ 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ] );
		
		$license_data	= json_decode( wp_remote_retrieve_body( $response ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$_response['message'] = is_wp_error( $response ) ? $response->get_error_message() : __( 'An error occurred, please try again.', 'pluggable' );
		}

		// it's an activation request
		elseif( $action == 'activate' ) {

			// license key is not OK?
			if ( false === $license_data->success ) {
				switch( $license_data->error ) {
					case 'expired' :

						$_response['message'] = sprintf(
							__( 'Your license key expired on %s.', 'pluggable' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;

					case 'disabled' :
					case 'revoked' :

						$_response['message'] = __( 'Your license key has been disabled.', 'pluggable' );
						break;

					case 'missing' :

						$_response['message'] = __( 'Invalid license.', 'pluggable' );
						break;

					case 'invalid' :
					case 'site_inactive' :

						$_response['message'] = __( 'Your license is not active for this URL.', 'pluggable' );
						break;

					case 'item_name_mismatch' :

						$_response['message'] = sprintf( __( 'This appears to be an invalid license key for %s.', 'pluggable' ), $item_name );
						break;

					case 'no_activations_left':

						$_response['message'] = __( 'Your license key has reached its activation limit.', 'pluggable' );
						break;

					default :

						$_response['message'] = __( 'An error occurred, please try again.', 'pluggable' );
						break;
				}

			}

			// license key is OK
			else {

				if( isset( $_GET['item_slug'] ) && '' != $_GET['item_slug'] ) {
					$this->slug = $_GET['item_slug'];
				}

				update_option( $this->get_license_key_name(), $license );
				update_option( $this->get_license_status_name(), $license_data->license );
				update_option( $this->get_license_expiry_name(), ( $license_data->expires == 'lifetime' ? 4765132799 : strtotime( $license_data->expires ) ) );
				update_option( $this->get_license_meta_name(), $license_data );
				update_option( $this->get_license_meta_name(), $license_data );
				update_option( $this->get_license_item_id_name(), $_GET['item_id'] );

				$_response['status']	= $license_data;
				$_response['message']	= __( 'License activated', 'pluggable' );
			} 

		}

		// it's a deactivation request
		elseif( $action == 'deactivate' ) {
			// if( ( isset( $license_data->license ) && $license_data->license == 'deactivated' ) || $this->_is_forced() ) { // "deactivated" or "failed"

			// 	if( isset( $_GET['item_slug'] ) && '' != $_GET['item_slug'] ) {
			// 		$this->slug = $_GET['item_slug'];
			// 	}

			// 	delete_option( $this->get_license_key_name() );
			// 	delete_option( $this->get_license_status_name() );
			// 	delete_option( $this->get_license_expiry_name() );
			// 	delete_option( $this->get_license_meta_name() );

			// 	$_response['status']	= true;
			// 	$_response['message'] = __( 'License deactivated', 'pluggable' );
			// }

			// want to deactivate? do it first without validating the license
			delete_option( $this->get_license_key_name() );
			delete_option( $this->get_license_status_name() );
			delete_option( $this->get_license_expiry_name() );
			delete_option( $this->get_license_meta_name() );
			delete_option( $this->get_license_item_id_name() );
		}

		// it's a verification request
		elseif( $action == 'check' ) {
			if( isset( $license_data->license ) && $license_data->license == 'valid' ) {
				$_response['status']	= true;
				$_response['message']	= __( 'License valid', 'pluggable' );
				$_response['data']		= $license_data;
				update_option( $this->get_license_meta_name(), $license_data );
			} else {
				$_response['status']	= false;
				$_response['message']	= __( 'License invalid', 'pluggable' );
			}
		}

		return $_response;
	}

	public function get_activation_url() {
		$query					= isset( $_GET ) ? $_GET : [];
		$query['pl-nonce']		= wp_create_nonce( 'pluggable' );

		$activation_url = add_query_arg( [
			'item_slug'	=> $this->slug,
			'item_id'	=> $this->item_id,
			'pl-nonce'	=> wp_create_nonce( 'pluggable' ),
			'track'		=> base64_encode( $this->redirect ),
		], trailingslashit( $this->get_activation_page() ) );

		return apply_filters( 'pluggable-activation_url', $activation_url, $this->plugin );
	}

	public function get_deactivation_url() {
		$query					= isset( $_GET ) ? $_GET : [];
		$query['item_slug']		= $this->slug;
		$query['pl-nonce']		= wp_create_nonce( 'pluggable' );
		$query['pl-license']	= 'deactivate';

		$deactivation_url = add_query_arg( $query, $this->redirect );

		return apply_filters( 'pluggable-deactivation_url', $deactivation_url, $this->plugin );
	}

	public function get_renewal_url() {
		$query = [
			'edd_license_key'	=> $this->get_license_key(),
			'download_id'		=> $this->get_license_item_id(),
		];

		$renewal_url = add_query_arg( $query, trailingslashit( $this->server ) . 'order' );

		return apply_filters( 'pluggable-renewal_url', $renewal_url, $this->plugin );
	}

	public function get_activation_page() {
		return apply_filters( 'pluggable-activation_page', "{$this->server}/connect", $this->plugin );
	}

	// option_key in the wp_options table
	public function get_license_key_name() {
		return "_license_{$this->slug}_key";
	}

	// option_key in the wp_options table
	public function get_license_status_name() {
		return "_license_{$this->slug}_status";
	}

	// option_key in the wp_options table
	public function get_license_expiry_name() {
		return "_license_{$this->slug}_expiry";
	}

	// option_key in the wp_options table
	public function get_license_meta_name() {
		return "_license_{$this->slug}_meta";
	}

	// option_key in the wp_options table
	public function get_license_item_id_name() {
		return "_license_{$this->slug}_item_id";
	}

	public function get_license_item_id() {
		return get_option( $this->get_license_item_id_name() );
	}

	public function get_license_key() {
		return get_option( $this->get_license_key_name() );
	}

	public function get_license_status() {
		return get_option( $this->get_license_status_name() );
	}

	public function get_license_expiry() {
		$expiry = get_option( $this->get_license_expiry_name() );
		
		if( $expiry == 4765132799 ) return 'lifetime';

		return date_i18n( get_option( 'date_format' ), $expiry );
	}

	public function get_license_meta() {
		return get_option( $this->get_license_meta_name() );
	}

	public function _is_activated() {
		return $this->get_license_key() != '';
	}

	// backward compatibility
	public function _is_active() {
		return $this->_is_activated();
	}

	public function _is_invalid() {
		return $this->get_license_status() != 'valid';
	}

	public function _is_expired() {
		return time() >= get_option( $this->get_license_expiry_name() );
	}

	public function _is_forced() {
		return apply_filters( 'pluggable-is_forced', ( $this->_is_invalid() || $this->_is_expired() ), $this->plugin );
	}
}
