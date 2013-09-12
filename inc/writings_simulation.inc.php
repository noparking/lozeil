<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

class Writings_Simulation extends Record {
	public $name = "";
	public $amount_inc_vat = 0;
	public $periodicity = "";
	public $date_start = 0;
	public $date_stop = 0;
	public $display = 0;
	public $timestamp = 0;
	
	function __construct($id = 0, db $db = null) {
		parent::__construct($db);
		$this->id = $id;
	}

	function db($db) {
		if ($db instanceof db) {
			$this->db = $db;
		}
	}
	
	function load($id = null) {
		if (($id === null or $id == 0) and ($this->id === null or $this->id == 0)) {
			return false;

		} else {
			if ($id === null) {
				$id = $this->id;
			}
			return parent::load($this->db->config['table_writingssimulations'], array('id' => (int)$id));
		}
	}
	
	function save() {
		if (is_numeric($this->id) and $this->id != 0) {
			$this->id = $this->update();

		} else {
			$this->id = $this->insert();
		}

		return $this->id;
	}

	function delete() {
		$query = "DELETE FROM ".$this->db->config['table_writingssimulations'].
		" WHERE id = '".$this->id."'";
		$result = $this->db->query($query);
		$this->db->status($result[1], "u", __('writings simulations'));

		return $this->id;
	}
	
	function update() {
		$query = "UPDATE ".$this->db->config['table_writingssimulations'].
		" SET name = ".$this->db->quote($this->name).",
		amount_inc_vat = ".$this->amount_inc_vat.",
		periodicity = ".$this->db->quote($this->periodicity).",
		date_start = ".(int)$this->date_start.",
		date_stop = ".(int)$this->date_stop.",
		display = ".(int)$this->display.",
		timestamp = ".time()."
		WHERE id = ".(int)$this->id;
		
		$result = $this->db->query($query);
		$this->db->status($result[1], "u", __('writings simulations'));

		return $this->id;
	}
	
	function insert() {
		$result = $this->db->id("
			INSERT INTO ".$this->db->config['table_writingssimulations'].
		" SET name = ".$this->db->quote($this->name).",
		amount_inc_vat = ".$this->amount_inc_vat.",
		periodicity = ".$this->db->quote($this->periodicity).",
		date_start = ".(int)$this->date_start.",
		date_stop = ".(int)$this->date_stop.",
		display = ".(int)$this->display.",
		timestamp = ".time()
		);
		$this->id = $result[2];
		$this->db->status($result[1], "u", __('writings simulations'));

		return $this->id;
	}
	
	function fill($hash) {
		$writingssimulation = parent::fill($hash);
		
		if (isset($hash['amount_inc_vat'])) {
			$writingssimulation->amount_inc_vat = str_replace(",", ".", $hash['amount_inc_vat']);
		}
		if (isset($hash['display'])) {
			$writingssimulation->display = 1;
		} else {
			$writingssimulation->display = 0;
		}
		$writingssimulation->date_start = mktime(0, 0, 0, $hash['date_start']['m'], $hash['date_start']['d'], $hash['date_start']['Y']);
		$writingssimulation->date_stop = mktime(0, 0, 0, $hash['date_stop']['m'], $hash['date_stop']['d'], $hash['date_stop']['Y']);
		return $writingssimulation;
	}
	
