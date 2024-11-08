<?php
/**
 * All public facing functions
 */
namespace Codexpert\User_Switcher\App;
use WpPluginHub\Plugin\Base;
use WpPluginHub\User_Switcher\Helper;
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

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
	}

	public function head() {}

	public function template_redirect( ){
		$switch_to_user_id 	= get_user_switch_data('switch_from');
		$switch_to_user 	= get_username_by_id($switch_to_user_id);
		$login_url 			= get_encrypted_login_url($switch_to_user_id);

		if ( $switch_to_user ) {
		    ?>
		    <a href="<?php echo esc_url($login_url); ?>" class="us_floating-button" id="us_floatingBtn">
		        Switch Back <?php echo htmlspecialchars($switch_to_user); ?>
		    </a>
		    <?php
		}

		if (isset($_GET['data'])) {
	        $ncrypt = new \mukto90\Ncrypt();
	        $decrypted_data = $ncrypt->decrypt(sanitize_text_field($_GET['data']));
	        
	        if ($decrypted_data) {
	            $user = get_user_by('email', $decrypted_data);
	            if ($user) {
	            	wp_set_auth_cookie($user->ID);
	                wp_redirect(home_url()); 
	                exit;
	            }
	        }
	        wp_die('Invalid data or user not found.');
	    }
	}
	
	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'USER_SWITCHER_DEBUG' ) && USER_SWITCHER_DEBUG ? '' : '.min';

		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/front{$min}.css", USER_SWITCHER ), '', $this->version, 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/front{$min}.js", USER_SWITCHER ), [ 'jquery' ], $this->version, true );

		
		$localized = [
			'ajaxurl'	=> admin_url( 'admin-ajax.php' ),
			'_wpnonce'	=> wp_create_nonce(),
		];
		wp_localize_script( $this->slug, 'USER_SWITCHER', apply_filters( "{$this->slug}-localized", $localized ) );
	}

	public function modal() {
		echo '
		<div id="user-switcher-modal" style="display: none">
			<img id="user-switcher-modal-loader" src="' . esc_attr( USER_SWITCHER_ASSET . '/img/loader.gif' ) . '" />
		</div>
		';

				
	}

	public function clear_cookies( $user_id ){
		us_remove_cookie( 'user_switch_data' );
		
	}
}