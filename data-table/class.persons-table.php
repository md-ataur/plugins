<?php
if (!class_exists("WP_List_Table")) {
	require_once(ABSPATH.'/wp-admin/includes/class-wp-list-table.php');
}

class PersonsTable extends WP_List_Table{

	private $persons;

	function set_data($data){				
		$this->persons = $data;		
	}

	/* Columns Name show */
	function get_columns(){
		return [
			'cb'=>'<input type="checkbox">',
			'name'=>__('Name','datatable'),
			'email'=>__('Email','datatable'),
			'sex' =>__('Gender','tabledata'),
			'age'=>__('Age','datatable'),			
		];
	}

	/* Sortable function */
	function get_sortable_columns(){
		return [
			'age'=> ['ag', true],
			'name'=> ['n', true],
		];
	}

	/* Data filter */
    function extra_tablenav( $which ) {
		if ( 'top' == $which ):
		?>
		<div class="actions alignleft">
			<select name="filter_s" id="filter_s">
				<option value="all">All</option>
				<option value="M">Males</option>
				<option value="F">Females</option>
			</select>
			<?php
				// WP builtin button
                submit_button( __( 'Filter', 'tabledata' ), 'button', 'submit', false );
                ?>
		</div>
		<?php
		endif;
	}

	/* Prepare method */
	function prepare_items(){
		$this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
		
		/* Pagination */
		$this->set_pagination_args([
			'total_items' => count($this->persons),
			'per_page' => 3,
			'total_pages' => ceil(count($this->persons) / 3)
		]);
		$paged = $_REQUEST['paged'] ?? 1; // null clues operator in php7
		$data_chumnks = array_chunk($this->persons, 3);	// Split an array 	
		$this->items = $data_chumnks[$paged - 1];
	}

	/* Get columns value */
	function column_default($item, $column_name){
		return $item[$column_name];
	}
}
