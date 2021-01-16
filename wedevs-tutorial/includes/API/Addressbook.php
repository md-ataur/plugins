<?php
namespace WeDevs\Tutorial\API;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Server;

class Addressbook extends WP_REST_Controller {

    public function __construct() {
        /* Create Namespace */
        $this->namespace = 'academy/v1';
        /* Create Route */
        $this->rest_base = 'contacts';
    }

    /**
     * Register the routes
     *
     * @return void
     */
    public function register_route() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE, // GET Request
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'callback'            => [$this, 'get_items'],
                    'args'                => $this->get_collection_params(),
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE, // POST Request
                    'permission_callback' => [$this, 'create_item_permissions_check'],
                    'callback'            => [$this, 'create_item'],
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                ],
                'schema' => [$this, 'get_item_schema'],
            ]
        );

        /* This route for single item */
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                'args'   => [
                    'id' => [
                        'description' => __( 'Unique identifier for the object.' ),
                        'type'        => 'integer',
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'permission_callback' => [$this, 'get_item_permissions_check'],
                    'callback'            => [$this, 'get_item'],
                    'args'                => [
                        'context' => $this->get_context_param( ['default' => 'view'] ),
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'permission_callback' => [$this, 'update_item_permissions_check'],
                    'callback'            => [$this, 'update_item'],
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [$this, 'delete_item'],
                    'permission_callback' => [$this, 'delete_item_permissions_check'],
                ],
                'schema' => [$this, 'get_item_schema'],
            ]
        );
    }

/**
 * Fetch All Addresses/Items Mechanism
 * ==============================================================================
 */
    /**
     * Permission Check for get itmes
     *
     * @param  \WP_REST_Request $request
     *
     * @return boolean
     */
    public function get_items_permissions_check( $request ) {
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve a list of contact items.
     *
     * @param  \WP_Rest_Request $request
     *
     * @return \WP_Rest_Response|WP_Error
     */
    public function get_items( $request ) {
        $args = [];

        /* Get Collection Params */
        $params = $this->get_collection_params();

        foreach ( $params as $key => $value ) {
            if ( isset( $request[$key] ) ) {
                $args[$key] = $request[$key];
            }
        }

        /* change 'per_page' to 'number' */
        $args['number'] = $args['per_page'];
        $args['offset'] = $args['number'] * ( $args['page'] - 1 );

        /* unset others */
        unset( $args['per_page'] );
        unset( $args['page'] );

        $data     = [];
        $contacts = get_addresses( $args );
        foreach ( $contacts as $item ) {
            /* Prepare the item for the REST response */
            $response = $this->prepare_item_for_response( $item, $request );
            //return $response;

            /* Prepare a response for insertion into a collection. */
            $data[] = $this->prepare_response_for_collection( $response );
        }

        /* Paginate calculate */
        $total     = address_count();
        $max_pages = ceil( $total / (int) $args['number'] );

        /* Pass the data */
        $response = rest_ensure_response( $data );

        /* Value pass for the Paginate */
        $response->header( 'X-WP-Total', (int) $total );
        $response->header( 'X-WP-TotalPages', (int) $max_pages );

        return $response;

    }

    /**
     * Prepare links for the request.
     *
     * @param \WP_Post $post Post object.
     *
     * @return array Links for the given post.
     */
    protected function prepare_links( $item ) {
        $base = sprintf( '%s/%s', $this->namespace, $this->rest_base );

        $links = [
            'self'       => [
                'href' => rest_url( trailingslashit( $base ) . $item->id ),
            ],
            'collection' => [
                'href' => rest_url( $base ),
            ],
        ];

        return $links;
    }

/**
 * Create/Insert Item Mechanism
 * ==============================================================================
 */
    /**
     * Checks if a given request has access to create items.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|bool
     */
    public function create_item_permissions_check( $request ) {
        return $this->get_items_permissions_check( $request );
    }

    /**
     * Create one item from the collection.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|WP_REST_Response
     */
    public function create_item( $request ) {
        $contact = $this->prepare_item_for_database( $request );

        if ( is_wp_error( $contact ) ) {
            return $contact;
        }

        $contact_id = insert_address( $contact );

        if ( is_wp_error( $contact_id ) ) {
            $contact_id->add_data( ['status' => 400] );

            return $contact_id;
        }

        /* Fetch the item for response */
        $contact = $this->get_contact( $contact_id );

        /* Prepare the item for the REST response */
        $response = $this->prepare_item_for_response( $contact, $request );

        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $contact_id ) ) );

        return rest_ensure_response( $response );
    }

    /**
     * Prepare one item for create or update operation.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|object
     */
    protected function prepare_item_for_database( $request ) {
        $prepared = [];

        if ( isset( $request['name'] ) ) {
            $prepared['name'] = $request['name'];
        }

        if ( isset( $request['address'] ) ) {
            $prepared['address'] = $request['address'];
        }

        if ( isset( $request['phone'] ) ) {
            $prepared['phone'] = $request['phone'];
        }

        return $prepared;
    }

