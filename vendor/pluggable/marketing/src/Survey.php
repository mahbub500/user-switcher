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
 * @subpackage Survey
 * 
 * @author Pluggable <hi@pluggable.io>
 */
class Survey {
	
	public $args;

	public $plugin;

	public $plugin_file;
	
	public $activated_key;
	
	public function __construct( $plugin_file, $args = [] ) {

		if( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		
		$this->plugin_file		= $plugin_file;
		$this->plugin			= get_plugin_data( $this->plugin_file );
		$this->activated_key	= "pl-survey_{$this->plugin['TextDomain']}-activated";

		$this->args = wp_parse_args( $args, [
			'server'	=> 'https://my.pluggable.io',
			'hash'		=> 'a7719b8f-a43b-4c1d-aeb3-2823ef174f54',
			'text'		=> sprintf( __( 'Thanks for using <strong>%1$s</strong>!<br />Help us understand the plugin\'s usage on different sites for improved user satisfaction. Share your site URL and basic information (no passwords or sensitive data) to assist our continuous improvement efforts. Will you contribute?', 'pluggable' ), $this->plugin['Name'] ),
			'remind'	=> __( 'Remind me later', 'pluggable' ),
			'button'	=> __( 'Ok, but don\'t bother me again', 'pluggable' ),
			'delay'		=> 2 * DAY_IN_SECONDS,
		] );

		$this->hooks();
	}

	public function hooks() {
		register_activation_hook( $this->plugin_file, [ $this, 'activate' ] );
		register_deactivation_hook( $this->plugin_file, [ $this, 'deactivate' ] );
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
		add_action( 'admin_footer', [ $this, 'admin_footer' ] );
		add_action( "wp_ajax_{$this->plugin['TextDomain']}_survey", [ $this, 'ajax' ] );
	}

	public function activate() {
		update_option( $this->activated_key, date_i18n( 'U' ) );
	}

	public function deactivate() {
		delete_option( $this->activated_key );
	}

	public function admin_notices() {

		if( date_i18n( 'U' ) <= get_option( $this->activated_key ) + $this->args['delay'] ) return;

		printf(
			'<div id="%1$s-survey-notice" class="notice notice-success is-dismissible pl-survey pl-notice pl-shadow" data-slug="%1$s" data-nonce="%5$s">
				<p>%2$s</p>
				<p>
					<button class="button button-primary pl-survey-btn" data-participate="1">%3$s</button>
					<button class="button pl-survey-btn" data-participate="0">%4$s</button>
				</p>
			</div>',
			$this->plugin['TextDomain'],
			$this->args['text'],
			$this->args['button'],
			$this->args['remind'],
			wp_create_nonce(),
		);
	}

	public function admin_footer() {

		if( did_action( 'pl-survey_footer-loaded' ) ) return;
		do_action( 'pl-survey_footer-loaded' );
		
		?>
		<script type="text/javascript">
			jQuery(function($){
			    $(document).on('click', '.pl-survey .notice-dismiss, .pl-survey .pl-survey-btn', function(e){
			        $(this).prop('disabled', true);
			        var $slug = $(this).closest('.pl-survey').data('slug')
			        var $nonce = $(this).closest('.pl-survey').data('nonce')
			        var $participate = $(this).data('participate');
			        $.ajax({
			            url: ajaxurl,
			            data: { 'action' : $slug + '_survey', participate : $participate, _wpnonce : $nonce },
			            type: 'POST',
			            success: function(ret) {
			                $('#'+$slug+'-survey-notice').slideToggle(500)
			            }
			        })
			    })
			})
		</script>
		<?php
	}

	public function ajax() {
		if ( ! wp_verify_nonce( $_POST['_wpnonce'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized' ) ] );
	    }

	    if( ! isset( $_POST['participate'] ) ) { // cancel
	    	update_option( $this->activated_key, date_i18n( 'U' ) + YEAR_IN_SECONDS );
	    }
	    elseif( $_POST['participate'] == 0 ) { // remind later
	    	update_option( $this->activated_key, date_i18n( 'U' ) + WEEK_IN_SECONDS );
	    }
	    else { // agreed
	    	$user = wp_get_current_user();
	    	
	    	$url = add_query_arg( [ 
	    	    'fluentcrm'		=> 1,
	    	    'route'			=> 'contact',
	    	    'hash'			=> $this->args['hash'],
	    	    'first_name'    => $user->first_name,
	    	    'last_name'     => $user->last_name,
	    	    'email'     	=> $user->user_email,
	    	    'plugin'     	=> $this->plugin['TextDomain'],
	    	    'site_url'     	=> site_url(),
	    	], wp_unslash( $this->args['server'] ) );

	    	wp_remote_post( $url );

	    	update_option( $this->activated_key, date_i18n( 'U' ) + YEAR_IN_SECONDS );
	    }
	}
}