<?php
namespace Pluggable\Marketing;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Marketing
 * 
 * @subpackage Feature
 * 
 * @author Pluggable <hi@pluggable.io>
 */
class Feature {
	
	public $plugin;
	
	public $slug;
	
	public $args;
	
	public $server;
	
	public $name;

	public $featured_plugins;

	public $reserved_plugins;

	public function __construct( $plugin, $args = [] ) {

		if( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		
		$this->plugin 	= get_plugin_data( $plugin );

		$this->args = wp_parse_args( $args, [
			'server'	=> 'https://my.pluggable.io',
			'featured'	=> [
				'image-sizes',
				'wc-affiliate',
				'restrict-elementor-widgets',
				'coschool',
				'woolementor',
			],
			'reserved'	=> [
				'akismet',
				'classic-editor',
			],
		] );

		$this->server 	= $this->args['server'];
		$this->slug 	= $this->plugin['TextDomain'];
		$this->name 	= $this->plugin['Name'];

		$this->featured_plugins = $this->args['featured']; // last item in this array will show up first

		$this->reserved_plugins = $this->args['reserved']; // last item in this array will show up first
		
		$this->hooks();
	}

	public function hooks() {
		add_filter( 'plugins_api_result', [ $this, 'alter_api_result' ], 10, 3 );
	}

	/**
	 * Alter API result
	 */
	public function alter_api_result( $res, $action, $args ) {

		// some $vars
		$searching		= isset( $_REQUEST['s'] ) && $_REQUEST['s'] != '';
		$searching_wc	= $searching && strpos( 'woocommerce', $_REQUEST['s'] ) !== false;
		$searching_el	= $searching && strpos( 'elementor', $_REQUEST['s'] ) !== false;

		// not the Featured or Search tab
		if ( isset( $_GET['tab'] ) && ! in_array( $_GET['tab'], [ 'featured', 'search' ] ) ) return $res;

		// searching for WooCommerce
		if ( $searching_wc ) {
			$this->featured_plugins = [
				'wc-affiliate',
				'restrict-elementor-widgets',
				'woolementor',
			];
			$this->reserved_plugins = [ 'woocommerce' ];
		}

		// searching for Elementor
		elseif ( $searching_el ) {
			$this->featured_plugins = [
				'restrict-elementor-widgets',
				'woolementor',
			];
			$this->reserved_plugins = [ 'elementor' ];
		}

		// searching for something else
		elseif ( $searching ) {
			$this->featured_plugins = $this->reserved_plugins = [];
		}

		remove_filter( 'plugins_api_result', [ $this, 'alter_api_result' ] );
		
		// unset reserved plugins
		if( isset( $res->plugins ) && count( $res->plugins ) > 0 ) {
			foreach ( $res->plugins as $index => $plugin ) {
				if( is_array( $plugin ) && in_array( $plugin['slug'], $this->reserved_plugins ) ) {
					unset( $res->plugins[ $index ] );
				}
			}
		}

		// add ours
		if( count( $this->featured_plugins ) > 0 ) {
			foreach ( $this->featured_plugins as $featured_plugin ) {
				$res = $this->add_to_list( $featured_plugin, $res );
			}
		}

		// re-add reserved
		if( count( $this->reserved_plugins ) > 0 ) {
			foreach ( $this->reserved_plugins as $reserved ) {
				$res = $this->add_to_list( $reserved, $res );
			}
		}

		return $res;
	}

	/**
	 * Add a plugin to the fav list
	 */
	public function add_to_list( $plugin_slug, $res ) {
		if ( ! empty( $res->plugins ) && is_array( $res->plugins ) ) {
			foreach ( $res->plugins as $plugin ) {
				if ( is_object( $plugin ) && ! empty( $plugin->slug ) && $plugin->slug == $plugin_slug ) {
					return $res;
				}
			}
		}

		if ( isset( $res->plugins ) && is_array( $res->plugins ) && $plugin_info = get_transient( 'cx-plugin-info-' . $plugin_slug ) ) {
			array_unshift( $res->plugins, $plugin_info );
		}
		else {
			$plugin_info = plugins_api( 'plugin_information', array(
				'slug'   => $plugin_slug,
				'is_ssl' => is_ssl(),
				'fields' => array(
					'banners'           => true,
					'reviews'           => true,
					'downloaded'        => true,
					'active_installs'   => true,
					'icons'             => true,
					'short_description' => true,
				)
			) );

			if ( ! is_wp_error( $plugin_info ) ) {
				$res->plugins[] = $plugin_info;
				set_transient( 'cx-plugin-info-' . $plugin_slug, $plugin_info, DAY_IN_SECONDS * 7 );
			}
		}

		return $res;
	}
}
