<?php
/*
	lozeil
	$Author: perrick $
	$URL:  $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

class db {
	public $link = null;
	public $config = array();

	public static $log = null;

	function __construct($config = false) {
		if (!$config) {
			$config = $GLOBALS['dbconfig'];
		}
		$this->config($config);
	}

	function config(array $config) {
		$this->config = $config;

		$link = mysql_connect($this->config['host'], $this->config['user'], $this->config['pass'], isset($this->config['new']) ? (bool)$this->config['new'] : false);

		if (!$link) {
			trigger_error("Unable to connect to database server", E_USER_ERROR);
		} else {
			$this->link = $link;
			mysql_select_db($this->config['name'], $this->link);
			$this->query("SET NAMES 'utf8'");
		}

		return $this;
	}
	
	function initialize($queries) {
		if (is_array($queries)) {
			foreach ($queries as $query) {
				$this->initialize($query);
			}
		} else {
			$this->input($queries);
		}
	}
	
	function close() {
		if ($this->link) {
			return mysql_close($this->link);
		} else {
			trigger_error(mysql_error(), E_USER_ERROR);
		}
	}

	function input($query) {
		$result = $this->query($query);
		return array_shift($result);
	}

	function query($query) {
		if (!$this->link) {
			trigger_error(mysql_error(), E_USER_ERROR);
		} else {
			self::log($query);

			$result = mysql_query($query, $this->link);

			if ($result === false) {
				$this->query_error($query);
			} else {
				return array($result, (is_resource($result) ? mysql_num_rows($result) : mysql_affected_rows($this->link)));
			}
		}
	}

	function num_rows($query) {
		$result = $this->query($query);

		return $result[1];
	}

	function value($query) {
		$result  = $this->query($query);
		$element = $this->fetchArray($result[0]);
		return (is_array($element) ? current($element) : null);
	}

	function value_exists($query) {
		$result = $this->value($query);
		if ($result > 0) {
			return true;
		} else {
			return false;
		}
	}

	function table_exists($table) {
		$query = "SELECT COUNT(*) FROM ".$table;
		return $this->value_exists($query);
	}

	function database_exists($database) {
		$query = "SHOW DATABASES";
		$result = $this->query($query);
		while ($row = $this->fetchRow($result[0])) {
			if ($row[0] == $database) {
				return true;
			}
		}
		return false;
	}

	function id($query) {
		$result = $this->query($query);
		$result[] = $this->insert_id();
		return $result;
	}

	function insert_id() {
		return mysql_insert_id($this->link);
	}

	function fetch_row($result) {
		return mysql_fetch_row($result);
	}

	function fetch_array($result) {
		return mysql_fetch_array($result, MYSQL_ASSOC);
	}

	function query_error($query) {
		$backtraces = "";

		$level = 0;

		foreach (array_reverse(array_slice(debug_backtrace(), 1)) as $backtrace) {
			$backtraces .= '['.$level.'] File '.(isset($backtrace['file']) == false ? 'unknown' : $backtrace['file']).', line '.(isset($backtrace['line']) == false ? 'unknown' : $backtrace['line']);

			if (isset($backtrace['function']) == true) {
				$backtraces .= ', '.(isset($backtrace['class']) == false ? '' : $backtrace['class'] . '::').$backtrace['function'].'()';
			}

			$backtraces .= "\n";

			$level++;
		}

		trigger_error($backtraces."MySQL Error : ".mysql_error(). " -- with query : ".$query, E_USER_WARNING);
	}

	function status($result_id, $type, $record="") {
		if (!$record) {
			$record = __('record');
		}
		if ($type == "d") {
			if ($result_id == 1) {
				success_status($record." -> ".__('deletion OK'));
			} elseif ($result_id > 1) {
				success_status($record." -> ".__('deletions OK'));
			} elseif ($result_id == 0) {
				success_status($record." -> ".__('nothing to do'));
			} else {
				error_status($record." -> ".__('error while deleting'));
			}
		} elseif($type == "i") {
			if ($result_id == 1) {
				success_status($record." -> ".__('add OK'));
			} elseif ($result_id > 1) {
				success_status($record." -> ".__('adds OK'));
			} elseif ($result_id == 0) {
				success_status($record." -> ".__('nothing to do'));
			} elseif ($result_id == -1) {
				success_status($record." -> ".__('existing record'));
			} else {
				error_status($record." -> ".__('error while creating'));
			}
		} elseif($type == "u") {
			if ($result_id == 1) {
				success_status($record." -> ".__('update OK'));
			} elseif ($result_id > 1) {
				success_status($record." -> ".__('updates OK'));
			} elseif ($result_id == 0) {
				success_status($record." -> ".__('nothing to do'));
			} else {
				error_status($record." -> ".__('error while updating'));
			}
		} else {
			if ($result_id == 1) {
				success_status($record." -> ".__('seems OK'));
			} elseif ($result_id == 0) {
				success_status($record." -> ".__('nothing to do'));
			} else {
				error_status($record." -> ".__('unknown error'));
			}
		}

		return $this;
	}

	function quote($value) {
		$type = gettype($value);
		switch ($type) {
			case 'boolean':
				$value = (int)$value;
				break;
			case 'NULL':
				$value = 'NULL';
				break;
			case 'string':
				$value = "'".mysql_real_escape_string($value)."'";
				break;
		}
		return $value;
	}
	
	function fetchArray($result) {
		return mysql_fetch_array($result, MYSQL_ASSOC);
	}

	private function log($message) {
		static $number = 0;
		if (self::$log !== null) {
			error_log(($number === 0 ? '>>>>>> SESSION start <<<<<<'."\n" : "").date('d/m/y h:i:s')." [".++$number."] : ".$message."\n", 3, self::$log);
		}
	}

	function insertID() {
		return mysql_insert_id($this->link);
	}
	
	function fetchRow($result) {
		return mysql_fetch_row($result);
	}
}
