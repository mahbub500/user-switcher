<?php
namespace Codexpert\User_Switcher\App;

use Codexpert\Plugin\Base;
use Codexpert\Plugin\Metabox;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Admin
 * @author Codexpert <hi@codexpert.io>
 */
class Admin extends Base {

	public $plugin;
	
	public $slug;

	public $name;

	public $version;

	public $user_switch_id = 0;
	public $is_customizer = false;



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
	 * Internationalization
	 */
	public function i18n() {
		load_plugin_textdomain( 'user-switcher', false, USER_SWITCHER_DIR . '/languages/' );
	}

	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'USER_SWITCHER_DEBUG' ) && USER_SWITCHER_DEBUG ? '' : '.min';
		
		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/admin{$min}.css", USER_SWITCHER_FILE ), '', $this->version, 'all' );
		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/admin{$min}.js", USER_SWITCHER_FILE ), [ 'jquery' ], $this->version, true );

	    wp_enqueue_script( "{$this->slug}-react", plugins_url( 'spa/admin/build/index.js', USER_SWITCHER_FILE ), [ 'wp-element' ], '1.0.0', true );

	    $localized = [
	    	'homeurl'		=> get_bloginfo( 'url' ),
	    	'adminurl'		=> admin_url(),
	    	'asseturl'		=> USER_SWITCHER_ASSETS,
	    	'ajaxurl'		=> admin_url( 'admin-ajax.php' ),
	    	'_wpnonce'		=> wp_create_nonce(),
	    	'api_base'		=> get_rest_url(),
	    	'rest_nonce'	=> wp_create_nonce( 'wp_rest' ),
	    ];
	    
	    wp_localize_script( $this->slug, 'USER_SWITCHER', apply_filters( "{$this->slug}-localized", $localized ) );
	}

	public function admin_menu() {

		add_menu_page(
			__( 'User Switcher', 'user-switcher' ),
			__( 'User Switcher', 'user-switcher' ),
			'manage_options',
			'user-switcher',
			function(){},
			'dashicons-wordpress',
			25
		);

		add_submenu_page(
			'user-switcher',
			__( 'Help', 'user-switcher' ),
			__( 'Help', 'user-switcher' ),
			'manage_options',
			'user-switcher-help',
			function() {
				printf( '<div id="user-switcher_help"><p>%s</p></div>', __( 'Loading..', 'user-switcher' ) );
			}
		);

		add_submenu_page(
			'user-switcher',
			__( 'License', 'user-switcher' ),
			__( 'License', 'user-switcher' ),
			'manage_options',
			'user-switcher-license',
			function() {
				printf( '<div id="user-switcher_license"><p>%s</p></div>', __( 'Loading..', 'user-switcher' ) );
			}
		);
	}

	public function action_links( $links ) {
		$this->admin_url = admin_url( 'admin.php' );

		$new_links = [
			'settings'	=> sprintf( '<a href="%1$s">' . __( 'Settings', 'user-switcher' ) . '</a>', add_query_arg( 'page', $this->slug, $this->admin_url ) )
		];
		
		return array_merge( $new_links, $links );
	}

	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		
		if ( $this->plugin['basename'] === $plugin_file ) {
			$plugin_meta['help'] = '<a href="https://help.codexpert.io/" target="_blank" class="cx-help">' . __( 'Help', 'user-switcher' ) . '</a>';
		}

		return $plugin_meta;
	}

	public function update_cache( $post_id, $post, $update ) {
		wp_cache_delete( "us_{$post->post_type}", 'us' );
	}

	public function footer_text( ) {
		
		if ( $this->is_customizer ) {
			return; // If in customizer, don't show the switcher
		}
		?>
		<script type="text/template" id="user-switcher-window">
		<div class="user-switcher-content">
			<h2><%=userSwitcher.l8n.title%></h2>
			<a class="us-close-icon" title="<%=userSwitcher.l8n.closed%>"></a>
			<p class="description"><%=userSwitcher.l8n.description%></p>
			<form method="post">
				<input type="text" class="us-search-key" name="key" placeholder="<%=userSwitcher.l8n.search_placeholder%>" />
				<button type="submit" class="us-search-submit"><%=userSwitcher.l8n.submit_button%></button>
			</form>
			<div id="us-notice-box"></div>
			<div id="us-search-results"></div>
			<div id="us-navs">
				<button type="button" class="us-prev-button">&laquo; <%=userSwitcher.l8n.prev%></button>
				<button type="button" class="us-next-button"><%=userSwitcher.l8n.next%> &raquo;</button>
			</div>
		</div>
		</script>
		<script type="text/template" id="user-no-admin-bar">
		<div class="us-no-admin-content">
			<p class="description"><%=userSwitcher.l8n.guest_notice_info%></p>
			<a class="us-back">&larr; <%=userSwitcher.l8n.switch_back%></a>
			<a class="us-right us-search"><%=userSwitcher.l8n.search_users%> &rarr;</a>
			</div>
		</script>
		<script type="text/template" id="user-no-admin-bar-admin">
			<div class="us-no-admin-content us-no-admin">
				<% if( 'guest' !== userSwitcher.switch_to ) { %>
				<p class="us-guest-user"><%=userSwitcher.l8n.switch_to_guest%></p>
				<% } %>
				<% if ( '0' !== userSwitcher.switch_to ) { %>
				<p class="us-switch-back"><%=userSwitcher.l8n.switch_back%></p>
				<% } %>
				<p class="us-search-user"><%=userSwitcher.l8n.search_users%></p>
				<p class="description"><%=userSwitcher.l8n.name%></p>
			</div>
		</script>
		<?php
	}

	public function modal() {
		echo '
		<div id="user-switcher-modal" style="display: none">
			<img id="user-switcher-modal-loader" src="' . esc_attr( USER_SWITCHER_ASSETS . '/img/loader.gif' ) . '" />
		</div>';
	}

	public function set_cookie(){
		$cookie = $_COOKIE;
		$cookie_name = 'user_switcher_' . COOKIEHASH;

		if ( ! empty( $cookie[ $cookie_name ] ) ) {
			$this->user_switch_id = $cookie[ $cookie_name ];
		}
	}

	/**
	 * Add menus to admin bar
	 **/
	public function admin_bar_menu( $admin_bar_menu ) {
		$admin_bar_menu->add_menu( array(
			'id' => 'us-switcher-menu',
			 'title' => '<span class="us-icon us-main-menu">' . __( 'User Switcher' ) . '</span>',
		) );

		$admin_bar_menu->add_menu( array(
			'parent' => 'us-switcher-menu',
			'id' => 'us-to-guest',
			'title' => '<span class="us-icon us-guest-user">' . __( 'Switch to Guest User' ) . '</span>',
		) );

		if ( ! empty( $this->user_switch_id ) ) {
			$admin_bar_menu->add_menu( array(
				'parent' => 'us-switcher-menu',
				'id' => 'us-switch-back',
				'title' => '<span class="us-icon us-switch-back">' . __( 'Switch Back' ) . '</span>',
			) );
		}

		$admin_bar_menu->add_menu( array(
			'parent' => 'us-switcher-menu',
			'id' => 'us-search-users',
			'title' => '<span class="us-icon us-search-users">' . __( 'Search Users' ) . '</span>',
		) );
	}

	public function validate_current_user(){
		global $current_user;

		// Check if switch ID is present
		if ( ! empty( $this->user_switch_id ) ) {
			$fake_user = new WP_User( $this->user_switch_id );
			$current_user = $fake_user;
		}
	}
}