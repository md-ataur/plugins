<?php
/*
Plugin Name: Data Table
Plugin URI: http://example.com
Description: Data Table
Version: 1.0
Author: Ataur Rahman
Author URI: md-ataur.github.io
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: datatable
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
require_once "class.persons-table.php";

add_action("admin_menu","datatable_admin_page");
function datatable_admin_page(){
	add_menu_page( __('DataTable','datatable'), __('DataTable','datatable'), 'manage_options', 'datatable', 'display_table');
}

/* Search callback function */
function datatable_search_by_name($item){
	$name = strtolower($item['name']);
	$search_name = sanitize_text_field( $_REQUEST['s'] );
	if (strpos($name,$search_name)!== false) {
		return true;
	}
	return false;
}

function display_table(){
	include_once "dataset.php";	
	if ( isset( $_REQUEST['s'] ) && !empty($_REQUEST['s']) ) {
		$data = array_filter( $data, 'datatable_search_by_name' );
	}
	$orderby = $_REQUEST['orderby'] ?? '';
	$order = $_REQUEST['order'] ?? '';

	$table = new PersonsTable;
	if ('age' == $orderby) {
		if ('asc' == $order) {
			usort($data, function($item1, $item2){
				return $item1['age']<=>$item2['age'];
			});
		}else{
			usort($data, function($item1, $item2){
				return $item2['age']<=>$item1['age'];
			});
		}
	}else if ('name' == $orderby) {
		if ('asc' == $order) {
			usort($data, function($item1, $item2){
				return $item1['name']<=>$item2['name'];
			});
		}else{
			usort($data, function($item1, $item2){
				return $item2['name']<=>$item1['name'];
			});
		}
	}
	$table->set_data($data);
	$table->prepare_items();
	?>
	<div class="wrapper">
		<form action="" method="GET">
			<h2><?php _e("Persons","datatable");?></h2>
			<?php
			$table->search_box('search','search_id');
			$table->display();
			?>
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'];?>">
		</form>
	</div>
	<?php	
}