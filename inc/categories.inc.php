<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

class Categories extends Collector  {
	public $filters = null;
	
	function __construct($class = null, $table = null, $db = null) {
		$class = "Category";
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_categories'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}
	
	function get_where() {
		$where = parent::get_where();
		
		if (isset($this->id) and !empty($this->id)) {
			if (!is_array($this->id)) {
				$this->id = array((int)$this->id);
			}
			$where[] = $this->db->config['table_categories'].".id IN ".array_2_list($this->id);
		}
		if (isset($this->filters['vat_category'])) {
			$where[] = $this->db->config['table_categories'].".vat_category = ".(int)$this->filters['vat_category'];
		}
		
		return $where;
	}
	
	function filter_with() {
		$elements = func_get_args();
		foreach ($elements as $element) {
			foreach ($element as $key => $value) {
				$this->filters[$key] = $value;
			}
		}
	}
	
	function names() {
		$names = array();
		$names[0] = "--";
		foreach ($this as $category) {
			$names[$category->id] = $category->name;
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
								       'value' => utf8_ucfirst(__("default VAT")),
								       ),
								 array(
								       'type' => "th",
								       'value' => utf8_ucfirst(__("VAT category")),
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
		foreach ($this as $category) {
			if ($category->is_recently_modified()) {
				$class = "modified";
			} else {
				$class = "";
			}
			$checker = new Html_CheckBox("category[".$category->id."][checked]",$category->id);
			$checkbox_category_vat = new Html_Checkbox("category[".$category->id."][vat_category]", 1, $category->vat_category);
			
			$grid[$category->id] =  array(
				'class' => $class,
				'id' => 'table_'.$category->id,
				'cells' => array(
					array(
						'type' => "td",
						'value' => $checker->input(),
					),
					array(
						'type' => "td",
						'value' => htmlspecialchars($category->name),
					),
					array(
						'type' => "td",
						'value' => htmlspecialchars($category->vat),
					),
					array(
						'type' => "td",
						'value' => $checkbox_category_vat->input_readonly(),
					),
					array(
						'type' => "td",
						'value' => $category->links_to_operations(),
						'class' => "operations",
					),
			   )
		  );
		}

		return $grid;
	}

	function add_category() {
		$category = new Category();
		return '<div id=\'add_category\'>'.$category->show_form_add().ucfirst(__('add new category')).'</div>';
	}

	function grid() {
		return $this->grid_header() + $this->grid_body();
	}
	
	function show() {
		$html_table = new Html_table(array('lines' => $this->grid()));
		return $html_table->show();
	}
	
	function display() {
		return "<div id=\"table_categories\">".$this->show_form()."</div>";
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

		return "<div id=\"edit_categories\"><form id=\"form_categories\" method=\"POST\" action=\"\" name=\"categories_id\" >".
			$this->show().$checkbox->input().$select->item("").$submit->input()."</form></div>";
	}
}
