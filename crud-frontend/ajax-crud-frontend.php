<?php
/*
Plugin Name: Ajax Crud Frontend
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
			add_shortcode( 'ajax-crud-form', array($this,'crudfrontend_shortcode' ));
			add_action( 'wp_ajax_crudRSF', array($this, 'AjaxdataProcess') );
			add_action( 'wp_ajax_nopriv_crudRSF', array($this, 'AjaxdataProcess') );
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
		wp_enqueue_script( 'crud-ajax-reservation-js', plugin_dir_url( __FILE__ ).'assets/public/js/crud-ajax-reservation.js', array( 'jquery' ), time(), true );
		$ajaxUrl = admin_url( 'admin-ajax.php');
		wp_localize_script( 'crud-ajax-reservation-js', 'objurl', array('ajaxurl' => $ajaxUrl) );	
		
	}

	public static function AjaxdataProcess(){
		global $wpdb;
		$table_name = $wpdb->prefix."crudfrontend";
		if (check_ajax_referer( 'rsf_nonce_action', 'nf', false )) {
			$uid 		= sanitize_key( isset($_POST['uid'])?$_POST['uid']:'' );
			$CRFname 	= sanitize_text_field( isset($_POST['CRFname'])?$_POST['CRFname']:'' );
			$CRFemail 	= sanitize_email( isset($_POST['CRFemail'])?$_POST['CRFemail']:'' );
			$CRFphone 	= sanitize_text_field( isset($_POST['CRFphone'])?$_POST['CRFphone']:'' );
			$CRFperson 	= sanitize_text_field( isset($_POST['CRFperson'])?$_POST['CRFperson']:'' );
			$CRFdate 	= sanitize_text_field( isset($_POST['CRFdate'])?$_POST['CRFdate']:'' );
			$CRFtime 	= sanitize_text_field( isset($_POST['CRFtime'])?$_POST['CRFtime']:'' );
			$CRFMessage = sanitize_text_field( isset($_POST['CRFMessage'])?$_POST['CRFMessage']:'' );		
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
					die ('<p class="alert alert-success">Successfully Data Updated</p>');
				}else{
					$wpdb->insert("{$table_name}", $userdata);
					die ('<p class="alert alert-success">Successfully Data Stored</p>');
				}				
			}else{
				die('<p class="alert alert-danger">Your fields empty or incorrect email address</p>');
			}
		}
	}
	public static function crud_ajax_form(){
		global $wpdb;
		$table_name = $wpdb->prefix."crudfrontend";
		$id = sanitize_key(isset($_GET['uid']) ? $_GET['uid']: null);
		if ($id) {
			$result = $wpdb->get_row("SELECT * FROM {$table_name} WHERE id='{$id}'");
		}/* Data retrieve mechanism */		

		if (isset($_GET['action']) && $_GET['action'] == "delete") {
			$wpdb->delete($table_name, ['id'=>sanitize_key($_GET['uid'])]);
			$id = null;
		}/* Delete Mechanism */
		
		?>
		<div class="container">			
			<div class="col-md-6 offset-md-3">
				<div id="message"></div>
				<form action="<?php the_permalink(); ?>" id="Rform">
					<?php wp_nonce_field( 'rsf_nonce_action', 'rsf_nonce_field');?>
					<div class="form-group">
						<label for="name" class="label"><?php _e('Name','crudfrontend');?></label>
						<input type="text" class="form-control" id="RFname" value="<?php if($id) echo esc_attr($result->name);?>">
					</div>
					<div class="form-group">
						<label for="email" class="label"><?php _e('Email','crudfrontend');?></label>
						<input type="email" class="form-control" id="RFemail" value="<?php if($id) echo esc_attr($result->email);?>">                           
					</div>
					<div class="form-group">
						<label for="phone" class="label"><?php _e('Phone','crudfrontend');?></label>
						<input type="text" class="form-control" id="RFphone" value="<?php if($id) echo esc_attr($result->phone);?>">
					</div>
					<div class="form-group">
						<label for="persons" class="label"><?php _e('Number of Persons','crudfrontend');?></label>
						<select name="persons" id="RFperson" class="form-control">
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
							<input type="date" class="form-control" id="RFdate" value="<?php if($id) echo esc_attr($result->date);?>">	                            
						</div>
						<div class="form-group col-md-6">
							<label for="time" class="label"><?php _e('Time','crudfrontend');?></label>
							<input type="time" class="form-control" id="RFtime" value="<?php if($id) echo esc_attr($result->time);?>"autocomplete="off">
						</div>
					</div>	 
					<div class="form-group">
						<textarea class="form-control" id="RFMessage" rows="5"><?php if($id) echo esc_attr($result->message);?></textarea>
					</div>                                          
					<div class="row justify-content-center">						
						<?php 
						if ($id) {
							echo '<input type="hidden" name="id" id="uid" value="'.$result->id.'">';
							?>
							<button id="CrudReserveForm" class="btn btn-primary"><?php _e('Update','crudfrontend');?></button>&nbsp;
							<a class="btn btn-primary" href="<?php the_permalink();?>"><?php _e('Back','crudfrontend');?></a>
							<?php
						}else{
							?>
							<button id="CrudReserveForm" class="btn btn-primary"><?php _e('Reserve Now','crudfrontend');?></button>
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
									<td><?php echo esc_html( $user['date'] ); ?></td>
									<td><?php echo esc_html( $user['time'] ); ?></td>
									<td><?php echo esc_html( $user['message'] ); ?></td>
									<td><span><a id="Edit" href="?uid=<?php echo esc_html( $user['id'] ); ?>">Edit</a></span>&nbsp;&nbsp;<span><a href="?uid=<?php echo esc_html( $user['id'] ); ?>&action=delete">Delete</a></span></td>
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

			self::crud_ajax_form(); // echo the form

			// Turn off output buffering and then return the output echoed via the above functions.
			return ob_get_clean();
		}
	}

	new CrudAjaxReservationForm;
}


