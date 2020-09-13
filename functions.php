<?php
/**
 * LWH plugins practise
 =============================*/

/* plugin Word Count */
function twentynineteen_wordcount_heading($heading){
	$heading = "Total Words";
	return $heading;
}
add_filter( "wordcount_heading", "twentynineteen_wordcount_heading" );


function twentynineteen_wordcount_tag($tag){
	$tag = "h5";
	return $tag;
}
add_filter( "wordcount_tag", "twentynineteen_wordcount_tag" );
/* plugin Word Count end */


/* plugin posts-to-qrcode */
function twentynineteen_qrcode($post_types){
	$post_types[] = 'page';
	return $post_types;
}
add_filter( "qrcode_exclude_post_type", "twentynineteen_qrcode" );


function twentynineteen_country_list($countries){
	array_push($countries, "China");
	$countries = array_diff($countries, array('Pakistan'));		
	return $countries;	
}
add_filter("qrcode_countries", "twentynineteen_country_list");

