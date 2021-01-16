<?php
/**
 * Plugin Name:  Assets Ninja
 * Plugin URI:
 * Description:  Assets management in depth
 * Version:      1.0
 * Author:       Ataur Rahman
 * Author URI:
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  assets-ninja
 * Domain Path:  /languages
 */

/**
 *  OOP Way
 */

define( "ASN_ASSETS_DIR", plugin_dir_url( __FILE__ ) . "assets" );
define( "ASN_VERSION", time() );

if ( !class_exists( "AssetsNinja" ) ) {

    class AssetsNinja {

        function __construct() {

            /* add_action('init',array($this,'asn_init')); */

            add_action( "plugins_loaded", array( $this, "load_textdomain" ) );
            add_action( "wp_enqueue_scripts", array( $this, "frontend_assets" ) );
            add_action( "admin_enqueue_scripts", array( $this, "admin_assets" ) );
        }

        function load_textdomain() {
            load_plugin_textdomain( "assets-ninja", false, plugin_dir_url( __FILE__ ) . "/languages" );
        }

        /*
        // Assets register and deregister
        function asn_init(){
			wp_deregister_style('fontawesome-css');
			wp_register_style('fontawesome-css','//use.fontawesome.com/releases/v5.2.0/css/all.css');
        	wp_deregister_script('tinyslider-js');
        	wp_register_script('tinyslider-js','//cdn.jsdelivr.net/npm/tiny-slider@2.8.5/dist/tiny-slider.min.js',null,'1.0',true);
        }*/

        function frontend_assets() {
            wp_enqueue_script( "asn-main-js", ASN_ASSETS_DIR . "/public/js/main.js", array( "jquery" ), ASN_VERSION, true );
            $data = array(
                'name' => 'Hello World',
                'url'  => 'https://hello.com',
            );
            wp_localize_script( "asn-main-js", "sitedata", $data );
        }

        function admin_assets( $page ) {
            $_screen = get_current_screen();
			
			if ( "edit.php" == $page && "page" == $_screen->post_type ) {
                wp_enqueue_script( "asn-admin-js", ASN_ASSETS_DIR . "/admin/js/admin.js", array( "jquery" ), ASN_VERSION, true );
            }

			/* if('edit-tags.php' == $screen && 'category' == $_screen->taxonomy){
			wp_enqueue_script( 'asn-admin-js', ASN_ASSETS_DIR . "/admin/js/admin.js", array( 'jquery' ), VERSION, true );
			} */

        }

    }

    new AssetsNinja();
}