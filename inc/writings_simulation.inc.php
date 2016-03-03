<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

class Writings_Simulation extends Record {
	public $name = "";
	public $amount_inc_vat = 0;
	public $periodicity = "";
	public $date_start = 0;
	public $date_stop = 0;
	public $display = 0;
	public $timestamp = 0;
	public $evolution = "";
	
	private $evolutions = array();
	
	function __construct($id = 0, db $db = null) {
		parent::__construct($db);
		$this->id = $id;
		
		$this->evolutions = array(
			"none" => __('none'),
			"linear" => __('linear')
		);
	}

	function db($db) {
		if ($db instanceof db) {
			$this->db = $db;
		}
	}
	
	function load(array $key = array(), $table = "writingssimulations", $columns = null) {
		return parent::load($key, $table, $columns);
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
		$result = $this->db->query("
			DELETE FROM ".$this->db->config['table_writingssimulations']."
			WHERE id = ".(int)$this->id
		);
		$this->db->status($result[1], "d", __('writings simulations'));
		return $this->id;
	}
	
	function update() {
		$result = $this->db->query("
			UPDATE ".$this->db->config['table_writingssimulations']."
			SET name = ".$this->db->quote($this->name).",
			amount_inc_vat = ".$this->amount_inc_vat.",
			periodicity = ".$this->db->quote($this->periodicity).",
			date_start = ".(int)$this->date_start.",
			date_stop = ".(int)$this->date_stop.",
			display = ".(int)$this->display.",
			timestamp = ".time().",
			evolution = ".$this->db->quote($this->evolution)."
			WHERE id = ".(int)$this->id
		);
		$this->db->status($result[1], "u", __('writings simulations'));
		return $this->id;
	}
	
	function insert() {
		$result = $this->db->query_with_id("
			INSERT INTO ".$this->db->config['table_writingssimulations']."
			SET name = ".$this->db->quote($this->name).",
			amount_inc_vat = ".$this->amount_inc_vat.",
			periodicity = ".$this->db->quote($this->periodicity).",
			date_start = ".(int)$this->date_start.",
			date_stop = ".(int)$this->date_stop.",
			display = ".(int)$this->display.",
			timestamp = ".time().",
			evolution = ".$this->db->quote($this->evolution)
		);
		$this->id = $result[2];
		$this->db->status($result[1], "i", __('writings simulations'));
		return $this->id;
	}
	
	function fill($hash) {
		$writingssimulation = parent::fill($hash);
		
		switch ($hash['evolution']) {
			case 'none':
				$writingssimulation->evolution = "";
				break;
			case 'linear':
				if (!empty($hash['evolution_periodical'])) {
					$hash['evolution_periodical'] = str_replace(",", ".", $hash['evolution_periodical']);
					if (is_numeric($hash['evolution_periodical'])) {
						$writingssimulation->evolution = "linear".":".$hash['evolution_periodical'];
					} else {
						$writingssimulation->evolution = "";
					}
				} else {
					$writingssimulation->evolution = "";
				}
				break;
			default:
				break;
		}
		
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
		return "<div id=\"insert_simulations\"><span class=\"button\" id=\"insert_simulations_show\">".utf8_ucfirst(__('show form'))."</span></div>";
	}
	
	function display() {
		$form = "<div id=\"edit_simulations_form\">
			<form method=\"post\" name=\"edit_simulations_form\" action=\"\" enctype=\"multipart/form-data\">";
		
		$input_hidden = new Html_Input("action", "insert");
		$form .= $input_hidden->input_hidden();

		$name = new Html_Input("name", $this->name);
		$amount_inc_vat = new Html_Input("amount_inc_vat", $this->amount_inc_vat);
		$evolution = new Html_Select("evolution", $this->evolutions);
		$evolution_periodical = new Html_Input("evolution_periodical");
		$date_start = new Html_Input_Date("date_start");
		$date_stop = new Html_Input_Date("date_stop");
		$periodicity = new Html_Input("periodicity", $this->periodicity);
		$display = new Html_Checkbox("display", "display", 1);
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
				'evolution' => array(
					'value' => $evolution->item(__('evolution')).$evolution_periodical->input(),
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
		
		$form .= "</form></div>";

		return $form;
	}
	
	function form_in_table() {
		$form = "<div id=\"table_edit_writingssimulation\">
			<form method=\"post\" name=\"table_edit_writingssimulation_form\" action=\"\" enctype=\"multipart/form-data\">";
		
		$evolution_selected = explode(":", $this->evolution);
		$evolution_value = (isset($evolution_selected[1]) and is_numeric($evolution_selected[1])) ? $evolution_selected[1] : "";
		
		$input_hidden = new Html_Input("action", "edit");
		$form .= $input_hidden->input_hidden();
		$input_hidden_id = new Html_Input("id", $this->id);
		$form .= $input_hidden_id->input_hidden();
		$name = new Html_Input("name", $this->name);
		$amount_inc_vat = new Html_Input("amount_inc_vat", $this->amount_inc_vat);
		$evolution = new Html_Select("evolution", $this->evolutions, $evolution_selected[0]);
		$evolution_periodical = new Html_Input("evolution_periodical", $evolution_value);
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
				'evolution' => array(
					'value' => $evolution->item(__('evolution')).$evolution_periodical->input(),
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
		
		$form .= "</form></div>";

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
				return false;
			default :
				return true;
		}
	}
	
	function show_form_modify() {
		$input_hidden_id = new Html_Input("table_simulations_modify_id", $this->id);
		$input_hidden_action = new Html_Input("action", "form_edit");
		$submit = new Html_Input("table_simulations_modify_submit", "", "submit");
		
		$form = "<div class=\"modify\">
					<form method=\"post\" name=\"table_simulations_modify\" action=\"\" enctype=\"multipart/form-data\">".
						$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input()."
					</form>
				</div>";
			
		return $form;
	}

	function show_form_duplicate() {
		$input_hidden_id = new Html_Input("table_simulations_form_duplicate_id", $this->id);
		$input_hidden_action = new Html_Input("action", "form_duplicate");
		$submit = new Html_Input("table_simulations_duplicate_submit", "", "submit");
		
		$form = "<div class=\"duplicate\">
					<form method=\"post\" name=\"table_simulations_form_duplicate\" action=\"\" enctype=\"multipart/form-data\">".
						$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input()."
					</form>
				</div>";
		
		return $form;
	}
	
	function show_operations() {
		return $this->show_form_modify().$this->show_form_duplicate().$this->form_delete();
	}
	
	function form_duplicate() {
		$input_hidden_id = new Html_Input("simulation_id", $this->id);
		$input_hidden_action = new Html_Input("action", "duplicate");
		$submit = new Html_Input("table_simulations_duplicate_submit", utf8_ucfirst(__('save')), "submit");
		$input_value = new Html_Input("table_simulations_duplicate_amount", "");
		
		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'duplicate' => array(
					'value' => $input_value->item(utf8_ucfirst(__('duplicate')))." ".__('time(s)'),
				),
				'submit' => array(
					'value' => $submit->item(""),
				),
			)
		);
		$list = new Html_List($grid);
		$form = "<div class=\"form_duplicate\">
					<form method=\"post\" name=\"table_simulations_duplicate\" action=\"\" enctype=\"multipart/form-data\">".
						$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$list->show()."
					</form>
				</div>";
		
		return $form;
	}
	
	function form_delete() {
		$form = "<div class=\"delete\"><form method=\"post\" name=\"table_simulations_delete\" action=\"\" enctype=\"multipart/form-data\">";
		$input_hidden_id = new Html_Input("table_simulations_delete_id", $this->id);
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
