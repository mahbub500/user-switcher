<?php
/**
 * All admin facing functions
 */
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

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->server	= $this->plugin['server'];
		$this->version	= $this->plugin['Version'];
	}

	/**
	 * Internationalization
	 */
	public function i18n() {
		load_plugin_textdomain( 'user-switcher', false, USER_SWITCHER_DIR . '/languages/' );
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
		
		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/admin{$min}.css", USER_SWITCHER ), '', $this->version, 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/admin{$min}.js", USER_SWITCHER ), [ 'jquery' ], $this->version, true );

		wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js', [ 'jquery' ], null, true );
		wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css' );

		$localized = [
			'ajaxurl'	=> admin_url( 'admin-ajax.php' ),
			'_wpnonce'	=> wp_create_nonce(),
		];
		wp_localize_script( $this->slug, 'USER_SWITCHER', apply_filters( "{$this->slug}-localized", $localized ) );	
	}

	public function footer_text( $text ) {
		if( get_current_screen()->parent_base != $this->slug ) return $text;

		return sprintf( __( 'Built with %1$s by the folks at <a href="%2$s" target="_blank">Codexpert, Inc</a>.' ), '&hearts;', 'https://codexpert.io' );
	}

	public function modal() {
		echo '
		<div id="user-switcher-modal" style="display: none">
			<img id="user-switcher-modal-loader" src="' . esc_attr( USER_SWITCHER_ASSET . '/img/loader.gif' ) . '" />
		</div>';

		 ?>
	     <div id="us-switcher-modal" style="display: none;">
	        <div class="us-switcher-modal-content">
	            <span class="us-switcher-close">&times;</span>	            
	            <h2><?php echo esc_html(__('User Switcher')); ?></h2>
	            <p><?php echo esc_html(__('Search users by name, display name, or email.')); ?></p>
	            <form id="us-switcher-form">
	                <select class="us-user-name qlfv-user-onchange" id="user-info">
                        </select>
					<p>
						<input type="submit" id="us-switcher-button" value="<?php _e( 'Go', 'user-switcher' ); ?>" class="button button-primary " />
                    </p>
				</form>
	            <div id="us-switcher-results"></div>
	        </div>
	    </div>
    <?php
	}
	/**
	 * Add menus to admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance, passed by reference.
	 */
	public function admin_bar_menu( $wp_admin_bar ) {
	    $wp_admin_bar->add_menu( array(
	        'id'    => 'us-switcher-menu',
	        'title' => '<span class="us-icon us-main-menu">' . __( 'User Switcher' ) . '</span>',
	    ) );

	    $wp_admin_bar->add_menu(array(
	        'parent' => 'us-switcher-menu',
	        'id'     => 'us-to-guest',
	        'title'  => '<button id="switch-to-guest-button"><span class="us-icon us-guest-user">' . __('Switch to User') . '</span></button>',
	        'meta'   => array(
	            'html' => '',
	        ),
	    ));
	}

}