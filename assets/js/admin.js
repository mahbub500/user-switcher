/* global USER_SWITCHER, jQuery */
(function ($) {
	'use strict';

	const US = {

		selectedUser: null,
		searchTimer: null,
		currentRole: '',
		recentKey: 'us_recent_' + ( USER_SWITCHER.me ? USER_SWITCHER.me.id : '0' ),

		/* ── Selectors ─────────────────────────────────────────────── */
		$modal:       function () { return $( '#us-switcher-modal' ); },
		$keyword:     function () { return $( '#us-keyword' ); },
		$clearBtn:    function () { return $( '#us-clear-btn' ); },
		$roleStrip:   function () { return $( '#us-role-strip' ); },
		$userGrid:    function () { return $( '#us-user-grid' ); },
		$recentWrap:  function () { return $( '#us-recent-wrap' ); },
		$recentGrid:  function () { return $( '#us-recent-grid' ); },
		$loadingSt:   function () { return $( '#us-loading-state' ); },
		$emptySt:     function () { return $( '#us-empty-state' ); },
		$resultLabel: function () { return $( '#us-results-label' ); },
		$foot:        function () { return $( '#us-modal-foot' ); },
		$switchBtn:   function () { return $( '#us-switch-btn' ); },
		$toast:       function () { return $( '#us-toast' ); },

		/* ── Init ──────────────────────────────────────────────────── */
		init: function () {
			this.bindEvents();
			this.loadRoles();
		},

		/* ── Roles ─────────────────────────────────────────────────── */
		loadRoles: function () {
			var self = this;
			$.get( USER_SWITCHER.ajaxurl, {
				action: 'get_roles',
				_wpnonce: USER_SWITCHER._wpnonce
			}, function ( res ) {
				if ( ! res.success ) return;
				$.each( res.data, function ( key, label ) {
					self.$roleStrip().append(
						$( '<button>', { class: 'us-role-pill', 'data-role': key, text: label } )
					);
				} );
			} );
		},

		/* ── Events ────────────────────────────────────────────────── */
		bindEvents: function () {
			var self = this;

			/* Open via admin bar */
			$( document ).on( 'click', '#wp-admin-bar-us-switcher-menu > .ab-item, .us-adminbar-item > a', function ( e ) {
				e.preventDefault();
				self.openModal();
			} );

			/* Close */
			$( document ).on( 'click', '#us-close-btn, #us-overlay', function () {
				self.closeModal();
			} );

			/* ESC key closes; Alt+S opens */
			$( document ).on( 'keydown', function ( e ) {
				if ( e.key === 'Escape' ) { self.closeModal(); }
				if ( e.altKey && e.key.toLowerCase() === 's' ) {
					e.preventDefault();
					self.openModal();
				}
			} );

			/* Search input */
			$( document ).on( 'input', '#us-keyword', function () {
				var val = self.$keyword().val().trim();
				self.$clearBtn().toggle( val.length > 0 );
				clearTimeout( self.searchTimer );
				self.searchTimer = setTimeout( function () {
					self.searchUsers( val );
				}, 280 );
			} );

			/* Clear search */
			$( document ).on( 'click', '#us-clear-btn', function () {
				self.$keyword().val( '' ).focus();
				self.$clearBtn().hide();
				self.searchUsers( '' );
			} );

			/* Role filter */
			$( document ).on( 'click', '.us-role-pill', function () {
				$( '.us-role-pill' ).removeClass( 'active' );
				var btn = $( this ).addClass( 'active' );
				self.currentRole = btn.data( 'role' );
				self.searchUsers( self.$keyword().val().trim() );
			} );

			/* Select user (single click) */
			$( document ).on( 'click', '.us-user-card', function () {
				var card = $( this );
				self.selectUser( {
					id:     card.data( 'id' ),
					name:   card.data( 'name' ),
					email:  card.data( 'email' ),
					role:   card.data( 'role' ),
					rlabel: card.data( 'rlabel' ),
					avatar: card.data( 'avatar' )
				} );
			} );

			/* Double-click to switch immediately */
			$( document ).on( 'dblclick', '.us-user-card', function () {
				var card = $( this );
				self.selectUser( {
					id:     card.data( 'id' ),
					name:   card.data( 'name' ),
					email:  card.data( 'email' ),
					role:   card.data( 'role' ),
					rlabel: card.data( 'rlabel' ),
					avatar: card.data( 'avatar' )
				} );
				self.doSwitch();
			} );

			/* Enter key on focused card */
			$( document ).on( 'keydown', '.us-user-card', function ( e ) {
				if ( e.key === 'Enter' ) { $( this ).trigger( 'click' ); }
			} );

			/* Switch Now button */
			$( document ).on( 'click', '#us-switch-btn', function () {
				self.doSwitch();
			} );

			/* Switch-back bar (admin pages) */
			$( document ).on( 'click', '#us_floatingBtn', function ( e ) {
				e.preventDefault();
				self.switchBack( $( this ).attr( 'href' ) );
			} );
		},

		/* ── Modal open / close ────────────────────────────────────── */
		openModal: function () {
			this.$modal().css( 'display', 'flex' ).addClass( 'us-open' );
			$( 'body' ).addClass( 'us-no-scroll' );
			this.populateCurrentUser();
			this.populateRecentSection();
			this.resetSelection();
			this.searchUsers( '' );
			var self = this;
			setTimeout( function () { self.$keyword().focus(); }, 180 );
		},

		closeModal: function () {
			this.$modal().removeClass( 'us-open' );
			var self = this;
			setTimeout( function () {
				if ( ! self.$modal().hasClass( 'us-open' ) ) {
					self.$modal().css( 'display', 'none' );
				}
			}, 220 );
			$( 'body' ).removeClass( 'us-no-scroll' );
			this.$keyword().val( '' );
			this.$clearBtn().hide();
			this.currentRole = '';
			$( '.us-role-pill' ).removeClass( 'active' ).first().addClass( 'active' );
			this.resetSelection();
		},

		resetSelection: function () {
			this.selectedUser = null;
			this.$foot().hide();
			$( '.us-user-card' ).removeClass( 'us-selected' );
		},

		/* ── Current user in header ────────────────────────────────── */
		populateCurrentUser: function () {
			var me = USER_SWITCHER.me;
			if ( ! me ) return;
			$( '#us-me-name' ).text( me.name );
			$( '#us-me-avatar' ).attr( { src: me.avatar, alt: me.name } );
		},

		/* ── Recent users ──────────────────────────────────────────── */
		getRecentUsers: function () {
			try { return JSON.parse( localStorage.getItem( this.recentKey ) || '[]' ); }
			catch ( e ) { return []; }
		},

		saveRecentUser: function ( user ) {
			var recent = this.getRecentUsers().filter( function ( u ) { return u.id !== user.id; } );
			recent.unshift( {
				id: user.id, display_name: user.name,
				email: user.email, role: user.role, role_label: user.rlabel, avatar: user.avatar
			} );
			localStorage.setItem( this.recentKey, JSON.stringify( recent.slice( 0, 5 ) ) );
		},

		populateRecentSection: function () {
			var self    = this;
			var recent  = this.getRecentUsers();
			if ( ! recent.length ) { this.$recentWrap().hide(); return; }

			var ids = recent.map( function ( u ) { return u.id; } ).join( ',' );
			$.get( USER_SWITCHER.ajaxurl, {
				action: 'get_user_info',
				user_ids: ids,
				_wpnonce: USER_SWITCHER._wpnonce
			}, function ( res ) {
				self.$recentGrid().empty();
				if ( res.success && res.data.length ) {
					$.each( res.data, function ( i, u ) {
						self.$recentGrid().append( self.cardHTML( u ) );
					} );
					self.$recentWrap().show();
				} else {
					self.$recentWrap().hide();
				}
			} ).fail( function () {
				self.$recentGrid().empty();
				$.each( recent, function ( i, u ) {
					self.$recentGrid().append( self.cardHTML( {
						id: u.id, display_name: u.display_name, email: u.email,
						role: u.role, role_label: u.role_label, avatar: u.avatar
					} ) );
				} );
				self.$recentWrap().show();
			} );
		},

		/* ── Search ────────────────────────────────────────────────── */
		searchUsers: function ( keyword ) {
			var self = this;

			/* Show / hide recent section */
			if ( keyword || this.currentRole ) {
				this.$recentWrap().hide();
			} else if ( this.$recentGrid().children( '.us-user-card' ).length ) {
				this.$recentWrap().show();
			}

			/* Update results label */
			var label = 'All Users';
			if ( keyword && this.currentRole ) {
				label = this.currentRole + ' · “' + keyword + '”';
			} else if ( keyword ) {
				label = 'Results for “' + keyword + '”';
			} else if ( this.currentRole ) {
				label = this.currentRole.charAt( 0 ).toUpperCase() + this.currentRole.slice( 1 ) + 's';
			}
			this.$resultLabel().text( label );

			this.$loadingSt().show();
			this.$emptySt().hide();
			this.$userGrid().find( '.us-user-card' ).remove();

			$.get( USER_SWITCHER.ajaxurl, {
				action: 'search_users',
				keyword: keyword,
				role: this.currentRole,
				_wpnonce: USER_SWITCHER._wpnonce
			}, function ( res ) {
				self.$loadingSt().hide();
				if ( res.success && res.data.length ) {
					$.each( res.data, function ( i, u ) {
						self.$userGrid().append( self.cardHTML( u ) );
					} );
					/* Re-highlight selected */
					if ( self.selectedUser ) {
						self.$userGrid().find( '.us-user-card[data-id="' + self.selectedUser.id + '"]' )
							.addClass( 'us-selected' );
					}
				} else {
					self.$emptySt().show();
				}
			} ).fail( function () {
				self.$loadingSt().hide();
				self.$emptySt().text( 'Error loading users.' ).show();
			} );
		},

		/* ── Card HTML ─────────────────────────────────────────────── */
		cardHTML: function ( u ) {
			var name     = u.display_name || u.login || '';
			var roleKey  = u.role || 'subscriber';
			var roleLabel = u.role_label || ( roleKey.charAt( 0 ).toUpperCase() + roleKey.slice( 1 ) );
			var initials = name.split( ' ' ).map( function ( p ) {
				return p.charAt( 0 );
			} ).join( '' ).slice( 0, 2 ).toUpperCase();

			var escapedName   = $( '<div>' ).text( name ).html();
			var escapedEmail  = $( '<div>' ).text( u.email || '' ).html();
			var escapedRole   = $( '<div>' ).text( roleLabel ).html();
			var escapedRoleK  = $( '<div>' ).text( roleKey ).html();
			var escapedAvatar = $( '<div>' ).text( u.avatar || '' ).html();

			return '<div class="us-user-card"'
				+ ' data-id="'     + u.id          + '"'
				+ ' data-name="'   + escapedName   + '"'
				+ ' data-email="'  + escapedEmail  + '"'
				+ ' data-role="'   + escapedRoleK  + '"'
				+ ' data-rlabel="' + escapedRole   + '"'
				+ ' data-avatar="' + escapedAvatar + '"'
				+ ' tabindex="0" role="option"'
				+ ' title="' + escapedEmail + '">'
				+ '<div class="us-card-avatar" data-initials="' + initials + '">'
				+ '<img src="' + escapedAvatar + '" alt="" class="us-av-img" onerror="this.remove()" />'
				+ '</div>'
				+ '<div class="us-card-info">'
				+ '<span class="us-card-name">' + escapedName + '</span>'
				+ '<span class="us-card-email">' + escapedEmail + '</span>'
				+ '</div>'
				+ '<span class="us-role-badge us-role-' + escapedRoleK + '">' + escapedRole + '</span>'
				+ '<span class="us-check-mark">&#10003;</span>'
				+ '</div>';
		},

		/* ── Select ────────────────────────────────────────────────── */
		selectUser: function ( user ) {
			this.selectedUser = user;
			$( '.us-user-card' ).removeClass( 'us-selected' );
			$( '.us-user-card[data-id="' + user.id + '"]' ).addClass( 'us-selected' );
			$( '#us-foot-avatar' ).attr( { src: user.avatar, alt: user.name } );
			$( '#us-foot-name' ).text( user.name );

			var roleClass = 'us-role-badge us-role-' + ( user.role || '' ).toLowerCase();
			$( '#us-foot-role' ).attr( 'class', roleClass ).text( user.rlabel || user.role );

			this.$foot().show();
		},

		/* ── Switch ────────────────────────────────────────────────── */
		doSwitch: function () {
			var self = this;
			if ( ! this.selectedUser ) return;
			var btn = this.$switchBtn();
			btn.prop( 'disabled', true ).addClass( 'us-loading' ).text( 'Switching…' );

			$.post( USER_SWITCHER.ajaxurl, {
				action: 'switch_user',
				user_id: this.selectedUser.id,
				_wpnonce: USER_SWITCHER._wpnonce
			}, function ( res ) {
				if ( res.success && res.data && res.data.url ) {
					self.saveRecentUser( self.selectedUser );
					self.showToast( 'Switching to ' + self.selectedUser.name + '…', 'success' );
					setTimeout( function () {
						window.location.href = res.data.url;
					}, 700 );
				} else {
					self.showToast( 'Could not switch. Please try again.', 'error' );
					btn.prop( 'disabled', false ).removeClass( 'us-loading' ).text( 'Switch Now' );
				}
			} ).fail( function () {
				self.showToast( 'Request failed. Please try again.', 'error' );
				btn.prop( 'disabled', false ).removeClass( 'us-loading' ).text( 'Switch Now' );
			} );
		},

		/* ── Switch back ───────────────────────────────────────────── */
		switchBack: function ( url ) {
			$.post( USER_SWITCHER.ajaxurl, {
				action: 'remove_cookie',
				_wpnonce: USER_SWITCHER._wpnonce
			}, function ( res ) {
				if ( res.success ) { window.location.href = url; }
			} );
		},

		/* ── Toast ─────────────────────────────────────────────────── */
		showToast: function ( msg, type ) {
			type = type || 'info';
			var t = this.$toast();
			t.stop( true ).removeClass( 'us-success us-error us-info' )
				.addClass( 'us-' + type ).text( msg ).fadeIn( 200 );
			setTimeout( function () { t.fadeOut( 400 ); }, 3500 );
		}
	};

	$( document ).ready( function () { US.init(); } );

} )( jQuery );
