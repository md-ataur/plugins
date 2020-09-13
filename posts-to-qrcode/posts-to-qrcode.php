<?php
/*
Plugin Name: Posts to QR code
Plugin URI: 
Description: Posts to QR code
Author: Ataur Rahman
Author URI: 
Text Domain: posts-to-qrcode
Domain Path: /languages/
Version: 1.0
*/


function qrcode_load_textdomain(){
	load_plugin_textdomain( "posts-to-qrcode", false, dirname(__FILE__)."/languages" );	
}
add_action( "plugins_loaded", "qrcode_load_textdomain" );

/* QR Code show on the post */
function qrcode_display($content){
	$current_post_id 	= get_the_ID();
	$current_post_title = get_the_title( $current_post_id );
	$current_post_url 	= urlencode(get_the_permalink( $current_post_id ));
	$current_post_type 	= get_post_type( $current_post_id );
	//echo "Post type: ";
	//echo $current_post_type;

	/* Apply filters. Easily any developer can define the post type in function.php*/
	$exclue_post_type = apply_filters( "qrcode_exclude_post_type", array() );
	
	if (in_array($current_post_type, $exclue_post_type)) {
		return $content;
	}

	/* Dimension */
	$width 		= get_option('qrcode_width');
	$height 	= get_option('qrcode_height');
	$width 		= $width?$width: 180;
	$height 	= $height?$height: 180;
	$dimension	= apply_filters( "qrcode_dimension", "{$width}x{$height}");

	$img_src = sprintf("https://api.qrserver.com/v1/create-qr-code/?data=%s&size=%s&margin=0",$current_post_url,$dimension);
	$content .= sprintf("<div class='qrcode'><img src='%s' alt='%s' /></div>",$img_src,$current_post_title);
	return $content;
}
add_filter( "the_content", "qrcode_display" );


/* admin settings > General */
function qrcode_settings_init(){
	add_settings_section( "qrcode_section", __("Posts to QR Code","posts-to-qrcode"), "qrcode_section_callback", "general" );

	/**
	 * add_settings_field( $id:string, $title:string, $callback:callable, $page:string, $section:string, $args:array ); 
	 */	
	add_settings_field( "qrcode_width", __("QR Code Width", "posts-to-qrcode"), "qrcode_display_field", "general", "qrcode_section", array('qrcode_width'));
	add_settings_field( "qrcode_height", __("QR Code Height", "posts-to-qrcode"), "qrcode_display_field", "general", "qrcode_section", array('qrcode_height'));
	
	
	/**
	 * register_setting( $option_group:string, $option_name:string, $args:array );
	 */	
	register_setting( "general", "qrcode_width", array("sanitize_callback"=>"esc_attr") );
	register_setting( "general", "qrcode_height", array("sanitize_callback"=>"esc_attr") );
	
}
add_action( "admin_init", "qrcode_settings_init" );


function qrcode_section_callback(){
	echo "<div>".__('Settings for Posts To QR Plugin','posts-to-qrcode')."</div>";
}

/* Display input field */
function qrcode_display_field($arrgs){
	$option = get_option( $arrgs[0] );
	printf("<input type='text' id='%s' name='%s' value='%s' />", $arrgs[0], $arrgs[0], $option);
}

/*function qrcode_display_width(){
	$width = get_option('qrcode_width');
	printf("<input type='text' id='%s' name='%s' value='%s' />", "qrcode_width", "qrcode_width", $width);
}

function qrcode_display_height(){
	$height = get_option('qrcode_height');
	printf("<input type='text' id='%s' name='%s' value='%s' />", "qrcode_height", "qrcode_height", $height);
}*/
