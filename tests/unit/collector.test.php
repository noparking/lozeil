<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class Collector_Class extends Collector {
	function __construct() {
		parent::__construct(__CLASS__, "writings");
	}

	public function test_get_query($columns = array(), array $where = array(), $limit = "", array $join = array()) {
		$this->columns = $columns;
		$this->where = $where;
		$this->limit = $limit;
		$this->join = $join;

		return $this->get_query();
	}

	protected function get_where() {
		return $this->where;
	}

	protected function get_limit() {
		return $this->limit;
	}

	protected function get_join() {
		return $this->join;
	}
}

class tests_Collector extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"writings"
		);
	}
	
	function test_get_query__with_join() {
		$object = new Collector_Class();
		
		$join = array(
			"INNER JOIN table_request ON table_quote.request_id = table_request.id",
			"INNER JOIN table_project ON table_request.project_id = table_project.id",
		);

		$this->assertNoPattern("/INNER JOIN/", $object->test_get_query());

		$this->assertPattern("/INNER JOIN.*INNER JOIN/", $object->test_get_query(array(), array(), "", $join));
	}
	
	function test_get_query__avec_calc_found_rows() {
		$object = new Collector_Class();

		$object->calc_found_rows(true);
		$this->assertPattern("/SQL_CALC_FOUND_ROWS/", $object->get_query());

		$object->calc_found_rows(false);
		$this->assertNoPattern("/SQL_CALC_FOUND_ROWS/", $object->get_query());

		$this->backupTables("writings");

		$writing = new Writing();
		$writing->save();
		$writing = new Writing();
		$writing->save();

		$writings = new Writings();
		$writings->calc_found_rows(true);
		$writings->select();

		$this->assertEqual(count($writings), 2);
		$this->assertEqual($writings->found_rows, 2);
		
		$writings = new Writings();
		$writings->calc_found_rows(true);
		$writings->set_limit(1);
		$writings->select();

		$this->assertEqual(count($writings), 1);
		$this->assertEqual($writings->found_rows, 2);

		$this->restoreTables();
	}
}
