<?php
/**
 * Plugin Name:  Post Taxonomy Meta Field
 * Plugin URI:
 * Description:  Post Taxonomy Meta Field
 * Version:      1.0
 * Author:       Ataur Rahman
 * Author URI:
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  post-taxonomy-meta-field
 * Domain Path:  /languages
 */


if (!class_exists("PostTaxMeta")) {
	
	class PostTaxMeta{

		function __construct(){
			add_action( "plugins_loaded", array( $this, "ptmf_load_textdomain" ));
			add_action( "admin_enqueue_scripts", array($this, "ptmf_admin_assets"));
			
			/* Hook For Metabox add */
			add_action( "admin_menu", array( $this, "ptmf_metabox_add" ));

			/* Hook For Meta value save */
			add_action( "save_post", array($this, "ptmf_meta_save") );
		}

		function ptmf_load_textdomain(){
			load_plugin_textdomain( "post-taxonomy-meta-field", false, plugin_dir_path( __FILE__ )."/languages" );
		}

		function ptmf_admin_assets(){
			wp_enqueue_style( "bootstrap-css", plugin_dir_url( __FILE__ )."assets/admin/css/bootstrap.min.css" );
			wp_enqueue_style( "chosen-css", "//cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" );
			wp_enqueue_script( "jquery-chosen", "//cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js", array("jquery"), "1.0", true );
			wp_enqueue_script( "main-js", plugin_dir_url( __FILE__ )."assets/admin/js/main.js", array("jquery"), time(), true );
		}

		/* Nonce Check */
		private function is_check($nonce_field, $nonce_action, $post_id){
			$nonce = isset( $_POST[$nonce_field] ) ? $_POST[$nonce_field] : '';
			
			/* Check nonce value empty or not */
			if ("" == $nonce) {
				return false;
			}

			/* Verify nonce value */
			if (!wp_verify_nonce( $nonce, $nonce_action )) {
				return false;
			}

			/* Check edit capability for current user */
			if (!current_user_can( "edit_post" )) {
				return false;
			}

			/* Check WP Autosave */
			if (wp_is_post_autosave( $post_id )) {
				return false;
			}
			
			if (wp_is_post_revision( $post_id )) {
				return false;
			}
			return true;

		}

		/* Callback function for meta add */
		function ptmf_metabox_add(){
			add_meta_box( "ptmf_post_metaid", __("Post Meta Field", "post-taxonomy-meta-field"), array($this, "ptmf_metabox_display"), array("page"), "normal");			
		}

		/* Meta field display */
		function ptmf_metabox_display($post){
			$post_value = get_post_meta( $post->ID, 'ptmf_post_option', true );	
			$term_value = get_post_meta( $post->ID, 'ptmf_taxonomy_term', true );	

			/* Post query */
			$_posts = new WP_Query(array(
				'post_type' => 'post',
				'posts_per_page'=> -1 
			));

			/* echo "<pre>";
			print_r($_posts);
			echo "</pre>"; */

			$dropdown_list = '';
			$post_value = is_array($post_value)?$post_value:[];
			while ($_posts->have_posts()) {
				$_posts->the_post();
				$selected = '';
				if (in_array(get_the_ID(), $post_value)) {
					$selected = "selected";
				}
				$dropdown_list .= sprintf("<option %s value='%s'>%s</option>", $selected, get_the_ID(), get_the_title());
			}
			wp_reset_query();


			/* Taxonomy term */
			$term_dropdown_list = '';
			$_terms = get_terms( array(
				'taxonomy' => 'category',
				'hide_empty' => false
			));

			foreach ($_terms as $_term) {
				$term_id = $_term->term_id;
				$term_name = $_term->name;
				$selected = ($term_id == $term_value) ? "selected" : "";
				$term_dropdown_list .= sprintf("<option %s value='%s'>%s</option>", $selected, $term_id, $term_name);
			}

			/* nonce field */
			wp_nonce_field( "ptmf_action", "ptmf_nonce_field" );

			$label1 = __("Select Post","post-taxonomy-meta-field");
			$label2 = __("Select Tag","post-taxonomy-meta-field");
			$post_meta_html = <<<EOD
<div class="form-group">
	<label for="ptmf_select_post">{$label1}</label>
	<div class="px-0 col-sm-4">
		<select data-placeholder="Select your post" multiple="multiple" class="chosen-select custom-select px-2" name="ptmf_select_post[]">
			<option value="0">{$label1}</option>
			{$dropdown_list}
		</select>
	</div>
</div>
<div class="form-group">
	<label for="ptmf_select_term">{$label2}</label>
	<div class="px-0 col-sm-4">
		<select class="chosen-select custom-select px-2" name="ptmf_select_term">
			<option value="0">{$label2}</option>
			{$term_dropdown_list}
		</select>
	</div>
</div>
EOD;
		echo $post_meta_html;

		}	

		/* Callback function for meta value save on the database */
		function ptmf_meta_save($post_id){			
			if (!$this->is_check("ptmf_nonce_field", "ptmf_action", $post_id)) {
				return $post_id;
			}

			$post_select = isset($_POST['ptmf_select_post']) ? $_POST['ptmf_select_post'] : '';
			$term_select = isset($_POST['ptmf_select_term']) ? $_POST['ptmf_select_term'] : '';

			update_post_meta( $post_id, 'ptmf_post_option', $post_select );
			update_post_meta( $post_id, 'ptmf_taxonomy_term', $term_select );

		}
	}

	new PostTaxMeta();
	
}