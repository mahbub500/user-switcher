<?php
namespace Codexpert\Plugin;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Include required files and fix pagination
 */
if ( !function_exists('convert_to_screen') || ( !class_exists( 'WP_List_Table' ) && !is_admin() ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    require_once( ABSPATH . 'wp-admin/includes/screen.php' );
    require_once( ABSPATH . 'wp-admin/includes/class-wp-screen.php' );
    require_once( ABSPATH . 'wp-admin/includes/template.php' );

    /**
     * Fixes pagination
     */
    if( !isset( $_REQUEST['paged'] ) ) {
        $_REQUEST['paged'] = explode( '/page/', $_SERVER['REQUEST_URI'], 2 );
        if( isset( $_REQUEST['paged'][ 1 ] ) ) list( $_REQUEST['paged'], ) = explode( '/', $_REQUEST['paged'][ 1 ], 2 );
        if( isset( $_REQUEST['paged'] ) and $_REQUEST['paged'] != '' ) {
            $_REQUEST['paged'] = intval( $_REQUEST['paged'] );
            if( $_REQUEST['paged'] < 2 ) $_REQUEST['paged'] = '';
        } else {
            $_REQUEST['paged'] = '';
        }
    }
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Table extends \WP_List_Table {
    
    public $config;

    /**
     * Constructor function
     */
    public function __construct( $config ) {
        $this->config = wp_parse_args( $config, [ 'screen' => 'default' ] );
        parent::__construct( $this->config );
    }

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items() {
        $columns        = $this->get_columns();
        $hidden         = $this->get_hidden_columns();
        $sortable       = $this->get_sortable_columns();

        $data           = $this->table_data();
        
        if( isset( $this->config['orderby'] ) ) {
            usort( $data, [ &$this, 'sort_data' ] );
        }

        $per_page       = isset( $this->config['per_page'] ) ? $this->config['per_page'] : 10;
        $current_page   = $this->get_pagenum();
        $total_items    = count( $data );

        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page
        ] );

        $data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

        $this->_column_headers = [ $columns, $hidden, $sortable ];
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns() {
        $columns = $this->config['columns'];

        if( isset( $this->config['bulk_actions'] ) && count( $this->config['bulk_actions'] ) > 0 ) {
            $columns = [ 'cb' => '<input type="checkbox" />' ] + $columns;
        }

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns() {
        return isset( $this->config['hidden'] ) ? $this->config['hidden'] : [];
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns() {

        $sortable = isset( $this->config['sortable'] ) ? $this->config['sortable'] : array_keys( $this->config['columns'] );

        $columns = [];
        foreach ( $sortable as $column ) {
            $columns[ $column ] = [ $column, false ];
        }

        return $columns;
    }

    /**
     * Get the table views
     *
     * @return Array
     */
    protected function get_views() {
        return isset( $this->config['views'] ) ? $this->config['views'] : [];
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data() {
        return $this->config['data'];
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name ) {
        return $item[ $column_name ];
    }

    /**
     * Generates and display row actions links for the list table.
     *
     * @since 4.3.0
     *
     * @param object $item        The item being acted upon.
     * @param string $column_name Current column name.
     * @param string $primary     Primary column name.
     * @return empty string
     */
    protected function handle_row_actions( $item, $column_name, $primary ) {
        return;
    }

    public function column_cb( $item ) {
        if( !isset( $this->config['bulk_actions'] ) || count( $this->config['bulk_actions'] ) <= 0 ) return;

        return sprintf( '<input type="checkbox" name="%s[]" value="%s" />', 'ids', isset( $item['id'] ) ? $item['id'] : $item[ $this->config['orderby'] ] );
    }

    public function get_bulk_actions() {
        $actions = [];
        
        if( !isset( $this->config['bulk_actions'] ) || count( $this->config['bulk_actions'] ) <= 0 ) return $actions;

        foreach ( $this->config['bulk_actions'] as $action => $label ) {
            $actions[ $action ] = $label;
        }

        return $actions;
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b ) {
        // Set defaults
        $orderby = isset( $this->config['orderby'] ) ? $this->config['orderby'] : 'id';
        $order = isset( $this->config['order'] ) ? $this->config['order'] : 'desc';

        // If orderby is set, use this as the sort column
        if( ! empty( $_GET['orderby'] ) ) {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if( ! empty( $_GET['order'] ) ) {
            $order = $_GET['order'];
        }

        $result = strnatcmp( $a[ $orderby ], $b[ $orderby ] );

        if( $order === 'asc' ) {
            return $result;
        }

        return -$result;
    }

    /**
     * Extra table nav section
     */
    public function extra_tablenav( $which ) {
		do_action( 'cx-plugin_tablenav', $this->config, $which );
	}

    /**
     * Overwrites the parent method
     */
    public function single_row( $item ) {
        printf( '<tr class="%s">', apply_filters( 'cx-plugin_table_row_class', 'cx-table-row', $item ) );
        $this->single_row_columns( $item );
        printf( '</tr>' );
    }
}