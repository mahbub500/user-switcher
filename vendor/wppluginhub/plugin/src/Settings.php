<?php
namespace WpPluginHub\Plugin;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @package Plugin
 * @subpackage Settings
 * @author WpPluginHub <mahbubmr500@gmil.com>
 */
class Settings extends Fields {
	
	/**
	 * @var array $config
	 */
	public $config;

	/**
	 * @var array $sections
	 */
	public $sections;

	public function __construct( $args = [] ) {

		// default values
		$defaults = [
			'id'			=> 'wph-settings',
			'label'			=> __( 'Settings' ),
			'priority'      => 10,
			'capability'    => 'manage_options',
			'icon'          => 'dashicons-wordpress',
			'position'      => 25,
			'sections'		=> [],
		];

		$this->config = wp_parse_args( apply_filters( 'wph-settings-args', $args ), $defaults );
		$this->sections	= apply_filters( 'wph-settings-sections', $this->config['sections'] );

		parent::hooks();
		self::hooks();
	}

	public function hooks() {
		$this->action( 'admin_enqueue_scripts', 'enqueue_scripts', 99 );
		$this->action( 'admin_menu', 'admin_menu', $this->config['priority'] );
		$this->priv( 'wph-settings', 'save_settings' );
		$this->priv( 'wph-reset', 'reset_settings' );
	}

	public function enqueue_scripts() {

		if( ! isset( $_GET['page'] ) || $_GET['page'] != $this->config['id'] ) return;

		parent::enqueue_scripts();
    }

	public function admin_menu() {
		if( isset( $this->config['parent'] ) && $this->config['parent'] != '' ) {
			add_submenu_page( $this->config['parent'], $this->config['header'], $this->config['label'], $this->config['capability'], $this->config['id'], array( $this, 'callback_fields' ) );
		}
		else {
			add_menu_page( $this->config['header'], $this->config['label'], $this->config['capability'], $this->config['id'], array( $this, 'callback_fields' ), $this->config['icon'], $this->config['position'] );
		}
	}

	public function save_settings() {
		if( ! wp_verify_nonce( $_POST['_wpnonce'] ) || ! current_user_can( 'manage_options' ) ) {
			wp_send_json( array( 'status' => 0, 'message' => __( 'Unauthorized!' ) ) );
		}

		$posted_data	= $this->sanitize( $_POST, 'array' );

		$option_name	= $posted_data['option_name'];
		$page_load		= $posted_data['page_load'];
		$is_savable		= apply_filters( 'wph-settings-savable', true, $option_name, $posted_data );

		if( ! $is_savable ) {
			wp_send_json( apply_filters( 'wph-settings-response', array( 'status' => -1, 'message' => __( 'Ignored' ) ), $this->sanitize( $_POST, 'array' ) ) );
		}

		unset( $posted_data['action'] );
		unset( $posted_data['option_name'] );
		unset( $posted_data['page_load'] );
		unset( $posted_data['_wpnonce'] );
		unset( $posted_data['_wp_http_referer'] );

		update_option( $option_name, $posted_data );
		
		do_action( 'wph-settings-saved', $option_name, $posted_data );
		
		wp_send_json( apply_filters( 'wph-settings-response', array( 'status' => 1, 'message' => __( 'Settings Saved!' ), 'page_load' => $page_load ), $posted_data ) );
	}

	public function reset_settings() {
		if( ! wp_verify_nonce( $_POST['_wpnonce'] ) || ! current_user_can( 'manage_options' ) ) {
			wp_send_json( array( 'status' => 0, 'message' => __( 'Unauthorized!' ) ) );
		}

		$posted_data	= $this->sanitize( $_POST, 'array' );

		$option_name	= $posted_data['option_name'];
		$is_savable		= apply_filters( 'wph-settings-resetable', true, $option_name, $posted_data );

		if( ! $is_savable ) {
			wp_send_json( apply_filters( 'wph-settings-response', array( 'status' => -1, 'message' => __( 'Ignored' ) ), $posted_data ) );
		}

		delete_option( $option_name );

		do_action( 'wph-settings-reset', $option_name );

		wp_send_json( apply_filters( 'wph-settings-response', array( 'status' => 1, 'message' => __( 'Settings Reset!' ) ), $posted_data ) );
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
		return parent::sanitize( $input, $type );
	}
}