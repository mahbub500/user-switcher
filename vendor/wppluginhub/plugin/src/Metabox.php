<?php
namespace WpPluginHub\Plugin;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @package Plugin
 * @subpackage Metabox
 * @author WpPluginHub <mahbubmr500@gmil.com>
 */
class Metabox extends Fields {

	/**
	 * @var array $config
	 */
	public $config;

	/**
	 * @var array $sections
	 */
	public $sections;

	public function __construct( $args = [] ) {

		// default values
		$defaults = [
			'id'			=> 'wph-metabox',
			'label'			=> __( 'Metabox' ),
			'post_type'		=> [ 'post', 'page' ],
			'context'		=> 'normal',
			'box_priority'	=> 'high',
			'sections'		=> [],
		];

		$this->config 	= wp_parse_args( apply_filters( 'wph-metabox-args', $args ), $defaults );
		$this->sections	= apply_filters( 'wph-metabox-sections', $this->config['sections'] );

		parent::hooks();
		self::hooks();
	}

	public function hooks() {
		$this->action( 'admin_enqueue_scripts', 'enqueue_scripts', 99 );
		$this->action( 'add_meta_boxes' , 'add_meta_box' );
		$this->action( 'save_post', 'save_meta_fields', 10, 2 );
	}

	public function enqueue_scripts() {
		if( get_the_ID() != '' && in_array( get_post_type( get_the_ID() ), (array) $this->config['post_type'] ) ) {
			parent::enqueue_scripts();
		}
    }

	public function add_meta_box() {
		add_meta_box( $this->config['id'], $this->config['label'], [ $this, 'callback_fields' ], (array) $this->config['post_type'], $this->config['context'], $this->config['box_priority'] );
	}
	
	public function save_meta_fields( $post_id, $post ) {
		foreach ( $this->sections as $id => $section ) {
			if( isset( $_POST[ $id ] ) ) {
				update_post_meta( $post->ID, $id, $_POST[ $id ] );
			}
		}
	}
}