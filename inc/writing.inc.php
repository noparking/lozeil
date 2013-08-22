<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

class Writing extends Record {
	public $id = 0;
	public $account_id = 0;
	public $source_id = 0;
	public $amount_inc_tax = 0;
	public $type_id = 0;
	public $vat = 0;
	public $amount_excl_tax = 0;
	public $delay = 0;
	public $paid = 0;
	
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
			return parent::load($this->db->config['table_writings'], array('id' => (int)$id));
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
		$query = "DELETE FROM ".$this->db->config['table_writings'].
		" WHERE id = '".$this->id."'";
		$result = $this->db->query($query);
		$this->db->status($result[1], "u", __('writing'));

		return $this->id;
	}
	
	function update() {
		$vat = is_null($this->vat) ? "vat = NULL" : "vat = ".$this->vat;
		$amount_inc_tax = is_null($this->amount_inc_tax) ? "amount_inc_tax = NULL" : "amount_inc_tax = ".$this->amount_inc_tax;
		$amount_excl_tax = is_null($this->amount_excl_tax) ? "amount_excl_tax = NULL" : "amount_excl_tax = ".$this->amount_excl_tax;
		$query = "UPDATE ".$this->db->config['table_writings'].
		" SET account_id = ".(int)$this->account_id.",
		source_id = ".(int)$this->source_id.",
		".$amount_inc_tax.",
		type_id  = ".(int)$this->type_id.",
		".$vat.",
		".$amount_excl_tax.",
		paid = ".(int)$this->paid.",
		delay = ".(int)$this->delay."
		WHERE id = ".(int)$this->id;
		$result = $this->db->query($query);
		$this->db->status($result[1], "u", __('writing'));

		return $this->id;
	}
	
	function insert() {
		$vat = is_null($this->vat) ? "vat = NULL" : "vat = ".$this->vat;
		$amount_inc_tax = is_null($this->amount_inc_tax) ? "amount_inc_tax = NULL" : "amount_inc_tax = ".$this->amount_inc_tax;
		$amount_excl_tax = is_null($this->amount_excl_tax) ? "amount_excl_tax = NULL" : "amount_excl_tax = ".$this->amount_excl_tax;
		$result = $this->db->id("
			INSERT INTO ".$this->db->config['table_writings']."
			SET account_id = ".(int)$this->account_id.",
			source_id = ".(int)$this->source_id.",
			".$amount_inc_tax.",
			type_id  = ".(int)$this->type_id.",
			".$vat.",
			".$amount_excl_tax.",
			delay = ".(int)$this->delay.",
			paid = ".(int)$this->paid
		);
		$this->id = $result[2];
		$this->db->status($result[1], "u", __('writing'));

		return $this->id;
	}
	
	function get_name_from_table($table) {
		if ($table[strlen($table) - 1] == 's') {
			$table_row = substr($table, 0, -1);
		} else {
			$table_row = $table;
		}
		$where = $table_row."_id";
		$query = "SELECT ".$table.".name as name ".
		" FROM ".$table.
		" WHERE ".$this->$where." = ".$table.".id";

		$result = $this->db->query($query);
		while ($row = $this->db->fetchArray($result[0])) {
			if (isset($row['name'])) {
				$name = $row['name'];
				return $name;
			} else {
				return "";
			}
		}
	}
	
	function paid_to_text() {
		if($this->paid == 0) {
			return __("non");
		} else {
			return __("oui");
		}
	}
	
	function merge(Writing $to_merge) {
		$this->account_id = (isset($to_merge->account_id) and $to_merge->account_id > 0) ? (int)$to_merge->account_id : $this->account_id;
		$this->source_id = (isset($to_merge->source_id) and $to_merge->source_id > 0) ? (int)$to_merge->source_id : $this->source_id;
		$this->amount_excl_tax = isset($to_merge->amount_excl_tax) ? $to_merge->amount_excl_tax : $this->amount_excl_tax;
		$this->amount_inc_tax = isset($to_merge->amount_inc_tax) ? $to_merge->amount_inc_tax : $this->amount_inc_tax;
		$this->delay = isset($to_merge->delay) ? $to_merge->delay : $this->delay;
		$this->vat = isset($to_merge->vat) ? $to_merge->vat : $this->vat;
		$this->type_id = (isset($to_merge->type_id) and $to_merge->type_id > 0) ? $to_merge->type_id : $this->type_id;
		$this->paid = isset($to_merge->paid) ? $to_merge->paid : $this->paid;
		$this->save();
		$to_merge->delete();
	}
	
	function split($amount = 0) {
		if (isset($this->amount_inc_tax)) {
			$this->amount_inc_tax = ($this->amount_inc_tax - $amount);
			$this->amount_excl_tax = round(($this->amount_inc_tax/(($this->vat/100) + 1)), 6);
			$this->save();
			$new_writing = new Writing();
			$new_writing->id = $this->id;
			$new_writing->load();
			$new_writing->id = 0;
			$new_writing->amount_inc_tax = $amount;
			$new_writing->amount_excl_tax = round($amount/(($this->vat/100) + 1), 6);
			$new_writing->save();
		}
	}
	
	function form() {
		$form = "<div class=\"form_add_edit_writing\">
			<form method=\"post\" name=\"form_writing\" id=\"form_writing\" action=\"\" enctype=\"multipart/form-data\">";
		
		if ($this->id) {
			$input_hidden = new Html_Input("action", "do_edit");
			$input_hidden->id = $this->id;
		} else {
			$input_hidden = new Html_Input("action", "insert");
		}
		$form .= $input_hidden->input_hidden();
		
		$input_hidden_id = new Html_Input("id", $this->id);
		$form .= $input_hidden_id->input_hidden();
		
		if ($this->delay > 0) {
			$date = date('Y', $this->delay)."-".date('m', $this->delay)."-".date('d', $this->delay);
		} else {
			$date = date('Y', $_SESSION['month_encours'])."-".date('m', $_SESSION['month_encours'])."-".date('d', $_SESSION['month_encours']);
		}
		
		$accounts = new Accounts();
		$accounts->select();
		$accounts_name = $accounts->names();
		$types = new Types();
		$types->select();
		$types_name = $types->names();
		$sources = new Sources();
		$sources->select();
		$sources_name = $sources->names();
		
		$delay = new Html_Input("delay", $date, "date");
		$account = new Html_Select("account_id", $accounts_name, $this->account_id);
		$source = new Html_Select("source_id", $sources_name, $this->source_id);
		$type = new Html_Select("type_id", $types_name, $this->type_id);
		$amount_excl_tax = new Html_Input("amount_excl_tax", $this->amount_excl_tax);
		$vat = new Html_Input("vat", $this->vat);
		$amount_inc_tax = new Html_Input("amount_inc_tax", $this->amount_inc_tax);
		$paid = new Html_Radio("paid", array(__("no"),__("yes")), $this->paid);
		$submit = new Html_Input("submit", "", "submit");
		$submit->value =__('save');
		
		$grid = array();
		$grid['class'] = "itemsform";
		$grid['leaves']['delay']['value'] = $delay->item(__('delay'));
		$grid['leaves']['account']['value'] = $account->item(__('account'));
		$grid['leaves']['source']['value'] = $source->item(__('source'));
		$grid['leaves']['type']['value'] = $type->item(__('type'));
		$grid['leaves']['amount_excl_tax']['value'] = $amount_excl_tax->item(__('amount excluding tax'));
		$grid['leaves']['vat']['value'] = $vat->item(__('VAT'));
		$grid['leaves']['amount_inc_tax']['value'] = $amount_inc_tax->item(__('amount including tax'));
		$grid['leaves']['paid']['value'] = $paid->item(__('paid'));
		$grid['leaves']['submit']['value'] = $submit->item("");
		
		$list = new Html_List($grid);
		$form .= $list->show();
		
		$form .= "</form></div>";

		return $form;
	}
	
	function form_edit() {
		$form = "<div class=\"modify\"><form method=\"post\" name=\"edit_writing\" id=\"edit_writing\" action=\"\" enctype=\"multipart/form-data\">";
		$input_hidden_id = new Html_Input("id", $this->id);
		$input_hidden_action = new Html_Input("action", "getid");
		$submit = new Html_Input("edit_submit", "", "submit");
		$form .= $input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input();
		$form .= "</form></div>";
		return $form;
	}
	
	function form_duplicate() {
		$form = "<div class=\"duplicate\"><form method=\"post\" name=\"duplicate_writing\" id=\"duplicate_writing\" action=\"\" enctype=\"multipart/form-data\">";
		$input_hidden_id = new Html_Input("id", $this->id);
		$input_hidden_action = new Html_Input("action", "duplicate");
		$submit = new Html_Input("duplicate_submit", "", "submit");
		$input_hidden_value = new Html_Input("duplicate_amount", "");
		$form .= $input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input().$input_hidden_value->input_hidden();
		$form .= "</form></div>";
		return $form;
	}
	
	function form_delete() {
		$form = "<div class=\"delete\"><form method=\"post\" name=\"delete_writing\" id=\"delete_writing\" action=\"\" enctype=\"multipart/form-data\">";
		$input_hidden_id = new Html_Input("id", $this->id);
		$input_hidden_action = new Html_Input("action", "delete");
		$submit = new Html_Input("delete_submit", "", "submit");
		$submit->properties = array(
			'onclick' => "javascript:return confirm('".utf8_ucfirst(__('are you sure?'))."')"
		);
		$form .= $input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input();
		$form .= "</form></div>";
		return $form;
	}
	
	function form_split() {
		$form = "<div class=\"split\"><form method=\"post\" name=\"split_writing\" id=\"split_writing\" action=\"\" enctype=\"multipart/form-data\">";
		$input_hidden_id = new Html_Input("id", $this->id);
		$input_hidden_action = new Html_Input("action", "split");
		$submit = new Html_Input("split_submit", "", "submit");
		$input_hidden_value = new Html_Input("split_amount", "");
		$form .= $input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input().$input_hidden_value->input_hidden();
		$form .= "</form></div>";
		return $form;
	}
	
	function fill($hash) {
		$writing = parent::fill($hash);
		$delay = explode("-", $writing->delay);
		$writing->delay = mktime(0, 0, 0, $delay[1], $delay[2], $delay[0]);
		return $writing;
	}
	
	function duplicate($amount) {
		if ($amount > 0) {
			$new_writing->delay = $this->delay;
			for ( $i=1; $i<=$amount; $i++) {
				$new_writing = $this;
				$new_writing->id = 0;
				$new_writing->delay = strtotime('+1 months', $new_writing->delay);
				$new_writing->save();
			}
		}
	}
	
	function get_form_new() {
		$form = "<div class=\"new\"><form method=\"post\" name=\"new_writing\" id=\"new_writing\" action=\"\" enctype=\"multipart/form-data\">";
		$input_hidden_action = new Html_Input("action", "getnew");
		$submit = new Html_Input("new_submit", __('add new line') ,"submit");
		$form .= $input_hidden_action->input_hidden().$submit->input();
		$form .= "</form></div>";
		return $form;
	}
}
