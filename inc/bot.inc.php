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
		$this->db = $db;
		$this->directory_cfg = dirname(__FILE__)."/../cfg";
		$this->dbconfig = new Config_File(dirname(__FILE__)."/../cfg/config.inc.php", "dbconfig");
	}
	
	function setup() {
		echo utf8_ucfirst(__('database configuration'))."\n";
		while(empty($dbname)) {
			 $dbname = $this->input(__('name'));
		};
		while(empty($dbuser)) {
			 $dbuser = $this->input(__('username'));
		};
		$dbpass = $this->input_hidden(__('password'));

		
		$config_file = new Config_File($this->directory_cfg."/config.inc.php");
		if (!$config_file->exists()) {
			$dist_config_file = new Config_File(dirname(__FILE__)."/../cfg/config.inc.php.dist");

			if (!$dist_config_file->exists()) {
				die("Configuration file '".$dist_config_file."' does not exist");
			} else {
				try {
					$config_file->copy($dist_config_file);
				} catch (exception $exception) {
					die($exception->getMessage());
				}
			}
		}
		
		$this->dbconfig->update(array(
			'dbconfig' => array(
				'dbconfig' => array(
					"name" => $dbname,
					"user" => $dbuser,
					"pass" => $dbpass
				)
			)
		));
		
		$param_file = new Param_File($this->directory_cfg."/param.inc.php");
		if (!$param_file->exists()) {
			$dist_param_file = new Param_File(dirname(__FILE__)."/../cfg/param.inc.php.dist");

			if (!$dist_param_file->exists()) {
				die("Parameters file '".$dist_param_file."' does not exist");
			} else {
				try {
					$param_file->copy($dist_param_file);
				} catch (exception $exception) {
					die($exception->getMessage());
				}
			}
		}
		
		$load_config = new Config_File(dirname(__FILE__)."/../cfg/config.inc.php", "dbconfig");
		$load_config->load_at_global_level();
		if ($this->db === null) {
			$this->db = new db();
		}
		$this->reinstall_database();
		
		echo utf8_ucfirst(__('create a default user'))."\n";
		while(empty($username)) {
			 $username = $this->input(__('username'));
		};
		$password = $this->input_hidden(__('password'));
		$this->db->query("INSERT INTO ".$GLOBALS['dbconfig']['table_users']." (id, username, password) VALUES (1, '".$username."', password('".$password."'));");
	}
	
	function reinstall_database() {
		$this->uninstall_database();
		return $this->install_database();
	}

	function install_database() {
		if ($this->db === null) {
			$this->db = new db();
		}
		$queries = array();
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
	
	function input($message) {
	  fwrite(STDOUT, "$message: ");
	  $input = trim(fgets(STDIN));
	  return $input;
	}
	
	function input_hidden($message) {
		fwrite(STDOUT, "$message: ");
		$oldStyle = shell_exec('stty -g');
		shell_exec('stty -echo');
		$password = rtrim(fgets(STDIN), "\n");
		echo "\n";
		shell_exec('stty ' . $oldStyle);
		return $password;
	}
}