	function form() {
		$form = "<div id=\"edit_writingssimulation\">
			<span class=\"button\" id=\"edit_writingssimulation_show\">".utf8_ucfirst(__('show form'))."</span>
			<span class=\"button\" id=\"edit_writingssimulation_hide\">".utf8_ucfirst(__('hide form'))."</span>
			<span class=\"button\" id=\"edit_writingssimulation_cancel\">".Html_Tag::a(link_content("content=writingssimulations.php"),utf8_ucfirst(__('cancel record')))."</span>
			<div class=\"edit_writingssimulation_form\">
			<form method=\"post\" name=\"edit_writingssimulation_form\" action=\"\" enctype=\"multipart/form-data\">";
		
		$input_hidden = new Html_Input("action", "insert");
		$form .= $input_hidden->input_hidden();

		$name = new Html_Input("name", $this->name);
		$amount_inc_vat = new Html_Input("amount_inc_vat", $this->amount_inc_vat);
		$date_start = new Html_Input_Date("date_start");
		$date_stop = new Html_Input_Date("date_stop");
		$periodicity = new Html_Input("periodicity", $this->periodicity);
		$display = new Html_Checkbox("display", "display");
		$submit = new Html_Input("submit", "", "submit");
		$submit->value =__('save');
		
		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'name' => array(
					'value' => $name->item(__('name')),
				),
				'amount_inc_vat' => array(
					'value' => $amount_inc_vat->item(__('amount including vat')),
				),
				'date_start' => array(
					'value' => $date_start->item(__('start date')),
				),
				'date_stop' => array(
					'value' => $date_stop->item(__('stop date')),
				),
				'periodicity' => array(
					'value' => $periodicity->item(__('periodicity')),
				),
				'display' => array(
					'value' => $display->item(__('display')),
				),
				'submit' => array(
					'value' => $submit->item(""),
				),
			)
		);				
		$list = new Html_List($grid);
		$form .= $list->show();
		
		$form .= "</form></div></div>";

		return $form;
	}
	
	function form_in_table() {
		$form = "<tr class=\"table_writingssimulation_form_modify\"><td colspan=\"7\" ><div id=\"table_edit_writingssimulation\">
			<span class=\"button\" id=\"table_edit_writingssimulation_cancel\">".Html_Tag::a(link_content("content=writingssimulations.php"),utf8_ucfirst(__('cancel record')))."</span>
			<div class=\"table_edit_writingssimulation_form\">
			<form method=\"post\" name=\"table_edit_writingssimulation_form\" action=\"\" enctype=\"multipart/form-data\">";
		
		$input_hidden = new Html_Input("action", "edit");
		$form .= $input_hidden->input_hidden();
		
		$input_hidden_id = new Html_Input("id", $this->id);
		$form .= $input_hidden_id->input_hidden();

		$name = new Html_Input("name", $this->name);
		$amount_inc_vat = new Html_Input("amount_inc_vat", $this->amount_inc_vat);
		$date_start = new Html_Input_Date("date_start", $this->date_start);
		$date_stop = new Html_Input_Date("date_stop", $this->date_stop);
		$periodicity = new Html_Input("periodicity", $this->periodicity);
		$display = new Html_Checkbox("display", "display", $this->display);
		$submit = new Html_Input("submit", "", "submit");
		$submit->value =__('save');
		
		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'name' => array(
					'value' => $name->item(__('name')),
				),
				'amount_inc_vat' => array(
					'value' => $amount_inc_vat->item(__('amount including vat')),
				),
				'date_start' => array(
					'value' => $date_start->item(__('start date')),
				),
				'date_stop' => array(
					'value' => $date_stop->item(__('stop date')),
				),
				'periodicity' => array(
					'value' => $periodicity->item(__('periodicity')),
				),
				'display' => array(
					'value' => $display->item(__('display')),
				),
				'submit' => array(
					'value' => $submit->item(""),
				),
			)
		);				
		$list = new Html_List($grid);
		$form .= $list->show();
		
		$form .= "</form></div></div></td></tr>";

		return $form;
	}

	function is_form_valid($form) {
		switch (true) {
			case empty($form['name']) :
			case empty($form['amount_inc_vat']) :
			case empty($form['date_start']['d']) :
			case empty($form['date_start']['m']) :
			case empty($form['date_start']['Y']) :
			case empty($form['date_stop']['d']) :
			case empty($form['date_stop']['m']) :
			case empty($form['date_stop']['Y']) :
			case empty($form['periodicity']) :
				return false;
			default :
				return true;
		}
	}
	
	function form_modify() {
		return "<div class=\"modify\">".
			Html_Tag::a(link_content("content=writingssimulations.php")," ").
			"</div>";
	}

	function show_operations() {
		return $this->form_modify().$this->form_duplicate().$this->form_delete();
	}
	
	
	function form_duplicate() {
		$form = "<div class=\"duplicate\"><form method=\"post\" name=\"table_writingssimulation_duplicate\" action=\"\" enctype=\"multipart/form-data\">";
		$input_hidden_id = new Html_Input("table_writingssimulation_duplicate_id", $this->id);
		$input_hidden_action = new Html_Input("action", "duplicate");
		$submit = new Html_Input("table_writingssimulation_duplicate_submit", "", "submit");
		$input_hidden_value = new Html_Input("table_writingssimulation_duplicate_amount", "");
		$form .= $input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input().$input_hidden_value->input_hidden();
		$form .= "</form></div>";
		return $form;
	}
	
	function form_delete() {
		$form = "<div class=\"delete\"><form method=\"post\" name=\"table_writingssimulation_delete\" action=\"\" enctype=\"multipart/form-data\">";
		$input_hidden_id = new Html_Input("table_writingssimulation_delete_id", $this->id);
		$input_hidden_action = new Html_Input("action", "delete");
		$submit = new Html_Input("table_writings_deletesimulation_submit", "", "submit");
		$submit->properties = array(
			'onclick' => "javascript:return confirm('".utf8_ucfirst(__('are you sure?'))."')"
		);
		$form .= $input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input();
		$form .= "</form></div>";
		return $form;
	}
	
	function is_recently_modified(){
		if($this->timestamp > (time() - 10)) {
			return true;
		}
		return false;
	}
	
	function duplicate($amount) {
		if (is_numeric($amount) and $amount > 0) {
			for ($i=1; $i<=$amount; $i++) {
				$new_writing = $this;
				$new_writing->id = 0;
				$new_writing->save();
			}
		}
	}
}
