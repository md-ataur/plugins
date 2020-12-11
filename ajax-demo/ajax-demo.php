<?php
/*
Plugin Name: Ajax Demo
Plugin URI:
Description: Ajax Demo
Version: 1.0.0
Author: Ataur Rahman
Author URI:
License: GPLv2 or later
Text Domain: ajax-demo
*/

add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( 'toplevel_page_ajax-demo' == $hook ) {
        wp_enqueue_style( 'pure-grid-css', '//unpkg.com/purecss@1.0.1/build/grids-min.css' );
        wp_enqueue_style( 'ajax-demo-css', plugin_dir_url( __FILE__ ) . "assets/css/style.css", null, time() );
        wp_enqueue_script( 'ajax-demo-js', plugin_dir_url( __FILE__ ) . "assets/js/main.js", array( 'jquery' ), time(), true );

        $action = 'ajd_protected';
        $ajd_nonce = wp_create_nonce( $action );
        wp_localize_script(
            'ajax-demo-js',
            'plugindata',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'ajd_nonce' => $ajd_nonce )
        );

        wp_localize_script( 'ajax-demo-js', 'bucket', array('name' => 'Hasin Hayder', 'email' => 'me@hasin.me') );
    }
} );

add_action( 'admin_menu', function () {
    add_menu_page( 'Ajax Demo', 'Ajax Demo', 'manage_options', 'ajax-demo', 'ajaxdemo_admin_page' );
} );

function ajaxdemo_admin_page() {
    ?>
        <div class="container" style="padding-top:20px;">
            <h1>Ajax Demo</h1>
            <div class="pure-g">
                <div class="pure-u-1-4" style='height:100vh;'>
                    <div class="plugin-side-options">
                        <button class="action-button" data-task='simple_ajax_call'>Simple Ajax Call</button>
                        <button class="action-button" data-task='unp_ajax_call'>Unprivileged Ajax Call</button>
                        <button class="action-button" data-task='ajd_localize_script'>Why wp_localize_script</button>
                        <button class="action-button" data-task='ajd_secure_ajax_call'>Security with Nonce</button>
                    </div>
                </div>
                <div class="pure-u-3-4">
                    <div class="plugin-demo-content">
                        <h3 class="plugin-result-title">Result</h3>
                        <div id="plugin-demo-result" class="plugin-result"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php
}

// By default Only logged in users
add_action( 'wp_ajax_ajd_simple', function () {
    $data = $_POST['data'];
    echo "Hello " . strtoupper( $data );
    die();
} );


function ajd_processor() {
    $data = $_POST['data'];
    echo "Hello " . strtoupper( $data );
    die();
}
// Only logged in users
add_action( 'wp_ajax_ajd_priv', 'ajd_processor' );
// Not logged in users
add_action( 'wp_ajax_nopriv_ajd_priv', 'ajd_processor' );


add_action( 'wp_ajax_ajd_process_user', function () {
    $person = $_POST['person'];
    echo "The Email Address Of {$person['name']} is {$person['email']}";
    die();
} );


/* Nonce check */
function ajd_protected() {
    $secret = $_POST['secret'];
    $nonce = $_POST['ajd_nonce'];
    $action = 'ajd_protected';
    if ( wp_verify_nonce( $nonce, $action ) ) {
        echo "Authorized ".strtoupper($secret);
    }else{
        echo "You are not authorized";
    }
    die();
}
// Only logged in users
add_action( 'wp_ajax_ajd_protected', 'ajd_protected' );
// Not logged in users
add_action( 'wp_ajax_nopriv_ajd_protected', 'ajd_protected' );