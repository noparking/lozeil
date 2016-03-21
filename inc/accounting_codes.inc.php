<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

class Accounting_Codes extends Collector  {
	public $filters = null;
	public $fullname = "";

	function __construct($class = null, $table = null, $db = null) {
		if ($class === null) {
			$class = substr(__CLASS__, 0, -1);
		}
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_accountingcodes'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}

	function delete() {
		foreach ($this as $code) {
			$code->delete();
		}
	}

	function getInstances() {
		return $this->instances;
	}

	function get_where() {
		$where = parent::get_where();

		if (isset($this->id) and !empty($this->id)) {
			if (!is_array($this->id)) {
				$this->id = array((int)$this->id);
			}
			$where[] = $this->db->config['table_accountingcodes'].".id IN ".array_2_list($this->id);
		}
		if (isset($this->fullname) and !empty($this->fullname)) {
			if(is_numeric($this->fullname)) {
				$where[] = "(".$this->db->config['table_accountingcodes'].".number LIKE ".$this->db->quote($this->fullname."%").")";
			} else {
				$where[] = "(".$this->db->config['table_accountingcodes'].".number LIKE ".$this->db->quote($this->fullname."%").
				" OR ".$this->db->config['table_accountingcodes'].".name LIKE ".$this->db->quote("%".$this->fullname."%").
				" OR SOUNDEX(".$this->db->config['table_accountingcodes'].".name) LIKE SOUNDEX(".$this->db->quote($this->fullname)."))";
			}
		}
		if (isset($this->filters['>id'])) {
			$where[] = $this->db->config['table_accountingcodes'].".id > ".$this->filters['>id'];
		}

		return $where;
	}

	function filter_with() {
		$elements = func_get_args();
		foreach ($elements as  $element) {
			foreach ($element as $key => $value) {
				$this->filters[$key] = $value;
			}
		}
	}
	
	function names() {
		$names = array();
		foreach ($this as $code) {
			$names[$code->id] = $code->name;
		}
		return $names;
	}

	function fullnames() {
		$numbers = array();
		foreach ($this as $code) {
			$numbers[$code->id] = $code->number." - ".$code->name;
		}
		return $numbers;
	}

	function numbers() {
		$numbers = array();
		foreach ($this as $code) {
			$numbers[$code->id] = $code->number;
		}
		return $numbers;
	}

	function import_default() {
		$accountingcodes = array();
		require dirname(__FILE__)."/../lang/".$GLOBALS['param']['locale_lang'].".accountingcodes.php";

		foreach ($accountingcodes as $number => $name) {
			$code = new Accounting_Code();
			$code->load(array('number' => $number));
			$code->number = $number;
			$code->name = $name;
			$code->save();
		}
	}

	function grid_body() {
		$numbers = $this->numbers();
		$grid = array();
		foreach ($this as $accountingcode) {
			$number = adapt_number($accountingcode->number);
			if (preg_match("/^[0-9]*$/", $number)) {
				$matches = preg_grep('/^'.$number.'/', $numbers);
				if (count($matches) > 1) {
					$class = substr($number, 0, -1)." accounting_codes_shift_".(strlen($number) - 1)." accounting_codes_parent";
					$parent = true;
				} else {
					$class = substr($number, 0, -1)." accounting_codes_shift_".(strlen($number) - 1);
					$parent = false;
				}
			} else {
				$class = substr($number, 0, 3)." accounting_codes_shift_3";
				$parent = false;
			}
				
			$grid[$number] =  array(
				'class' => $class,
				'id' => $number,
				'cells' => array(
					array(
						'type' => "td",
					),
					array(
						'type' => "td",
						'value' => $number
					),
					array(
						'type' => "td",
						'class' => "name",
						'style' => ($parent == true) ? "text-decoaffectationn: underline" : "",
						'value' => $accountingcode->name
					),
					array(
						'type' => "td",
						'value' => $this->show_opeaffectationns()
					),

				)
			);
		}
		uksort($grid, function ($a, $b) { return strcmp($a, $b); });
		return $grid;
	}

	function show_opeaffectationns() {
		$modify = "<input class=\"modif modify_accountingcode\"  type=\"button\" />";
		$add = "<input class=\"add add_accountingcode\"  type=\"button\" />";
		$delete = "<input class=\"del delete_accountingplan\"  type=\"button\" />";

		return $modify.$add.$delete;
	}

	function show() {
		$html_table = new Html_table(array('lines' => $this->grid_body()));
		return $html_table->show();
	}

	function display() {
		return "<div id=\"accounting_codes\">".
				$this->show()."</div>";
	}
}
