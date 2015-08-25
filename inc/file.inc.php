<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class File extends Record {
	public $id = 0;
	public $writings_id = 0;
	public $hash = "";
	public $value = "";
	
	
	function __construct($id = 0, db $db = null) {
		parent::__construct($db);
		$this->id = $id;
	}

	function db($db) {
		if ($db instanceof db) {
			$this->db = $db;
		}
	}
	
	function load(array $key = array(), $table = "files", $columns = null) {
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
			INSERT INTO ".$this->db->config['table_files']."
			SET writings_id = ".$this->writings_id.", ".
			"hash = ".$this->db->quote($this->hash).", ".
			"value = ".$this->db->quote($this->value)
		);
		$this->id = $result[2];
		$this->db->status($result[1], "i", __('file'));

		return $this->id;
	}
	
	function update() {
		$result = $this->db->query("UPDATE ".$this->db->config['table_files'].
			" SET writings_id = ".$this->writings_id.", ".
			"hash = ".$this->db->quote($this->hash).", ".
			"value = ".$this->db->quote($this->value)
		);
		$this->db->status($result[1], "u", __('file'));

		return $this->id;
	}

	function delete() {
		$result = $this->db->query("DELETE FROM ".$this->db->config['table_files'].
			" WHERE id = '".$this->id."'"
		);
		$this->db->status($result[1], "d", __('file'));

		return $this->id;
	}
	
	function save_attachment($raw_file) {
		$key = key($raw_file);
		$file = $raw_file[$key];
		$writing_id = substr($key, 6);
		$writing = new Writing();
		if ($writing->load(array('id' => (int)$writing_id)) and $file['error'] == 0) {
			$name_hashed = hash_hmac("sha256", time()."_".$file['name'], uniqid());
			if (move_uploaded_file($file['tmp_name'], dirname( __FILE__ )."/../var/upload/".$name_hashed)) {
				$this->writings_id = (int)$writing_id;
				$this->hash = $name_hashed;
				$this->value = $file['name'];
				if (!$writing->attachment) {
					$writing->attachment = 1;
					$writing->update();
				}
				$this->save();
			}
			else {
				return false;
			}
		} else {
			return false;
		}
		return true;
	}
	
	function open_attachment() {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$this->value);
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		ob_clean();
		flush();
		readfile(dirname( __FILE__ )."/../var/upload/".$this->hash);
	}
	
	function delete_attachment() {
		$writing = new Writing();
		$writing->load(array('id' => $this->writings_id));
		unlink(dirname( __FILE__ )."/../var/upload/".$this->hash);
		$this->delete();
		$files = new Files();
		$files->filter_with(array('writings_id' => $this->writings_id));
		$files->select();
		if (count($files) == 0) {
			$writing->attachment = 0;
			$writing->update();
		}
	}
}
