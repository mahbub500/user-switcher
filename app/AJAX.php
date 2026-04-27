<?php
/**
 * All AJAX related functions
 */
namespace wppluginhub\User_Switcher\App;
use WpPluginHub\Plugin\Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AJAX extends Base {

	public $plugin;

	public function __construct( $plugin ) {
		$this->plugin  = $plugin;
		$this->slug    = $this->plugin['TextDomain'];
		$this->name    = $this->plugin['Name'];
		$this->version = $this->plugin['Version'];
	}

	public function search_users() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized', 'user-switcher' ) ], 401 );
		}

		$current_user_id = get_current_user_id();
		$keyword         = isset( $_GET['keyword'] ) ? sanitize_text_field( wp_unslash( $_GET['keyword'] ) ) : '';
		$role            = isset( $_GET['role'] )    ? sanitize_text_field( wp_unslash( $_GET['role'] ) )    : '';

		$args = [
			'exclude' => [ $current_user_id ],
			'number'  => 30,
			'orderby' => 'display_name',
			'order'   => 'ASC',
		];

		if ( ! empty( $keyword ) ) {
			$args['search']         = '*' . esc_attr( $keyword ) . '*';
			$args['search_columns'] = [ 'user_login', 'user_nicename', 'display_name', 'user_email' ];
		}

		if ( ! empty( $role ) ) {
			$args['role'] = $role;
		}

		$user_query = new \WP_User_Query( $args );
		$users      = $user_query->get_results();

		if ( ! empty( $users ) ) {
			$user_array = [];
			foreach ( $users as $user ) {
				$roles        = $user->roles;
				$role_key     = ! empty( $roles ) ? $roles[0] : 'subscriber';
				$user_array[] = [
					'id'           => $user->ID,
					'login'        => $user->user_login,
					'display_name' => $user->display_name ?: $user->user_login,
					'email'        => $user->user_email,
					'role'         => $role_key,
					'role_label'   => ucfirst( $role_key ),
					'avatar'       => get_avatar_url( $user->ID, [ 'size' => 48 ] ),
				];
			}
			wp_send_json_success( $user_array );
		} else {
			wp_send_json_error( [ 'message' => __( 'No users found', 'user-switcher' ) ] );
		}
	}

	public function get_roles() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized', 'user-switcher' ) ], 401 );
		}

		global $wp_roles;
		$roles = [];
		foreach ( $wp_roles->roles as $key => $role ) {
			$roles[ $key ] = $role['name'];
		}
		wp_send_json_success( $roles );
	}

	public function get_user_info() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized', 'user-switcher' ) ], 401 );
		}

		$raw_ids  = isset( $_GET['user_ids'] ) ? sanitize_text_field( wp_unslash( $_GET['user_ids'] ) ) : '';
		$user_ids = array_filter( array_map( 'intval', explode( ',', $raw_ids ) ) );

		$users = [];
		foreach ( $user_ids as $user_id ) {
			$user = get_userdata( $user_id );
			if ( $user ) {
				$roles    = $user->roles;
				$role_key = ! empty( $roles ) ? $roles[0] : 'subscriber';
				$users[]  = [
					'id'           => $user->ID,
					'login'        => $user->user_login,
					'display_name' => $user->display_name ?: $user->user_login,
					'email'        => $user->user_email,
					'role'         => $role_key,
					'role_label'   => ucfirst( $role_key ),
					'avatar'       => get_avatar_url( $user->ID, [ 'size' => 48 ] ),
				];
			}
		}
		wp_send_json_success( $users );
	}

	public function switch_user() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized', 'user-switcher' ) ], 401 );
		}

		$switch_from = get_current_user_id();

		if ( ! isset( $_POST['user_id'] ) ) {
			wp_send_json_error( [ 'message' => __( 'No user selected', 'user-switcher' ) ] );
		}

		$switch_to_user = intval( sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) );

		$switch_data = [
			'switch_from'    => $switch_from,
			'switch_to_user' => $switch_to_user,
			'switched_at'    => time(),
		];

		user_switcher_us_set_cookie( 'user_switch_data', wp_json_encode( $switch_data ), time() + DAY_IN_SECONDS );

		$login_url = user_switcher_encrypted_login_url( $switch_to_user );

		wp_send_json_success( [
			'url'     => $login_url,
			'message' => __( 'User switched successfully!', 'user-switcher' ),
		] );
	}

	public function remove_cookie() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized', 'user-switcher' ) ], 401 );
		}

		user_switcher_remove_cookie( 'user_switch_data' );

		wp_send_json_success( [ 'message' => __( 'Cookie Removed', 'user-switcher' ) ] );
	}
}
