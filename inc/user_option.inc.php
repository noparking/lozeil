<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class User_Option extends Record {
	public $id = 0;
	public $user_id = 0;
	public $name = "";
	public $value = "";

	function __construct($id = 0, db $db = null) {
		parent::__construct($db);
		$this->id = (int)$id;
	}

	function insert() {
		if ($this->id > 0) {
			$this->id = 0;
		}

		$query = "INSERT INTO ".$this->db->config['table_useroptions'].
			" SET user_id = ".(int)$this->user_id.", ".
			" name = ".$this->db->quote($this->name).", ".
			" value = ".$this->db->quote($this->value);

		list($bool, , $this->id) = $this->db->id($query);

		return $bool;
	}

	function update() {
		if ($this->id <= 0) {
			return false;
		} else {
			$query = "UPDATE ".$this->db->config['table_useroptions'].
				" SET user_id = ".(int)$this->user_id.", ".
				" name = ".$this->db->quote($this->name).", ".
				" value = ".$this->db->quote($this->value).
				" WHERE id = ".$this->id;

			list(, $affected_rows) = $this->db->query($query);

			return $affected_rows == 1;
		}
	}

	function replace() {
		if ($this->id <= 0) {
			return false;
		} else {
			list(, $affected_rows) = $this->db->query("
				REPLACE INTO ".$this->db->config['table_useroptions']."
				SET id = ".(int)$this->id.",
				user_id = ".(int)$this->user_id.",
				name = ".$this->db->quote($this->name).",
				value = ".$this->db->quote($this->value)
			);

			return $affected_rows == 1;
		}
	}

	function delete() {
		if ($this->id <= 0) {
			return false;
		} else {
			list(, $affected_rows) = $this->db->query("DELETE FROM ".$this->db->config['table_useroptions']." WHERE id = ".$this->id);

			if ($affected_rows <= 0) {
				return false;
			} else {
				$this->id = 0;
				return true;
			}
		}
	}

	function save() {
		return (is_numeric($this->id) and $this->id > 0) ? $this->update() : $this->insert();
	}

	function load(array $key = array(), $table = "useroptions", $columns = null) {
		return parent::load($key, $table, $columns);
	}
}
