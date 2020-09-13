<?php
/*
Plugin Name: Admin Settings
Plugin URI: 
Description: Admin Settings
Author: Ataur Rahman
Author URI: 
Text Domain: admin-settings
Domain Path: /languages/
Version: 1.0
*/


function admin_settings_textdomain(){
	load_plugin_textdomain( "admin-settings", false, dirname(__FILE__)."/languages" );	
}
add_action( "plugins_loaded", "admin_settings_textdomain" );


/* admin settings > General */
function admin_settings_init(){
	add_settings_section( "settings_section", __("Additional Settings", "admin-settings"), "", "general" );

	/**
	 * add_settings_field( $id:string, $title:string, $callback:callable, $page:string, $section:string, $args:array ); 
	 */		
	
	add_settings_field( "select_option", __("Dropdown", "admin-settings"), "settings_display_select", "general", "settings_section");
	add_settings_field( "checkbox_option", __("Select Countries", "admin-settings"), "settings_display_checkbox", "general", "settings_section");
	add_settings_field( "switcher", __("Switcher", "admin-settings"), "display_switcher", "general", "settings_section");
	
	/**
	 * register_setting( $option_group:string, $option_name:string, $args:array );
	 */
		
	register_setting( "general", "select_option", array("sanitize_callback"=>"esc_attr") );	
	register_setting( "general", "checkbox_option");
	register_setting( "general", "switcher");

}
add_action( "admin_init", "admin_settings_init" );


/* Dropdown callback */
function settings_display_select(){
	$option = get_option( "select_option");	
	$countries = array(
		'None',
		'America',
		'Africa',
		'Bhutan',
		'Bangladesh',
		'Nepal',
		'India'
	);

	printf("<select id='%s' name='%s'>", "select_option", "select_option");
	foreach ($countries as $country) {
		$selected = '';
		if ($option == $country){
			$selected = 'selected';
		}
		printf("<option value='%s' %s>%s</option>", $country, $selected, $country);
	}
	echo "</select>";
}


/* Checkbox callback */
function settings_display_checkbox(){
	$option = get_option( "checkbox_option");	
	$countries = array(		
		'America',
		'Africa',
		'Bhutan',
		'Bangladesh',
		'Pakistan',
		'Nepal',
		'India'
	);
	
	foreach ($countries as $country) {
		$selected = '';
		if (is_array($option) && in_array($country, $option)){
			$selected = 'checked';
		}
		printf("<input type='checkbox' name='checkbox_option[]' value='%s' %s /> %s <br>", $country, $selected, $country);
	}	
}


/* Switcher callback */
function display_switcher(){
	$option = get_option('switcher');
	echo '<div id="toggle1"></div>';
	echo "<input type='hidden' name='switcher' id='switcher' value='".$option."'>";
}
function switcher_assets($screen){
	if ('options-general.php' == $screen) {		
		wp_enqueue_style( "minitoggle-style", plugin_dir_url( __FILE__ )."assets/css/minitoggle.css" );
		wp_enqueue_script( "minitoggle-js", plugin_dir_url( __FILE__ )."assets/js/minitoggle.js" , array('jquery'), "1.0", true );
		wp_enqueue_script( "main-js", plugin_dir_url( __FILE__ )."assets/js/main.js" , array('jquery'), time(), true );
	}
}
add_action( "admin_enqueue_scripts", "switcher_assets" );