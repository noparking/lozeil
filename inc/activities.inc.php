<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Activities extends Collector  {
	public $filters = null;

	function __construct($class = null, $table = null, $db = null) {
		if ($class === null) {
			$class = "Activity";
		}
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_activities'];
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
			$query_where[] = $this->db->config['table_activities'].".id IN ".array_2_list($this->id);
		}
		if (isset($this->filters['global'])) {
			$query_where[] = $this->db->config['table_activities'].".global = ".(int)$this->filters['global'];
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

	function delete() {
		$this->select();
		foreach ($this as $activity) {
			$activity->delete();
		}
	}

	function delete_in_cascade() {
		$this->select();
		foreach ($this as $activity) {
			$activity->delete_in_cascade();
		}
	}

	function create_default_plan($number_default) {
		if ($number_default <= 1) {
			$activity = new Activity();
			$activity->name = ucfirst(__("activity"));
			$activity->create_single_default_plan();
		} else {
			$activity = new Activity();
			$activity->name = "Global";
			$activity->global = 1;
			$activity->save();
			$activity->create_multiple_default_plan("global");
			for ($i = 1; $i <= $number_default; $i++) {
				$activity = new Activity();
				$activity->name = "Activité ".$i;
				$activity->save();
				if ($activity->name == "Activité 1") {
					$activity->create_multiple_default_plan("first");
				} else {
					$activity->create_multiple_default_plan("other");			
				}
			}
		}
	}

	function global_exists() {
		$this->filter_with(array('global' => 1));
		$this->select();
		if ($this->count() == 1) {
			return true;
		} else {
			return false;
		}
	}

	function names() {
		$this->select();
		$names = array();
		foreach ($this as $activity) {
			$names[$activity->id] = $activity->name;
		}
		asort($names, SORT_STRING);
		return $names;
	}
	
	function grid_header() {
		$checkbox = new Html_Checkbox("checkbox_all_up", "check");
		$grid = array(
			'header' => array(
				'class' => "table_header",
				'cells' => array(
					array(
						'type' => "th",
						'id' => "checkbox",
						'value' => $checkbox->input()
					),
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__("name")),
					),
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__("right")),
					),
				)
			)
		);
		return $grid;
	}

	function grid_body() {
		$activity_number = 0;
		foreach ($this as $activity) {
			$activity_number++;
			$class = "";
			if ($activity->is_recently_modified())
				$class = "modified";
			$input = new Html_Input("activities[".$activity->id."][name]", $activity->name);
			$checker = new Html_Checkbox("activities[".$activity->id."][checked]", $activity->id);
			
			$grid[$activity->id] =  array(
				'class' => $class,
				'id' => 'table_'.$activity->id,
				'cells' => array(
					array(
						'type' => "td",
						'value' => $checker->input(),
					),
					array(
						'type' => "td",
						'value' => htmlspecialchars($activity->name),
					),
					array(
						'type' => "td",
						'value' => $activity->show_operations(),
					),
				)
			);
		}

		$grid[] = array(
			'class' => "table_total",
			'cells' => array(
				array(
					'colspan' => "2",
					'type' => "th",
					'value' => ""
				),
				array(
					'type' => "th",
					'value' => ucfirst(__("number of activities")).": ".$activity_number
				)
			)
		);

		return $grid;
	}

	function add_activity() {
		$activity = new Activity();
		return "<div id=\"add_activity\">".$activity->show_form_add().ucfirst(__("add new activity"))."</div>";
	}

	function grid() {
		return $this->grid_header() + $this->grid_body();
	}
	
	function show_grid() {
		$html_table = new Html_table(array('lines' => $this->grid()));
		return $html_table->show();
	}
	
	function display() {
		return "<div id=\"table_activities\">".$this->show_form()."</div>";
	}

	function show_form() {

		$options = array(
			"none" => "--",
			"delete" => ucfirst(__("delete")),
		);
		$select = new Html_Select("action", $options, "none");
		$select->properties = array(
				'onchange' => "confirm_option('".utf8_ucfirst(__("are you sure?"))."')"
			);
		$checkbox = new Html_Checkbox("checkbox_all_down", "check");
		$submit = new Html_Input("submit", __("ok"), "submit");

		return "<div id=\"edit_activities\"><form id=\"form_activities\" method=\"post\"  action=\"\" >".
			$this->show_grid().$checkbox->input().$select->item("").$submit->input()."
		</form></div>";
	}
}
