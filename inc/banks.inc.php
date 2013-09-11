<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

class Banks extends Collector  {
	public $filters = null;
	
	function __construct($class = null, $table = null, $db = null) {
		if ($class === null) {
			$class = substr(__CLASS__, 0, -1);
		}
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_banks'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}
	
	function names() {
		$names = array();
		$names[0] = "--";
		foreach ($this as $bank) {
			$names[$bank->id] = $bank->name();
		}
		return $names;
	}
	
	function names_of_selected_banks() {
		$names = array();
		$names[0] = "--";
		foreach ($this as $bank) {
			if ($bank->selected == 1) {
				$names[$bank->id] = $bank->name();
			}
		}
		return $names;
	}
	
	function grid_header() {
		$grid = array(
			'header' => array(
				'class' => "table_header",
				'cells' => array(
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__("name")),
					),
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__("use")),
					),
				)
			)
		);
		return $grid;
	}
	
	function grid_body() {
		foreach ($this as $bank) {
			$input = new Html_Checkbox($bank->id, $bank->name, $bank->selected);
			$grid[$bank->id] =  array(
				'cells' => array(
					array(
						'type' => "td",
						'value' => $bank->name,
					),
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
					'colspan' => "2",
					'value' => $submit->item(""),
				),
			)
		);
		return $grid;
	}
	
	function grid() {
		return $this->grid_header() + $this->grid_body();
	}
	
	function show() {
		$html_table = new Html_table(array('lines' => $this->grid()));
		return $html_table->show();
	}
	
	function show_form() {
		return "<div id=\"edit_banks\"><form method=\"post\" name=\"banks_id\" action=\"\" enctype=\"multipart/form-data\">".
				$this->show()."</form></div>";
	}
	
	function get_id_from_name($name) {
		$id = 0;
		$this->select();
		foreach ($this as $instance) {
			if (preg_match("/".$name."/", $instance->name) and $instance->selected) {
				$id = $instance->id;
			}
		}
		return $id;
	}
}
