<?php
/**
 * Plugin Name:  QuickTags Demo
 * Plugin URI:
 * Description:  Demonstration for QuickTags
 * Version:      1.0
 * Author:       Ataur Rahman
 * Author URI:
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  quicktags-demo
 * Domain Path:  /languages
 */


if (!class_exists("QuickTags")) {
	
	class QuickTags{

		function __construct(){
			add_action( "plugins_loaded", array($this,"quicktags_load_textdomain") );
			add_action( "admin_enqueue_scripts", array($this,"quicktags_assets") );
		}

		function quicktags_load_textdomain(){
			load_plugin_textdomain( "quicktags-demo", false, plugin_dir_path( __FILE__ )."/languages" );
		}

		function quicktags_assets($screen){
			if ("post.php" == $screen) {
				wp_enqueue_script( "quicktags-qt-js", plugin_dir_url( __FILE__ )."assets/admin/js/qt.js", array("quicktags"), time(), true );
			}
		}
	}

	new QuickTags();
}