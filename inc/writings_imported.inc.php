<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Writings_Imported extends Collector  {
	public $filters = null;
	
	function __construct($class = null, $table = null, $db = null) {
		$class = "Writing_Imported";
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_writingsimported'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}
	
	function get_where() {
		$query_where = parent::get_where();
		
		if (isset($this->filters['banks_id'])) {
			$query_where[] = $this->db->config['table_writingsimported'].".banks_id = ".(int)$this->filters['banks_id'];
		}
		if (isset($this->filters['sources_id'])) {
			$query_where[] = $this->db->config['table_writingsimported'].".sources_id = ".(int)$this->filters['sources_id'];
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
		foreach ($this as $writing_imported) {
			$writing_imported->delete();
		}
	}
}
