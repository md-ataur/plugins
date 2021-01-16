<?php
/*
Plugin Name: CRUD Backend
Plugin URI: http://example.com
Description: CRUD Backend
Version: 1.0
Author: Ataur Rahman
Author URI: md-ataur.github.io
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: crud-backend
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define('CRUD_VERSION','1.2');

require_once('class.crud-table.php');

add_action( 'admin_enqueue_scripts', function ( $screen ) {
	if ( "toplevel_page_crud" == $screen ) {
		wp_enqueue_style( 'dbdemo-style', plugin_dir_url( __FILE__ ) . 'assets/css/form.css' );
	}
} );

function crud_table_init(){
	global $wpdb;
	$table_name = $wpdb->prefix."crud";

	/* Table create */
	$sql = "CREATE TABLE {$table_name} (
		id INT NOT NULL AUTO_INCREMENT,
		name VARCHAR(250),
		email VARCHAR(250),
		PRIMARY KEY (id)
	);";
	require_once(ABSPATH."wp-admin/includes/upgrade.php");
	dbDelta( $sql );
	add_option('CRUD_VERSION',CRUD_VERSION);

	/* Version check */
	if (get_option('CRUD_VERSION') != CRUD_VERSION) {
		$table_name = $wpdb->prefix."crud";
		$sql = "CREATE TABLE {$table_name} (
			id INT NOT NULL AUTO_INCREMENT,
			name VARCHAR(250),
			email VARCHAR(250),
			PRIMARY KEY (id)
		);";
		// Table column delete
		$wpdb->query( "ALTER TABLE $table_name DROP COLUMN phone" );
		dbDelta( $sql );
		// Version update
		update_option( 'CRUD_VERSION', CRUD_VERSION );
	}

	/* Initially Data insert */
	$wpdb->insert($table_name, [
		'name'=>'Johir',
		'email'=>'Johir@gmail.com',
	]);
	$wpdb->insert($table_name, [
		'name'=>'Asif',
		'email'=>'asif@gmail.com',
	]);
}
register_activation_hook( __FILE__, 'crud_table_init' );

/* Data Flush */
function crud_tableData_flush(){
	global $wpdb;
	$table_name = $wpdb->prefix."crud";
	$query = "TRUNCATE TABLE {$table_name}";
	$wpdb->query($query);
}
register_deactivation_hook( __FILE__, 'crud_tableData_flush' );


/* Create admin Menu and data show */
add_action( 'admin_menu', function(){
	add_menu_page( 'CRUD', 'CRUD', 'manage_options', 'crud', 'crud_admin_page' );
} );

function crud_admin_page(){
	global $wpdb;
	$table_name = $wpdb->prefix."crud";

	if (isset($_GET['action']) && $_GET['action'] == 'delete') {
		$wpdb->delete($table_name, ['id'=>sanitize_key($_GET['id'])]);
		$_GET['id'] = null;
	}/* Delete Mechanism */

	
	/* Data read for edit */
	$id = $_GET['id'] ?? null;
	$id = sanitize_key($id);
	if ($id) {
		$result = $wpdb->get_row("SELECT * FROM {$table_name} WHERE id='{$id}'");	
	}
	?>
	<div class="form_box">
        <div class="form_box_header">
			<?php _e( 'Data Form', 'database-demo' ) ?>
        </div>
        <div class="form_box_content">
			<form action="<?php echo admin_url('admin-post.php');?>" method="post">	
				<input type="hidden" name="action" value="crud_add_record">
				<?php wp_nonce_field("crud","nonce");?>				
				<div>
					<label><strong><?php _e('Name','database-demo');?></strong></label>
					<input type="text" class="form_text" name="name" required value="<?php if($id) echo esc_attr($result->name);?>">
				</div>
				<div>
					<label><strong><?php _e('Email','database-demo');?></strong></label>
	                <input type="text" class="form_text" name="email" required value="<?php if($id) echo esc_attr($result->email);?>">
	            </div>	            
	            <?php 
	            if ($id) {
	            	echo "<input type='hidden' name='id' value='{$id}'>";
	            	submit_button("Update Record");
	            }else{
	            	submit_button("Add Record"); 
	            }		        
	            ?>
	        </form>	        
	    </div>
    </div>

    <div class="form_box mt-5">
	    <div class="form_box_header">
			<?php _e( 'Users Data', 'database-demo' ) ?>
	    </div>
	    <div class="form_box_content">
	    	<?php
			/* Data fetch and showing in the table */
	    	global $wpdb;
	    	$data = $wpdb->get_results("SELECT id, name, email FROM {$wpdb->prefix}crud ORDER BY id DESC", ARRAY_A);
			
			// object create for CRUD_UserData class
			$crudObject = new CRUD_UserData($data);

			// Call prepare_items() method
			$crudObject->prepare_items();

			// Call display() method to show data
	    	$crudObject->display();
	    	?>
	    </div>
	</div>
	<?php
}

/* Data save and data update mechanism */
add_action('admin_post_crud_add_record',function(){
	global $wpdb;
	$table_name = $wpdb->prefix."crud";
	
	if (isset($_POST['submit'])) {
		$nonce = sanitize_text_field($_POST['nonce']);
		if (wp_verify_nonce( $nonce, "crud" )) {
	    	$name = sanitize_text_field($_POST['name']);
	    	$email = sanitize_text_field($_POST['email']);
	    	$id = sanitize_text_field($_POST['id']);
	    	$data = [
	    		'name' => $name,
	    		'email' => $email,
	    	];
	    	if ($id) {
	    		$wpdb->update( "{$table_name}", $data, ['id'=>$id] );
	    		wp_redirect( admin_url('admin.php?page=crud'));
	    	}else{
	    		$wpdb->insert("{$table_name}", $data);
	    		wp_redirect( admin_url('admin.php?page=crud'));
	    	}
    	}else{
    		_e("You are not allowed","crud");
    	}
    }
});

