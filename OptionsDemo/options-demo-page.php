<?php
class OptionDemoPage{

	function __construct() {
		add_action( 'admin_menu', array( $this, 'optionsdemo_create_admin_page' ) );
		add_action( 'admin_post_optionsdemo_admin_page', array( $this, 'optionsdemo_save_form' ) );
	}

	function optionsdemo_create_admin_page(){
		$page_title = __( 'Options Admin Page ', 'optionsdemo' );
		$menu_title = __( 'Options Admin Page', 'optionsdemo' );
		$capability = 'manage_options';
		$slug       = 'optionsdemopage';
		$callback   = array( $this, 'optionsdemo_page_content' );
		//add_options_page( $page_title, $menu_title, $capability, $slug, $callback );
		add_menu_page( $page_title, $menu_title, $capability, $slug, $callback );
	}

	function optionsdemo_page_content(){
		require_once(plugin_dir_path( __FILE__ )."/form.php");
	}

	function optionsdemo_save_form(){

		check_admin_referer("optionsdemo");
		
		if(isset($_POST['optionsdemo_longitude2'])){
			update_option('optionsdemo_longitude2',sanitize_text_field($_POST['optionsdemo_longitude2']));
		}
		wp_redirect(admin_url('admin.php?page=optionsdemopage'));
	}
}

new OptionDemoPage();