<?php
/*
Plugin Name: Dashboard Widget
Plugin URI:
Description:
Version: 1.0
Author: Ataur Rahman
Author URI: 
License: GPLv2 or later
Text Domain: dashboardwidget
Domain Path: /languages/
*/


function ddw_load_textdomain() {
	load_plugin_textdomain( 'dashboardwidget', false, plugin_dir_path( __FILE__ ) . "/languages" );
}
add_action( 'plugins_loaded', 'ddw_load_textdomain' );

function dashboard_widget() {
	if ( current_user_can( 'edit_dashboard' ) ) {
		wp_add_dashboard_widget( 'dashboardwidget_id',
			__( 'Dashboard Widget', 'dashboardwidget' ),
			'dashboardwidget_output',
			'dashboardwidget_configure'
		);
	}else {
		wp_add_dashboard_widget( 'dashboardwidget_id',
			__( 'Dashboard Widget', 'dashboardwidget' ),
			'dashboardwidget_output'
		);
	}
}
add_action( 'wp_dashboard_setup', 'dashboard_widget' );

// Widget Output
function dashboardwidget_output() {
	$number_of_posts = get_option( 'dashboardwidget_nop', 5 );
	$feeds           = array(
		array(
			'url'          => 'https://techtalksbd.wordpress.com/feed',
			'items'        => $number_of_posts,
			'show_summary' => 0,
			'show_author'  => 0,
			'show_date'    => 0,
		)
	);
	wp_dashboard_primary_output( 'dashboardwidget', $feeds );
}

// Widget Configure
function dashboardwidget_configure() {
	$number_of_posts = get_option( 'dashboardwidget_nop', 5 );
	
	/* Nonce check */
	if ( isset( $_POST['dashboard-widget-nonce'] ) && wp_verify_nonce( $_POST['dashboard-widget-nonce'], 'edit-dashboard-widget_dashboardwidget_id' ) ) {
		if ( isset( $_POST['ddw_nop'] ) && $_POST['ddw_nop'] > 0 ) {
			$number_of_posts = sanitize_text_field( $_POST['ddw_nop'] );
			// data save to the database
			update_option( 'dashboardwidget_nop', $number_of_posts );
		}
	}
	?>
    <p>
        <label>Number of Posts:</label><br/>
        <input type="text" name="ddw_nop" id="ddw_nop" class="widefat" value="<?php echo $number_of_posts; ?>">
    </p>
	<?php
}