<?php
/*
Plugin Name: Word Count
Plugin URI: 
Description: The content word count.
Author: Ataur Rahman
Author URI: 
Text Domain: word-count
Domain Path: /languages/
Version: 1.0
*/

/*function wordcount_activation_hook(){}
register_activation_hook( __FILE__, "wordcount_activation_hook" );

function wordcount_deactivation_hook(){}
register_deactivation_hook( __FILE__, "wordcount_deactivation_hook" );*/


function wordcount_load_textdomain(){
	load_plugin_textdomain( "word-count", false, dirname(__FILE__)."/languages" );
}
add_action( "plugins_loaded", "wordcount_load_textdomain" );

function wordcount_count_words($content){
	$stripped_content = strip_tags($content);
	$wcount = str_word_count($stripped_content);
	$label = __("Total number of words","wordcount");
	$label = apply_filters( "wordcount_heading", $label );
	$tag = apply_filters( "wordcount_tag", 'h2' );
	$content .= sprintf("<%s>%s: %s </%s>",$tag,$label,$wcount,$tag);
	return $content;
}
add_filter( "the_content", "wordcount_count_words" );