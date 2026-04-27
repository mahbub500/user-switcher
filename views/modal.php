<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="us-switcher-modal" style="display:none;" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Switch User', 'user-switcher' ); ?>">

	<div class="us-overlay" id="us-overlay"></div>

	<div class="us-modal-wrap">

		<!-- Header -->
		<div class="us-modal-head">
			<div class="us-head-title">
				<span class="dashicons dashicons-admin-users us-head-icon"></span>
				<strong><?php esc_html_e( 'Switch User', 'user-switcher' ); ?></strong>
			</div>
			<div class="us-head-me" id="us-head-me">
				<img class="us-avatar-sm" id="us-me-avatar" src="" alt="" />
				<span class="us-me-name" id="us-me-name"></span>
			</div>
			<button class="us-close-btn" id="us-close-btn" aria-label="<?php esc_attr_e( 'Close', 'user-switcher' ); ?>">&#10005;</button>
		</div>

		<!-- Search + Role Filters -->
		<div class="us-modal-search-bar">
			<div class="us-search-box">
				<span class="dashicons dashicons-search us-search-ico"></span>
				<input
					type="search"
					id="us-keyword"
					class="us-search-input"
					placeholder="<?php esc_attr_e( 'Search by name, email or username\xe2\x80\xa6', 'user-switcher' ); ?>"
					autocomplete="off"
				/>
				<button class="us-clear-btn" id="us-clear-btn" title="<?php esc_attr_e( 'Clear', 'user-switcher' ); ?>" style="display:none;">&#10005;</button>
			</div>
			<div class="us-role-strip" id="us-role-strip">
				<button class="us-role-pill active" data-role=""><?php esc_html_e( 'All', 'user-switcher' ); ?></button>
			</div>
		</div>

		<!-- Scrollable Body -->
		<div class="us-modal-body">

			<!-- Recent Users -->
			<div id="us-recent-wrap" style="display:none;">
				<p class="us-section-label"><?php esc_html_e( 'Recent', 'user-switcher' ); ?></p>
				<div class="us-user-grid" id="us-recent-grid"></div>
			</div>

			<!-- All / Search Results -->
			<div id="us-results-wrap">
				<p class="us-section-label" id="us-results-label"><?php esc_html_e( 'All Users', 'user-switcher' ); ?></p>
				<div class="us-user-grid" id="us-user-grid">
					<div class="us-state-msg" id="us-loading-state">
						<span class="us-spinner"></span>
						<?php esc_html_e( 'Loading\xe2\x80\xa6', 'user-switcher' ); ?>
					</div>
					<div class="us-state-msg" id="us-empty-state" style="display:none;">
						<?php esc_html_e( 'No users found.', 'user-switcher' ); ?>
					</div>
				</div>
			</div>

		</div>

		<!-- Footer: selected preview + switch button -->
		<div class="us-modal-foot" id="us-modal-foot" style="display:none;">
			<div class="us-foot-preview">
				<img class="us-avatar-sm" id="us-foot-avatar" src="" alt="" />
				<div>
					<span class="us-foot-name" id="us-foot-name"></span>
					<span class="us-role-badge" id="us-foot-role"></span>
				</div>
			</div>
			<button class="us-switch-btn" id="us-switch-btn">
				<?php esc_html_e( 'Switch Now', 'user-switcher' ); ?>
			</button>
		</div>

	</div><!-- .us-modal-wrap -->

</div><!-- #us-switcher-modal -->

<div id="us-toast" class="us-toast" role="alert" aria-live="polite"></div>
