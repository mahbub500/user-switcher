<?php
namespace Codexpert\User_Switcher\API;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @package Plugin
 * @subpackage API
 * @author Codexpert <hi@codexpert.io>
 */
class User {
    
    public function add_meta( $request ) {
        $user_id    = $request->get_param( 'user_id' );
        $key        = $request->get_param( 'key' );
        $value      = $request->get_param( 'value' );

        if( empty( $key ) ) return;

        return add_user_meta( $user_id, $key, $value );
    }
    
    public function update_meta( $request ) {
        $user_id    = $request->get_param( 'user_id' );
        $key        = $request->get_param( 'key' );
        $value      = $request->get_param( 'value' );

        if( empty( $key ) ) return;

        return update_user_meta( $user_id, $key, $value );
    }
    
    public function get_meta( $request ) {
        $user_id    = $request->get_param( 'user_id' );
        $key        = $request->get_param( 'key' );
        $single     = $request->get_param( 'single' );

        if( empty( $key ) ) return;

        return get_user_meta( $user_id, $key, $single );
    }
}