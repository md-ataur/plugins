<?php
/**
 * Plugin Name:  Posts Column Demo
 * Plugin URI:
 * Description:  Posts Column Demo
 * Version:      1.0
 * Author:       Ataur Rahman
 * Author URI:
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  column-demo
 * Domain Path:  /languages
 */


if (!class_exists("ColumnDemo")) {
	
	class ColumnDemo{
		
		function __construct(){
			add_action( "column-demo", array( $this,"clmd_load_textdomain" ) );
			
			/* Column add on the post */
			add_filter( "manage_posts_columns",  array($this,"clmd_column_add") );
			
			/* Column add on the page */
			add_filter( "manage_pages_columns",  array($this,"clmd_column_add") );
			
			/* Data show on the post column */
			add_action( "manage_posts_custom_column", array($this,"clmd_column_data"), 10, 2);

			/* Data show on the page column */
			add_action( "manage_pages_custom_column", array($this,"clmd_column_data"), 10, 2);

			/* Filter add */
			add_action( "restrict_manage_posts", array($this,"clmd_filter" ));
			add_action( "restrict_manage_posts", array($this,"clmd_thumbfilter" ));

			/* Data Filtering */
			add_action( "pre_get_posts", array($this,"clmd_filter_data"));
			add_action( "pre_get_posts", array($this,"clmd_thumbfilter_data"));
		}

		function clmd_load_textdomain(){
			load_plugin_textdomain( "column-demo", false, plugin_dir_path( __FILE__ )."/languages" );
		}


		/* Callback function for Column add */
		function clmd_column_add($columns){				
			/* Posts column order change
			----------------------------			
			unset($columns['tags']);
			$columns['tags'] = 'Tags';				
			unset($columns['date']);			
			$columns['date'] = 'Date';
			*/
			$columns['id'] = __('Post ID','column-demo');	
			$columns['thumbnail'] = __('Thumbnail','column-demo');
			$columns['wordcount'] = __('Word Count','column-demo');

			return $columns;
		}

		/* Callback function for Data show on the column */
		function clmd_column_data($column, $post_id){
			if ("id" == $column) {
				echo esc_html( $post_id );
			}else if("thumbnail" == $column){
				$thumbnail = get_the_post_thumbnail($post_id, array(100,100));
				echo $thumbnail;
			}else if("wordcount" == $column){
				$_post = get_post($post_id);				
				$content = $_post->post_content;
				$wordn = str_word_count(strip_tags($content));
				/* If you don't use the strip_tags function then HTML, PHP tag will be counted */
				echo esc_html( $wordn );
			}			
		}


		/**
		 * Simple Filtering
		 * ====================================================
		 */
		
		/* Callback function for Filter add */
		function clmd_filter(){
			if (isset($_GET['post_type']) && $_GET['post_type'] != "post" ) {
				return;
			}
			$filter_value = isset($_GET['demofilter']) ? $_GET['demofilter'] : '';
			
			/* Filter title */
			$values = array(
				'0' => __('Select Status','column-demo'),
				'1' => __('Some Posts','column-demo'),
				'2' => __('Some Posts ++','column-demo')
			);

			?>
			<select name="demofilter">
				<?php
					foreach ($values as $key => $value) {					
						printf("<option value='%s' %s>%s</optoin>", $key, $key == $filter_value ? "selected" : '', $value);
					}
				?>
			</select>
			<?php
		}

		/* Callback function for data filtering */
		function clmd_filter_data($wpquery){
			if (!is_admin()) {
				return;
			}			
			$filter_value = isset($_GET['demofilter']) ? $_GET['demofilter'] : '';
			if ('1' == $filter_value) {
				$wpquery->set('post__in', array(1,2,3)); // Posts id
			}else if ('2' == $filter_value) {
				$wpquery->set('post__in', array(4,5)); // Posts id
			}
			return $wpquery;
		}


		/**
		 * Filter for Thumbnail
		 * ====================================================
		 */
		
		/* Callback function for Filter add */
		function clmd_thumbfilter(){
			if (isset($_GET['post_type']) && $_GET['post_type'] != "post" ) {
				return;
			}
			$filter_value = isset($_GET['thumfilter']) ? $_GET['thumfilter'] : '';
			$values = array(				
				'0' => __('Filtering by thumbnail','column-demo'),
				'1' => __('Has Thumbnail','column-demo'),
				'2' => __('No Thumbnail','column-demo')
			);			

			?>
			<select name="thumfilter">
				<?php
					foreach ($values as $key => $value) {					
						printf("<option value='%s' %s>%s</optoin>",$key, $key == $filter_value ? "selected" : '',$value);
					}
				?>
			</select>
			<?php
		}

		/* Callback function for data filtering */
		function clmd_thumbfilter_data($wpquery){
			if (!is_admin()) {
				return;
			}
			
			$filter_value = isset($_GET['thumfilter']) ? $_GET['thumfilter'] : '';
			if ('1' == $filter_value) {
				$wpquery->set('meta_query', array(
					array(
						'key' 		=> '_thumbnail_id',
						'compare' 	=> 'EXISTS'
					)
				));
			}else if ('2' == $filter_value) {
				$wpquery->set('meta_query', array(
					array(
						'key' 		=> '_thumbnail_id',
						'compare' 	=> 'NOT EXISTS'
					)
				));
			}

			return $wpquery;
		}
	}
	new ColumnDemo();
}