<?php
/**
 * Plugin Name:  ACF Demo
 * Plugin URI:
 * Description:  Advanced custom fields
 * Version:      1.0
 * Author:       Ataur Rahman
 * Author URI:
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  acf-demo
 * Domain Path:  /languages
 */

if (!class_exists("AcfDemo")) {
	
	class AcfDemo{

		function __construct(){
			/* Tgm library include */
			require_once plugin_dir_path( __FILE__ ) . 'lib/class-tgm-plugin-activation.php';			
			/* Hook for plugin loaded */
			add_action( "plugins_loaded", array( $this,"acfd_load_textdomain" ) );			
			/* Hook for tgm register */
			add_action( "tgmpa_register", array( $this,"acfd_register_required_plugins" ));
		}

		function acfd_load_textdomain(){
			load_plugin_textdomain( "acf-demo", false, plugin_dir_path( __FILE__ )."/languages" );
		}

		function acfd_register_required_plugins() {
			
			$plugins = array(
				array(
					'name'      => 'Advanced Custom Fields',
					'slug'      => 'advanced-custom-fields',
					'required'  => true,
				),
			);

			$config = array(
				'id'           => 'acf-demo',              	
				'default_path' => '',                      
				'menu'         => 'tgmpa-install-plugins', 	
				'parent_slug'  => 'plugins.php',           	
				'capability'   => 'manage_options',    		
				'has_notices'  => true,                    	
				'dismissable'  => true,                    	
				'dismiss_msg'  => '',                      
				'is_automatic' => false,                   	
				'message'      => '', 
			);

			tgmpa( $plugins, $config );
		}
	}
	new AcfDemo();
}