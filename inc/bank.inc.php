<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

class Bank extends Record {
	public $id = 0;
	public $name = "";
	public $selected = 0;
	
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
			return parent::load($this->db->config['table_banks'], array('id' => (int)$id));
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
	
	function insert() {
		$result = $this->db->id("
			INSERT INTO ".$this->db->config['table_banks']."
			SET name = ".$this->db->quote($this->name).", ".
			"selected = ".$this->selected
		);
		$this->id = $result[2];
		$this->db->status($result[1], "u", __('bank'));

		return $this->id;
	}
	
	function update() {
		$result = $this->db->query("UPDATE ".$this->db->config['table_banks'].
			" SET name = ".$this->db->quote($this->name).", ".
			"selected = ".$this->selected."
			WHERE id = ".(int)$this->id
		);
		$this->db->status($result[1], "u", __('bank'));

		return $this->id;
	}


	function delete() {
		$result = $this->db->query("DELETE FROM ".$this->db->config['table_banks'].
			" WHERE id = '".$this->id."'"
		);
		$this->db->status($result[1], "u", __('bank'));

		return $this->id;
	}
	
	function is_deletable() {
		$result = $this->db->value_exists("SELECT count(1) FROM ".$this->db->config['table_writings'].
			" WHERE banks_id = '".$this->id."'"
		);
		return !$result;
	}
	
	function name() {
		return 	$this->name;
	}
}
