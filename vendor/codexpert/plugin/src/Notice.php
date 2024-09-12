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
 * @subpackage Notice
 * @author Codexpert <hi@codexpert.io>
 */
class Notice extends Base {
	
	public $server;
	
	public $slug;
	
	public $name;

	public function __construct( $plugin ) {

		$this->plugin 	= $plugin;

		$this->server 	= $this->plugin['server'];
		$this->slug 	= $this->plugin['TextDomain'];
		$this->name 	= $this->plugin['Name'];
		
		$this->hooks();
	}

	public function hooks(){
		$this->action( 'admin_enqueue_scripts', 'enqueue_scripts', 99 );
		$this->action( 'plugins_loaded', 'collect' );
		$this->action( 'admin_notices', 'print' );
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'codexpert-product-notice', plugins_url( 'assets/css/notice.css', __FILE__ ), [], $this->plugin['Version'] );
	}

	public function collect() {

		global $cx_notices;

		if( isset( $this->plugin['min_wp'] ) && version_compare( get_bloginfo( 'version' ), $this->plugin['min_wp'], '<' ) ) {
			$notice = '<p>' . sprintf( __( '<strong>%s</strong> requires <i>WordPress version %s</i> or higher. You have <i>version %s</i> installed.', 'codexpert' ), $this->name, $this->plugin['min_wp'], get_bloginfo( 'version' ) ) . '</p>';
			self::add( $notice );
		}

		if( isset( $this->plugin['min_php'] ) && version_compare( PHP_VERSION, $this->plugin['min_php'], '<' ) ) {
			$notice = '<p>' . sprintf( __( '<strong>%s</strong> requires <i>PHP version %s</i> or higher. You have <i>version %s</i> installed.', 'codexpert' ), $this->name, $this->plugin['min_php'], PHP_VERSION ) . '</p>';
			self::add( $notice );
		}

		/**
		 * Dependencies
		 *
		 * @since 1.0
		 */
		$installed_plugins = get_plugins();
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		if( isset( $this->plugin['depends'] ) && is_array( $this->plugin['depends'] ) ) :
		foreach ( $this->plugin['depends'] as $plugin => $plugin_name ) {
			if( !in_array( $plugin, $active_plugins ) ) {

				$action_links = $this->_action_links( $plugin );
				$button_text = array_key_exists( $plugin, $installed_plugins ) ? __( 'Activate', 'codexpert' ) : __( 'Install', 'codexpert' );
				$action_link = array_key_exists( $plugin, $installed_plugins ) ? $action_links['activate'] : $action_links['install'];
			
				$notice = '<p>' . sprintf( __( '<strong>ALERT:</strong> In order for <strong>%1$s</strong> to run properly, <strong>%2$s</strong> needs to be activated. <a href="%3$s">%4$s %2$s Now</a>.', 'codexpert' ), $this->name, $plugin_name, $action_link, $button_text ) . '</p>';
				
				self::add( $notice, 'error', true );
			}
		}
		endif;
		
	}

	public function print() {

		if( did_action( 'cx-notice' ) || ! current_user_can( 'manage_options' ) ) return;
		do_action( 'cx-notice' );

		global $cx_notices;

		if( is_array( $cx_notices ) && count( $cx_notices ) ) {
			foreach( $cx_notices as $notice ) {
				$is_dismissible = isset( $notice['dismissible'] ) && $notice['dismissible'] ? ' is-dismissible': '';
				echo "
					<div class='notice notice-{$notice['type']}{$is_dismissible} cx-notice cx-shadow'>
						" . $notice['text'] . "
					</div>
				";
			}
		}
	}

	public static function add( $text = '', $type = 'error', $dismissible = false )  {
		global $cx_notices;

		$cx_notices[] = [
			'text'			=> $text,
			'type'			=> $type,
			'dismissible'	=> $dismissible,
		];
	}

	public function _action_links( $plugin, $action = '' ) {

		$exploded	= explode( '/', $plugin );
		$slug		= $exploded[0];

		$links = [
			'install'		=> wp_nonce_url( admin_url( "update.php?action=install-plugin&plugin={$slug}" ), "install-plugin_{$slug}" ),
			'update'		=> wp_nonce_url( admin_url( "update.php?action=upgrade-plugin&plugin={$plugin}" ), "upgrade-plugin_{$plugin}" ),
			'activate'		=> wp_nonce_url( admin_url( "plugins.php?action=activate&plugin={$plugin}&plugin_status=all&paged=1&s" ), "activate-plugin_{$plugin}" ),
			'deactivate'	=> wp_nonce_url( admin_url( "plugins.php?action=deactivate&plugin={$plugin}&plugin_status=all&paged=1&s" ), "deactivate-plugin_{$plugin}" ),
		];

		if( $action != '' && array_key_exists( $action, $links ) ) return $links[ $action ];

		return $links;
	}
}