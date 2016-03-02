<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Collector_Timed extends Collector {
	function select($raw = false) {
		$this->reset();

		$query = $this->get_query();

		$this->set_up_timed();
		list($records) = $this->db->query($query);
		if ($this->limit_row_count) {
			$this->found_rows = !$this->calc_found_rows ? count($this) : $this->db->value("SELECT FOUND_ROWS()");
		}
		$this->tear_down_timed();
		
		while ($record = $this->db->fetch_array($records)) {
			if ($raw) {
				$this->instances[] = $record;
			} else {
				$instance = $this->get_instance($record);

				if ($instance !== null) {
					foreach ($record as $column => $value) {
						if (isset($instance->{$column})) {
							$instance->{$column} = $value;
						}
					}

					$this->instances[] = $instance;
				}
			}
		}

		return $this;
	}
	
	function set_up_timed() {
		$this->db->query($this->get_create_temporary_table());
		$this->db->query($this->get_lock());
		$this->db->query($this->get_insert_temporary_table());		
	}
	
	function tear_down_timed() {
		$this->db->query($this->get_unlock());
		$this->db->query($this->get_drop_temporary_table());
	}

	function get_query() {
		$calc_found_rows = ($this->calc_found_rows) ? "SQL_CALC_FOUND_ROWS " : "";
		$columns = $this->get_columns();
		$from = $this->get_from();
		$where = $this->get_where();
		$limit = $this->get_limit();
		$join = $this->get_join();
		$temporay_join = $this->get_temporary_join();
		$group_by = $this->get_group_by();
		$order = $this->get_order();

		$query = "SELECT ".$calc_found_rows.join(', ', $columns)." FROM ".join(', ', $from);
		
		if ($temporay_join) {
			$query .= " ".$temporay_join;
		}

		if (sizeof($join) > 0) {
			$query .= " ".join(" ", $join);
		}

		if (sizeof($where) > 0) {
			$query .= " WHERE ".join(" AND ", $where);
		}

		$query .= $group_by;
		$query .= $order;
		$query .= $limit;

		return $query;
	}
	
	function get_create_temporary_table() {
		return "CREATE TEMPORARY TABLE tmp (
			id BIGINT(21) DEFAULT '0' NOT NULL,
			".$this->table."_id BIGINT(21) DEFAULT '0' NOT NULL
		)";
	}
	
	function get_lock() {
		$tables = array_merge(array($this->table), array_keys($this->get_join()));
		return "LOCK TABLES ".join(" read, ", array_unique($tables))." read";
	}
	
	function get_insert_temporary_table() {
		return "INSERT INTO tmp
			SELECT MAX(id), ".$this->table."_id
			FROM ".$this->table."
			GROUP BY ".$this->table."_id
		";
	}
	
	function get_temporary_join() {
		return "INNER JOIN tmp ON ".$this->table.".id = tmp.id";
	}
	
	function get_unlock() {
		return "UNLOCK TABLES;";
	}
	
	function get_drop_temporary_table() {
		return "DROP TABLE tmp;";
	}
	
	function get_order() {
		$order = "";

		if ($this->order_col_name !== null) {
			$order = " ORDER BY ".$this->order_col_name;

			if ($this->order_direction !== null) {
				$order .= " ".$this->order_direction;
			}
		}

		return $order;
	}
}
