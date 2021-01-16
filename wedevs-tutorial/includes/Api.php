<?php
namespace WeDevs\Tutorial;

class Api {

    function __construct() {
        add_action( 'rest_api_init', [$this, 'register_api'] );
    }

    public function register_api() {
        $addressbook = new API\Addressbook();
        $addressbook->register_route();
    }
}
