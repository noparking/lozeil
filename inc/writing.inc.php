<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

class Writing extends Record {
	public $account_id = 0;
	public $amount_excl_vat = 0;
	public $amount_inc_vat = 0;
	public $bank_id = 0;
	public $comment = "";
	public $delay = 0;
	public $id = 0;
	public $information = "";
	public $paid = 0;
	public $search_index = "";
	public $source_id = 0;
	public $type_id = 0;
	public $unique_key = "";
	public $vat = 0;
	
	function __construct($id = 0, db $db = null) {
		parent::__construct($db);
		$this->id = $id;
	}

	function db($db) {
		if ($db instanceof db) {
			$this->db = $db;
		}
	}
	
	function search_index() {
		$bank = new Bank();
		$bank->load($this->bank_id);
		$source = new Source();
		$source->load($this->source_id);
		$type = new Type();
		$type->load($this->type_id);
		$account = new Account();
		$account->load($this->account_id);
		return $this->vat." ".$this->amount_excl_vat." ".$this->amount_inc_vat." ".$bank->name." ".$this->comment." ".$this->information." ".$source->name." ".$account->name." ".$type->name;
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
		$query = "UPDATE ".$this->db->config['table_writings'].
		" SET account_id = ".(int)$this->account_id.",
		bank_id = ".(int)$this->bank_id.",
		source_id = ".(int)$this->source_id.",
		amount_inc_vat = ".$this->amount_inc_vat.",
		type_id  = ".(int)$this->type_id.",
		vat = ".$this->vat.",
		amount_excl_vat = ".$this->amount_excl_vat.",
		comment = ".$this->db->quote($this->comment).",
		information = ".$this->db->quote($this->information).",
		paid = ".(int)$this->paid.",
		delay = ".(int)$this->delay.",
		search_index = ".$this->db->quote($this->search_index())."
		WHERE id = ".(int)$this->id;
		
		$result = $this->db->query($query);
		$this->db->status($result[1], "u", __('writing'));

		return $this->id;
	}
	
	function insert() {
		$result = $this->db->id("
			INSERT INTO ".$this->db->config['table_writings']."
			SET account_id = ".(int)$this->account_id.",
			bank_id = ".(int)$this->bank_id.",
			source_id = ".(int)$this->source_id.",
			amount_inc_vat = ".$this->amount_inc_vat.",
			type_id  = ".(int)$this->type_id.",
			vat = ".$this->vat.",
			amount_excl_vat = ".$this->amount_excl_vat.",
			comment = ".$this->db->quote($this->comment).",
			information = ".$this->db->quote($this->information).",
			delay = ".(int)$this->delay.",
			search_index = ".$this->db->quote($this->search_index()).",
			unique_key = ".$this->db->quote($this->unique_key).",
			paid = ".(int)$this->paid
		);
		$this->id = $result[2];
		$this->db->status($result[1], "u", __('writing'));

		return $this->id;
	}
	
	function merge_from(Writing $to_merge) {
		$this->account_id = (isset($to_merge->account_id) and $to_merge->account_id > 0) ? (int)$to_merge->account_id : $this->account_id;
		$this->bank_id = (isset($to_merge->bank_id) and $to_merge->bank_id > 0) ? (int)$to_merge->bank_id : $this->bank_id;
		$this->source_id = (isset($to_merge->source_id) and $to_merge->source_id > 0) ? (int)$to_merge->source_id : $this->source_id;
		$this->amount_excl_vat = isset($to_merge->amount_excl_vat) ? $to_merge->amount_excl_vat : $this->amount_excl_vat;
		$this->amount_inc_vat = isset($to_merge->amount_inc_vat) ? $to_merge->amount_inc_vat : $this->amount_inc_vat;
		$this->comment = isset($to_merge->comment) ? $to_merge->comment : $this->comment;
		$this->delay = isset($to_merge->delay) ? $to_merge->delay : $this->delay;
		$this->information = isset($to_merge->information) ? $to_merge->information : $this->information;
		$this->vat = isset($to_merge->vat) ? $to_merge->vat : $this->vat;
		$this->type_id = (isset($to_merge->type_id) and $to_merge->type_id > 0) ? $to_merge->type_id : $this->type_id;
		$this->paid = isset($to_merge->paid) ? $to_merge->paid : $this->paid;
		$this->search_index = $this->search_index();
		$this->save();
		$to_merge->delete();
	}
	
	function split($amount = 0) {
		if (isset($this->amount_inc_vat)) {
			$this->amount_inc_vat = ($this->amount_inc_vat - $amount);
			$this->amount_excl_vat = round(($this->amount_inc_vat/(($this->vat/100) + 1)), 6);
			$this->search_index = $this->search_index();
			$this->save();
			$writing = new Writing();
			$writing->id = $this->id;
			$writing->load();
			$writing->id = 0;
			$writing->amount_inc_vat = $amount;
			$writing->amount_excl_vat = round($amount/(($this->vat/100) + 1), 6);
			$writing->search_index = $this->search_index();
			$writing->save();
		}
	}
	
