<?php
/*
Plugin Name: Notice Ninja
Plugin URI: http://example.com
Description: 
Version: 1.0
Author: Ataur Rahman
Author URI: md-ataur.github.io
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: notice-ninja
*/

if (!class_exists("NoticeNinja")) {
    class NoticeNinja{
        public function __construct() {
            add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
            add_action( 'admin_notices', array($this, 'noticeninja_admin_notice') );
            add_action( 'admin_enqueue_scripts', array($this, 'load_scripts'));
        }

        /* Load Textdomain */
        function load_textdomain() {
            load_plugin_textdomain( 'popupcreator', false, plugin_dir_path( __FILE__ ) . "languages" );
        }

        function load_scripts(){
            wp_enqueue_script('notice-js', plugin_dir_url(__FILE__).'assets/js/notice.js', array('jquery'), time(), true);
        }

        function noticeninja_admin_notice(){            
            global $pagenow;
            if(in_array($pagenow, ['index.php', 'themes.php'])){            
                if(!(isset($_COOKIE['notice']) && $_COOKIE['notice'] == 1)){
                    ?>
                    <div id="noticeninja" class="notice notice-success is-dismissible">
                        <p>Welcome to Notice!</p>
                    </div>
                <?php
                }
            }
        }
    }

    new NoticeNinja;
}