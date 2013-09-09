<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

class Writing extends Record {
	public $categories_id = 0;
	public $amount_excl_vat = 0;
	public $amount_inc_vat = 0;
	public $banks_id = 0;
	public $comment = "";
	public $day = 0;
	public $id = 0;
	public $information = "";
	public $paid = 0;
	public $search_index = "";
	public $sources_id = 0;
	public $number = "";
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
		$bank->load($this->banks_id);
		$source = new Source();
		$source->load($this->sources_id);
		$category = new Category();
		$category->load($this->categories_id);
		return date("d/m/Y",$this->day)." ".$this->vat." ".$this->amount_excl_vat." ".$this->amount_inc_vat." ".$bank->name." ".$this->comment." ".$this->information." ".$source->name." ".$category->name;
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
		" SET categories_id = ".(int)$this->categories_id.",
		banks_id = ".(int)$this->banks_id.",
		sources_id = ".(int)$this->sources_id.",
		amount_inc_vat = ".$this->amount_inc_vat.",
		number  = ".(int)$this->number.",
		vat = ".$this->vat.",
		amount_excl_vat = ".$this->amount_excl_vat.",
		comment = ".$this->db->quote($this->comment).",
		information = ".$this->db->quote($this->information).",
		paid = ".(int)$this->paid.",
		day = ".(int)$this->day.",
		unique_key = ".$this->db->quote($this->unique_key).",	
		search_index = ".$this->db->quote($this->search_index())."
		WHERE id = ".(int)$this->id;
		
		$result = $this->db->query($query);
		$this->db->status($result[1], "u", __('writing'));

