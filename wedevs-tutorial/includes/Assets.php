<?php
namespace WeDevs\Tutorial;

/**
 * Assets handlers class
 */
if ( !class_exists( 'Assets' ) ) {

    class Assets {

        function __construct() {
            add_action( 'wp_enqueue_scripts', [$this, 'register_frontend_assets'] );
            add_action( 'admin_enqueue_scripts', [$this, 'register_admin_assets'] );
        }

        public function register_frontend_assets() {
            /**
             * Best practice
             *
             * First of all you need to register the script then you can enqueue the script.
             * In this way you can use the script for a specific page like (Admin Menu)
             */
            wp_register_style( 'frontend-style', WD_TUTORIAL_ASSETS . '/css/frontend.css', false, filemtime( WD_TUTORIAL_PATH . '/assets/css/frontend.css' ) );
            wp_register_script( 'frontend-js', WD_TUTORIAL_ASSETS . '/js/frontend.js', ['jquery'], filemtime( WD_TUTORIAL_PATH . '/assets/js/frontend.js' ), true );

            wp_enqueue_style( 'frontend-style' );
            wp_enqueue_script( 'frontend-js' );

        }

        public function register_admin_assets() {
            wp_register_script( 'admin-js', WD_TUTORIAL_ASSETS . '/js/admin.js', ['jquery'], filemtime( WD_TUTORIAL_PATH . '/assets/js/admin.js' ), true );
        }
    }
}
