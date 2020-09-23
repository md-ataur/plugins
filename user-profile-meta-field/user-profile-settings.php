<?php
/**
 * Plugin Name:  User Profile Meta Field
 * Plugin URI:
 * Description:  User Profile Meta Field
 * Version:      1.0
 * Author:       Ataur Rahman
 * Author URI:
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  user-profile
 * Domain Path:  /languages
 */


if (!class_exists("UserProfile")) {
	
	class UserProfile{

		function __construct(){
			add_action( "plugins_loaded", array($this,"usp_load_texdomain" ));
			add_filter( "user_contactmethods", array($this, "usp_user_contact_methods") );
		}

		function usp_load_texdomain(){
			load_plugin_textdomain( "user-profile", false, plugin_dir_path( __FILE__ )."/languages" );
		}

		function usp_user_contact_methods($methods){
			$methods['facebook'] = __("Facebook","user-profile");
			$methods['linkedin'] = __("Linked In","user-profile");
			$methods['twitter'] = __("Twitter","user-profile");
			return $methods;
		}
	}

	new UserProfile();
}


/**
 * How to show user meta field value in content.php
 * ------------------------------------------------ 
 * echo esc_url(get_the_author_meta('facebook'));
 */