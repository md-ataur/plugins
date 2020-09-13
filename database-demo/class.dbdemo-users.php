<?php
if (!class_exists("WP_List_Table")) {
	require_once('ABSPATH'.'wp-admin/includes/class-wp-list-table.php');
}
class DBTableUsers extends WP_List_Table{
	private $_items;
	function __construct($data){
		parent::__construct();
		$this->_items = $data;
	}
	function get_columns(){
		return [
			'cb'=> '<input type="checkbox">',
			'name'=> __('Name','database-demo'),
			'email'=> __('Email','database-demo'),
			'action'=> __('Action','database-demo'),
		];
	}
	function column_name($item){
		$actions = [			
			'edit'  => sprintf( '<a href="?page=dbdemo&id=%s">%s</a>', $item['id'], __( 'Edit', 'database-demo' ) ),
			'delete' => sprintf( '<a href="?page=dbdemo&id=%s&action=%s">%s</a>', $item['id'], 'delete', __('Delete','database-demo') ),
		];
		return sprintf('%s %s', $item['name'],$this->row_actions($actions));
	}
	function column_cb( $item ) {
		return "<input type='checkbox' value='{$item['id']}'>";
	}
	function column_action( $item ){
		$link = admin_url('?page=dbdemo&id=') . $item['id'];		
		return '<a href="'.esc_url($link).'">'.__('Edit','database-demo').'</a>';		
	}

	function prepare_items(){
		$per_page = 2;
		$current_page = $this->get_pagenum();
		$total_items = count($this->_items);
		$this->set_pagination_args([
			'total_items'=>$total_items,
			'per_page'=>$per_page
		]);
		$data = array_slice($this->_items,($current_page-1)*$per_page,$per_page);
		$this->items = $data;
		$this->_column_headers = array($this->get_columns(),[],[]);
	}
	function column_default($item, $column_name){
		return $item[$column_name];		
	}
}
