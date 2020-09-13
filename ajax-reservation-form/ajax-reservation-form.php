<?php
/*
Plugin Name: Ajax Reservation Form
Plugin URI: http://example.com
Description: Reservation Form
Version: 1.0
Author: Ataur Rahman
Author URI: md-ataur.github.io
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ajaxrf
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( !class_exists( 'AjaxReservationForm' ) ) {

    class AjaxReservationForm {

        public function __construct() {
            add_action( 'plugins_loaded', array( $this, 'ajaxrf_load_textdomain' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'ajaxrf_enqueue_scripts' ) );
            add_shortcode( 'ajax_reservation_form', array( $this, 'ajaxrf_shortcode' ) );
            add_action( 'wp_ajax_ajaxRSF', array( $this, 'AjaxdataProcess' ) );
            add_action( 'wp_ajax_nopriv_ajaxRSF', array( $this, 'AjaxdataProcess' ) );
        }
        public function ajaxrf_load_textdomain() {
            load_plugin_textdomain( 'ajaxrf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }
        public function ajaxrf_enqueue_scripts() {
            wp_enqueue_style( 'bootstrap', plugin_dir_url( __FILE__ ) . 'assets/public/css/bootstrap.min.css' );
            wp_enqueue_script( 'ajax-reservation-js', plugin_dir_url( __FILE__ ) . 'assets/public/js/ajax-reservation.js', array( 'jquery' ), time(), true );
            $ajaxUrl = admin_url( 'admin-ajax.php' );
            wp_localize_script( 'ajax-reservation-js', 'url', array( 'ajaxUrl' => $ajaxUrl ) );
        }

        public static function AjaxdataProcess() {
            if ( check_ajax_referer( 'rsf_nonce_action', 'rn', false ) ) {
                $name = sanitize_text_field( isset( $_POST['RFname'] ) ? $_POST['RFname'] : '' );
                $email = sanitize_email( isset( $_POST['RFemail'] ) ? $_POST['RFemail'] : '' );
                $phone = sanitize_text_field( isset( $_POST['RFphone'] ) ? $_POST['RFphone'] : '' );
                $person = sanitize_text_field( isset( $_POST['RFperson'] ) ? $_POST['RFperson'] : '' );
                $date = sanitize_text_field( isset( $_POST['RFdate'] ) ? $_POST['RFdate'] : '' );
                $time = sanitize_text_field( isset( $_POST['RFtime'] ) ? $_POST['RFtime'] : '' );
                $message = sanitize_text_field( isset( $_POST['RFMessage'] ) ? $_POST['RFMessage'] : '' );

                if ( !empty( $name ) && !empty( $email ) && !empty( $phone ) && !empty( $person ) && !empty( $date ) && !empty( $time ) ) {
                    $data = array(
                        'name'    => $name,
                        'email'   => $email,
                        'phone'   => $phone,
                        'person'  => $person,
                        'date'    => $date,
                        'time'    => $time,
                        'message' => $message,
                    );
                    //print_r($data);
                    $reservation_arrg = array(
                        'post_title'  => wp_sprintf( '%s Reservation for %s persons on %s - %s', $name, $person, $date . " : " . $time, $email ),
                        'post_type'   => 'reservation',
                        'post_status' => 'publish',
                        'post_author' => 1,
                        'post_date'   => date( 'Y-m-d H:i:s' ),
                        'meta_input'  => $data,
                    );
                    $wp_error = '';
                    wp_insert_post( $reservation_arrg, $wp_error );
                    if ( !$wp_error ) {
                        echo "Successfully reserved";
                    }
                    
                    $email_message = wp_sprintf( "From: %s \nEmail: %s \nPhone: %s \nPersons: %s \nTime: %s \nMessage: %s", $name, $email, $phone, $person, $time, $message );
                    //$admin_email = get_option('admin_email');
                    wp_mail( 'contact@onlivetech.com', $name, $email_message );
                    die( '<p class="alert alert-success">Successfully sent your reservation</p>' );
                } else {
                    die( '<p class="alert alert-danger">Fields empty or incorrect email </p>' );
                }
            }
        }

        public static function ajax_reservation_form() {
            ?>
			<div class="container">
				<div class="col-md-6 offset-md-3">
					<div id="msg"></div>
	                <form action="<?php the_permalink();?>" id="Rform">
	                	<?php wp_nonce_field( 'rsf_nonce_action', 'rsf_nonce_field' );?>
                        <div class="form-group">
                            <label for="name" class="label"><?php _e( 'Name', 'ajaxrf' );?></label>
                            <input type="text" class="form-control" id="RFname">
                        </div>
                        <div class="form-group">
                            <label for="email" class="label"><?php _e( 'Email', 'ajaxrf' );?></label>
                            <input type="email" class="form-control" id="RFemail">
                        </div>
                        <div class="form-group">
                            <label for="phone" class="label"><?php _e( 'Phone', 'ajaxrf' );?></label>
                            <input type="text" class="form-control" id="RFphone">
                        </div>
                        <div class="form-group">
                            <label for="persons" class="label"><?php _e( 'Number of Persons', 'ajaxrf' );?></label>
                            <select name="persons" id="RFperson" class="form-control">
                                <option value="1"><?php _e( '1 person', 'ajaxrf' );?> </option>
                                <option value="2"><?php _e( '2 person', 'ajaxrf' );?> </option>
                                <option value="3"><?php _e( '3 person', 'ajaxrf' );?></option>
                                <option value="4"><?php _e( '4 person', 'ajaxrf' );?></option>
                                <option value="5"><?php _e( '5 person', 'ajaxrf' );?></option>
                            </select>
                        </div>
                        <div class="row">
	                        <div class="form-group col-md-6">
	                            <label for="date" class="label"><?php _e( 'Date', 'ajaxrf' );?></label>
	                            <input type="date" class="form-control" id="RFdate">
	                        </div>
	                        <div class="form-group col-md-6">
	                            <label for="time" class="label"><?php _e( 'Time', 'ajaxrf' );?></label>
	                            <input type="time" class="form-control" id="RFtime" autocomplete="off">
	                        </div>
                        </div>
                        <div class="form-group">
                        	<textarea class="form-control" id="RFMessage" rows="5"></textarea>
                        </div>
	                    <div class="row justify-content-center">
	                    	<button id="reserveForm" class="btn btn-primary"><?php _e( 'Reserve Now', 'ajaxrf' );?></button>
	                    </div>
	                </form>
	            </div>
			</div>
			<?php
}

        public function ajaxrf_shortcode() {
            // Turn on output buffering.
            ob_start();

            self::ajax_reservation_form(); // echo the form

            // Turn off output buffering and then return the output echoed via the above functions.
            return ob_get_clean();
        }
    }

    new AjaxReservationForm;
}
