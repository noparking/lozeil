<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

class Users extends Collector {
	public $filters = null;
	
	function __construct($class = null, $table = null, $db = null) {
		if ($class === null) {
			$class = substr(__CLASS__, 0, -1);
		}
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_users'];
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
			$query_where[] = $this->db->config['table_users'].".id IN ".array_2_list($this->id);
		}
		if (isset($this->filters['username'])) {
			$query_where[] = $this->db->config['table_users'].".username = ".$this->db->quote($this->filters['username']);
		}
		if (isset($this->filters['email'])) {
			$query_where[] = $this->db->config['table_users'].".email = ".$this->db->quote($this->filters['email']);
		}
		
		return $query_where;
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
						'value' => __("Name"),
					),
					array(
						'type' => "th",
						'value' => __("Username"),
					),
					array(
						'type' => "th",
						'value' => __("Email"),
					),
					array(
						'type' => "th",
						'value' => "",
					),
				),
			),
		);
		return $grid;
	}

	function grid_body() {
		$account_number = 0;
		foreach ($this as $user) {
			$account_number++;
			$class = "";
			if ($user->is_recently_modified())
				$class = "modified";
			$checker = new Html_Checkbox("users[".$user->id."][checked]", $user->id);
			$grid[$user->id] =  array(
				'class' => $class,
				'id' => 'table_'.$user->id,
				'cells' => array(
					array(
						'type' => "td",
						'value' => $checker->input(),
					),
					array(
						'type' => "td",
						'value' => htmlspecialchars($user->name),
					),
					array(
						'type' => "td",
						'value' => htmlspecialchars($user->username),
					),
					array(
						'type' => "td",
						'value' => htmlspecialchars($user->email),
					),
					array(
						'type' => "td",
						'value' => $user->links_to_operations(),
						'class' => "operations",
					),
				),
			);
		}

		return $grid;
	}

 	function grid() {
		return $this->grid_header() + $this->grid_body();
	}
	
	function show() {
		$html_table = new Html_table(array('lines' => $this->grid()));
		return $html_table->show();
	}
	
	function display() {
		return "<div id=\"table_users\">".$this->show_form()."</div>";
	}

	function show_form() {

		$options = array(
			"none" => "--",
			"delete" => ucfirst(__('delete')),
		);
		$select = new Html_Select("action", $options, "none");
		$select->properties = array(
				'onchange' => "confirm_option('".utf8_ucfirst(__('are you sure?'))."')"
			);
		$checkbox = new Html_Checkbox("checkbox_all_down", "check");
		$submit = new Html_Input("submit", __('ok'), "submit");

		return "<div id=\"edit_users\"><form method=\"post\" id=\"form_users\"  name=\"users_id\" action=\"\" enctype=\"multipart/form-data\">".
				$this->show().$checkbox->input().$select->item("").$submit->input()."</form></div>";
	}
	
	function filter_with() {
		$elements = func_get_args();
		foreach ($elements as $element) {
			foreach ($element as $key => $value) {
				$this->filters[$key] = $value;
			}
		}
	}
}
