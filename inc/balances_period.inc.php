<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

class Balances_Period extends Collector {
	public $filters = null;
	
	function __construct($class = null, $table = null, $db = null) {
		$class = "Balance_Period";
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_balancesperiod'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}
	
	function get_where() {
		$query_where = parent::get_where();
		
		if (isset($this->filters['id']) and !empty($this->filters['id'])) {
			if (!is_array($this->filters['id'])) {
				$this->filters['id'] = array((int)$this->filters['id']);
			}
			$query_where[] = $this->db->config['table_balancesperiod'].".id IN ".array_2_list($this->filters['id']);
		}
		if (isset($this->filters['start']) and !empty($this->filters['start'])) {
			$query_where[] = $this->db->config['table_balancesperiod'].".start => ".$this->filters['start'];
		}
		if (isset($this->filters['stop']) and !empty($this->filters['stop'])) {
			$query_where[] = $this->db->config['table_balancesperiod'].".stop <= ".$this->filters['stop'];
		}

		return $query_where;
	}
	
	function filter_with() {
		$elements = func_get_args();
		foreach ($elements as  $element) {
			foreach ($element as $key => $value) {
				$this->filters[$key] = $value;
			}
		}
	}

	function delete() {
		$this->select();
		foreach ($this as $balance_period) {
			$balance_period->delete();
		}
	}
}