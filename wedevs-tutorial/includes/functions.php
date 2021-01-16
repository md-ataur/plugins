<?php
/**
 * Insert and update mechanism
 *
 * @param array $args
 *
 * @return int/WP_errors
 *
 **/
function insert_address( $args = [] ) {
    global $wpdb;

    if ( empty( $args['name'] ) ) {
        return new \WP_Error( 'no-name', __( 'You must provide a name.', 'wedevs-tutorial' ) );
    }

    $defaults = [
        'name'       => '',
        'address'    => '',
        'phone'      => '',
        'created_by' => get_current_user_id(),
        'created_at' => current_time( 'mysql' ),
    ];

    /* Merge user defined arguments into defaults array */
    $data = wp_parse_args( $args, $defaults );

    if ( isset( $data['id'] ) ) {

        /**
         * Update mechanism
         *
         * update( $table, $data, $where, $format = null, $where_format = null );
         */
        $id = $data['id'];
        unset( $data['id'] );

        $updated = $wpdb->update(
            $wpdb->prefix . 'address_book',
            $data,
            ['id' => $id],
            [
                '%s', // string value
                '%s',
                '%s',
                '%d', // integer value
                '%s',
            ],
            ['%d']
        );

        return $updated;

    } else {

        /**
         * Insert mechanism
         *
         * insert( $table, $data, $format );
         */
        $inserted = $wpdb->insert(
            $wpdb->prefix . 'address_book',
            $data,
            [
                '%s', // string value
                '%s',
                '%s',
                '%d', // integer value
                '%s',
            ]
        );

        if ( ! $inserted ) {
            return new \WP_Error( 'failed-to-insert', __( 'Failed to insert data', 'wedevs-tutorial' ) );
        }

        return $wpdb->insert_id;
    }
}

/**
 * Fetch all data
 *
 * @param  array  $args
 *
 * @return array
 */
function get_addresses( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'  => 20,    // how many addresses you want to show
        'offset'  => 0,     // offset for pagination
        'orderby' => 'id',
        'order'   => 'DESC',
    ];

    /* Merge user defined arguments into defaults array */
    $args = wp_parse_args( $args, $defaults );

    $sql = $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}address_book
        ORDER BY {$args['orderby']} {$args['order']}
        LIMIT %d, %d",
        $args['offset'], $args['number']
    );

    $items = $wpdb->get_results( $sql );

    return $items; // pass $items in this method get_addresses( $args = [] )
}

/**
 * How many addresses have inside the table. Get the count of total address
 *
 * @return int
 */
function address_count() {
    global $wpdb;
    return (int) $wpdb->get_var( "SELECT count(id) FROM {$wpdb->prefix}address_book" );
}

/**
 * Fetch a single data
 *
 * @param  int $id
 *
 * @return object
 */
function get_address_by_id( $id ) {
    global $wpdb;
    return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}address_book WHERE id = %d", $id, ) );
}

/**
 * Delete mechanism
 *
 * delete( $table, $where, $where_format = null );
 *
 * @param  int $id
 *
 * @return int|boolean
 */
function delete_address_by_id( $id ) {
    global $wpdb;

    return $wpdb->delete(
        $wpdb->prefix . 'address_book',
        ['id' => $id],
        ['%d']
    );
}