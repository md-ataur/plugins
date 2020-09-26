<?php
/**
 * Plugin Name:  Options Demo Page
 * Plugin URI:
 * Description:  Demonstration for plugin option demo page
 * Version:      1.0
 * Author:       Ataur Rahman
 * Author URI:
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  options-demo-page
 * Domain Path:  /languages
 */

class OptionDemoPage{

	function __construct() {
		add_action( 'plugins_loaded', array( $this, 'odp_bootstrap' ) );			
		add_action( 'admin_menu', array( $this, 'odp_create_admin_page' ) );
		/* This hook name according to hidden input field action */
		add_action( 'admin_post_optionsdemo_admin_page', array( $this, 'odp_save_form' ) );
	}

	/* Load text domain */
	public function odp_bootstrap() {
		load_plugin_textdomain( 'options-demo-page', false, plugin_dir_path( __FILE__ ) . "/languages" );
	}

	/* Options page create */
	function odp_create_admin_page(){
		$page_title = __( 'Options Admin Page ', 'options-demo-page' );
		$menu_title = __( 'Options Admin Page', 'options-demo-page' );
		$capability = 'manage_options';
		$slug       = 'optionsdemopage';
		$callback   = array( $this, 'odp_page_content' );
		//add_options_page( $page_title, $menu_title, $capability, $slug, $callback );
		add_menu_page( $page_title, $menu_title, $capability, $slug, $callback );
	}

	/* include form */
	function odp_page_content(){
		require_once(plugin_dir_path( __FILE__ )."/form.php");
	}

	/* Data save to the database */
	function odp_save_form(){
		/* Nonce check */
		check_admin_referer("odp");
		// echo "<pre>";
		// print_r($_POST);
		// echo "</pre>";
		// die();
		if(isset($_POST['optionsdemo_longitude2'])){
			update_option('optionsdemo_longitude2',sanitize_text_field($_POST['optionsdemo_longitude2']));
		}
		wp_redirect(admin_url('admin.php?page=optionsdemopage'));
	}
}

new OptionDemoPage();