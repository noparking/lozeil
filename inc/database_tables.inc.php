<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Database_Tables {
	public $hl;
	public $elements = array();

	protected $db;

	function __construct($hl = null, $db = null) {
		$this->hl = $hl;

		if ($db == null) {
			$db = new db();
		}
		$this->db = $db;
	}

	function sources() {
		$sources = array(
			new Database_Tables_Source($this->db)
		);

		switch ($this->hl) {
			case "en_EN":
				$sources[] = new Database_Tables_en_EN($this->db);
				break;

			case "en_BE":
				$sources[] = new Database_Tables_en_BE($this->db);
				break;

			case "fr_BE":
				$sources[] = new Database_Tables_fr_BE($this->db);
				break;

			case "nl_BE":
				$sources[] = new Database_Tables_nl_BE($this->db);
				break;

			case "fr_FR":
			default:
				$sources[] = new Database_Tables_fr_FR($this->db);
				break;
		}

		return $sources;
	}

	function prepare() {
		foreach ($this->sources() as $source) {
			$this->elements = array_merge_recursive($this->elements, $source->enumerate());
		}
	}

	function install($table = null) {
		$queries = null;
		if ($table == null) {
			$queries = $this->elements;
		} else if (isset($this->elements[$table])) {
			$queries = $this->elements[$table];
		}

		if ($queries != null) {
			$this->db->initialize($queries);
		}
	}
	
	function uninstall($table = null) {
		$queries = null;
		if ($table == null) {
			$queries = array();
			foreach ($this->elements as $table => $element) {
				$queries[] = "DROP TABLE ".$table;
			}
		} elseif (isset($this->elements[$table])) {
			$queries = "DROP TABLE ".$table;
		}
		
		if ($queries != null) {
			$this->db->initialize($queries);
		}
	}
}
