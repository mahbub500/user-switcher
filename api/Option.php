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
class Option {
	
	public function add( $request ) {
        $key    = $request->get_param( 'key' );
        $value  = $request->get_param( 'value' );

        if( empty( $key ) ) return;

        return add_option( $key, $value );
    }
    
    public function update( $request ) {
        $key    = $request->get_param( 'key' );
        $value  = $request->get_param( 'value' );

        if( empty( $key ) ) return;

        return update_option( $key, $value );
    }
    
    public function get( $request ) {
        $key        = $request->get_param( 'key' );
        $default    = $request->get_param( 'default' );

        if( empty( $key ) ) return;

        return get_option( $key, $default );
    }
}