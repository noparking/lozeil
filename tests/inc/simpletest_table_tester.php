<?php
/*
	lozeil
	$Author: adrien $
	$URL: svn://svn.noparking.net/var/repos/opentime/tests/inc/simpletest_table_tester.php $
	$Revision: 5145 $

	Copyright (C) No Parking 2013 - 2013
*/

class RecordExistsExpectation extends SimpleExpectation {
	private $table = "";
	private $record = array();

	function __construct($table, array $record, $message = '%s') {
		$this->table = $table;
		$this->record = $record;
		parent::__construct($message);
	}

	function test($record_number) {
		return $record_number > 0;
	}

	function testMessage($record_number) {
		$expected = array();

		foreach ($this->record as $column => $value) {
				$expected[] = $column." = ". ($value === "" ? "''" : $value);
		}

		return "Record [". join(', ', $expected)."] not found in table [". $this->table."]";
	}
}

class RecordNotExistsExpectation extends SimpleExpectation {
	private $table = '';
	private $record = array();

	function __construct($table, array $record, $message = '%s') {
		$this->table = $table;
		$this->record = $record;
		parent::__construct($message);
	}

	function test($record_number) {
		return $record_number <= 0;
	}

	function testMessage($record_number) {
		$expected = array();

		foreach ($this->record as $column => $value) {
				$expected[] = $column." = ". ($value === "" ? "''" : $value);
		}

		return "Record [". join(", ", $expected)."] found in table [". $this->table."]";
	}
}

class CountRecordExpectation extends SimpleExpectation {
	private $table = '';
	private $record = array();
	private $count = 0;

	function __construct($table, array $record, $count = 0, $message = '%s') {
		$this->table = $table;
		$this->record = $record;
		$this->count = $count;
		parent::__construct($message);
	}

	function test($count) {
		return $this->count == $count;
	}

	function testMessage($count) {
		$expected = array();

		foreach ($this->record as $column => $value) {
				$expected[] = $column." = ". ($value == "" ? "''" : $value);
		}

		return $count."/". $this->count." record(s) [". join(", ", $expected)."] found in table [". $this->table."]";
	}
}

class RecordNotDuplicateExpectation extends SimpleExpectation {
	private $table = '';
	private $record = array();

	function __construct($table, array $record, $message = '%s') {
		$this->table = $table;
		$this->record = $record;
		parent::__construct($message);
	}

	function test($count) {
		return $count <= 1;
	}

	function testMessage($count) {
		$expected = array();

		foreach ($this->record as $column => $value) {
				$expected[] = $column." = ".($value == "" ? "''" : $value);
		}

		return "Record [". join(", ", $expected)."] is duplicate ". $count." time(s) in table [". $this->table."]";
	}
}

class TableHasSizeExpectation extends SimpleExpectation {
	private $table = '';
	private $size = array();


	function __construct($table, $size, $message = '%s') {
		$this->table = $table;
		$this->size = $size;
		parent::__construct($message);
	}

	function test($size) {
		return $size == $this->size;
	}

	function testMessage($size) {
		return "Table [". $this->table."] has not size [". $this->size."], real size is [". $size."]";
	}
}

abstract class TableTestCase extends UnitTestCase {
	protected $db = null;
	protected $backups = array();

	function __construct($label = false, db $db = null) {
		parent::__construct($label);
		if ($db === null) {
			$db = new db();
		}
		$this->db = $db;
	}

	function initializeTables() {
		$tables = func_get_args();

		foreach ($tables as $table) {
			$this->initializeTable($table);
		}
	}
	
	function initializeTable($table) {
		$this->db->query("DROP TABLE IF EXISTS ".$table);
		
		$directory = dirname(__FILE__)."/../../";
		
                if (is_dir($directory)) {
                        foreach (new directoryIterator($directory) as $node) {
                                if (substr($node->getFilename(), 0, 1) != "." and $node->isDir()) {
                                        $path = $node->getPathname()."/content.sql.php";
                                        if (file_exists($path)) {
                                                require $path;
                                        }
                                }
                        }
                }
		
		$this->db->initialize($queries[$table]);
	}

