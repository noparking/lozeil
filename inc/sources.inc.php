<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

class Sources extends Collector  {
	public $filters = null;
	
	function __construct($class = null, $table = null, $db = null) {
		if ($class === null) {
			$class = substr(__CLASS__, 0, -1);
		}
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_sources'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}
	
	function names() {
		$names = array();
		$names[0] = "--";
		foreach ($this as $source) {
			$names[$source->id] = $source->name();
		}
		return $names;
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
		
		foreach ($this as $source) {
			$input = new Html_Input($source->id, $source->name);
			$grid[$source->id] =  array(
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
	
	function show() {
		$html_table = new Html_table(array('lines' => $this->grid_body()));
		return $html_table->show();
	}
	
	function show_form() {
		return "<div id=\"edit_sources\"><form method=\"post\" name=\"sources_id\" action=\"\" enctype=\"multipart/form-data\">".
				$this->show()."</form></div>";
	}
}
