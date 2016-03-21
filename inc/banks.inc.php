<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

class Banks extends Collector  {
	public $filters = null;
	
	function __construct($class = null, $table = null, $db = null) {
		if ($class === null) {
			$class = substr(__CLASS__, 0, -1);
		}
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_banks'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}
	
	function names() {
		$names = array();
		$names[0] = "--";
		foreach ($this as $bank) {
			$names[$bank->id] = $bank->name;
		}
		return $names;
	}
	
	function names_of_selected_banks() {
		$names = array();
		$names[0] = "--";
		foreach ($this as $bank) {
			if ($bank->selected == 1) {
				$names[$bank->id] = $bank->name;
			}
		}
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
								       'value' => utf8_ucfirst(__("iban")),
								       ),
								 array(
								       'type' => "th",
								       'value' => utf8_ucfirst(__("in use")),
								       ),
								 array(
								       'type' => "th",
								       'value' => utf8_ucfirst(__("right")),
								       ),
						))
			      );
		return $grid;
	}

	function grid_body() {
		$bank_number = 0;
		foreach ($this as $bank) {
			$bank_number++;
			$class = "";
			if ($bank->is_recently_modified())
				$class = "modified";
			$input = new Html_Input("checkbox_test", $bank->name);
			$checker = new Html_Checkbox("banks[".$bank->id."][checked]", $bank->id);
			$iban = new Html_Input("banks[".$bank->id."][iban]", $bank->iban);
			$checkbox = new Html_Checkbox("banks[".$bank->id."][selected]", $bank->name, $bank->selected);
			
			$grid[$bank->id] =  array(
				'class' => $class,
				'id' => 'table_'.$bank->id,
				'cells' => array(
					array(
						'type' => "td",
						'value' => $checker->input(),
					),
					array(
						'type' => "td",
						'value' => htmlspecialchars($bank->name),
					),
					array(
						'type' => "td",
						'value' => htmlspecialchars($bank->iban),
					),
					array(
						'type' => "td",
						'value' => $checkbox->input_readonly(),
					),
					array(
						'type' => "td",
						'value' => $bank->show_operations(),
					),
				)
			);
		}
		return $grid;
	}

	function add_bank() {
		$bank = new Bank();
		return '<div id=\'add_bank\'>'.$bank->show_form_add().ucfirst(__('add new bank')).'</div>';
	}

	function grid() {
		return $this->grid_header() + $this->grid_body();
	}
	
	function show() {
		$html_table = new Html_table(array('lines' => $this->grid()));
		return $html_table->show();
	}
	
	function display() {
		return "<div id=\"table_banks\">".$this->show_form()."</div>";
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

		return "<div id=\"edit_banks\"><form method=\"post\" id=\"form_banks\"  name=\"banks_id\" action=\"\" enctype=\"multipart/form-data\">".
				$this->show().$checkbox->input().$select->item("").$submit->input()."</form></div>";
	}

	function show_select($name) {
	  $this->select();
	  $array = array(0 => '--');
	  foreach ($this->instances as $instance) {
	    $array[$instance->id] = $instance->name;
	  }

	  $select = new Html_Select($name,$array);
	  return $select->selectbox() ;
	}
	
	function get_id_from_name($name) {
		$id = 0;
		$this->select();
		foreach ($this as $instance) {
			if (preg_match("/".$name."/", $instance->name) and $instance->selected) {
				$id = $instance->id;
			}
		}
		return $id;
	}
	

	function get_instances()
	{
	  return $this->instances;
	}
}
