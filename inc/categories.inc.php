<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

class Categories extends Collector  {
	public $filters = null;
	
	function __construct($class = null, $table = null, $db = null) {
		$class = "Category";
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_categories'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}
	
	function names() {
		$names = array();
		$names[0] = "--";
		foreach ($this as $category) {
			$names[$category->id] = $category->name();
		}
		return $names;
	}
	
	function grid_header() {
		$grid = array(
			'header' => array(
				'cells' => array(
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__("categories")),
					)
				)
			)
		);
		return $grid;
	}
	
	function grid_body() {
		$input = new Html_Input("name_new");
		$grid[0] =  array(
			'id' => 0,
			'cells' => array(
				array(
					'type' => "td",
					'value' => $input->item(""),
				),
			)
		);
		
		foreach ($this as $category) {
			$input = new Html_Input($category->id, $category->name);
			$grid[$category->id] =  array(
				'cells' => array(
					array(
						'type' => "td",
						'value' => $input->item(""),
					),
				)
			);
		}
		
		$submit = new Html_Input("submit", __('save'), "submit");
		$grid[] =  array(
			'cells' => array(
				array(
					'type' => "td",
					'value' => $submit->item(""),
				),
			)
		);
		return $grid;
	}
		
	
	function grid_footer() {
		return array();
	}

	function grid() {
		return $this->grid_header() + $this->grid_body() + $this->grid_footer();
	}
	
	function show() {
		$html_table = new Html_table(array('lines' => $this->grid()));
		return $html_table->show();
	}
	
	function show_form() {
		return "<div id=\"edit_categories\"><form method=\"post\" name=\"categories_id\" action=\"\" enctype=\"multipart/form-data\">".
				$this->show()."</form></div>";
	}
}
