<?php
/*
Plugin Name:  Tiny Slider
Plugin URI:   
Description:  Tiny Slider
Version:      2.1
Author:       Ataur Rahman
Author URI:   
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  tinyslider
Domain Path:  /languages
*/


function tinys_load_textdomain(){
	load_plugin_textdomain( "tinyslider", false, dirname(__FILE__)."/languages" );
}
add_action( "plugins_loaded", "tinys_load_textdomain" );

function tinys_assets(){
	wp_enqueue_style( "tinys-css", "//cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.1/tiny-slider.css");
	wp_enqueue_script( "tinys-js", "//cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.1/min/tiny-slider.js", null, "2.1", true );
	wp_enqueue_script( "tinys-main-js", plugin_dir_url( __FILE__ )."assets/js/main.js", array("jquery"), time(), true );
}
add_action( "wp_enqueue_scripts", "tinys_assets" );


/* Parent Shortcode */
function tinys_shortcode_tslider($param, $content){
	$content = do_shortcode( $content );
	
	$shortcode_output = <<<EOD
	<div class="slider">
		{$content}
	</div>

EOD;
	return $shortcode_output;
}
add_shortcode( "tslider", "tinys_shortcode_tslider" );



/* Child Shortcode */
function tinys_shortcode_tslide($arguments){
	$defaults = array(
		"caption" 	=> "",
		"id"		=> "",
		"size"		=> "large"
	);	
	$attributes = shortcode_atts( $defaults, $arguments );		
	$image_src = wp_get_attachment_image_src( $attributes['id'], $attributes['size']);	

	$shortcode_output = <<<EOD
<div>
	<p><img src="{$image_src[0]}" alt="{$attributes['caption']}"/></p>
	<p>{$attributes['caption']}</p>
</div>
EOD;

	return $shortcode_output;
}
add_shortcode( "tslide", "tinys_shortcode_tslide" );
