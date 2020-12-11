<?php
/*
Plugin Name: Ajax Contact Form
Plugin URI: http://example.com
Description: Simple WordPress Contact Form
Version: 1.0
Author: Ataur Rahman
Author URI: md-ataur.github.io
License: GPL v2 or later
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  ajax-contact-form
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if (!class_exists('AjaxContactForm')) {
	class AjaxContactForm{

		public function __construct(){
			add_action( 'plugins_loaded', array($this, 'ajax_load_textdomain') );
			add_action( 'wp_enqueue_scripts', array($this, 'ajax_load_scripts') );
			add_action( 'wp_ajax_ajaxCForm', array($this, 'ajax_data_process' ) );
			add_action( 'wp_ajax_nopriv_ajaxCForm', array($this, 'ajax_data_process' ) );
			add_shortcode( 'ajax_contact_form', array($this, 'ajax_shortcode') );
		}		
		public function ajax_load_textdomain(){
			load_plugin_textdomain( 'ajax-contact-form', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}
		public function ajax_load_scripts(){
			wp_enqueue_style( 'bootstrap', plugin_dir_url( __FILE__ ).'assets/public/css/bootstrap.min.css');
			wp_enqueue_script( 'ajax-js', plugin_dir_url( __FILE__ ).'assets/public/js/ajax-cf.js', array( 'jquery' ), time(), true );
			$ajaxurl = admin_url( 'admin-ajax.php');
			wp_localize_script( 'ajax-js', 'curl', array('ajaxurl' => $ajaxurl) );
		}

		public static function ajax_data_process(){
			if (check_ajax_referer( 'nonce_action', 'nonce', false )) {
				$cFName = sanitize_text_field( isset($_POST['cFName'])?$_POST['cFName']:'' );
				$cLName = sanitize_text_field( isset($_POST['cLName'])?$_POST['cLName']:'' );
				$cSubject = sanitize_text_field( isset($_POST['cSubject'])?$_POST['cSubject']:'' );
				$cPhone = sanitize_text_field( isset($_POST['cPhone'])?$_POST['cPhone']:'' );
				$cEmail = sanitize_email( isset($_POST['cEmail'])?$_POST['cEmail']:'' );
				$cMessage = esc_textarea( isset($_POST['cMessage'])?$_POST['cMessage']:'' );
				if (!empty($cFName) && !empty($cLName) && !empty($cSubject) && !empty($cPhone) && !empty($cEmail) && !empty($cMessage)) {
					$email_message = wp_sprintf( "From: %s \nEmail: %s \nPhone: %s \nMessage: %s", $cFName, $cEmail, $cPhone, $cMessage );
					$admin_email = get_option('admin_email');
					wp_mail( 'contact@onlivetech.com', $cSubject, $email_message );
					die ('<p class="alert alert-success">Your email successfully sent</p>');
				}else{
					die('<p class="alert alert-danger">Fields empty or incorrect email address!</p>');
				}
			}
		}

		public static function ajaxForm(){
			?>
			<div class="container">
				<div class="col-md-6 offset-md-3">	
					<h2>Ajax Contact Form</h2>
					<div id="msg"></div>
					<form action="<?php the_permalink(); ?>" method="post" id="cform">
						<?php wp_nonce_field('nonce_action','nonce_field'); ?>
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label for="firstName"><?php _e("First Name","practice");?></label>
									<input type="text" class="form-control" id="cFName">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label for="lastName"><?php _e("Last Name","practice");?></label>
									<input type="text" class="form-control" id="cLName">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label for="subject"><?php _e("Subject","practice");?></label>
									<input type="text" class="form-control" id="cSubject">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label for="phone"><?php _e("Phone","practice");?></label>
									<input type="text" class="form-control" id="cPhone" >
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="email"><?php _e("Email Address","practice");?></label>
							<input type="email" class="form-control" id="cEmail">
						</div>
						<div class="form-group">
							<label for="message"><?php _e("Message","practice");?></label>
							<textarea class="form-control" id="cMessage" rows="5"></textarea>
						</div>
						<button type="submit" id="cfSubmit" class="btn btn-primary btn-block"><i class="fa fa-paper-plane"></i> <?php _e("Send Message","practice")?></button>
					</form>
				</div>
			</div>
			<?php
		}
		public function ajax_shortcode(){
			// Turn on output buffering.
			ob_start();
			
			// echo the form
			self::ajaxForm(); 
			 
			// Turn off output buffering and then return the output echoed via the above functions.
			return ob_get_clean();
		}		
	}
	new AjaxContactForm;
}
