<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2015 */

class Bayesian_Element extends Record {
	public $id = 0;
	public $element = "";
	public $field = "";
	public $table_name = "";
	public $table_id = 0;
	public $occurrences = 0;
	
	function __construct($id = 0, db $db = null) {
		parent::__construct($db);
		$this->id = $id;
	}

	function db($db) {
		if ($db instanceof db) {
			$this->db = $db;
		}
	}
	
	function load(array $key = array(), $table = "bayesianelements", $columns = null) {
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
		$result = $this->db->id("
			INSERT INTO ".$this->db->config['table_bayesianelements']." 
			SET element = ".$this->db->quote((string)$this->element).",
			field = ".$this->db->quote($this->field).",
			table_name = ".$this->db->quote($this->table_name).",
			table_id = ".(int)$this->table_id.",
			occurrences = ".(int)$this->occurrences
		);
		$this->id = $result[2];
		//$this->db->status($result[1], "u", __('bayesian dictionary'));
		return $this->id;
	}
	
	function update() {
		$result = $this->db->query("
			UPDATE ".$this->db->config['table_bayesianelements']."
			SET element = ".$this->db->quote((string)$this->element).", 
			field = ".$this->db->quote($this->field).",
			table_name = ".$this->db->quote($this->table_name).",
			table_id = ".(int)$this->table_id.",
			occurrences = ".(int)$this->occurrences." 
			WHERE id = ".(int)$this->id
		);
		//$this->db->status($result[1], "u", __('bayesian dictionary'));
		return $this->id;
	}

	function delete() {
		$result = $this->db->query("
			DELETE FROM ".$this->db->config['table_bayesianelements']."
			WHERE id = ".(int)$this->id
		);
		//$this->db->status($result[1], "u", __('bayesian dictionary'));
		return $this->id;
	}
	
	function increment() {
		$result = $this->db->query("
			UPDATE ".$this->db->config['table_bayesianelements']."
			SET occurrences = occurrences + 1  
			WHERE element = ".$this->db->quote((string)$this->element)." AND 
			field = ".$this->db->quote($this->field)." AND
			table_name = ".$this->db->quote($this->table_name)." AND
			table_id = ".(int)$this->table_id
		);
		if ($result[1] == 0) {
			$result = $this->db->query("
				INSERT INTO ".$this->db->config['table_bayesianelements']."
				SET element = ".$this->db->quote($this->element).",
				field = ".$this->db->quote($this->field).",
				table_name = ".$this->db->quote($this->table_name).",
				table_id = ".(int)$this->table_id.",
				occurrences = 1
			");
		}
		//$this->db->status($result[1], "u", __('bayesian dictionary'));
	}
	
	function decrement() {
		$result = $this->db->query("
			UPDATE ".$this->db->config['table_bayesianelements']."
			SET occurrences = occurrences - 1  
			WHERE element = ".$this->db->quote((string)$this->element)." AND 
			field = ".$this->db->quote($this->field)." AND
			table_name = ".$this->db->quote($this->table_name)." AND
			table_id = ".(int)$this->table_id." AND
			occurrences > 0
		");
		//$this->db->status($result[1], "u", __('bayesian dictionary'));
	}
	
	function truncateTable() {
		$result = $this->db->query("
			TRUNCATE TABLE ".$this->db->config['table_bayesianelements']
		);
		//$this->db->status($result[1], "u", __('bayesian dictionary'));
	}
}
