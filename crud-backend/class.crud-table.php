<?php

if (!class_exists("WP_List_Table")) {
	require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');	
}


class CRUD_UserData extends WP_List_Table{
	private $_items;
	function __construct($data){
		parent::__construct();
		$this->_items = $data;
	}

	function get_columns(){
		return [
			'cb' => 'input type="checkbox"',
			'name'=> __('Name','crud-backend'),
			'email'=> __('Email','crud-backend'),			
		];
	}

	function column_name($item){
		$actions = [			
			'edit'  => sprintf( '<a href="?page=crud&id=%s">%s</a>', $item['id'], __( 'Edit', 'crud-backend' ) ),
			'delete' => sprintf( '<a href="?page=crud&id=%s&action=%s">%s</a>', $item['id'], 'delete', __('Delete','crud-backend') ),
		];
		return sprintf('%s %s', $item['name'],$this->row_actions($actions));
	}

	function column_cb($item){
		return "<input type='checkbox' value='{$item['id']}'>";
	}


	/* Pagination */
	function prepare_items(){
		$perpage = 2;
		$current_page = $this->get_pagenum();		
		$total_items = count($this->_items);
		$this->set_pagination_args([
			'total_items'=>$total_items,
			'per_page'=>$perpage
		]);		
		$data = array_slice($this->_items,($current_page-1)*$perpage,$perpage);		
		$this->items = $data;

		$this->_column_headers = array($this->get_columns(),[],[]);
	}
	
	function column_default($item, $column_name){
		return $item[$column_name];		
	}
}