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
		display = ".(int)$this->display."
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
		display = ".(int)$this->display
		);
		$this->id = $result[2];
		$this->db->status($result[1], "u", __('writings simulations'));

		return $this->id;
	}
}