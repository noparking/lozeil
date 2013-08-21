<?php
/*
	lozeil
	$Author: perrick $
	$URL:  $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

class Html_table {
	public $data = array();
	
	function __construct($cells = array()) {
		$this->data = $this->normalize($cells);
	}
	
	function show() {
		$content = "";
		$table_attribute = "";
		$table_data = $this->data;
		
		if ($table_data) {
			if (isset($table_data['groups'])) {
				foreach ($table_data['groups'] as $group) {
					$tbody_attribute = "";
					foreach ($group as $attribute => $attribute_value) {
						if (is_string($attribute) and $attribute != "lines") {
							$tbody_attribute .= " ".$attribute."=\"".$attribute_value."\"";
						}
					}
					$content_tbody = show_lines($group['lines']);

					$content .= "<tbody".$tbody_attribute.">".$content_tbody."</tbody>";
				}
			} else {
				$content = $this->show_lines($table_data['lines']);
			}

			foreach ($table_data as $attribute => $attribute_value) {
				if (is_string($attribute) and !in_array($attribute, array("groups", "lines"))) {
					$table_attribute .= " ".$attribute."=\"".$attribute_value."\"";
				}
			}

			$content = "<table".$table_attribute.">".$content."</table>";
		}

		return $content;
	}
	
	function normalize($table_data) {
		if (is_array($table_data)) {
			if (isset($table_data['lines']) or isset($table_data['groups'])) {
				return $table_data;
			}
			if (isset($table_data[0]) and is_array($table_data[0])) {
				return array('lines' => $table_data);
			}
		}

		return false;
	}
	
	function show_lines($lines_data = array()) {
		$content = "";

		foreach ($lines_data as $row) {
			$tr_attribute = "";
			if (!isset($row['cells'])) {
				$temp = $row;
				$row = array('cells' => $temp);
			}
			foreach ($row as $row_attribute => $row_attribute_value) {
				if (is_string($row_attribute) && $row_attribute != 'cells') {
					$tr_attribute .= " ".$row_attribute."=\"".$row_attribute_value."\"";
				}
			}
			$content .= "<tr".$tr_attribute.">";
			foreach ($row['cells'] as $cell) {
				$cell_value = $this->show_cell($cell);
				if ($cell_value === false) {
					return false;
				}
				$content .= $cell_value;
			}
			$content .= "</tr>";
		}

		return $content;
	}

	function show_cell($cell_data = array()) {
		$td_attribute = "";
		$type = 'td';
		$value = "";

		if (!is_array($cell_data)) {
			$value = $cell_data;
			$cell_data = array();
		}

		foreach ($cell_data as $attribute => $attribute_value) {
			if (is_array($attribute_value)) {
				return false;
			}
			if ($attribute === 'value') {
				$value = $attribute_value;
			} elseif ($attribute === 'type') {
				if ($attribute_value == 'th') {
					$type = 'th';
				}
			} elseif (is_string($attribute)) {
				$td_attribute .= " ".$attribute."=\"".$attribute_value."\"";
			}
		}
		return "<".$type.$td_attribute.">".$value."</".$type.">";
	}
}