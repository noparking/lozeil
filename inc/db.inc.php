<?php
/*
	lozeil
	$Author: perrick $
	$URL:  $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

class db implements serializable {
	public $dbLink = null;
	public $config = array();

	public static $log = null;

	protected $reporter = null;

	function __construct($config = false, db_reporter $db_reporter = null) {
		if (!$config) {
			$config = $GLOBALS['dbconfig'];
		}

		$this->config($config);


		if ($db_reporter === null) {
			$db_reporter = new db_reporter();
		}

		$this->db_reporter($db_reporter);
	}

	function serialize() {
		return serialize(array('config' => $this->config, 'reporter' => $this->db_reporter));
	}

	function unserialize($string) {
		$array = unserialize($string);

		if ($array !== false && isset($array['config']) && is_array($array['config']) && isset($array['reporter']) && $array['reporter'] instanceof db_reporter) {
			$this->config($array['config'])->db_reporter($array['reporter']);
		}
	}

	function config(array $config) {
		$this->config = $config;

		$link = mysql_connect($this->config['host'], $this->config['user'], $this->config['pass'], isset($this->config['new']) ? (bool)$this->config['new'] : false);

		if (!$link) {
			trigger_error("Unable to connect to database server", E_USER_ERROR);
		} else {
			$this->dbLink = $link;
			mysql_select_db($this->config['name'], $this->dbLink);
			$this->query("SET NAMES 'utf8'");
		}

		return $this;
	}
	
	function db_reporter(db_reporter $db_reporter = null) {
		$this->db_reporter = $db_reporter;
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
		if ($this->dbLink) {
			return mysql_close($this->dbLink);
		} else {
			trigger_error(mysql_error(), E_USER_ERROR);
		}
	}

	function input($query) {
		$result = $this->query($query);
		return array_shift($result);
	}

	function query($query) {
		if (!$this->dbLink) {
			trigger_error(mysql_error(), E_USER_ERROR);
		} else {
			self::log($query);

			$result = mysql_query($query, $this->dbLink);

			if ($result === false) {
				$this->query_error($query);
			} else {
				return array($result, (is_resource($result) ? mysql_num_rows($result) : mysql_affected_rows($this->dbLink)));
			}
		}
	}

	function num_rows($query) {
		$result = $this->query($query);

		return $result[1];
	}

	function getValue($query) {
		$result  = $this->query($query);
		$element = $this->fetchArray($result[0]);
		return (is_array($element) ? current($element) : null);
	}

	function getVerif($query) {
		$result = $this->getValue($query);
		if ($result > 0) {
			return true;
		} else {
			return false;
		}
	}

	function getVerifTable($table) {
		$query = "SELECT COUNT(*) FROM ".$table;
		return $this->getVerif($query);
	}

	function getVerifDatabase($database) {
		$query = "SHOW DATABASES";
		$result = $this->query($query);
		while ($row = $this->fetchRow($result[0])) {
			if ($row[0] == $database) {
				return true;
			}
		}
		return false;
	}

	function getID($query) {
		$result = $this->query($query);
		$result[] = $this->insertID();
		return $result;
	}

	function insertID() {
		return mysql_insert_id($this->dbLink);
	}

	function fetchRow($result) {
		return mysql_fetch_row($result);
	}

	function fetchArray($result) {
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

	function replace($string) {
		$patterns = array("\\\\");
		$replace  = array("\\");
		return eregi_replace($patterns, $replace, $string);
	}

	function construct_sql($table, $field, $value, $value_mixed="", $order="", $useraccess="", $userid="") {
		$where = "";
		if (!$value) {
			$value = "id";
		}
		if ($value_mixed) {
			$value_mixed = ", ".$value_mixed;
		}
		if (!$order) {
			$order = $field;
		}
		if ($table == $this->config['table_user']) {
			$field = $this->config['table_user'].".".$field;
			$value = $this->config['table_user'].".".$value;
			if (!$order) {
				$order = $field;
			} else {
				$order = $this->config['table_user'].".".$order;
			}
			$table .= ", ".$this->config['table_managementuser'];
			$where = " WHERE ".$this->config['table_managementuser'].".access != 'no' AND ".$this->config['table_managementuser'].".user_id = ".$this->config['table_user'].".id";
			if ($userid) {
				if (!preg_match("/aa/", $useraccess)) {
					if (preg_match("/a/", $useraccess)) {
						$where .= " AND (".$this->config['table_user'].".id = '".$userid."' OR ".$this->config['table_managementuser'].".responsible_id LIKE '%:\"".$userid."\"%')";
					} else {
						$where .= " AND ".$this->config['table_user'].".id = '".$userid."'";
					}
				}
			}
		}
		$sql = "SELECT ".$value.$value_mixed.", ".$field." FROM ".$table.$where." ORDER BY ".$order;
		return $sql;
	}

	function construct_where($table, $value, $userid="", $useraccess="", $project_validation="", $today="") {
		$customer_IN = "";
		$where = "WHERE customerstatus_id < '".$GLOBALS['param']['level_projectstatus']."'";
		if (!preg_match("/aa/i", $useraccess)) {
			if (preg_match("/a/i", $useraccess)) {
				$query = "SELECT DISTINCT id FROM ".$this->config['table_customer']." WHERE ".$this->config['table_customer'].".user_id LIKE '%:\"".$userid."\"%'";
				$result = $this->query($query);
				while ($row = $this->fetchArray($result[0])) {
					$customer_IN .= $row['id'].", ";
				}
			}
			$query = "SELECT DISTINCT customer_id FROM ".$this->config['table_project']." WHERE ".$this->config['table_project'].".user_id LIKE '%:\"".$userid."\"%'";
			$result = $this->query($query);
			while ($row = $this->fetchArray($result[0])) {
				$customer_IN .= $row['customer_id'].", ";
			}
			if (isset($customer_IN) and $customer_IN) {
				$customer_IN = "(".ereg_replace(", $", "", $customer_IN).")";
			} else {
				$customer_IN = "(0)";
			}

			if ($customer_IN) {
				$where .= " AND $value IN ".$customer_IN;
			}
		}
		return $where;
	}

	function arraybox($table, $field, $value, $value_mixed="", $order="") {
		$arraybox = array();
		if (strpos($value_mixed, ",") !== false) {
			$arrayline[$field] = array("name" => $field."_new", "value" => "");
			$value_array = list_2_array($value_mixed);
			foreach($value_array as $value_item) {
				$arrayline[$value_item] = array("name" => $value_item."_new", "value" => "");
			}
			$arraybox[] = $arrayline;
		} elseif ($value_mixed) {
			$arraybox[] = array($field => array("name" => $field."_new", "value" => ""), $value_mixed => array("name" => $value_mixed."_new", "value" => ""));
		} else {
			$arraybox[] = array($field => array("name" => $field."_new", "value" => ""));
		}
		$sql = $this->construct_sql($table, $field, $value, $value_mixed, $order);
		$result = $this->query($sql);
		while ($row = $this->fetchArray($result[0])) {
			if (strpos($value_mixed, ",") !== false) {
				$arrayline[$field] = array("name" => $field."_".$row[$value]."_".utf8_urlencode($row[$field]), "value" => $row[$field]);
				$value_array = list_2_array($value_mixed);
				foreach($value_array as $value_item) {
					$arrayline[$value_item] = array("name" => $value_item."_new", "value" => "");
					$arrayline[$value_item] = array("name" => $value_item."_".$row[$value]."_".utf8_urlencode($row[$value_item]), "value" => $row[$value_item]);
				}
				$arraybox[] = $arrayline;
			} elseif ($value_mixed) {
				$arraybox[] = array($field => array("name" => $field."_".$row[$value]."_".utf8_urlencode($row[$field]), "value" => $row[$field]), $value_mixed => array("name" => $value_mixed."_".$row[$value]."_".utf8_urlencode($row[$value_mixed]), "value" => $row[$value_mixed]));
			} else {
				$arraybox[] = array($field => array("name" => $field."_".$row[$value]."_".utf8_urlencode($row[$field]), "value" => $row[$field]));
			}
		}

		return $arraybox;
	}

	function getTreeForMultipleSelect($table, $field, $value, $select_name, $selected="", $order="", $table_1, $field_1, $value_1, $parent_1="", $link_1, $select_name_1, $selected_1="", $order_1="", $child_1="", $userid="", $useraccess="", $project_validation="", $today="", $tip="1") {
		$project_IN = "";
		if (!$value) {$value = "id";}
		if (!$select_name) {$select_name = $field;}
		if (!$order) {$order = $field;}
		if ($table == $this->config['table_customer']) {
			$where = $this->construct_where($table, $value, $userid, $useraccess, $project_validation, $today);
		}
		$result = $this->query("SELECT $value, $field FROM $table $where ORDER BY $order");

		$customer_list = array();
		$row_size = $result[1];
		$customer_list_array = array();
		$tree["0"]['value'] = "--";
		$tree["0"]['children']["0"]['value'] = "--";
		$tree["0"]['children']["0"]['children']["0"]['value'] = "--";
		while ($row = $this->fetchArray($result[0])) {
			$tree[$row[$value]]['value'] = $row[$field];
			$tree[$row[$value]]['children'][0]['value'] = "--";
		}
		
		if (!isset($tree[$selected])) {
			$customer = new Customer();
			$customer->load($selected);
			$tree[$customer->id]['value'] = $customer->name;
			$tree[$customer->id]['children'][0]['value'] = "--";
			$tree[$customer->id]['selected'] = "selected";
		}

		if (count($tree) == 2) {
			$keys = array_keys($tree);
			$key = array_pop($keys);
			$tree[$key]['selected'] = "selected";
		}
		
		if (!$value_1) {$value_1 = "id";}
		if (!$select_name_1) {$select_name_1 = $field_1;}
		if (!$order_1) {$order_1 = $field_1;}
		$where_1 = "WHERE (projectstatus_id < '".$GLOBALS['param']['level_projectstatus']."'";
		if ($table_1 == $this->config['table_project']) {
			if (is_int($child_1)) {
				$where_1 .= " AND ".$parent_1." = '0'";
				if ($child_1 > 0) {
					$where_1 .= " AND ".$value_1." != '".$child_1."'";
				}
			}
			if (!stristr($useraccess, "aa")) {
				$parent = array();
				if (stristr($useraccess, "a")) {
					$query = "SELECT ".$this->config['table_project'].".id, ".$this->config['table_project'].".parent" .
							" FROM ".$this->config['table_project'].", ".$this->config['table_customer'].
							" WHERE ".$this->config['table_project'].".customer_id = ".$this->config['table_customer'].".id" .
							" AND ".$this->config['table_customer'].".user_id LIKE '%:\"".$userid."\"%'";
					$result = $this->query($query);
					while ($row = $this->fetchArray($result[0])) {
						$project_IN .= $row['id'].", ";
						if (!in_array($row['parent'], $parent) and $row['parent']) {
								$parent[] = $row['parent'];
								$project_IN .= $row['parent'].", ";
						}
					}
				}
				$query = "SELECT id, parent FROM ".$this->config['table_project'].
						" WHERE ".$this->config['table_project'].".user_id LIKE '%:\"".$userid."\"%'";
				$result = $this->query($query);
				while ($row = $this->fetchArray($result[0])) {
					$project_IN .= $row['id'].", ";
					if (!in_array($row['parent'], $parent)) {
						if ($row['parent'] != 0) {
							$parent[] = $row['parent'];
							$project_IN .= $row['parent'].", ";
						}
					}
				}
				if (isset($project_IN) and $project_IN) {
					$project_IN = "(".substr($project_IN, 0, -2).")";
				} else {
					$project_IN = "(0)";
				}
				if ($project_IN) {
					$where_1 .= " AND ".$value_1." IN ".$project_IN;
				}
			}
			if ($project_validation) {
				$where_1 .= " AND validation < ".$today;
			}
		}
		if (strstr($where_1, "WHERE (")) {
			$where_1 .= ")";
		}
		if ($selected_1 > 0 and !strstr($useraccess, "aa")) {
			$where_1 .= (strstr($where_1, "WHERE (")) ? " OR " : " WHERE ";
			$where_1 .= $value_1." = ".$selected_1;
		}
		$query_1 = "SELECT ".$value_1.", ".$parent_1.", ".$field_1.", ".$link_1." FROM ".$table_1." ".$where_1." ORDER BY ".$order_1;
		$result_1 = $this->query($query_1);

		while ($row_1 = $this->fetchArray($result_1[0])) {
			if (isset($row_1[$parent_1]) and $row_1[$parent_1] > 0) {
				$tree[$row_1[$link_1]]['children'][$row_1[$parent_1]]['children']["0"]['value'] = "--";
				$tree[$row_1[$link_1]]['children'][$row_1[$parent_1]]['children'][$row_1[$value_1]]['value'] = $row_1[$field_1];
				if ($row_1[$value_1] == $selected_1) {
					unset($tree[$row_1[$link_1]]['selected']);
					if (!isset($tree[$row_1[$link_1]]['children'][$row_1[$parent_1]]['value'])) {
						$parent = new Project($row_1[$parent_1]);
						$tree[$row_1[$link_1]]['children'][$row_1[$parent_1]]['value'] = $parent->name();
					}
					$tree[$row_1[$link_1]]['children'][$row_1[$parent_1]]['children'][$row_1[$value_1]]['selected'] = "selected";
				}

			} else {
				$tree[$row_1[$link_1]]['children'][$row_1[$value_1]]['value'] = $row_1[$field_1];
				if ($row_1[$value_1] == $selected_1) {
					unset($tree[$row_1[$link_1]]['selected']);
					$tree[$row_1[$link_1]]['children'][$row_1[$value_1]]['selected'] = "selected";
				}
			}
		}

		return $tree;
	}

	function status($result_id, $type, $record="") {
		if ($this->db_reporter !== null)
		{
			if (!$record) {
				$record = $GLOBALS['txt_record'];
			}
			if ($type == "d") {
				if ($result_id == 1) {
					$this->db_reporter->report($record." -> ".$GLOBALS['status_del']);
				} elseif ($result_id > 1) {
					$this->db_reporter->report($record." -> ".$GLOBALS['status_del_multi']);
				} elseif ($result_id == 0) {
					$this->db_reporter->report($record." -> ".$GLOBALS['status_nothing']);
				} else {
					error_status($record." -> ".$GLOBALS['status_del_err']);
				}
			} elseif($type == "i") {
				if ($result_id == 1) {
					$this->db_reporter->report($record." -> ".$GLOBALS['status_add_ok']);
				} elseif ($result_id > 1) {
					$this->db_reporter->report($record." -> ".$GLOBALS['status_add_multi']);
				} elseif ($result_id == 0) {
					$this->db_reporter->report($record." -> ".$GLOBALS['status_nothing']);
				} elseif ($result_id == -1) {
					$this->db_reporter->report($record." -> ".$GLOBALS['status_exist']);
				} else {
					error_status($record." -> ".$GLOBALS['status_add_err']);
				}
			} elseif($type == "u") {
				if ($result_id == 1) {
					$this->db_reporter->report($record." -> ".$GLOBALS['status_upd']);
				} elseif ($result_id > 1) {
					$this->db_reporter->report($record." -> ".$GLOBALS['status_upd_multi']);
				} elseif ($result_id == 0) {
					$this->db_reporter->report($record." -> ".$GLOBALS['status_nothing']);
				} else {
					error_status($record." -> ".$GLOBALS['status_upd_err']);
				}
			} else {
				if ($result_id == 1) {
					$this->db_reporter->report($record." -> ".$GLOBALS['status_seemsOK']);
				} elseif ($result_id == 0) {
					$this->db_reporter->report($record." -> ".$GLOBALS['status_nothing']);
				} else {
					error_status($record." -> ".$GLOBALS['status_unk_err']);
				}
			}
		}

		return $this;
	}

    function quote($value) {
        $type = gettype($value);
        switch ($type) {
            case 'boolean':
                $value = (int) $value;
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

    private function log($message) {
    	static $number = 0;
    	if (self::$log !== null) {
			error_log(($number === 0 ? '>>>>>> SESSION start <<<<<<'."\n" : "").date('d/m/y h:i:s')." [".++$number."] : ".$message."\n", 3, self::$log);
    	}
    }
}
