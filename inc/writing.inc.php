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
		$where = $table."_id";
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
}
