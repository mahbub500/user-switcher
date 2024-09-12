<?php
namespace Codexpert\User_Switcher\App;

use Codexpert\Plugin\Base;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @package Plugin
 * @subpackage Shortcode
 * @author Codexpert <hi@codexpert.io>
 */
class Shortcode extends Base {

    public $plugin;
    
    public $slug;

    public $name;

    public $version;

    /**
     * Constructor function
     */
    public function __construct() {
        $this->plugin   = USER_SWITCHER;
        $this->slug     = $this->plugin['TextDomain'];
        $this->name     = $this->plugin['Name'];
        $this->version  = $this->plugin['Version'];
    }

    public function my_shortcode() {
        return __( 'My Shortcode', 'user-switcher' );
    }
}