<?php
/**
 * Plugin Name:  Codestar Demo
 * Plugin URI:
 * Description:  Codestar Framework
 * Version:      1.0
 * Author:       Ataur Rahman
 * Author URI:
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  codestar-demo
 * Domain Path:  /languages
 */


if (!class_exists("CsfDemo")) {
	
	class CsfDemo{

		function __construct(){
			add_action( "codestar-demo", array( $this,"csf_load_textdomain" ) );

			require_once plugin_dir_path( __FILE__ ) . 'lib/codestar/cs-framework.php';			
			define( 'CS_ACTIVE_FRAMEWORK',   false  ); // default true
			define( 'CS_ACTIVE_METABOX',     true ); // default true
			define( 'CS_ACTIVE_TAXONOMY',    false ); // default true
			define( 'CS_ACTIVE_SHORTCODE',   false ); // default true
			define( 'CS_ACTIVE_CUSTOMIZE',   false ); // default true
			define( 'CS_ACTIVE_LIGHT_THEME',  true  ); // default false

			add_action( "init", array($this,"csf_metabox"));

			if (function_exists('cs_framework_init')) {
				require_once plugin_dir_path( __FILE__ ) . 'inc/metabox.php';
			}
		}

		function csf_load_textdomain(){
			load_plugin_textdomain( "codestar-demo", false, plugin_dir_path( __FILE__ )."/languages" );
		}

		function csf_metabox(){
			CSFramework_Metabox::instance( array() );
		}


	}
	new CsfDemo();
}