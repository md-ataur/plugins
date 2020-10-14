<?php
/*
Plugin Name: Advertisement Widget
Plugin URI: 
Description:  Advertisement Widget
Version: 1.0
Author: LWHH
Author URI: 
License: GPLv2 or later
Text Domain: advertisement-widget
Domain Path: /languages/
*/

require_once plugin_dir_path(__FILE__)."widgets/class.advertisementwidget.php";

function advwidget_load_textdomain() {
	load_plugin_textdomain( 'advertisement-widget', false, plugin_dir_path( __FILE__ ) . "languages/" );
}
add_action( 'plugins_loaded', 'advwidget_load_textdomain' );

function widgetRegister(){
	register_widget('AdvertisementWidget');	
}
add_action('widgets_init','widgetRegister');

function advwidget_admin_enqueue_scripts($screen){
	if($screen == "widgets.php") {
		wp_enqueue_media();
		//wp_enqueue_style("widget-style", plugin_dir_url(__FILE__)."css/widget-style.css");
		wp_enqueue_script("advertisement-widget-js", plugin_dir_url(__FILE__)."js/advertisement-widget.js", array("jquery"), time(), true);
	}
}

add_action("admin_enqueue_scripts","advwidget_admin_enqueue_scripts");
