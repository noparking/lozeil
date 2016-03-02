<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Accounting_Codes_Affectation extends Collector  {
	public $filters = null;

	function __construct($class = null, $table = null, $db = null) {
		if ($class === null) {
			$class = "Accounting_Code_Affectation";
		}
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_accountingcodes_affectation'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}

	function accountingcode_ids() {
		$ids = array();
		foreach ($this as $affectation) {
			$ids[] = $affectation->accountingcodes_id;
		}

		return array_unique($ids);
	}

	function desaffect() {
		$this->select();
		foreach ($this as $affectation) {
			$affectation->desaffect();
		}
	}

	function delete() {
		$this->select();
		foreach ($this as $affectation) {
			$affectation->delete();
		}
	}

	function get_where() {
		$query_where = parent::get_where();
		
		if (isset($this->id) and !empty($this->id)) {
			if (!is_array($this->id)) {
				$this->id = array((int)$this->id);
			}
			$query_where[] = $this->db->config['table_accountingcodes'].".id IN ".array_2_list($this->id);
		}
		if (isset($this->fullname) and !empty($this->fullname)) {
			if(is_numeric($this->fullname)) {
				$query_where[] = "(".$this->db->config['table_accountingcodes'].".number LIKE ".$this->db->quote($this->fullname."%").")";
			} else {
				$query_where[] = "(".$this->db->config['table_accountingcodes'].".number LIKE ".$this->db->quote($this->fullname."%").
				" OR ".$this->db->config['table_accountingcodes'].".name LIKE ".$this->db->quote("%".$this->fullname."%").
				" OR SOUNDEX(".$this->db->config['table_accountingcodes'].".name) LIKE SOUNDEX(".$this->db->quote($this->fullname)."))";
			}
		}
		if (isset($this->filters['accountingcodes_id'])) {
			$query_where[] = $this->db->config['table_accountingcodes_affectation'].".accountingcodes_id = ".(int)$this->filters['accountingcodes_id'];

		}
		if (isset($this->filters['reportings_id'])) {
			$query_where[] = $this->db->config['table_accountingcodes_affectation'].".reportings_id = ".(int)$this->filters['reportings_id'];
		}

		return $query_where;
	}

	function filter_with() {
		$elements = func_get_args();
		foreach ($elements as $element) {
			foreach ($element as $key => $value) {
				$this->filters[$key] = $value;
			}
		}
	}

	function order_by_number($id) {
		$affectations = array();
		$result = $this->db->query("SELECT f.* FROM ".$this->db->config['table_accountingcodes_affectation']." as f
			LEFT JOIN ".$this->db->config['table_accountingcodes']." as a ON a.id = f.accountingcodes_id
			WHERE f.reportings_id = ".$id." 
			ORDER BY a.number"
		);

		while ($row = $this->db->fetch_array($result[0])) {
			$affectation = new Accounting_Code_Affectation();
			$affectation->fill($row);
			$affectations[] = $affectation;
		}

		return $affectations;
	}
}
