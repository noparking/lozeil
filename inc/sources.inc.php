<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

class Sources extends Collector  {
	public $filters = null;
	
	function __construct($class = null, $table = null, $db = null) {
		if ($class === null) {
			$class = substr(__CLASS__, 0, -1);
		}
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_sources'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}
	
	function names() {
		$names = array();
		$names[0] = "--";
		foreach ($this as $source) {
			$names[$source->id] = $source->name;
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
								       'value' => utf8_ucfirst(__("right")),
								       ),
						))
			      );
		return $grid;
	}

	function grid_body() {
		$source_number = 0;
		foreach ($this as $source) {
			$source_number++;
			$class = "";
			if ($source->is_recently_modified())
				$class = "modified";
			$checker = new Html_CheckBox("sources[".$source->id."][checked]",$source->id);
			
			$grid[$source->id] =  array(
				'class' => $class,
				'id' => 'table_'.$source->id,
				'cells' => array(
					array(
						'type' => "td",
						'value' => $checker->input(),
					),
					array(
						'type' => "td",
						'value' => htmlspecialchars($source->name),
					),
					array(
						'type' => "td",
						'value' => $source->links_to_operations(),
					),
				)
			);
		}

		$grid[] = array('class' => "table_total", 'cells' => array(array('colspan' => "2", 'type' => "th", 'value' => ""), array('type' => "th", 'value' => ucfirst(__('number of sources')).': '.$source_number)));
		return $grid;
	}

	function add_source() {
		$source = new Source();
		return "<div id='add_source'>".$source->show_form_add().ucfirst(__('add new source'))."</div>";
	}

	function show() {
		$html_table = new Html_table(array('lines' =>$this->grid_header()+ $this->grid_body()));
		return $html_table->show();
	}
	
	function display() {
		return "<div id=\"table_sources\">".$this->show_form()."</div>";
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

		return "<div id=\"edit_sources\"><form id=\"form_sources\" method=\"post\"  action=\"\" >"
		.$this->show().$checkbox->input().$select->item("").$submit->input()."</form></div>";
	}
}
