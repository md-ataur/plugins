<?php
/**
 * Plugin Name:  User Profile Settings
 * Plugin URI:
 * Description:  User Profile Settings
 * Version:      1.0
 * Author:       Ataur Rahman
 * Author URI:
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  user-profile-settings
 * Domain Path:  /languages
 */


if (!class_exists("UserProfile")) {
	
	class UserProfile{

		function __construct(){
			add_action( "plugins_loaded", array($this,"usp_load_texdomain" ));
			add_filter( "user_contactmethods", array($this, "usp_user_contact_methods") );
		}

		function usp_load_texdomain(){
			load_plugin_textdomain( "user-profile-settings", false, plugin_dir_path( __FILE__ )."/languages" );
		}

		function usp_user_contact_methods($methods){
			$methods['facebook'] = __("Facebook","user-profile-settings");
			$methods['linkedin'] = __("Linked In","user-profile-settings");
			$methods['twitter'] = __("Twitter","user-profile-settings");
			return $methods;
		}
	}

	new UserProfile();
}