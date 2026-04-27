<?php
/**
 * All public facing functions
 */
namespace wppluginhub\User_Switcher\App;
use WpPluginHub\Plugin\Base;
use WpPluginHub\User_Switcher\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Common extends Base {

	public $plugin;

	public function __construct( $plugin ) {
		$this->plugin  = $plugin;
		$this->slug    = $this->plugin['TextDomain'];
		$this->name    = $this->plugin['Name'];
		$this->version = $this->plugin['Version'];
	}

	public function show_switch_button() {
		$original_user_id = user_switcher_user_switch_data( 'switch_from' );
		$login_url        = user_switcher_encrypted_login_url( $original_user_id );

		if ( ! $original_user_id || ! $login_url ) {
			return;
		}

		$original_user   = get_userdata( $original_user_id );
		$original_name   = $original_user
			? ( $original_user->display_name ?: $original_user->user_login )
			: '';
		$original_avatar = get_avatar_url( $original_user_id, [ 'size' => 32 ] );

		$switched_user_id   = user_switcher_user_switch_data( 'switch_to_user' );
		$switched_user_data = $switched_user_id ? get_userdata( $switched_user_id ) : null;
		$switched_name      = $switched_user_data
			? ( $switched_user_data->display_name ?: $switched_user_data->user_login )
			: '';
		?>
		<div class="us-switch-back-bar" id="us-switch-back-bar">
			<?php if ( $switched_name ) : ?>
			<div class="us-switch-back-info">
				<span class="us-switch-back-badge"><?php esc_html_e( 'Switched', 'user-switcher' ); ?></span>
				<span class="us-switch-back-as"><?php echo esc_html( $switched_name ); ?></span>
			</div>
			<span class="us-switch-back-sep">|</span>
			<?php endif; ?>
			<a href="<?php echo esc_url( $login_url ); ?>" class="us-switch-back-link" id="us_floatingBtn">
				<img src="<?php echo esc_url( $original_avatar ); ?>" alt="" class="us-switch-back-avatar" />
				<?php
				/* translators: %s: original admin username */
				printf( esc_html__( 'Back to %s', 'user-switcher' ), esc_html( $original_name ) );
				?>
			</a>
		</div>
		<?php
	}
}
