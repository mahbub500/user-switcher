<?php
namespace Codexpert\User_Switcher\App;

use Codexpert\Plugin\Base;
use Codexpert\Plugin\Setup;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Wizard
 * @author Codexpert <hi@codexpert.io>
 */
class Wizard extends Base {

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

	public function action_links( $links ) {
		$this->admin_url = admin_url( 'admin.php' );

		$new_links = [
			'wizard'	=> sprintf( '<a href="%1$s">%2$s</a>', add_query_arg( [ 'page' => "{$this->slug}_setup" ], $this->admin_url ), __( 'Setup Wizard', 'user-switcher' ) )
		];
		
		return array_merge( $new_links, $links );
	}

	public function render() {

		// force setup once
		if( get_option( "{$this->slug}_setup" ) != 1 ) {
			update_option( "{$this->slug}_setup", 1 );
			wp_safe_redirect( add_query_arg( [ 'page' => "{$this->slug}_setup" ], admin_url( 'admin.php' ) ) );
			exit;
		}

		$this->plugin['steps'] = [
			'one'	=> [
				'label'		=> __( 'One' ),
				'callback'	=> [ $this, 'callback_step_1' ],
				'action'	=> [ $this, 'save_one' ],
			],
			'two'	=> [
				'label'		=> __( 'Two' ),
				'callback'	=> [ $this, 'callback_step_2' ],
				'action'	=> [ $this, 'save_two' ],
			],
			'three'	=> [
				'label'		=> __( 'Three' ),
				'template'	=> USER_SWITCHER_DIR . '/views/setup-wizard/step-3.php',
				'action'	=> [ $this, 'save_three' ],
			],
			'four'	=> [
				'label'		=> __( 'Four' ),
				'content'	=> __( 'This is Step-4 of the setup wizard! Added as a string!', 'user-switcher' ),
				'action'	=> [ $this, 'save_three' ],
				'redirect'	=> add_query_arg( [ 'page' => "{$this->slug}" ], admin_url( 'admin.php' ) )
			],
		];

		new Setup( $this->plugin );
	}

	public function callback_step_1() {
		_e( 'This is Step-1 of the setup wizard. Generated from a callback method!', 'user-switcher' );
		?>
		<input type="date" name="date">
		<?php
	}

	public function callback_step_2() {
		_e( 'This is Step-2 of the setup wizard. Generated from a callback method!', 'user-switcher' );
		?>
		<input type="time" name="time">
		<?php
	}

	public function save_one() {
		// save one to DB
		if( isset( $_POST['date'] ) ) {
			update_option( 'date', $this->sanitize( $_POST['date'] ) );
		}
	}

	public function save_two() {
		// save two to DB
		if( isset( $_POST['time'] ) ) {
			update_option( 'time', $this->sanitize( $_POST['time'] ) );
		}
	}

	public function save_three() {
		// save three to DB
	}

}