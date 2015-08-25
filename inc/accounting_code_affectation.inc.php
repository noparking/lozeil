<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

class Accounting_Code_Affectation extends Record {
	public $id = 0;
	public $accountingcodes_id = 0;
	public $reportings_id = 0;
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
	
	function load(array $key = array(), $table = "accountingcodes_affectation", $columns = null) {
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
	
	function insert() {
		$result = $this->db->id("INSERT INTO ".$this->db->config['table_accountingcodes_affectation']."
			SET id = ".(int)$this->id.",
				accountingcodes_id = ".$this->db->quote($this->accountingcodes_id)." ,
				reportings_id = ".$this->db->quote($this->reportings_id)." ,
				timestamp = ".time()
		);
		$this->id = $result[2];
		$this->db->status($result[1], "i", __("accounting code"));
		
		return $this->id;
	}
	
	function update() {
		$result = $this->db->query("UPDATE ".$this->db->config['table_accountingcodes_affectation'].
			" SET accountingcodes_id = ".$this->db->quote($this->accountingcodes_id)." ,
				  reportings_id = ".$this->db->quote($this->reportings_id)." ,
				  timestamp = ".time()." 
			  WHERE id = ".(int)$this->id
		);
		
		return $this->id;
	}

	function update_id($id) {
		$query = "UPDATE ".$this->db->config['table_accountingcodes_affectation']." 
			SET id = ".(int)$id."
			WHERE id = ".(int)$this->id;

		$this->db->query($query);
		return $id;
	}

	function delete() {
		$result = $this->db->query("DELETE FROM ".$this->db->config['table_accountingcodes_affectation'].
				" WHERE id = '".$this->id."'"
		);
		$this->db->status($result[1], "d", __("accounting code"));
		return $this->id;
	}

	function desaffect() {
		$this->reportings_id = 0;
		$this->save();
	}

	function is_recently_modified(){
		if ($this->timestamp > (time() - 10)) {
			return true;
		}
		return false;
	}

	function form_edit_with_reporting($reporting_id) {
		$reporting = new Reporting();
		$code = new Accounting_Code();
		$code->load(array('id' => $this->accountingcodes_id));

		$includeinto = new Html_Select("reportingcode", $reporting->form_include_accountingcode(), $reporting_id);
		$input_hidden_id = new Html_Input("accountingcodes_id", $code->id, "hidden");
		$input_hidden_action = new Html_Input("action", "edit_accountingcode", "submit");
		$name_input = new Html_Input("name", $code->name);
		$affectation_id = new Html_Input("affectation_id", $this->id, "hidden");
		$affectation_reporting_id = new Html_Input("affectation_reporting_id", $this->reportings_id, "hidden");
		$submit = new Html_Input("submit", __("modify"), "submit");

		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'title' => array(
					'value' => "<h1>".__("add new reporting")."</h1>"
				),
				'name' => array(
					'value' => $name_input->item(ucfirst(__("name")))
				),
				'include' => array(
					'value' => $includeinto->item(ucfirst(__("included in")))
				),
				'submit' => array(
					'value' => $submit->input()
				)
			)
		);

		$list = new Html_List($grid);

		$form = "<div class=\"form_edit_accountingcode\">
			<form method=\"post\" name=\"table_accountingcodes_edit\" action=\"\" enctype=\"multipart/form-data\">".
				$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$affectation_id->input_hidden().$affectation_reporting_id->input_hidden().$list->show()."
			</form>
		</div>";

		return $form;
	}

	function form_edit_not_affected() {
		$reporting = new Reporting();
		$code = new Accounting_Code();
		$code->load(array('id' => $this->accountingcodes_id));

		$name_input = new Html_Input("name", $code->name);
		$input_hidden_id = new Html_Input("accountingcodes_id", $code->id, "hidden");
		$input_hidden_action = new Html_Input("action", "edit_accountingcode_non_affected", "submit");
		$includeinto = new Html_Select("reportingcode", $reporting->form_include_accountingcode(), "none");
		$submit = new Html_Input("submit", __("modify"), "submit");

		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'title' => array(
					'value' => "<h1>".__("add new reporting")."</h1>"
				),
				'name' => array(
					'value' => $name_input->item(ucfirst(__("name")))
				),
				'include' => array(
					'value' => $includeinto->item(ucfirst(__("included in")))
				),
				'submit' => array(
					'value' => $submit->input()
				)
			)
		);

		$list = new Html_List($grid);

		$form = "<div class=\"form_edit_accountingcode_non_affected\">
			<form method=\"post\" name=\"table_accountingcodes_non_affected_edit\" action=\"\" enctype=\"multipart/form-data\">".
				$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$list->show()."
			</form>
		</div>";

		return $form;
	}
}
