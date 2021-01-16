<?php
/*
Plugin Name: weDevs Tutorial
Plugin URI: http://example.com
Description:
Version: 1.0
Author: Ataur Rahman
Author URI: md-ataur.github.io
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wedevs-tutorial
*/

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Required Autoloader
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Prevent inheritance of a class using the (final) keyword
 */
final class WeDevs_Tutorial {

    /**
     * Plugin version
     */
    const version = '1.0';

    /**
     * Class Constructor
     */
    private function __construct() {

        $this->define_constans();

        register_activation_hook( __FILE__, [$this, 'activate'] );

        add_action( 'plugins_loaded', [$this, 'init_plugin'] );
    }

    /**
     * Initialize a Singleton Instance
     *
     * A singleton is a particular kind of class that can be instantiated only once.
     * What "instantiated only once means?" It simply means that if an object of that class was already instantiated, the system will return it instead of creating new one.
     *
     * @return \WeDevs_Tutorial
     */
    public static function init() {
        static $instance = null;

        if ( $instance === null ) {
            $instance = new WeDevs_Tutorial();
        }
        return $instance;
    }

    /**
     * Constants define
     */
    public function define_constans() {
        define( 'WD_TUTORIAL_VERSION', self::version );
        define( 'WD_TUTORIAL_PATH', __DIR__ );
        define( 'WD_TUTORIAL_ASSETS', plugin_dir_url( __FILE__ ) . 'assets' );
    }

    /**
     * Callback function for plugin activation
     */
    public function activate() {
        $installer = new WeDevs\Tutorial\Installer();
        $installer->run();
    }

    /**
     * Other files and classes initialize
     */
    public function init_plugin() {

        /* Assets object */
        new WeDevs\Tutorial\Assets();

        if ( is_admin() ) {
            /* Object create for Addressbook class */
            $obj = new WeDevs\Tutorial\Admin\Addressbook();

            /* Object create for Menu class and pass the addressbook object */
            new WeDevs\Tutorial\Admin\Menu( $obj );

            /* This action hook for form handler (Addressbook.php) */
            add_action( 'admin_init', [$obj, 'form_handler'] );

            /* This action hook for delete (Addressbook.php) */
            add_action( 'admin_post_wd-delete-address', [$obj, 'delete_address'] );

        } else {
            new WeDevs\Tutorial\Frontend\Shortcode();
        }

        /* Api object */
        new WeDevs\Tutorial\Api();
    }
}

/**
 * Initialize the main plugin
 *
 * @return \WeDevs_Tutorial
 */
function wedevs_tutorial() {
    return WeDevs_Tutorial::init();
}
/* Call the function */
wedevs_tutorial();