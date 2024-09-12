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
 * @subpackage Installer
 * @author Codexpert <hi@codexpert.io>
 */
class Installer extends Base {

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

	/**
	 * Installer. Runs once when the plugin in activated.
	 *
	 * @since 1.0
	 */
	public function install() {

		/**
		 * Schedule an event
		 */
		if ( ! wp_next_scheduled( 'codexpert-daily' ) ) {
		    wp_schedule_event( date_i18n( 'U' ), 'daily', 'codexpert-daily' );
		}
	}

	/**
	 * Uninstaller. Runs once when the plugin in deactivated.
	 *
	 * @since 1.0
	 */
	public function uninstall() {
		
		/**
		 * Remove scheduled hooks
		 */
		wp_clear_scheduled_hook( 'codexpert-daily' );
	}

	public function update() {
		$new_version = $this->version;
		$old_version = get_option( "{$this->slug}_db-version" );

		if( $new_version == $old_version ) return;

		update_option( "{$this->slug}_db-version", $this->version, false );

		// upgrader actions
		do_action( "{$this->slug}_version-updated", $new_version, $old_version );
	}
}