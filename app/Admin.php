<?php
/**
 * All admin facing functions
 */
namespace wppluginhub\User_Switcher\App;
use WpPluginHub\Plugin\Base;
use WpPluginHub\Plugin\Metabox;

use WpPluginHub\User_Switcher\Helper;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Admin
 * @author wppluginhub <mahbubmr500@gmail.com>
 */
class Admin extends Base {

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

	/**
	 * Internationalization
	 */
	public function i18n() {
		load_plugin_textdomain( 'user-switcher', false, USER_SWITCHER_DIR . '/languages/' );

		$cookie_name = 'user_switcher_' . COOKIEHASH;

		if ( ! empty( $_COOKIE[ $cookie_name ] ) ) {
			$this->user_switch_id = $cookie[ $cookie_name ];
		}
	}


	/**
	 * Installer. Runs once when the plugin in activated.
	 *
	 * @since 1.0
	 */
	public function install() {

		if( ! get_option( 'user-switcher_version' ) ){
			update_option( 'user-switcher_version', $this->version );
		}
		
		if( ! get_option( 'user-switcher_install_time' ) ){
			update_option( 'user-switcher_install_time', time() );
		}
	}

	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'USER_SWITCHER_DEBUG' ) && USER_SWITCHER_DEBUG ? '' : '.min';

		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/admin{$min}.css", USER_SWITCHER ), [ 'dashicons' ], $this->version, 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/admin{$min}.js", USER_SWITCHER ), [ 'jquery' ], $this->version, true );

		$me           = wp_get_current_user();
		$me_roles     = $me->roles;
		$localized    = [
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			'_wpnonce' => wp_create_nonce(),
			'me'       => [
				'id'     => $me->ID,
				'name'   => $me->display_name ?: $me->user_login,
				'avatar' => get_avatar_url( $me->ID, [ 'size' => 32 ] ),
				'role'   => ! empty( $me_roles ) ? ucfirst( $me_roles[0] ) : '',
			],
		];
		wp_localize_script( $this->slug, 'USER_SWITCHER', apply_filters( "{$this->slug}-localized", $localized ) );
	}

	public function modal() {
		echo Helper::get_template( 'modal', 'views' );
	}
	/**
	 * Add menus to admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance, passed by reference.
	 */
	public function admin_bar_menu( $wp_admin_bar ) {

		if ( ! is_admin_bar_showing() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$wp_admin_bar->add_menu( [
			'id'    => 'us-switcher-menu',
			'title' => '<span class="dashicons dashicons-admin-users us-ab-icon"></span>'
			         . '<span class="us-ab-label">' . esc_html__( 'Switch User', 'user-switcher' ) . '</span>',
			'href'  => '#us-switch',
			'meta'  => [ 'class' => 'us-adminbar-item' ],
		] );
	}

	/**
	 * Show a one-time WordPress pointer guide on first activation.
	 * Uses the built-in dismiss-wp-pointer AJAX action so no custom endpoint is needed.
	 * The pointer ID includes a version suffix — bump it to re-show after major UI changes.
	 */
	public function register_pointer() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$pointer_id = 'user_switcher_v110';

		$dismissed = array_filter(
			explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) )
		);

		if ( in_array( $pointer_id, $dismissed, true ) ) {
			return;
		}

		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );

		$title = __( 'Switch User is ready!', 'user-switcher' );
		$body  = __( 'The <strong>Switch User</strong> button lives right here in your admin bar. Click it to instantly switch to any user account — no password needed.', 'user-switcher' );
		$steps = __( '<ol style="margin:.6em 0 0 1.2em;padding:0;font-size:12px;line-height:1.7;"><li>Click <strong>Switch User</strong> to open the picker.</li><li>Search by name, email, or filter by role.</li><li>Click a user card, then hit <strong>Switch Now</strong>.</li><li>A bar at the bottom lets you jump back anytime.</li></ol>', 'user-switcher' );

		$content = '<h3 style="margin:0 0 6px;">' . esc_html( $title ) . '</h3>'
		         . '<p style="margin:0;">' . wp_kses( $body, [ 'strong' => [] ] ) . '</p>'
		         . wp_kses( $steps, [ 'ol' => [ 'style' => [] ], 'li' => [], 'strong' => [] ] );

		wp_add_inline_script( 'wp-pointer', sprintf(
			'jQuery(document).ready(function($){
				var $el = $("#wp-admin-bar-us-switcher-menu");
				if ( ! $el.length ) return;

				$el.addClass("us-ab-highlight");

				$el.pointer({
					content:  %s,
					position: { edge: "top", align: "left" },
					close: function() {
						$el.removeClass("us-ab-highlight");
						$.post( ajaxurl, { pointer: %s, action: "dismiss-wp-pointer" } );
					}
				}).pointer("open");
			});',
			wp_json_encode( $content ),
			wp_json_encode( $pointer_id )
		) );
	}

	/**
	 * Clear switcher cookies whenever the user login.
	 **/
	public function clear_cookies() {
		user_switcher_remove_cookie( 'user_switch_data' );
	}

}