<?php
if (!class_exists("WP_List_Table")) {
	require_once('ABSPATH'.'wp-admin/includes/class-wp-list-table.php');
}

class PersonsTable extends WP_List_Table{

	function set_data($data){
		parent::__construct();
		$this->items = $data;
	}
	function get_columns(){
		return [
			'cb'=>'<input type="checkbox">',
			'name'=>__('Name','datatable'),
			'email'=>__('Email','datatable'),
			'age'=>__('Age','datatable'),			
		];
	}
	function get_sortable_columns(){
		return [
			'age'=> ['age', true],
			'name'=> ['name', true],
		];
	}
	function prepare_items(){
		$this->_column_headers = array($this->get_columns(),array(), $this->get_sortable_columns());
	}
	function column_default($item, $column_name){
		return $item[$column_name];
	}
}
