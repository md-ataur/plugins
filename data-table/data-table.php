<?php
/*
Plugin Name: Data Table
Plugin URI: 
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

/* Add Admin Menu */
add_action("admin_menu","datatable_admin_page");
function datatable_admin_page(){
	add_menu_page( __('Data Table','datatable'), __('Data Table','datatable'), 'manage_options', 'datatable', 'display_table');
}

/* Search callback function */
function datatable_search_by_name($item){
	$name = strtolower($item['name']); // array data
	$search_name = sanitize_text_field( $_REQUEST['s'] ); /* user search data */
	if (strpos($name, $search_name)!== false) {
		return true;
	}
	return false;
}

/* Filter callback function */
function datatable_filter_sex($item){
    $sex = $_REQUEST['filter_s']??'all';
    if('all' == $sex){
        return true;
    }else{
        if( $sex == $item['sex']){
            return true;
        }
    }
    return false;
}

/* Admin Menu Call back function */
function display_table(){	
	// dataset file include
	include_once "dataset.php";		
	
	// Class object
	$table = new PersonsTable;	

	/* Sorting orderby = Column Name and order = Asc or Desc */
	$orderby = $_REQUEST['orderby'] ?? '';
	$order = $_REQUEST['order'] ?? '';	

	if ('ag' == $orderby) {
		if ('asc' == $order) {
			usort($data, function($item1, $item2){
				return $item1['age']<=>$item2['age']; // Spaceship (<=>) operator.
			});
		}else{
			usort($data, function($item1, $item2){
				return $item2['age']<=>$item1['age'];
			});
		}
	}else if ('n' == $orderby) {
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
	
	/* Search value check */
	if ( isset( $_REQUEST['s'] ) && !empty($_REQUEST['s']) ) {
		$data = array_filter( $data, 'datatable_search_by_name' );		
	}

	/* Filter value check */
	if ( isset( $_REQUEST['filter_s'] ) && !empty($_REQUEST['filter_s']) ) {
		$data = array_filter( $data, 'datatable_filter_sex' );
	}

	$table->set_data($data);
	$table->prepare_items();
	
	?>
	<div class="wrapper">
		<form action="" method="GET">
			<h1><?php _e("Persons","datatable");?></h1>
			<?php
			/* Display search form */
			$table->search_box('search','search_id');
			/* Display Table */
			$table->display();
			?>
			<!-- Submition on the current page -->
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'];?>">
		</form>
	</div>
	<?php	
}