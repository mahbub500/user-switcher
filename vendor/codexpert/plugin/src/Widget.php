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
 * @subpackage Widget
 * @author Codexpert <hi@codexpert.io>
 */
class Widget extends Base {

	public static $_instance;
	
	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		$this->action( 'wp_dashboard_setup', 'dashboard_widget', 99 );
	}
	
	/**
	 * Adds a widget in /wp-admin/index.php page
	 *
	 * @since 1.0
	 */
	public function dashboard_widget() {
		wp_add_dashboard_widget( 'cx-overview', __( 'WordPress Blogs & Tutorials', 'cx-plugin' ), [ $this, 'callback_dashboard_widget' ] );

		// Move our widget to top.
		global $wp_meta_boxes;

		$dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
		$ours = [
			'cx-overview' => $dashboard['cx-overview'],
		];

		$wp_meta_boxes['dashboard']['normal']['core'] = array_merge( $ours, $dashboard );
	}

	/**
	 * Call back for dashboard widget in /wp-admin/
	 *
	 * @see dashboard_widget()
	 *
	 * @since 1.0
	 */
	public function callback_dashboard_widget() {
		$utm = [ 'utm_source' => 'dashboard', 'utm_medium' => 'metabox', 'utm_campaign' => 'blog-post' ];
		
		echo '<ul id="cx-posts"></ul>'; // populated with React

		$_links = apply_filters( 'cx-overview_links', [
			'products'	=> [
				'url'		=> add_query_arg( $utm, 'https://codexpert.io/products/' ),
				'label'		=> __( 'Our Plugins', 'cx-plugin' ),
				'target'	=> '_blank',
			],
			'hire'	=> [
				'url'		=> add_query_arg( $utm, 'https://codexpert.io/blog/' ),
				'label'		=> __( 'Blog', 'cx-plugin' ),
				'target'	=> '_blank',
			],
		] );

		$footer_links = [];
		foreach ( $_links as $id => $link ) {
			$_has_icon = ( $link['target'] == '_blank' ) ? '<span class="screen-reader-text">' . __( '(opens in a new tab)', 'cx-plugin' ) . '</span> <span aria-hidden="true" class="dashicons dashicons-external"></span>' : '';

			$footer_links[] = "<a href='{$link['url']}' target='{$link['target']}'>{$link['label']}{$_has_icon}</a>";
		}

		echo '<p class="community-events-footer">' . implode( ' | ', $footer_links ) . '</p>';
	}
}