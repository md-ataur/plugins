<?php
/*
Plugin Name: Database Demo
Plugin URI: http://example.com
Description: Database Demo
Version: 1.0
Author: Ataur Rahman
Author URI: md-ataur.github.io
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: database-demo
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once('class.dbdemo-users.php');

define('DBDEMO_VERSION', '1.1');

/* Table create */
function dbdemo_init(){
	/* Global object $wpdb */
	global $wpdb;
	$table_name = $wpdb->prefix.'persons';
	$sql = "CREATE TABLE {$table_name} (
			id INT NOT NULL AUTO_INCREMENT,
			name VARCHAR(250),
			email VARCHAR(250),
			PRIMARY KEY (id)
	);";
	
	/* Upgrade file include for dbDelta */
	require_once(ABSPATH. "wp-admin/includes/upgrade.php");	
	
	/* dbDelta is a especial function for column add */
	dbDelta($sql);
	
	/* Option table */
	add_option( 'DBDEMO_VERSION', DBDEMO_VERSION );

	/* Version Check */
	if (get_option( 'DBDEMO_VERSION')!= DBDEMO_VERSION) {
		$sql = "CREATE TABLE {$table_name} (
			id INT NOT NULL AUTO_INCREMENT,
			name VARCHAR(250),
			email VARCHAR(250),
			age INT,
			PRIMARY KEY (id)
		);";
		dbDelta($sql);
		update_option( 'DBDEMO_VERSION', DBDEMO_VERSION );
	}
}
register_activation_hook( __FILE__, "dbdemo_init" );

/* Initially Data load when plugin is activate */
function dbdemo_load_data(){
	global $wpdb;
	$table_name = $wpdb->prefix.'persons';
	$wpdb->insert($table_name, [
		'name' => 'Jone Doe',
		'email'=> 'jone@doe.com'
	]);
	$wpdb->insert($table_name, [
		'name' => 'Smammar',
		'email'=> 'sammmer@doe.com'
	]);
}
register_activation_hook( __FILE__, "dbdemo_load_data" );

/* Data Flush when plugin is deactivate */
function dbdemo_flush_data(){
	global $wpdb;
	$table_name = $wpdb->prefix.'persons';
	$query = "TRUNCATE TABLE {$table_name}";
	$wpdb->query($query);
}
register_deactivation_hook( __FILE__, "dbdemo_flush_data" );

/* Admin enqueue scripts */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( "toplevel_page_dbdemo" == $hook ) {
		wp_enqueue_style( 'dbdemo-style', plugin_dir_url( __FILE__ ) . 'assets/css/form.css' );
	}
} );

/* Create admin Menu and data show */
add_action( 'admin_menu', function(){
	add_menu_page( 'DB Demo', 'DB Demo', 'manage_options', 'dbdemo', 'dbdemo_admin_page' );
} );

function dbdemo_admin_page(){
	global $wpdb;
	
	if (isset($_GET['action']) && $_GET['action'] == 'delete') {
		$wpdb->delete("{$wpdb->prefix}persons", ['id'=>sanitize_key($_GET['id'])]);
		$_GET['id'] = null;
	}/* Delete Mechanism */
	
	$id = $_GET['id'] ?? 0;
	$id = sanitize_key( $id );
	if ($id) {
		$result = $wpdb->get_row("select * from {$wpdb->prefix}persons WHERE id='{$id}'");		
		/*if ($result) {
			echo "<h1>Data Show</h1>";
			echo "Name: {$result->name} <br/>";
			echo "Email: {$result->email} <br/>";
		}*/
	}
	?>
	<div class="form_box">
        <div class="form_box_header">
			<?php _e( 'Data Form', 'database-demo' ) ?>
        </div>
        <div class="form_box_content">
			<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
				
				<?php wp_nonce_field('dbdemo','nonce'); ?>
				
				<input type="hidden" name="action" value="dbdemo_add_record">

				<div>
					<label><strong><?php _e('Name','database-demo');?></strong></label>
					<input type="text" class="form_text" name="name" required value="<?php if($id) echo $result->name;?>">
				</div>
				<div>
					<label><strong><?php _e('Email','database-demo');?></strong></label>
	                <input type="text" class="form_text" name="email" required value="<?php if($id) echo $result->email;?>">
	            </div>
				<?php
				if ($id){
					echo '<input type="hidden" name="id" value="'.$id.'">';
					submit_button("Update Record");
				}else{
					submit_button("Add Record");
				}
				/*if (isset($_POST['submit'])) {
					$nonce = sanitize_text_field( isset($_POST['nonce'])?$_POST['nonce']:'' );
					if ( wp_verify_nonce( $nonce, 'dbdemo' ) ) {
						$name  = sanitize_text_field( $_POST['name'] );
						$email = sanitize_text_field( $_POST['email'] );
						$id    = sanitize_text_field( $_POST['id'] );
						if ( $id ) {
							$wpdb->update( "{$wpdb->prefix}persons", [ 'name' => $name, 'email' => $email ], [ 'id' => $id ] );
						} else {
							$wpdb->insert( "{$wpdb->prefix}persons", [ 'name' => $name, 'email' => $email ] );				
						}
					}
				}*/
				?>
			</form>
		</div>
	</div>

	<!-- Users data display -->
	<div class="form_box" style="margin-top:15px;">
        <div class="form_box_header">
			<?php _e( 'Users Data', 'database-demo' ) ?>
        </div>
        <div class="form_box_content">
        	<?php
        	global $wpdb;
        	$query = $wpdb->get_results( "SELECT id, name, email FROM {$wpdb->prefix}persons ORDER BY id DESC", ARRAY_A );
        	//print_r($UsersData);
        	$DBTUsers = new DBTableUsers($query);
        	$DBTUsers->prepare_items();
        	$DBTUsers->display();
        	?>
        </div>
    </div>
	<?php
}

/**
 * You have set input hidden value 'dbdemo_add_record' with 'admin_post_' hoook 
 * add_action('admin_post_{$action}');
 */

add_action( 'admin_post_dbdemo_add_record', function () {
	global $wpdb;
	$nonce = sanitize_text_field( $_POST['nonce'] );
	if ( wp_verify_nonce( $nonce, 'dbdemo' ) ) {
		$name  = sanitize_text_field( $_POST['name'] );
		$email = sanitize_text_field( $_POST['email'] );
		$id    = sanitize_text_field( $_POST['id'] );
		if ( $id ) {
			$wpdb->update( "{$wpdb->prefix}persons", [ 'name' => $name, 'email' => $email ], [ 'id' => $id ] );			
			wp_redirect( admin_url( 'admin.php?page=dbdemo&id=' ) . $id);
		} else {
			$wpdb->insert( "{$wpdb->prefix}persons", [ 'name' => $name, 'email' => $email ] );
			/*$new_id = $wpdb->insert_id;
			wp_redirect(admin_url('admin.php?page=dbdemo&pid='.$new_id));*/
			wp_redirect( admin_url( 'admin.php?page=dbdemo' ) );
		}
	}
} );
