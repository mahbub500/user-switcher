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
 * @subpackage Deactivator
 * 
 * @author Pluggable <hi@pluggable.io>
 */
class Deactivator {

	public $plugin;
	
	public $slug;
	
	public $name;
	
	public $args;
	
	public $server;
	
	public $basename;
	
	public function __construct( $plugin, $args = [] ) {

		if( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		
		$this->plugin 	= get_plugin_data( $plugin );

		$this->args = wp_parse_args( $args, [
			'server'	=> 'https://my.pluggable.io'
		] );

		$this->server 	= $this->args['server'];
		$this->slug 	= $this->plugin['TextDomain'];
		$this->name 	= $this->plugin['Name'];
		$this->basename	= plugin_basename( $plugin );
		
		$this->hooks();
	}

	public function hooks() {

		add_action( 'admin_footer', [ $this, 'deactivation_survey_modal' ], 99 );

		if( did_action( 'pl-plugin-deactivation' ) ) return;
		do_action( 'pl-plugin-deactivation' );

		add_action( 'admin_head', [ $this, 'head' ], 99 );
		add_action( 'wp_ajax_pl-plugin-deactivation', [ $this, 'send_deactivation_survey' ], 99 );
	}

	public function head() {
		?>
		<style type="text/css">
			.pl-plugin-deactivation-survey-overlay{background:rgba(0,0,0,.5);position:fixed;top:0;left:0;height:100vh;width:100%;z-index:10000;display:none;align-items:center;justify-content:center}.pl-plugin-deactivation-survey-modal{width:50%;background:#fff;padding:10px;display:grid;grid-template-columns:1fr;max-width:660px;border-radius:7px}.pl-plugin-deactivation-survey-modal .pl-plugin-dsm-header{padding:0 15px}.pl-plugin-deactivation-survey-modal .pl-plugin-dsm-body{padding:20px 15px}.pl-plugin-deactivation-survey-modal .pl-plugin-deactivation-reasons{display:grid;gap:10px;margin-bottom:15px;grid-template-columns:repeat(3,1fr)}.pl-plugin-dsm-header .pl-title{margin-top:0;float:left}.pl-plugin-deactivation-survey-modal .pl-plugin-deactivation-reasons .pl-plugin-deactivation-reason label{display:block;border:1px solid #ccc;padding:10px;text-align:center;height:40px;border-radius:4px}.pl-plugin-dsm-footer .button:not(.button-primary,.pl-plugin-dsm-close):not(:hover){border-color:#dee0e100;color:#949799;background:#ffffff00}.pl-plugin-deactivation-survey-modal .pl-plugin-deactivation-reasons .pl-plugin-deactivation-reason label.active{background:#007cba;color:#fff;border-color:#007cba}.pl-plugin-deactivation-survey-modal .pl-plugin-deactivation-reasons .pl-plugin-deactivation-reason input{display:none}.pl-plugin-deactivation-survey-modal .pl-plugin-dsm-reason-details-input{width:100%;border:1px solid #ccc;display:none}.pl-plugin-deactivation-survey-modal .pl-plugin-dsm-footer{border-top:1px solid #f1f1f1;display:flex;justify-content:space-between;padding:20px 10px 10px 10px}.pl-heading{font-weight:400;text-align:center;overflow:hidden}.pl-desc{color:#d7d7d7;font-style:italic;text-align:right;margin:0 auto 5px auto}.pl-consent-label{float:right;font-size:12px;padding-right:5px;cursor:pointer}
		</style>
		<?php
	}

	public function deactivation_survey_modal()	{
		if ( get_current_screen()->base != 'plugins' ) return;
		
		?>
		<script type="text/javascript">
			jQuery(function($){

				$(document).on( 'click', '.pl-plugin-dsm-close', function(e){
					e.preventDefault()
					$('.pl-plugin-deactivation-survey-overlay').hide()
				} )

				$(document).on( 'click', '.pl-plugin-deactivation-reason', function(e){
					var par = $(this);
					if ( $( 'input', par ).prop("checked") == false ){
					 	$('label', par).removeClass('active');
					 }else{
						$('label', par).addClass('active');
					 }		 
					$('.pl-plugin-dsm-reason-details-input').slideDown();
				} )

				$(document).on( 'click', '.pl-consent-label', function(e){
					var desc = $(this).data('desc');
					alert(desc)
				} )
				
				$(document).on( 'submit', '.pl-plugin-deactivation-survey-form', function(e){
					e.preventDefault();
					var data = $(this).serializeArray()
					var parent = $(this);
					$('.pl-plugin-dsm-submit', parent).prop('disabled', true);
					 
					$.ajax({
						url: ajaxurl,
						data: data,
						type: 'POST',
						dataType: 'JSON',
						success: function(resp){				
							window.location.href='';
						}
					});
				} );

				$(document).on( 'click', 'tr[data-plugin="<?php echo $this->basename; ?>"] .deactivate a', function(e){
					e.preventDefault()
					$('.pl-plugin-deactivation-survey-overlay').css('display', 'flex');
					$('.pl-plugin-dsm-skip-btn').prop('href', $(this).attr('href'));
					$('#cxd-plugin-name').val( "<?php echo $this->slug; ?>" );
				} )
			})
		</script>
		<?php

		if( did_action( 'pl-plugin-deactivation-modal' ) ) return;
		do_action( 'pl-plugin-deactivation-modal' );
		
		$user = wp_get_current_user();
		?>
		<div class="pl-plugin-deactivation-survey-overlay">
			<div class="pl-plugin-deactivation-survey-modal">

				<div class="pl-plugin-deactivation-survey-form">
					<form method="post" class="pl-plugin-deactivation-survey-form">
						<input type="hidden" name="plugin" value="" id="cxd-plugin-name">
						<input type="hidden" name="action" value="pl-plugin-deactivation">
						<div class="pl-plugin-dsm-header">
							<h3 class="pl-heading">
								<?php printf( __( 'We\'re so sorry to see you go, %s!', 'pluggable' ), $user->display_name ); ?>
							</h3>
							<p class="pl-heading"><?php _e( 'Would you mind telling us why you are deactivating so we can improve it? 🤔', 'pluggable' ) ?></p>
						</div>
						<div class="pl-plugin-dsm-body">
							<div class="pl-plugin-deactivation-reasons">
								<?php
								foreach ( $this->get_reasons() as $key => $label ) {
									echo "
									<div class='pl-plugin-deactivation-reason'>
										<label for='{$key}'>{$label}</label>
										<input type='checkbox' name='reason[]' value='{$key}' id='{$key}'>
									</div>
									";
								}
								?>
							</div>
							<div class="pl-plugin-dsm-reason-details">
								<textarea class="pl-plugin-dsm-reason-details-input" name="explanation" rows="5" placeholder="Please Explain"></textarea>
							</div>
						</div>
						<div class="pl-plugin-dsm-footer">
							<a href="" class="button pl-plugin-dsm-skip-btn"><?php _e( 'Skip & Deactivate', 'pluggable' ) ?></a>
							<div class="pl-plugin-dsm-submit">
								<button class="button pl-plugin-dsm-btn pl-plugin-dsm-close"><?php _e( 'Cancel', 'pluggable' ) ?></button>
								&nbsp;
								<button class="button button-primary pl-plugin-dsm-btn pl-plugin-dsm-submit" type="submit"><?php _e( 'Submit & Deactivate', 'pluggable' ) ?></button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	public function send_deactivation_survey()	{
		// deactivate the plugin first
		deactivate_plugins( $this->basename );

		$user = wp_get_current_user();

		// send data
		$url = add_query_arg( [ 
		    'rest_route'    => '/plugins/deactivation',
		    'first_name'    => $user->first_name,
		    'last_name'     => $user->last_name,
		    'email'     	=> $user->user_email,
		    'plugin'     	=> sanitize_text_field( $_POST['plugin'] ),
		    'site_url'     	=> site_url( '/' ),
		    'delay'     	=> date_i18n( 'U' ) - (int) get_option( "{$this->slug}_install_time" ),
		    'reason'     	=> serialize( $_POST['reason'] ),
		    'explanation'	=> sanitize_textarea_field( $_POST['explanation'] ),
		], wp_unslash( $this->server ) );

		wp_remote_get( $url );

		wp_send_json( [ 'status' => 1, 'message' => __( 'Plugin deactivated' ) ] );
	}

	public function get_reasons() {
		$reasons = [
			'temporary'				=> 'It\'s a temporary deactivation',
			'found_better'			=> 'Found a better plugin',
			'features_missing'		=> 'Important features missing',
			'doesnt_work'			=> 'It doesn\'t work as expected',
			'mistakenly_installed'	=> 'I had installed it by mistake',
			'others'				=> 'Others',
		];

		return apply_filters( "pl-plugin-deactivation-reasons", $reasons, $this->slug );
	}
}
