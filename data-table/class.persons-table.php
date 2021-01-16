<?php
if (!class_exists("WP_List_Table")) {
	require_once(ABSPATH.'/wp-admin/includes/class-wp-list-table.php');
}

class PersonsTable extends WP_List_Table{

	private $_items;

	function set_data($data){				
		$this->_items = $data;		
	}

	/* Column Names show */
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

	/* Prepare the items */
	function prepare_items(){
		$this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
		
		/* Pagination */
		$perpage = 5;
		$total_items = count($this->_items);
		$current_page = $this->get_pagenum();		
		$offset       = ( $current_page - 1 ) * $perpage;
		
		$this->set_pagination_args([
			'total_items'=>$total_items,
			'per_page'=>$perpage
		]);		
		
		$data = array_slice($this->_items, $offset, $perpage);

		/* Assign the table data inside the items variable */
		$this->items = $data;
	}

	/* Get columns value */
	function column_default($item, $column_name){
		return $item[$column_name];
	}
}