	function form() {
		$form = "<div id=\"edit_writings\"><span id=\"edit_writings_show\">".utf8_ucfirst(__('show form'))."</span><span id=\"edit_writings_hide\">".utf8_ucfirst(__('hide form'))."</span>
			<span id=\"edit_writings_cancel\">".Html_Tag::a(link_content("content=lines.php&timestamp=".$_SESSION['timestamp']),utf8_ucfirst(__('cancel record')))."</span>";
		$form .= "<div class=\"edit_writings_form\">
			<form method=\"post\" name=\"edit_writings_form\" action=\"\" enctype=\"multipart/form-data\">";
		
		if ($this->id) {
			$input_hidden = new Html_Input("action", "edit");
			$input_hidden->id = $this->id;
		} else {
			$input_hidden = new Html_Input("action", "insert");
		}
		$form .= $input_hidden->input_hidden();
		
		$input_hidden_id = new Html_Input("id", $this->id);
		$form .= $input_hidden_id->input_hidden();
		
		if ($this->delay > 0) {
			$date = (int)$this->delay;
		} else {
			$date = (int)$_SESSION['timestamp'];
		}
		
		$accounts = new Accounts();
		$accounts->select();
		$accounts_name = $accounts->names();
		$types = new Types();
		$types->select();
		$types_name = $types->names();
		$banks = new Banks();
		$banks->select();
		$banks_name = $banks->names();
		$sources = new Sources();
		$sources->select();
		$sources_name = $sources->names();
		
		$datepicker = new Html_Input_Date("datepicker");
		$datepicker->value = $date;
		$account = new Html_Select("account_id", $accounts_name, $this->account_id);
		$source = new Html_Select("source_id", $sources_name, $this->source_id);
		$type = new Html_Select("type_id", $types_name, $this->type_id);
		$bank = new Html_Select("bank_id", $banks_name, $this->bank_id);
		$amount_excl_vat = new Html_Input("amount_excl_vat", $this->amount_excl_vat);
		$vat = new Html_Input("vat", $this->vat);
		$amount_inc_vat = new Html_Input("amount_inc_vat", $this->amount_inc_vat);
		$comment = new Html_Textarea("comment", $this->comment);
		$paid = new Html_Radio("paid", array(__("no"),__("yes")), $this->paid);
		$submit = new Html_Input("submit", "", "submit");
		$submit->value =__('save');
		
		$grid = array();
		$grid['class'] = "itemsform";
		$grid['leaves']['date']['value'] = $datepicker->item(__('delay'));
		$grid['leaves']['account']['value'] = $account->item(__('account'));
		$grid['leaves']['source']['value'] = $source->item(__('source'));
		$grid['leaves']['type']['value'] = $type->item(__('type'));
		$grid['leaves']['bank']['value'] = $bank->item(__('bank'));
		$grid['leaves']['amount_excl_vat']['value'] = $amount_excl_vat->item(__('amount excluding tax'));
		$grid['leaves']['vat']['value'] = $vat->item(__('VAT'));
		$grid['leaves']['amount_inc_vat']['value'] = $amount_inc_vat->item(__('amount including tax'));
		$grid['leaves']['comment']['value'] = $comment->item(__('comment'));
		$grid['leaves']['paid']['value'] = $paid->item(__('paid'));
		$grid['leaves']['submit']['value'] = $submit->item("");
		
		
		$list = new Html_List($grid);
		$form .= $list->show();
		
		$form .= "</form></div></div>";

		return $form;
	}
	
	function form_duplicate() {
		$form = "<div class=\"table_writings_duplicate\"><form method=\"post\" name=\"table_writings_duplicate\" action=\"\" enctype=\"multipart/form-data\">";
		$input_hidden_id = new Html_Input("table_writings_duplicate_id", $this->id);
		$input_hidden_action = new Html_Input("action", "duplicate");
		$submit = new Html_Input("table_writings_duplicate_submit", "", "submit");
		$input_hidden_value = new Html_Input("table_writings_duplicate_amount", "");
		$form .= $input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input().$input_hidden_value->input_hidden();
		$form .= "</form></div>";
		return $form;
	}
	
	function form_delete() {
		$form = "<div class=\"table_writings_delete\"><form method=\"post\" name=\"table_writings_delete\" action=\"\" enctype=\"multipart/form-data\">";
		$input_hidden_id = new Html_Input("table_writings_delete_id", $this->id);
		$input_hidden_action = new Html_Input("action", "delete");
		$submit = new Html_Input("table_writings_delete_submit", "", "submit");
		$submit->properties = array(
			'onclick' => "javascript:return confirm('".utf8_ucfirst(__('are you sure?'))."')"
		);
		$form .= $input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input();
		$form .= "</form></div>";
		return $form;
	}
	
	function form_split() {
		$form = "<div class=\"table_writings_split\"><form method=\"post\" name=\"table_writings_split\" action=\"\" enctype=\"multipart/form-data\">";
		$input_hidden_id = new Html_Input("table_writings_split_id", $this->id);
		$input_hidden_action = new Html_Input("action", "split");
		$submit = new Html_Input("table_writings_split_submit", "", "submit");
		$input_hidden_value = new Html_Input("table_writings_split_amount", "");
		$form .= $input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input().$input_hidden_value->input_hidden();
		$form .= "</form></div>";
		return $form;
	}
	
	function fill($hash) {
		$writing = parent::fill($hash);
		if (isset($hash['datepicker'])) {
			$writing->delay = mktime(0, 0, 0, $hash['datepicker']['m'], $hash['datepicker']['d'], $hash['datepicker']['Y']);
		}
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
	
	function show_further_information() {
		if (!empty($this->information)) {
			return "<div class=\"table_writings_comment_further_information\">".nl2br($this->information)."</div>";
		} else {
			return "";
		}
	}
}
