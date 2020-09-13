<?php
/**
 * Plugin Name:  Taxonomy Meta
 * Plugin URI:
 * Description:  User Profile Settings
 * Version:      1.0
 * Author:       Ataur Rahman
 * Author URI:
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  tax-meta
 * Domain Path:  /languages
 */


if (!class_exists("TaxMeta")) {
	
	class TaxMeta{

		function __construct(){
			add_action( "plugins_loaded", array($this,"taxm_load_textdomain" ));
			add_action( "init", array($this,"taxm_bootstrap" ));
			
			/* add field */
			add_action( "category_add_form_fields", array($this,"taxm_category_form_field" ));
			
			/* edit field */
			add_action( "category_edit_form_fields", array($this,"taxm_category_edit_form_field" ));
			
			/* meta value save */
			add_action( "create_category", array($this,"taxm_save_category_meta" ));
			
			/* meta value update */
			add_action( "edit_category", array($this,"taxm_update_category_meta" ));
		}

		function taxm_load_textdomain(){
			load_plugin_textdomain( "tax-meta", false, plugin_dir_path( __FILE__ )."/languages" );
		}

		function taxm_bootstrap(){
			$arguments = array(
				'type'=> 'string',
				'sanitize_callback'=>'sanitize_text_field',
				'single'=>true,
				'description'=>'Sample meta field for category tax',
				'show_in_rest'=>true
			);
			register_meta( 'term', 'taxm_extra_info', $arguments );
		}

		/* add field */
		function taxm_category_form_field(){			
			?>
			<div class="form-field form-required term-name-wrap">
				<label for="tag-name"><?php _e("Extra Info","tax-meta");?></label>
				<input name="extra-info" id="extra-info" type="text" value="" size="40" aria-required="true">
				<p><?php _e("Some help Text","tax-meta");?></p>
			</div>
			<?php
		}

		/* edit field */
		function taxm_category_edit_form_field($post){			
			$extra_info = get_term_meta( $post->term_id, "taxm_extra_info", true );
			?>
			<tr class="form-field form-required term-name-wrap">
				<th scope="row"><label for="tag-name"><?php _e("Extra edit Info","tax-meta");?></label>
				</th>
				<td>
					<input name="extra-edit-info" id="extra-edit-info" type="text" value="<?php echo esc_attr($extra_info);?>" size="40" aria-required="true">
					<p><?php _e("Some help Text","tax-meta");?></p>
				</td>
			</tr>
			<?php
		}

		/* Meta value save on the database  */
		function taxm_save_category_meta($term_id){
			if (wp_verify_nonce( $_POST['_wpnonce_add-tag'], "add-tag" )) {
				$extra_info =  sanitize_text_field($_POST['extra-info']);
				update_term_meta( $term_id, "taxm_extra_info", $extra_info );
			}
		}


		/* Meta value update */
		function taxm_update_category_meta($term_id){
			if (wp_verify_nonce( $_POST['_wpnonce'], "update-tag_{$term_id}" )) {
				$extra_info =  sanitize_text_field($_POST['extra-edit-info']);
				update_term_meta( $term_id, "taxm_extra_info", $extra_info );
			}
		}
	}

	new TaxMeta();
}