<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

class Balances_Imported extends Collector  {
	public $filters = null;
	
	function __construct($class = null, $table = null, $db = null) {
		$class = "Balance_Imported";
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_balancesimported'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}
	
	function get_where() {
		$query_where = parent::get_where();
		
		if (isset($this->id) and !empty($this->id)) {
			if (!is_array($this->id)) {
				$this->id = array((int)$this->id);
			}
			$query_where[] = $this->db->config['table_balancesperiod'].".id IN ".array_2_list($this->id);
		}
		if (isset($this->filters['balance_id'])) {
			$query_where[] = $this->db->config['table_balancesimported'].".balance_id = ".(int)$this->filters['balance_id'];
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
		foreach ($this as $balance_imported) {
			$balance_imported->delete();
		}
	}
}
