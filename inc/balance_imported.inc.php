<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

class Balance_Imported extends Record {
	public $id = 0;
	public $hash = "";
	public $balance_id = 0;
	
	function __construct($id = 0, db $db = null) {
		parent::__construct($db);
		$this->id = $id;
	}

	function db($db) {
		if ($db instanceof db) {
			$this->db = $db;
		}
	}
	
	function load(array $key = array(), $table = "balancesimported", $columns = null) {
		return parent::load($key, $table, $columns);
	}
	
	function save() {
		if (is_numeric($this->id) and $this->id > 0) {
			$this->id = $this->update();

		} else {
			$this->id = $this->insert();
		}

		return $this->id;
	}
	
	function insert() {
		$result = $this->db->query_with_id("
			INSERT INTO `".$this->db->config['table_balancesimported']."`
			SET hash = ".$this->db->quote($this->hash).",
			balance_id  = ".(int)$this->balance_id
		);
		$this->id = $result[2];
		$this->db->status($result[1], "i", __('writings imported'));
		return $this->id;
	}
	
	function update() {
		$result = $this->db->query("
			UPDATE ".$this->db->config['table_balancesimported']."
			SET hash = ".$this->db->quote($this->hash).",
			balance_id = ".(int)$this->balance_id."
			WHERE id = ".(int)$this->id
		);
		$this->db->status($result[1], "u", __('writings imported'));
		return $this->id;
	}

	function delete() {
		if ($this->id > 0) {
			$result = $this->db->query("DELETE FROM ".$this->db->config['table_balancesimported']." WHERE id = '".$this->id."'");
			$this->db->status($result[1], "d", __('writings imported'));
		}

		return $this->id;
	}
}
