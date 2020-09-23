<?php
/*
Plugin Name: Taxonomy Meta Field
Plugin URI:
Description: Taxonomy Meta Field
Version: 1.0
Author: Ataur Rahman
Author URI: https://md-ataur.github.io/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: taxonomy-meta-field
Domain Path: /languages/
*/	

if (!class_exists("TaxMetaField")) {
	
	class TaxMetaField{

		function __construct(){
			add_action( "plugins_loaded", array($this,"txmf_load_textdomain" ));
			add_action( "init", array($this,"txmf_bootstrap" ));
			
			/* add field */
			add_action( "category_add_form_fields", array($this,"txmf_category_form_field" ));
			//add_action( "post_tag_add_form_fields", array($this,"txmf_tag_form_field" ));
			
			/* edit field */
			add_action( "category_edit_form_fields", array($this,"txmf_category_edit_form_field" ));
			//add_action( "post_tag_edit_form_fields", array($this,"txmf_tag_edit_form_field" ));
			
			/* meta value save */
			add_action( "create_category", array($this,"txmf_save_category_meta" ));
			//add_action( "create_post_tag", array($this,"txmf_save_tag_meta" ));
			
			/* meta value update */
			add_action( "edit_category", array($this,"txmf_update_category_meta" ));
			//add_action( "edit_post_tag", array($this,"txmf_update_tag_meta" ));
		}

		function txmf_load_textdomain(){
			load_plugin_textdomain( "taxonomy-meta-field", false, plugin_dir_path( __FILE__ )."/languages" );
		}

		function txmf_bootstrap(){
			$arguments = array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field', // Sanitization before data save
				'single'            => true,
				'description'       => 'Sample meta field for category taxonomy',
				'show_in_rest'      => true,
			);
			register_meta( 'term', 'txmf_extra_info', $arguments );
		}

		/* add field */
		function txmf_category_form_field(){			
			?>
			<div class="form-field form-required term-name-wrap">
				<label for="tag-name"><?php _e("Extra Info","taxonomy-meta-field");?></label>
				<input name="extra-info" id="extra-info" type="text" value="" size="40" aria-required="true">
				<p><?php _e("Some help Text","taxonomy-meta-field");?></p>
			</div>
			<?php
		}

		/* edit field */
		function txmf_category_edit_form_field($term){			
			$extra_info = get_term_meta( $term->term_id, "txmf_extra_info", true );
			?>
			<tr class="form-field form-required term-name-wrap">
				<th scope="row"><label for="tag-name"><?php _e("Extra edit Info","taxonomy-meta-field");?></label>
				</th>
				<td>
					<input name="extra-edit-info" id="extra-edit-info" type="text" value="<?php echo esc_attr($extra_info);?>" size="40" aria-required="true">
					<p><?php _e("Some help Text","taxonomy-meta-field");?></p>
				</td>
			</tr>
			<?php
		}

		/* Meta value save on the database  */
		function txmf_save_category_meta($term_id){
			if (wp_verify_nonce( $_POST['_wpnonce_add-tag'], "add-tag" )) {
				$extra_info =  sanitize_text_field($_POST['extra-info']);
				update_term_meta( $term_id, "txmf_extra_info", $extra_info );
			}
		}

		/* Meta value update */
		function txmf_update_category_meta($term_id){
			if (wp_verify_nonce( $_POST['_wpnonce'], "update-tag_{$term_id}" )) {
				$extra_info =  sanitize_text_field($_POST['extra-edit-info']);
				update_term_meta( $term_id, "txmf_extra_info", $extra_info );
			}
		}
	}

	new TaxMetaField();
}