		return $this->id;
	}
	
	function insert() {
		$result = $this->db->id("
			INSERT INTO ".$this->db->config['table_writings']."
			SET categories_id = ".(int)$this->categories_id.",
			banks_id = ".(int)$this->banks_id.",
			sources_id = ".(int)$this->sources_id.",
			amount_inc_vat = ".$this->amount_inc_vat.",
			number  = ".(int)$this->number.",
			vat = ".$this->vat.",
			amount_excl_vat = ".$this->amount_excl_vat.",
			comment = ".$this->db->quote($this->comment).",
			information = ".$this->db->quote($this->information).",
			day = ".(int)$this->day.",
			search_index = ".$this->db->quote($this->search_index()).",
			unique_key = ".$this->db->quote($this->unique_key).",
			paid = ".(int)$this->paid
		);
		$this->id = $result[2];
		$this->db->status($result[1], "u", __('writing'));

		return $this->id;
	}
	
	function merge_from(Writing $to_merge) {
		if ($this->banks_id == 0 or $to_merge->banks_id == 0) {
			if ($this->banks_id != 0) {
				$this->categories_id = $this->categories_id > 0 ? (int)$this->categories_id : $to_merge->categories_id;
				$this->banks_id = $this->banks_id > 0 ? (int)$this->banks_id : $to_merge->banks_id;
				$this->sources_id = $this->sources_id > 0 ? (int)$this->sources_id : $to_merge->sources_id;
				$this->comment = !empty($this->comment) ? $this->comment : $to_merge->comment;
				$this->information = !empty($this->information) ? $this->information : $to_merge->information;
				$this->number = !empty($this->number) ? $this->number : $to_merge->number;
				$this->search_index = $this->search_index();
				$this->unique_key = "";
				$this->save();
				$to_merge->delete();
			} else {
				$this->categories_id = $to_merge->categories_id > 0 ? (int)$to_merge->categories_id : $this->categories_id;
				$this->banks_id = $to_merge->banks_id > 0 ? (int)$to_merge->banks_id : $this->banks_id;
				$this->sources_id = $to_merge->sources_id > 0 ? (int)$to_merge->sources_id : $this->sources_id;
				$this->amount_excl_vat =  $to_merge->amount_excl_vat;
				$this->amount_inc_vat = $to_merge->amount_inc_vat;
				$this->comment = !empty($to_merge->comment) ? $to_merge->comment : $this->comment;
				$this->day = $to_merge->day;
				$this->information = !empty($to_merge->information) ? $to_merge->information : $this->information;
				$this->vat = $to_merge->vat;
				$this->number = !empty($to_merge->number) ? $to_merge->number : $this->number;
				$this->paid = $to_merge->paid;
				$this->search_index = $this->search_index();
				$this->unique_key = "";
				$this->save();
				$to_merge->delete();
			}
		} else {
			return false;
		}
	}
	
	function split($amount = 0) {
		$this->amount_inc_vat = ($this->amount_inc_vat - $amount);
		$this->amount_excl_vat = round(($this->amount_inc_vat/(($this->vat/100) + 1)), 6);
		$this->search_index = $this->search_index();
		$this->save();
		$writing = new Writing();
		$writing->load($this->id);
		$writing->id = 0;
		$writing->amount_inc_vat = $amount;
		$writing->amount_excl_vat = round($amount/(($this->vat/100) + 1), 6);
		$writing->search_index = $this->search_index();
		$writing->save();
	}
	
	function form() {
		$form = "<div id=\"edit_writings\">
			<span id=\"edit_writings_show\">".utf8_ucfirst(__('show form'))."</span>
			<span id=\"edit_writings_hide\">".utf8_ucfirst(__('hide form'))."</span>
			<span id=\"edit_writings_cancel\">".Html_Tag::a(link_content("content=writings.php&timestamp=".$_SESSION['timestamp']),utf8_ucfirst(__('cancel record')))."</span>
			<div class=\"edit_writings_form\">
			<form method=\"post\" name=\"edit_writings_form\" action=\"\" enctype=\"multipart/form-data\">";
		
		if ($this->id) {
			$input_hidden = new Html_Input("action", "edit", "submit");
			$input_hidden->id = $this->id;
		} else {
			$input_hidden = new Html_Input("action", "insert");
		}
		$form .= $input_hidden->input_hidden();
		
		$input_hidden_id = new Html_Input("id", $this->id);
		$form .= $input_hidden_id->input_hidden();
		
		if ($this->day > 0) {
			$date = (int)$this->day;
		} else {
			$date = (int)$_SESSION['timestamp'];
		}
		
		$categories = new Categories();
		$categories->select();
		$categories_name = $categories->names();
		$sources = new Sources();
		$sources->select();
		$sources_name = $sources->names();
		
		$datepicker = new Html_Input_Date("datepicker");
		$datepicker->value = $date;
		$category = new Html_Select("categories_id", $categories_name, $this->categories_id);
		$source = new Html_Select("sources_id", $sources_name, $this->sources_id);
		$number = new Html_Input("number", $this->number);
		$amount_excl_vat = new Html_Input("amount_excl_vat", $this->amount_excl_vat);
		$vat = new Html_Input("vat", $this->vat);
		$amount_inc_vat = new Html_Input("amount_inc_vat", $this->amount_inc_vat);
		$comment = new Html_Textarea("comment", $this->comment);
		$paid = new Html_Radio("paid", array(__("no"),__("yes")), $this->paid);
		$submit = new Html_Input("submit", "", "submit");
		$submit->value =__('save');
		
		if($this->banks_id > 0) {
			$datepicker->properties['disabled'] = "disabled";
			$amount_excl_vat->properties['disabled'] = "disabled";
			$amount_inc_vat->properties['disabled'] = "disabled";
			$paid->properties['disabled'] = "disabled";
		}
		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'date' => array(
					'value' => $datepicker->item(__('date')),
				),
				'category' => array(
					'value' => $category->item(__('category')),
				),
				'source' => array(
					'value' => $source->item(__('source')),
				),
				'number' => array(
					'value' => $number->item(__('piece nb')),
				),
				'amount_excl_vat' => array(
					'value' => $amount_excl_vat->item(__('amount excluding vat')),
				),
				'vat' => array(
					'value' => $vat->item(__('VAT')),
				),
				'amount_inc_vat' => array(
					'value' => $amount_inc_vat->item(__('amount including vat')),
				),
				'comment' => array(
					'value' => $comment->item(__('comment')),
				),
				'paid' => array(
					'value' => $paid->item(__('paid')),
				),
				'submit' => array(
					'value' => $submit->item(""),
				),
				'category' => array(
					'value' => $category->item(__('category')),
				),
			)
		);				
		$list = new Html_List($grid);
		$form .= $list->show();
		
		$form .= "</form></div></div>";

		return $form;
	}
	
	function form_in_table() {
		$form = "<div id=\"table_edit_writings\">
			<span id=\"table_edit_writings_cancel\">".Html_Tag::a(link_content("content=writings.php&timestamp=".$_SESSION['timestamp']),utf8_ucfirst(__('cancel record')))."</span>
			<div class=\"table_edit_writings_form\">
			<form method=\"post\" name=\"table_edit_writings_form\" action=\"".link_content("content=writings.php")."\" enctype=\"multipart/form-data\">";
		
		if ($this->id) {
			$input_hidden = new Html_Input("action", "edit", "submit");
			$input_hidden->id = $this->id;
		} else {
			$input_hidden = new Html_Input("action", "insert");
		}
		$form .= $input_hidden->input_hidden();
		
		$input_hidden_id = new Html_Input("id", $this->id);
		$form .= $input_hidden_id->input_hidden();
		
		if ($this->day > 0) {
			$date = (int)$this->day;
		} else {
			$date = (int)$_SESSION['timestamp'];
		}
		
		$categories = new Categories();
		$categories->select();
		$categories_name = $categories->names();
		$sources = new Sources();
		$sources->select();
		$sources_name = $sources->names();
		
		$datepicker = new Html_Input_Date("datepicker");
		$datepicker->value = $date;
		$category = new Html_Select("categories_id", $categories_name, $this->categories_id);
		$source = new Html_Select("sources_id", $sources_name, $this->sources_id);
		$number = new Html_Input("number", $this->number);
		$amount_excl_vat = new Html_Input("amount_excl_vat", $this->amount_excl_vat);
		$vat = new Html_Input("vat", $this->vat);
		$amount_inc_vat = new Html_Input("amount_inc_vat", $this->amount_inc_vat);
		$comment = new Html_Textarea("comment", $this->comment);
		$paid = new Html_Radio("paid", array(__("no"),__("yes")), $this->paid);
		$submit = new Html_Input("submit", "", "submit");
		$submit->value =__('save');
		
		if($this->banks_id > 0) {
			$datepicker->properties['disabled'] = "disabled";
			$amount_excl_vat->properties['disabled'] = "disabled";
			$amount_inc_vat->properties['disabled'] = "disabled";
			$paid->properties['disabled'] = "disabled";
		}
		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'date' => array(
					'value' => $datepicker->item(__('date')),
				),
				'category' => array(
					'value' => $category->item(__('category')),
				),
				'source' => array(
					'value' => $source->item(__('source')),
				),
				'number' => array(
					'value' => $number->item(__('piece nb')),
				),
				'amount_excl_vat' => array(
					'value' => $amount_excl_vat->item(__('amount excluding vat')),
				),
				'vat' => array(
					'value' => $vat->item(__('VAT')),
				),
				'amount_inc_vat' => array(
					'value' => $amount_inc_vat->item(__('amount including vat')),
				),
				'comment' => array(
					'value' => $comment->item(__('comment')),
				),
				'paid' => array(
					'value' => $paid->item(__('paid')),
				),
				'submit' => array(
					'value' => $submit->item(""),
				),
				'category' => array(
					'value' => $category->item(__('category')),
				),
			)
		);				
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
		if ($this->banks_id > 0) {
			return "<div class=\"table_writings_delete disabled\"></div>";
		} else {
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
	
	function form_modify() {
		return "<div class=\"table_writings_modify\">".
			Html_Tag::a(link_content("content=writings.php&timestamp=".$_SESSION['timestamp']."&writings_id=".$this->id)," ").
			"</div>";
	}
	
	function fill($hash) {
		$writing = parent::fill($hash);
		if (isset($hash['datepicker'])) {
			$writing->day = mktime(0, 0, 0, $hash['datepicker']['m'], $hash['datepicker']['d'], $hash['datepicker']['Y']);
		}
		if($writing->banks_id > 0) {
			$writing->amount_excl_vat = round(($writing->amount_inc_vat/(($writing->vat/100) + 1)), 6);
		}
		return $writing;
	}
	
	function duplicate($amount) {
		if (is_numeric($amount) and $amount > 0) {
			for ($i=1; $i<=$amount; $i++) {
				$new_writing = $this;
				$new_writing->id = 0;
				$new_writing->day = strtotime('+1 months', $new_writing->day);
				$new_writing->banks_id = 0;
				$new_writing->save();
			}
		} else {
			$split = preg_split("/(q)|(y)|(a)|(t)/", $amount, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			if (count($split) == 2 and is_numeric($split[0])) {
				if($split[1] == 'q' or $split[1] == 't') {
					for ($i=1; $i<=$split[0]; $i++) {
						$new_writing = $this;
						$new_writing->id = 0;
						$new_writing->day = strtotime('+3 months', $new_writing->day);
						$new_writing->banks_id = 0;
						$new_writing->save();
					}
				} elseif($split[1] == 'a' or $split[1] == 'y') {
					for ($i=1; $i<=$split[0]; $i++) {
						$new_writing = $this;
						$new_writing->id = 0;
						$new_writing->day = strtotime('+1 year', $new_writing->day);
						$new_writing->banks_id = 0;
						$new_writing->save();
					}
				}
			}
		}
	}
	
	function show_further_information() {
		if (!empty($this->information)) {
			return "<div class=\"table_writings_comment_further_information\">".nl2br($this->information)."</div>";
		}
		return "";
	}
	
	function show_operations() {
		return $this->form_split().$this->form_modify().$this->form_duplicate().$this->form_delete();
	}
	
	function is_insertable() {
		$query = "SELECT count(1) FROM ".$this->db->config['table_writings'].
		" WHERE unique_key = '".$this->unique_key."'";
		$result = $this->db->value_exists($query);
		return !$result;
	}
}
