<?php
/**
 * Plugin Name:  Our MetaBox
 * Plugin URI:
 * Description:  Our MetaBox
 * Version:      1.0
 * Author:       Ataur Rahman
 * Author URI:
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  our-metabox
 * Domain Path:  /languages
 */

if ( !class_exists( "OurMetaBox" ) ) {    

    class OurMetaBox {

        function __construct() {
            add_action( "plugins_loaded", array( $this, "omb_load_textdomain" ) );
            add_action( "admin_enqueue_scripts", array( $this, "omb_admin_assets" ) );

            /* Hook For Metabox add */
            //add_action( "add_meta_boxes", array( $this, "omb_metabox_display" ) );            
            //add_action( "load-post.php", array( $this, "omb_metabox_display" ) );
            add_action( "admin_menu", array( $this, "omb_metabox_display" ) );

            /* Hook For Meta value save */
            add_action( "save_post", array( $this, "omb_metavalue_save" ) );
            add_action( "save_post", array( $this, "omb_image_save" ) );
            add_action( "save_post", array( $this, "omb_gallery_save" ) );
        }

        function omb_load_textdomain() {
            load_plugin_textdomain( "our-metabox", false, plugin_dir_path( __FILE__ ) . "/languages" );
        }

        function omb_admin_assets() {
            wp_enqueue_style( "bootstrap-css", plugin_dir_url( __FILE__ ) . "assets/admin/css/bootstrap.min.css" );
            wp_enqueue_style( "jquery-ui-css", "//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" );
            wp_enqueue_script( "omb-main-js", plugin_dir_url( __FILE__ ) . "assets/admin/js/main.js", array( "jquery", "jquery-ui-datepicker" ), time(), true );
        }

        /* Meta fields check */
        private function is_secured( $nonce_field, $action, $post_id ) {

            $nonce_field = isset( $_POST[$nonce_field] ) ? $_POST[$nonce_field] : '';

            /* Check nonce value empty or not */
            if ( "" == $nonce_field ) {
                return false;
            }

            /* Verify nonce value */
            if ( !wp_verify_nonce( $nonce_field, $action ) ) {
                return false;
            }

            /* Check edit capability for current user */
            if ( !current_user_can( "edit_post", $post_id ) ) {
                return false;
            }

            /* Check WP Autosave */
            if ( wp_is_post_autosave( $post_id ) ) {
                return false;
            }

            if ( wp_is_post_revision( $post_id ) ) {
                return false;
            }

            return true;

        }

        /* Callback function for metabox display */
        function omb_metabox_display() {
            add_meta_box( "omb_metabox_id", __( "Meta Info", "our-metabox" ), array( $this, "omb_metabox_add" ), array( "post" ), "normal" );
            add_meta_box( "omb_upload_id", __( "Image Info", "our-metabox" ), array( $this, "omb_image_info" ), array( "post" ), "normal" );
            add_meta_box( "omb_gallery_id", __( "Gallery Info", "our-metabox" ), array( $this, "omb_gallery_info" ), array( "post" ), "normal" );
        }

/**
 * =======================================================================
 */
        /* Image Upload Metabox display and data fetch */
        function omb_image_info( $post ) {
            /* Get data from database */
            $image_id  = esc_attr( get_post_meta( $post->ID, "omb_image_id", true ) );
            $image_url = esc_attr( get_post_meta( $post->ID, "omb_image_url", true ) );

            /* Echo titles */
            $image_title = __( "Upload: ", "our-metabox" );
            $button      = __( "Image Upload", "our-metabox" );

            /* WP Nonce */
            wp_nonce_field( "omb_image_nonce_action", "omb_image_nonce_field" );

            /* Uplaod Field Display */
            $metabox_image = <<<EOD
<div class="form-group row">
	<label for="image_title" class="col-form-label col-sm-1">{$image_title}</label>
	<div class="col-sm-8">
		<button id="image_upload" class="btn btn-primary">{$button}</button>
		<input type="hidden" id="omb_image_id" name="omb_image_id" value="{$image_id}"/>
		<input type="hidden" id="omb_image_url" name="omb_image_url" value="{$image_url}" />
		<div class="mt-1" id="image_container"></div>
	</div>
</div>
EOD;
            echo $metabox_image;

        }

        /* Callback function for meta value save on the database */
        function omb_image_save( $post_id ) {
            if ( !$this->is_secured( "omb_image_nonce_field", "omb_image_nonce_action", $post_id ) ) {
                return $post_id;
            }

            $image_id  = isset( $_POST['omb_image_id'] ) ? $_POST['omb_image_id'] : '';
            $image_url = isset( $_POST['omb_image_url'] ) ? $_POST['omb_image_url'] : '';

            update_post_meta( $post_id, "omb_image_id", $image_id );
            update_post_meta( $post_id, "omb_image_url", $image_url );

        }

/**
 * =========================================================================
 */

        /* Gallery Upload Metabox display and data fetch */
        function omb_gallery_info( $post ) {
            /* Get data from database */
            $gallery_id  = get_post_meta( $post->ID, "omb_gallery_id", true );
            $gallery_url = get_post_meta( $post->ID, "omb_gallery_url", true );

            /* Echo titles */
            $gallery_title = __( "Upload: ", "our-metabox" );
            $button        = __( "Gallery Upload", "our-metabox" );

            /* WP Nonce */
            wp_nonce_field( "omb_gallery_nonce_action", "omb_gallery_nonce_field" );

            /* Uplaod Field Display */
            $metabox_gallery = <<<EOD
<div class="form-group row">
	<label for="gallery_title" class="col-form-label col-sm-1">{$gallery_title}</label>
	<div class="col-sm-8">
		<button id="gallery_upload" class="btn btn-primary">{$button}</button>
		<input type="hidden" id="omb_gl_id" name="omb_gallery_id" value="{$gallery_id}" />
		<input type="hidden" id="omb_gl_url" name="omb_gallery_url" value="{$gallery_url}" />
		<div class="mt-1" id="gallery_container"></div>
	</div>
</div>
EOD;
            echo $metabox_gallery;

        }

        /* Callback function for meta value save on the database */
        function omb_gallery_save( $post_id ) {
            if ( !$this->is_secured( "omb_gallery_nonce_field", "omb_gallery_nonce_action", $post_id ) ) {
                return $post_id;
            }

            $gallery_id  = isset( $_POST['omb_gallery_id'] ) ? $_POST['omb_gallery_id'] : '';
            $gallery_url = isset( $_POST['omb_gallery_url'] ) ? $_POST['omb_gallery_url'] : '';

            update_post_meta( $post_id, "omb_gallery_id", $gallery_id );
            update_post_meta( $post_id, "omb_gallery_url", $gallery_url );

        }

/**
 * =========================================================================
 */

        /* Metabox display and meta value retrieve */
        function omb_metabox_add( $post ) {
            
            /* Get meta field value */
            $meta_value_location = get_post_meta( $post->ID, "omb_location", true );
            $meta_value_country  = get_post_meta( $post->ID, "omb_country", true );
            $meta_value_date     = get_post_meta( $post->ID, "omb_date", true );
            $is_favourite        = get_post_meta( $post->ID, "is_favourite", true );
            $checked             = $is_favourite == 1 ? "checked" : 0;

            /* Get checkbox value */
            $saved_colors = get_post_meta( $post->ID, "omb_color_checkbox", true );

            /* Get radio value */
            $rd_color = get_post_meta( $post->ID, "omb_color_radio", true );

            /* Get Dropdown Option value */
            $select_color = get_post_meta( $post->ID, "omb_color_select", true );

            /* Echo Titles */
            $label1 = __( "Country:", "our-metabox" );
            $label2 = __( "Location:", "our-metabox" );
            $label3 = __( "Is Favourite ?", "our-metabox" );
            $label4 = __( "Checkbox: ", "our-metabox" );
            $label5 = __( "Radio: ", "our-metabox" );
            $label6 = __( "Select Color ", "our-metabox" );
            $label7 = __( "Publish Year: ", "our-metabox" );

            $colors = array(
                'red',
                'green',
                'blue',
                'white',
                'black',
                'yellow',
                'magenta',
                'pink',
            );

            /* WP Nonce */
            wp_nonce_field( "omb_nonce_action", "omb_nonce_field" );            

            /* input fields, datepicker and checkbox display */
            $metabox_html = <<<EOD
<div class="form-group row">
	<label for="omb_country" class="col-form-label col-sm-1">{$label1}</label>
	<div class="col-sm-8">
		<input type="text" class="form-control col-sm-3" name="omb_country" value="{$meta_value_country}" />
	</div>
</div>
<div class="form-group row">
	<label for="omb_location" class="col-form-label col-sm-1">{$label2}</label>
	<div class="col-sm-8">
		<input type="text" class="form-control col-sm-3" name="omb_location" value="{$meta_value_location}" />
	</div>
</div>
<div class="form-group row">
	<label for="omb_location" class="col-form-label col-sm-1">{$label7}</label>
	<div class="col-sm-8">
		<input type="text" class="form-control col-sm-3 datepicker" name="omb_date" value="{$meta_value_date}" />
	</div>
</div>

<p>
	<label class="mr-2" for="Is Favourite">{$label3}</label>
	<input type="checkbox" name="is_favourite" value="1" {$checked} />
</p>

<div class="form-group">
	<label class="mr-2">{$label4}</label>
EOD;

            /* Multiple checkbox display */
            $saved_colors = is_array( $saved_colors ) ? $saved_colors : array();
            foreach ( $colors as $color ) {
                $_color      = ucwords( $color );
                $checkdColor = in_array( $color, $saved_colors ) ? "checked" : "";
                $metabox_html .= <<<EOD
<div class="form-check form-check-inline">
	<input class="form-check-input" type="checkbox" name="omb_color[]" value="{$color}" {$checkdColor} />
	<label class="form-check-label" for="omb_clr{$color}">{$_color}</label>
</div>
EOD;
            }
            
            $metabox_html .= "</div>";

            /* Multiple Radio display */
            $metabox_html .= <<<EOD
<div class="form-group">
	<label class="mr-2">{$label5}</label>
EOD;
            foreach ( $colors as $color ) {
                $_color  = ucwords( $color );
                $checked = ( $color == $rd_color ) ? "checked" : "";
                $metabox_html .= <<<EOD
<div class="form-check form-check-inline">
    <input class="form-check-input" type="radio" name="omb_color_radio" value="{$color}" {$checked} />
    <label class="form-check-label" for="omb_radio{$color}">{$_color}</label>
</div>

EOD;
            }

            $metabox_html .= "</div>";

            /* Dropdown Option display */
            $metabox_html .= <<<EOD
<div class="form-group">
	<label for="omb_color_select">{$label6}</label>
	<div class="px-0 col-sm-2">
		<select class="custom-select px-2" id="omb_color_select" name="omb_color_select">
			<option value="0" >{$label6}</option>
EOD;
            foreach ( $colors as $color ) {
                $_color   = ucwords( $color );
                $selected = ( $color == $select_color ) ? "selected" : "";
                $metabox_html .= <<<EOD
                <option value="{$color}" {$selected}>{$_color}</option>
EOD;
            }

            $metabox_html .= "</select></div></div>";

            echo $metabox_html;
        }


        /* Callback function for meta value save on the database */
        function omb_metavalue_save( $post_id ) {
            if ( !$this->is_secured( "omb_nonce_field", "omb_nonce_action", $post_id ) ) {
                return $post_id;
            }

            $country         = isset( $_POST['omb_country'] ) ? $_POST['omb_country'] : '';
            $location        = isset( $_POST['omb_location'] ) ? $_POST['omb_location'] : '';
            $date            = isset( $_POST['omb_date'] ) ? $_POST['omb_date'] : '';
            $is_favourite    = isset( $_POST['is_favourite'] ) ? $_POST['is_favourite'] : 0;
            $checkbox_colors = isset( $_POST['omb_color'] ) ? $_POST['omb_color'] : array();
            $radio_color     = isset( $_POST['omb_color_radio'] ) ? $_POST['omb_color_radio'] : "";
            $select_color    = isset( $_POST['omb_color_select'] ) ? $_POST['omb_color_select'] : "";

            $country  = sanitize_text_field( $country );
            $location = sanitize_text_field( $location );

            update_post_meta( $post_id, "omb_country", $country );
            update_post_meta( $post_id, "omb_location", $location );
            update_post_meta( $post_id, "omb_date", $date );
            update_post_meta( $post_id, "is_favourite", $is_favourite );
            update_post_meta( $post_id, "omb_color_checkbox", $checkbox_colors );
            update_post_meta( $post_id, "omb_color_radio", $radio_color );
            update_post_meta( $post_id, "omb_color_select", $select_color );            
            
            /**
             * update_post_meta( int $post_id, string $meta_key, mixed $meta_value, mixed $prev_value = '' );
             * The value will be updated if already exist, otherwise, the new value will be saved
             */
        }

    }

    new OurMetaBox();
}
