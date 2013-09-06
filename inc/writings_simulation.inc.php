<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

class Writings_Simulation extends Collector  {
	
	public $filters = null;
	
	function __construct($class = null, $table = null, $db = null) {
		if ($class === null) {
			$class = substr(__CLASS__, 0, -1);
		}
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_writingssimulations'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}
	
	function grid_header() {
		$grid =  array(
			'header' => array(
				'cells' => array(
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__("name")),
					),
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__("amount including vat")),
					),
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__("month")),
					),
					array(
						'type' => "th",
						'value' => "",
					)
				)
			)
		);
		return $grid;
	}
	
	function grid_body() {
		$submit = new Html_Input("submit", __('save'), "submit");
		$input = new Html_Input("name_new");
		$grid[0] =  array(
			'id' => 0,
			'cells' => array(
				array(
					'type' => "td",
					'value' => $input->item(""),
				),
				array(
					'type' => "td",
					'value' => $input->item(""),
				),
				array(
					'type' => "td",
					'value' => $input->item(""),
				),
				array(
					'type' => "td",
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
		return "<div id=\"simulation\"><form method=\"post\" name=\"simulation\" action=\"\" enctype=\"multipart/form-data\">".
				$this->show()."</form></div>";
	}
}
