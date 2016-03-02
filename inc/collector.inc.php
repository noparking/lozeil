<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

class Collector implements iterator, countable, arrayAccess {
	
	protected $class = "";
	protected $table = "";
	protected $db = null;
	protected $calc_found_rows = false;
	protected $limit_offset = null;
	protected $limit_row_count = null;
	protected $order = array ();
	protected $order_extra = null;
	protected $columns = array ();
	protected $restrictions = array ();
	protected $instances = array ();
	
	function __construct($class, $table, db $db = null) {
		$this->class = $class;
		$this->table = $table;
		
		if ($db === null) {
			$db = new db ();
		}
		$this->db = $db;
	}
	
	function __set($restriction, $value) {
		$this->restrictions [$restriction] = $value;
	}
	
	function __get($restriction) {
		return isset ( $this->restrictions [$restriction] ) ? $this->restrictions [$restriction] : null;
	}
	
	function __isset($restriction) {
		return isset ( $this->restrictions [$restriction] );
	}
	
	function __unset($restriction) {
		if (isset ( $this->{$restriction} )) {
			unset ( $this->restrictions [$restriction] );
		}
	}
	
	function reset() {
		$this->instances = array ();
		$this->found_rows = 0;
		return $this;
	}
	
	function current() {
		return current ( $this->instances );
	}
	
	function rewind() {
		reset ( $this->instances );
		return $this;
	}
	
	function next() {
		next ( $this->instances );
		return $this;
	}
	
	function key() {
		return key ( $this->instances );
	}
	
	function valid() {
		return (key ( $this->instances ) !== null) ? true : false;
	}
	
	function end() {
		return end ( $this->instances );
	}
	
	function select_columns() {
		$this->columns = func_get_args ();
		return $this;
	}
	
	function set_group_by($group_by) {
		$this->group_by = $group_by;
		
		return $this;
	}
	
	function set_limit($row_count, $offset = null) {
		$this->limit_row_count = $row_count < 0 ? 0 : ( int ) $row_count;
		
		if ($offset !== null) {
			$this->limit_offset = $offset < 0 ? 0 : ( int ) $offset;
		}
		
		return $this;
	}
	
	function set_order($col_name, $direction = null) {
		$this->order_col_name = $col_name;
		
		if ($direction !== null) {
			$this->order_direction = $direction;
		}
		
		return $this;
	}
	
	function add_order($clause) {
		$this->order [] = $clause;
		return $this;
	}
	
	function select($raw = false) {
		$this->reset ();
		list ( $records ) = $this->db->query ( $this->get_query () );
		if ($this->limit_row_count or $this->calc_found_rows) {
			$this->found_rows = ! $this->calc_found_rows ? count ( $this ) : $this->db->value ( "SELECT FOUND_ROWS()" );
		}
		
		while ($record = $this->db->fetch_array($records)) {
			if ($raw) {
				$this->instances [] = $record;
			} else {
				$instance = $this->get_instance($record);
				if ($instance !== null) {
					foreach ($record as $column => $value) {
						if (isset($instance->{$column})) {
							$instance->{$column} = $value;
						}
					}
					$this->instances [] = $instance;
				}
			}
		}
		return $this;
	}
	
	function fill(array $array) {
		$this->instances = array ();
		$this->found_rows = 0;
		
		foreach ( $array as $values ) {
			$instance = new $this->class ();
			
			foreach ( $values as $column => $value ) {
				$instance->{$column} = $value;
			}
			
			$this->instances [] = $instance;
		}
		
		return $this;
	}
	
	function found_rows() {
		return $this->found_rows;
	}
	
	function getIterator() {
		return new arrayIterator ( $this->instances );
	}
	
	function count() {
		return sizeof ( $this->instances );
	}
	
	function offsetGet($offset) {
		return ! isset ( $this [$offset] ) ? null : $this->instances [$offset];
	}
	
	function offsetSet($offset, $value) {
		if ($offset === null) {
			$offset = count ( $this );
		}
		$this->instances [$offset] = $value;
	}
	
	function offsetExists($offset) {
		return isset ( $this->instances [$offset] );
	}
	
	function offsetUnset($offset) {
		if (isset ( $this->instances [$offset] )) {
			unset ( $this->instances [$offset] );
		}
	}
	
	function calc_found_rows($bool = true) {
		$this->calc_found_rows = ($bool == true);
		return $this;
	}
	
	function get_query() {
		$calc_found_rows = ($this->calc_found_rows) ? "SQL_CALC_FOUND_ROWS " : "";
		$columns = $this->get_columns ();
		$from = $this->get_from ();
		$where = $this->get_where ();
		$limit = $this->get_limit ();
		$join = $this->get_join ();
		$group_by = $this->get_group_by ();
		$order = $this->get_order ();
		
		$query = "SELECT " . $calc_found_rows . join ( ', ', $columns ) . " FROM " . join ( ', ', $from );
		
		if (sizeof ( $join ) > 0) {
			$query .= " " . join ( " ", $join );
		}
		
		if (sizeof ( $where ) > 0) {
			$query .= " WHERE " . join ( " AND ", $where );
		}
		
		$query .= $group_by;
		
		if (sizeof ( $order ) > 0) {
			$query .= " ORDER BY " . join ( ", ", $order );
		}
		
		$query .= $limit;
		
		return $query;
	}
	
	protected function get_where() {
		return array ();
	}
	
	protected function get_join() {
		return array ();
	}
	
	protected function get_columns() {
		$columns = array ();
		
		if (sizeof ( $this->columns ) <= 0) {
			$columns [] = "`" . $this->table . "`.*";
		} else {
			foreach ( $this->columns as $column ) {
				$columns [] = $column;
			}
		}
		return $columns;
	}
	
	protected function get_from() {
		return array (
				$this->table 
		);
	}
	
	protected function get_instance(array $record) {
		return new $this->class ( 0, $this->db );
	}
	
	protected static function get_db() {
		trigger_error ( "Method 'Collector::get_db' is deprecated since 24/05/2010. Please use the variable 'Collector::db'.", E_USER_WARNING );
	}
	
	protected function get_limit() {
		$limit = "";
		
		if ($this->limit_offset !== null or $this->limit_row_count !== null) {
			$limit = " LIMIT ";
			
			if ($this->limit_offset !== null) {
				$limit .= $this->limit_offset . ',';
			}
			
			if ($this->limit_row_count !== null) {
				$limit .= $this->limit_row_count;
			}
		}
		
		return $limit;
	}
	
	protected function get_group_by() {
		$group_by = "";
		if ($this->group_by !== null) {
			$group_by = " GROUP BY " . $this->group_by;
		}
		return $group_by;
	}
	
	protected function get_order() {
		return $this->order;
	}
}
