<?php
/*
Plugin Name: CTP Reservation
Plugin URI: http://example.com
Description: Custom post reservation
Version: 1.0
Author: Ataur Rahman
Author URI: md-ataur.github.io
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: cptreservation
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action('plugins_loaded', 'cptreservation_textdomain');
function cptreservation_textdomain(){
	load_plugin_textdomain( 'cptreservation', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );	
}

add_action( 'init', 'cptreservation_init' );
/**
 * Register a book post type.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function cptreservation_init() {
	$labels = array(
		'name'               => _x( 'Reservations', 'post type general name', 'cptreservation' ),
		'singular_name'      => _x( 'Reservation', 'post type singular name', 'cptreservation' ),
		/*'menu_name'          => _x( 'Reservations', 'admin menu', 'cptreservation' ),
		'name_admin_bar'     => _x( 'Reservation', 'add new on admin bar', 'cptreservation' ),
		'add_new'            => _x( 'Add New', 'cptreservation' ),
		'add_new_item'       => __( 'Add New Reservation', 'cptreservation' ),
		'new_item'           => __( 'New Reservation', 'cptreservation' ),
		'edit_item'          => __( 'Edit Reservation', 'cptreservation' ),
		'view_item'          => __( 'View Reservation', 'cptreservation' ),
		'all_items'          => __( 'All Reservations', 'cptreservation' ),
		'search_items'       => __( 'Search Reservations', 'cptreservation' ),
		'parent_item_colon'  => __( 'Parent Reservations:', 'cptreservation' ),
		'not_found'          => __( 'No books found.', 'cptreservation' ),
		'not_found_in_trash' => __( 'No books found in Trash.', 'cptreservation' )*/
	);

	$args = array(
		'labels'             => $labels,
		'description'        => __( 'Description.', 'cptreservation' ),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'reservation' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title' )
	);

	register_post_type( 'reservation', $args );
}