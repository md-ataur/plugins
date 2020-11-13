<?php
/*
Plugin Name: Plugin Action Link
Plugin URI:
Description:
Version: 1.0
Author: Ataur Rahman
Author URI: 
License: GPLv2 or later
Text Domain: plugin-action
Domain Path: /languages/
*/

add_action( 'admin_menu', function () {
	add_menu_page(
		__( 'Action Links', 'wp-quick-provision' ),
		__( 'Action Links', 'wp-quick-provision' ),
		'manage_options',
		'action_links',
		function () {
			?>
            <h1>Hello World</h1>
			<?php
		} );
} );

add_action( 'activated_plugin', function ( $plugin ) {		
	/* You need to check plugin file path because this hook roled for every plugin*/
	if ( plugin_basename( __FILE__ ) == $plugin ) {
		wp_redirect( admin_url( 'admin.php?page=action_links' ) );
		die();
	}
} );

/* Action link Add */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {	
	//print_r($links);
	$link = sprintf( "<a href='%s' style='color:#2324ff;'>%s</a>", admin_url( 'admin.php?page=action_links' ), __( 'Settings', 'plugin-action' ) );
	array_push( $links, $link );
	return $links;
} );

/* Row link Add */
add_filter( 'plugin_row_meta', function ( $links, $plugin ) {
	if ( plugin_basename( __FILE__ ) == $plugin ) {
		$link = sprintf( "<a href='%s' style='color:#ff3c41;'>%s</a>", esc_url( 'https://github.com/hasinhayder' ), __( 'Fork on Github', 'plugin-action' ) );
		array_push( $links, $link );
	}
	return $links;
}, 10, 2 );