/*! User Switcher -v 2.0.0 
 * Copyright (c) 2016;
 * Licensed: GPLv2+
 **/
/* global userSwitcher */
(function($){
	var us = userSwitcher;

	_.extend( userSwitcher, {
		// Store users
		users: {},

		suOn: false,

		pageReload: function() {
			window.location.reload();
		},

		/**
		 * Our single ajax request instance that we use to
		 * all request needed to switch between users.
		 **/
		SendRequest: Backbone.Model.extend({
			url: us._ajax_url + '?action=us_request&nonce=' + us.nonce,
			initialize: function() {
				this.on( 'error', this.serverError, this );
			},
			parse: function( response ) {
				var action = this.get( 'action' );

				if ( response.success ) {
					this.trigger( 'us:' + action + '_success', response.data );
				} else {
					this.trigger( 'us:' + action + '_error', response.data.message );
				}
			},
			turnOffEvents: function() {
				var action = this.get( 'action' );
				this.off( 'us:' + action + '_error' );
				this.off( 'us:' + action + '_success' );
			},
			serverError: function() {
				var action = this.get( 'action' );
				this.trigger( 'us:' + action + '_error', us.l8n.server_error );
			}
		}),

		SwitcherController: Backbone.View.extend({
			el: '#wp-admin-bar-us-switcher-menu',
			events: {
				'click #wp-admin-bar-us-to-guest': 'switchToGuest',
				'click #wp-admin-bar-us-search-users': 'searchUsers',
				'click #wp-admin-bar-us-switch-back': 'switchBack'
			},
			switchToGuest: function() {
				us.SendRequest.set({
					action: 'switch_user',
					user_id: 'guest',
					ajax: true
				});
				us.SendRequest.turnOffEvents();
				us.SendRequest.on( 'us:switch_user_success', us.pageReload );
				us.SendRequest.save();
			},
			searchUsers: function() {
				if ( false === us.suOn ) {
					us.suOn = new us.SwitcherWindow();
					us.suOn.render();
				} else {
					us.suOn.show();
				}
			},
			switchBack: function() {
				us.noAdminView.prototype.switchBack.apply(this);
			}
		}),

		noAdminView: Backbone.View.extend({
			className: 'us-no-admin-view',
			isguest: false,
			events: {
				'click .us-switch-back': 'switchBack',
				'click .us-search-user': 'searchUsers',
				'click .description': 'toggleOptions',
				'click .us-guest-user': 'switchToGuest'
			},
			initialize: function() {
				this.isguest = 'guest' === us.user_switch_to;
				this.render();
			},
			render: function() {
				var template_id = this.isguest ? '#user-no-admin-bar' : '#user-no-admin-bar-admin';
				var template = _.template( $( template_id ).html() );
				this.$el.append(template);
				this.$el.appendTo( 'body' );
			},
			toggleOptions: function(ev) {
				var p = this.$el.find( 'p' ).not( ev.currentTarget ),
					is_visible = p.is( ':visible' );
				p[ is_visible ? 'slideUp' : 'slideDown']();
			},
			switchToGuest: function() {
				us.SwitcherController.prototype.switchToGuest.apply(this);
			},
			switchBack: function() {
				us.SendRequest.set({
					action: 'restore_account',
					ajax: true
				});
				us.SendRequest.turnOffEvents();
				us.SendRequest.on( 'us:restore_account_success', us.pageReload );
				us.SendRequest.save();
			},
			searchUsers: function() {
				us.SwitcherController.prototype.searchUsers.apply(this);
			}
		}),

		SwitcherWindow: Backbone.View.extend({
			className: 'us-window',
			page: 1,
			per_page: 20,
			events: {
				'submit form': 'searchUsers',
				'click .switch_to_user button': 'switchUser',
				'keydown .us-search-key': 'hideNotice',
				'click .us-close-icon': 'hide',
				'click .us-prev-button': 'searchPrev',
				'click .us-next-button': 'searchNext'
			},
			render: function() {
				var template = _.template( $('#user-switcher-window' ).html() );

				this.$el.append(template);
				this.$el.appendTo( 'body' );
				this.$el.find( '[name="key"]' ).focus();

				this.notice_container = this.$el.find( '#us-notice-box' );
				this.result_container = this.$el.find( '#us-search-results' );
				this.navs_container = this.$el.find( '#us-navs' );
				this.next_button = this.navs_container.find( '.us-next-button' );
				this.prev_button = this.navs_container.find( '.us-prev-button' );
				this.form = this.$el.find( 'form' );
			},
			showNotice: function( message ) {
				this.notice_container.empty().slideUp().html(message).slideDown();
			},
			hideNotice: function() {
				this.notice_container.empty().slideUp();
			},
			searchUsers: function(ev) {
				var form = $(ev.currentTarget),
					input = form.find( '[name="key"]' ),
					term = input.val();

				if ( 1 > term.length ) {
					// Start searching only if there are at least 3 characters entered
					this.showNotice( us.l8n.notice.char_limit );
					return false;
				}
				this.hideNotice();

				if ( us.users[term] && us.users[term][this.page] ) {
					// Get results from cache
					this.result_container.empty().html( us.users[term][this.page].users );
					return false;
				}

				input.addClass( 'us-searching' );
				us.SendRequest.set({
					action: 'search_users',
					term: term,
					page: this.page
				});

				function removeLoader() {
					input.removeClass('us-searching');
				}

				us.SendRequest.turnOffEvents();
				us.SendRequest.on( 'us:search_users_error', removeLoader );
				us.SendRequest.on( 'us:search_users_error', this.showNotice, this );
				us.SendRequest.on( 'us:search_users_success', removeLoader );
				us.SendRequest.on( 'us:search_users_success', this.showResults, this );
				us.SendRequest.save();

				return false;
			},
			showResults: function( data ) {
				var key = us.SendRequest.get('term'),
					page = us.SendRequest.get( 'page' );

				us.users[key] = {};
				us.users[key][page] = data;
				this.result_container.empty().show().html(data.users);
				this.toggleNavs(data.total);
			},
			switchUser: function( ev ) {
				var div = $(ev.currentTarget),
					user_id = div.data('id');

				us.SendRequest.set({
					action: 'switch_user',
					user_id: user_id,
					ajax: true
				});
				us.SendRequest.turnOffEvents();
				us.SendRequest.on( 'us:switch_user_success', us.pageReload );
				us.SendRequest.save();
			},
			show: function() {
				this.$el.show();
			},
			hide: function() {
				this.$el.hide();
			},
			toggleNavs: function(total) {
				var ctotal = this.page * this.per_page;
				if ( ctotal < total || this.page > 1 ) {
					this.navs_container.show();
				}
				this.prev_button[ this.page > 1 ? 'addClass' : 'removeClass' ]('active');
				this.next_button[ ctotal < total ? 'addClass' : 'removeClass' ]('active');
			},
			searchPrev: function(ev) {
				var target = $(ev.currentTarget);

				if ( target.is( '.active') ) {
					this.page -= 1;
					if ( this.page <= 0 ) {
						this.page = 1;
					}
					this.form.submit();
				}
			},
			searchNext: function(ev) {
				var target = $(ev.currentTarget);

				if ( target.is( '.active' ) ) {
					this.page += 1;
					this.form.submit();
				}
			}
		})
	});

	// Initial the single request instance
	us.SendRequest = new us.SendRequest();

	$(document).ready(function() {
		if ( ( 'guest' === us.switch_to ) ||
			( ! us.admin_bar && ! us.is_admin ) ) {
			new us.noAdminView();
		} else {
			new us.SwitcherController();
		}
	});

})(jQuery);