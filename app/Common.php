<?php
/**
 * All public facing functions
 */
namespace wppluginhub\User_Switcher\App;
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
 * @author wppluginhub <mahbubmr500@gmail.com>
 */
class Common extends Base {

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

	public function show_switch_button(){
		$switch_to_user_id 	= stu_user_switch_data('switch_from');
		$switch_to_user 	= get_username_by_id($switch_to_user_id);
		$login_url 			= get_encrypted_login_url($switch_to_user_id);

		if ( $switch_to_user ) {
		    ?>
		    <a href="<?php echo esc_url( $login_url ); ?>" class="us_floating-button" id="us_floatingBtn">
		        <?php echo esc_html__( 'Switch Back', 'switch-to-user' ) . ' ' . esc_html( $switch_to_user ); ?>
		    </a>
		    <?php
		}

	}
}