<?php 
?>
 <div id="us-switcher-modal" style="display: none;">
    <div class="us-switcher-modal-content">
        <span class="us-switcher-close">&times;</span>	            
        <h2><?php echo esc_html(__('User Switcher', 'user-switch')); ?></h2>
        <p><?php echo esc_html(__('Search users by name, display name, or email.', 'user-switch')); ?></p>
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