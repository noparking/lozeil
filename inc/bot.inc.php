<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

class Bot {
	public $directory_cfg = "";
	
	function __construct(db $db = null) {
		if ($db === null) {
			$db = new db();
		}
		$this->db = $db;
		$this->directory_cfg = dirname(__FILE__)."/../cfg";
	}
	
	
	function reinstall_database() {
		$this->uninstall_database();
		return $this->install_database();
	}

	function install_database() {
		$queries = array();
		$db = new db();
		require dirname(__FILE__)."/../sql/content.sql.php";
		$this->db->initialize($queries);
		return true;
	}
	
	function uninstall_database() {
		$db = new db();
			$tables = array();
			foreach ($GLOBALS['dbconfig'] as $parameter => $table) {
				if (substr($parameter, 0, 6) == 'table_') {
					$tables[] = $table;
				}
			}
		$db->query("DROP TABLE IF EXISTS ".join(", ", $tables));
		return true;
	}
	
	function help() {
		$help = "Methods available within Lozeil:"."\n";
		$ReflectionClass = new ReflectionClass('Bot');
		foreach ($ReflectionClass->getMethods() as $method) {
			if (!in_array($method->getName(), array("help", "__construct"))) {
				$help .= "--".$method->getName()."\n";
			}
		}
		return $help;
	}
	
	function update() {
		$this->log(__("Start updating Lozeil"));
		$this->update_svn();
		$this->update_lozeil();
		$this->log(__("Finish updating Lozeil"));
		return true;
	}
	
	function update_svn() {
		$this->log(__("Start updating SVN"));
		$result = exec("svn up ".realpath(dirname(__FILE__)."/../../../"));
		$this->log(__("Finish updating SVN"));
		return $result;
	}
	
	function update_lozeil() {
		$this->log(__("Start updating application"));
		$update = new Update();
		$current = $update->current() + 1;
		$last = $update->last();

		for ($i = $current; $i <= $last; $i++) {
			if (method_exists($update, "to_".$i)) {
				$update->{"to_".$i}();
				$update->config("version", $i);
			}
		}
		$this->log(__("Finish updating application"));
	}

	function update_plugin() {
		$this->log(__("Start updating plugin"));
		$update = new Accounts_Update();
		$current = $update->current() + 1;
		$last = $update->last();

		for ($i = $current; $i <= $last; $i++) {
			if (method_exists($update, "to_".$i)) {
				$update->{"to_".$i}();
				$update->config("accounts_version", $i);
			}
		}
		$this->log(__("Finish updating application"));
	}
	
	function log($message) {
		Message::log($message);
		return $this;
	}
}
