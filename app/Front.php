<?php
namespace Codexpert\User_Switcher\App;

use Codexpert\Plugin\Base;
use Codexpert\User_Switcher\Helper;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Front
 * @author Codexpert <hi@codexpert.io>
 */
class Front extends Base {

	public $plugin;
	
	public $slug;

	public $name;

	public $version;
	public $is_customizer;

	/**
	 * Constructor function
	 */
	public function __construct() {
		$this->plugin	= USER_SWITCHER;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
	}

	public function add_admin_bar( $admin_bar ) {
		if( ! current_user_can( 'manage_options' ) ) return;

		$admin_bar->add_menu( [
			'id'    => $this->slug,
			'title' => $this->name,
			'href'  => add_query_arg( 'page', $this->slug, admin_url( 'admin.php' ) ),
			'meta'  => [
				'title' => $this->name,            
			],
		] );
	}

	public function head() {}
	
	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'USER_SWITCHER_DEBUG' ) && USER_SWITCHER_DEBUG ? '' : '.min';

		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/front{$min}.css", USER_SWITCHER_FILE ), '', $this->version, 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/front{$min}.js", USER_SWITCHER_FILE ), [ 'jquery' ], $this->version, true );

		wp_enqueue_script( "{$this->slug}-react", plugins_url( 'spa/front/build/index.js', USER_SWITCHER_FILE ), [ 'wp-element' ], '1.0.0', true );
		
		$localized = [
			'ajaxurl'		=> admin_url( 'admin-ajax.php' ),
			'_wpnonce'		=> wp_create_nonce(),
			'rest_nonce'	=> wp_create_nonce( 'wp_rest' ),
		];
		
		wp_localize_script( $this->slug, 'USER_SWITCHER', apply_filters( "{$this->slug}-localized", $localized ) );
	}

	public function modal() {
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
}