<?php
namespace WeDevs\Tutorial\Admin;

if ( !class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * WP List Table Class
 */
class Address_List extends \WP_List_Table {

    function __construct() {
        parent::__construct( [
            'singular' => 'contact',
            'plural'   => 'contacts',
            'ajax'     => false,
        ] );
    }

    /**
     * Message to show if no data found
     *
     * @return void
     */
    function no_items() {
        _e( 'No address found', 'wedevs-tutorial' );
    }

    /**
     * Get the column names
     *
     * @return array
     */
    public function get_columns() {
        return [
            'cb'         => '<input type="checkbox" />',
            'name'       => __( 'Name', 'wedevs-tutorial' ),
            'address'    => __( 'Address', 'wedevs-tutorial' ),
            'phone'      => __( 'Phone', 'wedevs-tutorial' ),
            'created_at' => __( 'Date', 'wedevs-tutorial' ),
        ];
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = [
            'name'       => ['name', true],
            'created_at' => ['created_at', true],
        ];

        return $sortable_columns;
    }

    /**
     * Render the "name" column
     *
     * @param  object $item
     *
     * @return string
     */
    public function column_name( $item ) {
        $actions = [];

        $actions['edit']   = sprintf( '<a href="%s" title="%s">%s</a>', admin_url( 'admin.php?page=wedevs-tutorial&action=edit&id=' . $item->id ), $item->id, __( 'Edit', 'wedevs-tutorial' ), __( 'Edit', 'wedevs-tutorial' ) );
        $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" onclick="return confirm(\'Are you sure?\');" title="%s">%s</a>', wp_nonce_url( admin_url( 'admin-post.php?action=wd-delete-address&id=' . $item->id ), 'delete-address' ), $item->id, __( 'Delete', 'wedevs-tutorial' ), __( 'Delete', 'wedevs-tutorial' ) );

        return sprintf(
            '<a href="%1$s"><strong>%2$s</strong></a> %3$s', admin_url( 'admin.php?page=wedevs-tutorial&action=view&id' . $item->id ), $item->name, $this->row_actions( $actions )
        );
    }

    /**
     * Render the "cb" column
     *
     * @param  object $item
     *
     * @return string
     */
    protected function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="address_id[]" value="%d" />', $item->id
        );
    }

    /**
     * Prepare items
     *
     * @return void
     */
    public function prepare_items() {
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];

        /* Sorting orderby = Column Name and order = Asc or Desc */
        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'];
        }

        /* Pagination */
        $per_page     = 4;
        $current_page = $this->get_pagenum();
        $offset       = ( $current_page - 1 ) * $per_page;

        $this->set_pagination_args( [
            'total_items' => address_count(),
            'per_page'    => $per_page,
        ] );

        $args = [
            'number' => $per_page,
            'offset' => $offset,
        ];

        /* Assign the table data inside the items variable */
        $this->items = get_addresses( $args );

    }

    /**
     * Get columns value
     *
     * @param  object $item
     * @param  string $column_name
     *
     * @return string
     */
    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'created_at':
                // Data format
                return wp_date( get_option( 'date_format' ), strtotime( $item->created_at ) );
            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }
}