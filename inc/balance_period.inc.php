<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

class Balance_Period extends Record {
	public $id = 0;
	public $start = 0;
	public $stop = 0;

	function __construct($id = 0, db $db = null) {
		parent::__construct($db);
		$this->id = $id;
	}

	function db($db) {
		if ($db instanceof db) {
			$this->db = $db;
		}
	}
	
	function load(array $key = array(), $table = "balancesperiod", $columns = null) {
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

	function update() {
		$result = $this->db->query("UPDATE `".$this->db->config['table_balancesperiod']."`
			SET start = ".(int)$this->start.",
			stop = ".(int)$this->stop."
			WHERE id = ".(int)$this->id
		);		
		$this->db->status($result[1], "u", __('line'));

		return $this->id;
	}
	
	function insert() {
		$result = $this->db->id("
			INSERT INTO `".$this->db->config['table_balancesperiod']."`
			SET start = ".(int)$this->start.",
			stop = ".(int)$this->stop
		);
		$this->id = $result[2];
		$this->db->status($result[1], "i", __('line'));

		return $this->id;
	}

	function delete() {
		if ($this->id > 0) {
			$result = $this->db->query("DELETE FROM ".$this->db->config['table_balancesperiod']." WHERE id = '".$this->id."'");
			$this->db->status($result[1], "d", __('line'));
		}

		return $this->id;
	}
}