/**
 * Edit Item Mechanism
 * ==============================================================================
 */
    /**
     * Get the single item, if the ID is valid.
     *
     * @param int $id Supplied ID.
     *
     * @return Object|\WP_Error
     */
    protected function get_contact( $id ) {
        $contact = get_address_by_id( $id );

        if ( ! $contact ) {
            return new WP_Error( 'rest_contact_invalid_id', __( 'Invalid contact ID.' ), ['status' => 404] );
        }

        return $contact;
    }

    /**
     * Check if a given request has access to get a specific item.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|bool
     */
    public function get_item_permissions_check( $request ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        $contact = $this->get_contact( $request['id'] );

        if ( is_wp_error( $contact ) ) {
            return $contact;
        }

        return true;
    }

    /**
     * Retrieve one item from the collection.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|\WP_REST_Response
     */
    public function get_item( $request ) {
        $contact = $this->get_contact( $request['id'] );

        $response = $this->prepare_item_for_response( $contact, $request );
        $response = rest_ensure_response( $response );

        return $response;
    }

/**
 * Update Item Mechanism
 * ==============================================================================
 */
    /**
     * Checks if a given request has access to update a specific item.
     *
     * @param \WP_REST_Request $request Full data about the request.
     *
     * @return \WP_Error|bool
     */
    public function update_item_permissions_check( $request ) {
        return $this->get_item_permissions_check( $request );
    }

    /**
     * Update one item from the collection.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|\WP_REST_Response
     */
    public function update_item( $request ) {
        /* Fetch item */
        $contact = $this->get_contact( $request['id'] );
        /* Prepare item */
        $prepared = $this->prepare_item_for_database( $request );

        $prepared = array_merge( (array) $contact, $prepared );

        $updated = insert_address( $prepared );

        if ( ! $updated ) {
            return new WP_Error( 'rest_not_updated', __( 'Sorry, the address could not be updated.' ), ['status' => 400] );
        }

        $contact  = $this->get_contact( $request['id'] );
        $response = $this->prepare_item_for_response( $contact, $request );

        return rest_ensure_response( $response );
    }

/**
 * Delete Item Mechanism
 * ==============================================================================
 */

    /**
     * Checks if a given request has access to delete a specific item.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|bool
     */
    public function delete_item_permissions_check( $request ) {
        return $this->get_item_permissions_check( $request );
    }

    /**
     * Delete one item from the collection.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|WP_REST_Response
     */
    public function delete_item( $request ) {
        $contact  = $this->get_contact( $request['id'] );
        $previous = $this->prepare_item_for_response( $contact, $request );

        $deleted = delete_address_by_id( $request['id'] );

        if ( ! $deleted ) {
            return new WP_Error( 'rest_not_deleted', __( 'Sorry, the address could not be deleted.' ), [ 'status' => 400 ] );
        }

        $data = [
            'deleted'  => true,
            'previous' => $previous->get_data(),
        ];

        $response = rest_ensure_response( $data );

        return $data;
    }

/**
 * Others Mechanism
 * ==============================================================================
 */
    /**
     * Prepare the item for the REST response.
     *
     * @param mixed           $item    WordPress representation of the item.
     * @param \WP_REST_Request $request Request object.
     *
     * @return \WP_Error|WP_REST_Response
     */
    public function prepare_item_for_response( $item, $request ) {
        $data   = [];
        $fields = $this->get_fields_for_response( $request );

        if ( in_array( 'id', $fields, true ) ) {
            $data['id'] = (int) $item->id;
        }

        if ( in_array( 'name', $fields, true ) ) {
            $data['name'] = $item->name;
        }

        if ( in_array( 'address', $fields, true ) ) {
            $data['address'] = $item->address;
        }

        if ( in_array( 'phone', $fields, true ) ) {
            $data['phone'] = $item->phone;
        }

        if ( in_array( 'date', $fields, true ) ) {
            $data['date'] = mysql_to_rfc3339( $item->created_at );
        }

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
        $data    = $this->filter_response_by_context( $data, $context );

        $response = rest_ensure_response( $data );

        /* Add multiple links to the response */
        $response->add_links( $this->prepare_links( $item ) );

        return $response;
    }

    /**
     * Retrieve the contact schema, conforming to JSON Schema.
     *
     * @return array
     */
    public function get_item_schema() {
        /* If has schema then return the schema otherwise create new schema */
        if ( $this->schema ) {
            return $this->add_additional_fields_schema( $this->schema );
        }

        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'contact',
            'type'       => 'object',
            'properties' => [
                'id'      => [
                    'description' => __( 'Unique identifier for the object.' ),
                    'type'        => 'integer',
                    'context'     => ['view', 'edit'],
                    'readonly'    => true,
                ],
                'name'    => [
                    'description' => __( 'Name of the contact.' ),
                    'type'        => 'string',
                    'context'     => ['view', 'edit'],
                    'required'    => true,
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'address' => [
                    'description' => __( 'Address of the contact.' ),
                    'type'        => 'string',
                    'context'     => ['view', 'edit'],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ],
                ],
                'phone'   => [
                    'description' => __( 'Phone number of the contact.' ),
                    'type'        => 'string',
                    'required'    => true,
                    'context'     => ['view', 'edit'],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'date'    => [
                    'description' => __( "The date the object was published, in the site's timezone." ),
                    'type'        => 'string',
                    'format'      => 'date-time',
                    'context'     => ['view'],
                    'readonly'    => true,
                ],
            ],
        ];

        /* Assign in the parent schema */
        $this->schema = $schema;

        /* Return parent schema */
        return $this->add_additional_fields_schema( $this->schema );
    }

    /**
     * Retrieve the query params for collections and unset the search param.
     *
     * @return array
     */
    public function get_collection_params() {
        $params = parent::get_collection_params();

        unset( $params['search'] );

        return $params;
    }
}