	function assertTableHasSize($table, $size) {
		$this->assert(new TableHasSizeExpectation($table, $size), $this->db->getValue("SELECT COUNT(*) FROM ". $table));
	}

	function assertRecordsExists($table, array $records) {
		$query = "SELECT COUNT(*) FROM ". $table." WHERE ";

		foreach ($records as $record) {
			$this->assert(new RecordExistsExpectation($table, $record), $this->db->getValue($query.join(" AND ", $this->getWhere($record))));
		}
	}

	function assertRecordsNotExists($table, array $records) {
		$query = "SELECT COUNT(*) FROM ". $table." WHERE ";

		foreach ($records as $record) {
			$this->assert(new RecordNotExistsExpectation($table, $record), $this->db->getValue($query.join(" AND ", $this->getWhere($record))));
		}
	}

	function assertRecordExists($table, array $record) {
		$query = "SELECT COUNT(*) FROM ". $table." WHERE ";

		$this->assert(new RecordExistsExpectation($table, $record), $this->db->getValue($query.join(" AND ", $this->getWhere($record))));
	}

	function assertRecordNotExists($table, array $record) {
		$query = "SELECT COUNT(*) FROM ". $table." WHERE ";

		$this->assert(new RecordNotExistsExpectation($table, $record), $this->db->getValue($query.join(" AND ", $this->getWhere($record))));
	}

	function assertRecordsNotDuplicate($table, array $records) {
		$query = "SELECT COUNT(*) FROM ". $table." WHERE ";

		foreach ($records as $record) {
			$this->assert(new RecordNotDuplicateExpectation($table, $record), $this->db->getValue($query.join(" AND ", $this->getWhere($record))));
		}
	}

	function assertCountRecords($table, array $records, $count) {
		$query = "SELECT COUNT(*) FROM ". $table." WHERE ";

		foreach ($records as $record) {
			$this->assert(new CountRecordExpectation($table, $record, $count), $this->db->getValue($query.join(" AND ", $this->getWhere($record))));
		}
	}

	function assertSameTable($table, $records) {
		$recordSize = sizeof($records);
		$tableSize = $this->db->getValue("SELECT COUNT(*) FROM ". $table);

		if ($recordSize != $tableSize) {
			$this->assert(new TableHasSizeExpectation($table, $recordSize), $tableSize);
		} else {
			$this->assertRecordsExists($table, $records);
		}
	}

	function getRecords($table, array $columns = array()) {
		$records = array();

		list($recordSet) = $this->db->query("SELECT ".(sizeof($columns) == 0 ? '*' : join(', ', $columns))." FROM ".$table);

		while ($record = $this->db->fetchArray($recordSet)) {
			$records[] = $record;
		}

		return $records;
	}

	function truncateTable($table) {
		$this->db->query("TRUNCATE ". $table);
	}

	function truncateTables() {
		$tables = func_get_args();

		foreach ($tables as $table) {
			$this->truncateTable($table);
		}
	}

	function insertIntoTable($table, array $records) {
		foreach ($records as $record) {
			$columns = array();
			$values = array();

			foreach ($record as $column => $value) {
				$columns[] = $column;
				$values[] = $this->db->quote($value);
			}

			$this->db->query("INSERT INTO ".$table." (".join(",", $columns).") VALUES (".join(", ", $values).")");
		}
	}

	function getLastInsertId() {
		return $this->db->insertID();
	}

	function insertIntoTables() {
		$tableRecords = func_get_args();

		foreach ($tableRecords as $table => $records) {
			$this->insertIntoTables($table, $records);
		}
	}

	function backupTables() {
		$tables = func_get_args();

		foreach ($tables as $table) {
			$this->backups[$table] = $this->getRecords($table);
		}
	}

	function restoreTables() {
		foreach ($this->backups as $table => $records) {
			$this->truncateTable($table);
			$this->insertIntoTable($table, $records);
		}

		$this->backups = array();
	}

	private function getWhere($record) {
		$where = array();

		foreach ($record as $column => $value) {
			switch (true) {
				case is_string($value):
					$where[] = $column." = ".$this->db->quote($value);
					break;
				case $value === null:
					$where[] = $column." IS NULL";
					break;
				default:
					$where[] = $column." = ".$value;
			}
		}

		return $where;
	}
}
