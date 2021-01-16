<?php
namespace WeDevs\Tutorial\Admin;

class Menu {

    public $addressbook;

    function __construct( $catch_obj ) {
        /* Catch the object and assign in a property */
        $this->addressbook = $catch_obj;

        /* Menu register hook */
        add_action( 'admin_menu', [$this, 'admin_menu'] );
    }

    /**
     * Call back function for menu hook
     */
    public function admin_menu() {
        $parent_slug = 'wedevs-tutorial';
        $capability  = 'manage_options';

        $hook = add_menu_page( __( 'weDevs Tutorial', 'wedevs-tutorial' ), __( 'weDevs Tutorial', 'wedevs-tutorial' ), $capability, $parent_slug, [$this->addressbook, 'addressbook_page'], 'dashicons-welcome-learn-more' );
        add_submenu_page( $parent_slug, __( 'Address Book', 'wedevs-tutorial' ), __( 'Address Book', 'wedevs-tutorial' ), $capability, $parent_slug, [$this->addressbook, 'addressbook_page'] );
        add_submenu_page( $parent_slug, __( 'Settings', 'wedevs-tutorial' ), __( 'Settings', 'wedevs-tutorial' ), $capability, 'settings-page', [$this, 'settings_page'] );

        /* Load script for a specific page */
        add_action( 'admin_head-' . $hook, [$this, 'enqueue_assets'] );
    }

    /**
     * Call back function for Address Book menu
     */
    /*public function addressbook_page(){
    $address = new Addressbook();
    $address->addressbook();
    } */

    /**
     * Call back function for submenu
     */
    public function settings_page() {
        echo "Hello Settings";
    }

    /**
     * Enqueue scripts and styles
     *
     * @return void
     */
    public function enqueue_assets() {
        wp_enqueue_script( 'admin-js' );
    }
}