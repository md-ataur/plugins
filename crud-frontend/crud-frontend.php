<?php
/*
Plugin Name: Crud Frontend
Plugin URI: http://example.com
Description: Crud Frontend
Version: 1.0
Author: Ataur Rahman
Author URI: md-ataur.github.io
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: crud-frontend
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if(!class_exists('CrudAjaxReservationForm')){

	class CrudAjaxReservationForm{
		
		public function __construct(){
			add_action('plugins_loaded', array($this, 'crudfrontend_load_textdomain' ));			
			add_action('wp_enqueue_scripts', array($this, 'crudfrontend_enqueue_scripts'));
			add_shortcode( 'crud-frontend', array($this,'crudfrontend_shortcode' ));			
			register_activation_hook( __FILE__, array($this,'crud_table_init') );
		}

		function crud_table_init(){
			global $wpdb;
			$table_name = $wpdb->prefix."crudfrontend";

			/* Table create in Database */
			$sql = "CREATE TABLE {$table_name} (
				id INT NOT NULL AUTO_INCREMENT,
				name VARCHAR(250),
				email VARCHAR(250),
				phone VARCHAR(250),
				person INT(11),
				date DATE,
				time TIME,
				message VARCHAR(250),
				PRIMARY KEY (id)
			);";
			require_once(ABSPATH."wp-admin/includes/upgrade.php");
			dbDelta( $sql );		
		}

	public function crudfrontend_load_textdomain(){
		load_plugin_textdomain( 'crud-frontend', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	public function crudfrontend_enqueue_scripts(){
		wp_enqueue_style( 'bootstrap', plugin_dir_url( __FILE__ ).'assets/public/css/bootstrap.min.css');		
		
	}

	public static function crud_form(){
		global $wpdb;
		$table_name = $wpdb->prefix."crudfrontend";	
		$msg = "";
			
		if(isset($_POST['submit'])){
			$CRFname 	= sanitize_text_field( isset($_POST['RFname'])?$_POST['RFname']:'' );
			$CRFemail 	= sanitize_email( isset($_POST['RFemail'])?$_POST['RFemail']:'' );
			$CRFphone 	= sanitize_text_field( isset($_POST['RFphone'])?$_POST['RFphone']:'' );
			$CRFperson 	= sanitize_text_field( isset($_POST['RFperson'])?$_POST['RFperson']:'' );
			$CRFdate 	= sanitize_text_field( isset($_POST['RFdate'])?$_POST['RFdate']:'' );
			$CRFtime 	= sanitize_text_field( isset($_POST['RFtime'])?$_POST['RFtime']:'' );
			$CRFMessage = sanitize_text_field( isset($_POST['RFMessage'])?$_POST['RFMessage']:'' );		
			
			if (!empty($CRFname) && !empty($CRFemail) && !empty($CRFphone) && !empty($CRFperson) && !empty($CRFdate) && !empty($CRFtime)) {
				$userdata = array(
					'name'    => $CRFname,
					'email'   => $CRFemail,
					'phone'   => $CRFphone,
					'person'  => $CRFperson,
					'date'    => $CRFdate,
					'time'    => $CRFtime,
					'message' => $CRFMessage,
				);
				//print_r($userdata);
				
				$wpdb->insert("{$table_name}", $userdata);
				$msg = ('<p class="alert alert-success">Successfully Data Stored</p>');
				
			}else{
				$msg = ('<p class="alert alert-danger">Your fields empty or incorrect email address</p>');
			}
		}

		if(isset($_POST['update'])){
			$uid 		= sanitize_key( isset($_POST['id'])?$_POST['id']:'' );			
			$CRFname 	= sanitize_text_field( isset($_POST['RFname'])?$_POST['RFname']:'' );
			$CRFemail 	= sanitize_email( isset($_POST['RFemail'])?$_POST['RFemail']:'' );
			$CRFphone 	= sanitize_text_field( isset($_POST['RFphone'])?$_POST['RFphone']:'' );
			$CRFperson 	= sanitize_text_field( isset($_POST['RFperson'])?$_POST['RFperson']:'' );
			$CRFdate 	= sanitize_text_field( isset($_POST['RFdate'])?$_POST['RFdate']:'' );
			$CRFtime 	= sanitize_text_field( isset($_POST['RFtime'])?$_POST['RFtime']:'' );
			$CRFMessage = sanitize_text_field( isset($_POST['RFMessage'])?$_POST['RFMessage']:'' );		
			
			if (!empty($CRFname) && !empty($CRFemail) && !empty($CRFphone) && !empty($CRFperson) && !empty($CRFdate) && !empty($CRFtime)) {
				$userdata = array(
					'name'    => $CRFname,
					'email'   => $CRFemail,
					'phone'   => $CRFphone,
					'person'  => $CRFperson,
					'date'    => $CRFdate,
					'time'    => $CRFtime,
					'message' => $CRFMessage,
				);
				//print_r($data);
				if ($uid) {
					$wpdb->update("{$table_name}", $userdata, ['id'=>$uid]);			    	
					$msg = ('<p class="alert alert-success">Successfully Data Updated</p>');
				}
			}else{
				$msg = ('<p class="alert alert-danger">Your fields empty or incorrect email address</p>');
			}
		}
	
	
		$id = sanitize_key(isset($_GET['id']) ? $_GET['id']: null);
		if ($id) {
			$result = $wpdb->get_row("SELECT * FROM {$table_name} WHERE id='{$id}'");
		}/* Data retrieve mechanism */		

		if (isset($_GET['action']) && $_GET['action'] == "delete") {			
			$wpdb->delete($table_name, ['id'=>sanitize_key($_GET['id'])]);
			$msg = ('<p class="alert alert-success">Data has been Deleted</p>');
			$id = null;
		}/* Delete Mechanism */
		
?>
		<div class="container">			
			<div class="col-md-6 offset-md-3">
				<div id="message">
					<?php echo $msg;?>
				</div>
				<form action="<?php the_permalink(); ?>" id="Rform" method="post">
					<?php wp_nonce_field( 'rsf_nonce_action', 'rsf_nonce_field');?>
					<div class="form-group">
						<label for="name" class="label"><?php _e('Name','crudfrontend');?></label>
						<input type="text" class="form-control" name="RFname" value="<?php if($id) echo esc_attr($result->name);?>">
					</div>
					<div class="form-group">
						<label for="email" class="label"><?php _e('Email','crudfrontend');?></label>
						<input type="email" class="form-control" name="RFemail" value="<?php if($id) echo esc_attr($result->email);?>">                           
					</div>
					<div class="form-group">
						<label for="phone" class="label"><?php _e('Phone','crudfrontend');?></label>
						<input type="text" class="form-control" name="RFphone" value="<?php if($id) echo esc_attr($result->phone);?>">
					</div>
					<div class="form-group">
						<label for="persons" class="label"><?php _e('Number of Persons','crudfrontend');?></label>
						<select name="RFperson" class="form-control">
							<option value="1" <?php if( $id && $result->person=="1") echo 'selected="selected"'; ?> ><?php _e('1 person','crudfrontend');?></option>
							<option value="2" <?php if( $id && $result->person=="2") echo 'selected="selected"'; ?>><?php _e('2 person','crudfrontend');?></option>
							<option value="3" <?php if( $id && $result->person=="3") echo 'selected="selected"'; ?>><?php _e('3 person','crudfrontend');?></option>
							<option value="4" <?php if( $id && $result->person=="4") echo 'selected="selected"'; ?>><?php _e('4 person','crudfrontend');?></option>
							<option value="5" <?php if( $id && $result->person=="5") echo 'selected="selected"'; ?>><?php _e('5 person','crudfrontend');?></option>
						</select>                            
					</div>
					<div class="row">
						<div class="form-group col-md-6">
							<label for="date" class="label"><?php _e('Date','crudfrontend');?></label>
							<input type="date" class="form-control" name="RFdate" value="<?php if($id) echo esc_attr($result->date);?>">	                            
						</div>
						<div class="form-group col-md-6">
							<label for="time" class="label"><?php _e('Time','crudfrontend');?></label>
							<input type="time" class="form-control" name="RFtime" value="<?php if($id) echo esc_attr($result->time);?>"autocomplete="off">
						</div>
					</div>	 
					<div class="form-group">
						<textarea class="form-control" name="RFMessage" rows="5"><?php if($id) echo esc_attr($result->message);?></textarea>
					</div>                                          
					<div class="row justify-content-center">						
						<?php 
						if ($id) {
							echo '<input type="hidden" name="id" value="'.$result->id.'">';
							?>
							<input type="submit" name="update" class="btn btn-primary" value="Update">&nbsp;
							<a href="<?php the_permalink();?>">
								<button><?php _e('Back','crudfrontend');?></button>
							</a>
							<?php
						}else{
							?>
							<input type="submit" name="submit" class="btn btn-primary" value="Reserve Now">
							<?php
						}
						?>						
					</div>
				</form>
			</div>
			<div class="usertable col-md-12">
				<h5><?php _e("Data Show","crudfrontend");?></h5>	
				<div class="items">	            	
					<table class="table table-striped">
						<thead>
							<tr>
								<th scope="col"><?php _e("ID","crudfrontend");?></th>
								<th scope="col"><?php _e("Name","crudfrontend");?></th>
								<th scope="col"><?php _e("Email","crudfrontend");?></th>
								<th scope="col"><?php _e("Person","crudfrontend");?></th>
								<th scope="col"><?php _e("Phone","crudfrontend");?></th>
								<th scope="col"><?php _e("Date","crudfrontend");?></th>
								<th scope="col"><?php _e("Time","crudfrontend");?></th>
								<th scope="col"><?php _e("Message","crudfrontend");?></th>
								<th scope="col"><?php _e("Action","crudfrontend");?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							global $wpdb;
							$table_name = $wpdb->prefix."crudfrontend";
							$users = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY id DESC", ARRAY_A);
							foreach ($users as $user) {
								?>
								<tr>
									<th scope="row"><?php echo esc_html( $user['id'] ); ?></th>
									<td><?php echo esc_html( $user['name'] ); ?></td>
									<td><?php echo esc_html( $user['email'] ); ?></td>
									<td><?php echo esc_html( $user['person'] ); ?></td>
									<td><?php echo esc_html( $user['phone'] ); ?></td>
									<td><?php echo esc_html( $user['date'] ); ?></td>
									<td><?php echo esc_html( $user['time'] ); ?></td>
									<td><?php echo esc_html( $user['message'] ); ?></td>
									<td><span><a id="Edit" href="?id=<?php echo esc_html( $user['id'] ); ?>">Edit</a></span>&nbsp;&nbsp;<span><a href="?id=<?php echo esc_html( $user['id'] ); ?>&action=delete">Delete</a></span></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>			
		</div>
		<?php
	}

	public function crudfrontend_shortcode(){
			// Turn on output buffering.
			ob_start();

			self::crud_form(); // echo the form

			// Turn off output buffering and then return the output echoed via the above functions.
			return ob_get_clean();
		}
	}

	new CrudAjaxReservationForm;

}


