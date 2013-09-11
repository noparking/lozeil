<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

class Writings extends Collector {
	public $filters = null;
	
	private $month = 0;
	
	function __construct($class = null, $table = null, $db = null) {
		if ($class === null) {
			$class = substr(__CLASS__, 0, -1);
		}
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_writings'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}
	
	function get_join() {
		$join = parent::get_join();
		$join[] = "
			LEFT JOIN ".$this->db->config['table_categories']."
			ON ".$this->db->config['table_categories'].".id = ".$this->db->config['table_writings'].".categories_id
		";
		$join[] = "
			LEFT JOIN ".$this->db->config['table_sources']."
			ON ".$this->db->config['table_sources'].".id = ".$this->db->config['table_writings'].".sources_id
		";
		$join[] = "
			LEFT JOIN ".$this->db->config['table_banks']."
			ON ".$this->db->config['table_banks'].".id = ".$this->db->config['table_writings'].".banks_id
		";
		
		return $join;
	}
	
	function get_columns() {
		$columns = parent::get_columns();
		$columns[] = $this->db->config['table_categories'].".name as category_name, ".$this->db->config['table_sources'].".name as source_name, ".$this->db->config['table_banks'].".name as bank_name";

		return $columns;
	}
	
	function grid_header() {
		$grid = array(
			'header' => array(
				'class' => "table_header",
				'cells' => array(
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("day"),
						'id' => "day",
						'value' => utf8_ucfirst(__("date")),
					),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("category_name"),
						'id' => "category_name",
						'value' => utf8_ucfirst(__("category")),
					),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("source_name"),
						'id' => "source_name",
						'value' => utf8_ucfirst(__("source")),
						),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("number"),
						'id' => "number",
						'value' => utf8_ucfirst(__('piece nb')),
					),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("amount_excl_vat"),
						'id' => "amount_excl_vat",
						'value' => utf8_ucfirst(__("amount excluding vat")),
					),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("vat"),
						'id' => "vat",
						'value' => __("VAT"),
					),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("amount_inc_vat"),
						'id' => "amount_inc_vat",
						'value' => utf8_ucfirst(__("amount including vat")),
					),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("comment"),
						'id' => "comment",
						'value' => utf8_ucfirst(__("comment")),
					),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("bank_name"),
						'id' => "bank_name",
						'value' => utf8_ucfirst(__("bank")),
					),
					array(
						'type' => "th",
						'id' => "operations",
						'value' => utf8_ucfirst(__("operations")),
					),
				),
			),
		);		
		return $grid;
	}
	
	function determine_table_header_class($header_column_name) {
		$class = "sort";
		if ($_SESSION['order_col_name'] == $header_column_name) {
			if ($_SESSION['order_direction'] == "ASC") {
				$class .= " sortedup";
			} else {
				$class .= " sorteddown";
			}
		}
		return $class;
	}
	
	function grid_body() {
		$categories = new Categories();
		$categories->select();
		$categories_names = $categories->names();
		$sources = new Sources();
		$sources->select();
		$sources_name = $sources->names();
		$banks = new Banks();
		$banks->select();
		$banks_name = $banks->names();
		$grid = array();
		$selected_writing = determine_integer_from_post_get_session(null, "writings_id");

		foreach ($this as $writing) {
			$class_comment = "";
			$informations = $writing->show_further_information();
			if (!empty($informations)) {
				$class_comment = "table_writings_comment";
			}
			if ($writing->is_recently_modified()) {
				$class = "draggable modified";
			} else {
				$class = "draggable";
			}
			
			$grid[] =  array(
				'class' => $class,
				'id' => "table_".$writing->id,
				'cells' => array(
					array(
						'type' => "td",
						'value' => date("d/m/Y", $writing->day),
					),
					array(
						'type' => "td",
						'value' => isset($categories_names[$writing->categories_id]) ? $categories_names[$writing->categories_id] : "",
					),
					array(
						'type' => "td",
						'value' => isset($sources_name[$writing->sources_id]) ? $sources_name[$writing->sources_id] : "",
					),
					array(
						'type' => "td",
						'value' => $writing->number,
					),
					array(
						'type' => "td",
						'value' => round($writing->amount_excl_vat, 2),
					),
					array(
						'type' => "td",
						'value' => $writing->vat,
					),
					array(
						'type' => "td",
						'value' => round($writing->amount_inc_vat, 2),
					),
					array(
						'type' => "td",
						'class' => $class_comment,
						'value' => $writing->comment.$informations,
					),
					array(
						'type' => "td",
						'value' => isset($banks_name[$writing->banks_id]) ? $banks_name[$writing->banks_id] : "",
					),
					array(
						'type' => "td",
						'class' => "operations",
						'value' => $writing->show_operations(),
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
		return "<div id=\"table_writings\"><form method=\"post\" name=\"edit_writings_form\" action=\"\" enctype=\"multipart/form-data\">".$this->show()."</form></div>";
	}
	
	function show_timeline_at($timestamp) {
		$grid = array();
		$this->month = determine_first_day_of_month($timestamp);
		
		$writings = new Writings();
		$writings->filter_with(array('stop' => strtotime('+11 months', $this->month)));
		$writings->select_columns('amount_inc_vat', 'day');
		$writings->select();

		$timeline_iterator = strtotime('-2 months', $this->month);

		while ($timeline_iterator <= strtotime('+10 months', $this->month)) {
			$class = "navigation";
			if ($timeline_iterator == $this->month) {
				$class = "encours";
			} 
			$grid['leaves'][$timeline_iterator]['class'] = "heading_timeline_month_".$class;
			$next_month = determine_first_day_of_next_month($timeline_iterator);
			$balance = $writings->show_balance_at($next_month);
			
			$balance_class = $balance > 0 ? "positive_balance" : "negative_balance";
			
			$grid['leaves'][$timeline_iterator]['value'] = Html_Tag::a(link_content("content=writings.php&timestamp=".$timeline_iterator),
					utf8_ucfirst($GLOBALS['array_month'][date("n",$timeline_iterator)])."<br />".
					date("Y", $timeline_iterator))."<br /><br />
					<span class=\"".$balance_class."\">".$balance."</span>";
			$timeline_iterator = $next_month;
		}
		$list = new Html_List($grid);
		$timeline = $list->show();

		return $timeline;
	}
	
	function display_timeline_at($timestamp) {
		return "<div id=\"heading_timeline\">".$this->show_timeline_at($timestamp)."</div>";
	}
	
	function get_where() {
		$query_where = parent::get_where();
		
		if (isset($this->filters['start'])) {
			$query_where[] = $this->db->config['table_writings'].".day >= ".(int)$this->filters['start'];
		}
		if (isset($this->filters['stop'])) {
			$query_where[] = $this->db->config['table_writings'].".day <= ".(int)$this->filters['stop'];
		}
		if (isset($this->filters['*']) and !empty($this->filters['*'])) {
			$query_where[] = $this->db->config['table_writings'].".search_index LIKE ".$this->db->quote("%".$this->filters['*']."%");
		}
		if (isset($this->filters['categories_id'])) {
			$query_where[] = $this->db->config['table_writings'].".categories_id = ".(int)$this->filters['categories_id'];
		}
		
		return $query_where;
	}
	
	function show_balance_on_current_date() {
		return Html_Tag::a(link_content("content=writings.php&timestamp=".determine_first_day_of_month(time())),utf8_ucfirst(__("accounting on"))." ".get_time("d/m/Y")." : ".$this->show_balance_at(time())." ".__("€"));
	}
	
	function show_balance_at($timestamp) {
		$amount = 0;
		foreach ($this->instances as $writing) {
			if($writing->day < $timestamp) {
				$amount += $writing->amount_inc_vat;
			}
		}
		return round($amount, 2);
	}
	
	function show_balance_between($timestamp_min, $timestamp_max) {
		$amount = 0;
		foreach ($this->instances as $writing) {
			if($writing->day >= $timestamp_min and $writing->day < $timestamp_max) {
				$amount += $writing->amount_inc_vat;
			}
		}
		return round($amount, 2);
	}
	
	function form_filter($value = "") {
		$form = "<div class=\"extra_filter_writings\"><form method=\"post\" name=\"extra_filter_writings_form\" action=\"\" enctype=\"multipart/form-data\">";
		$input_hidden_action = new Html_Input("action", "filter");
		$input = new Html_Input("extra_filter_writings_value",$value);
		$form .= $input_hidden_action->input_hidden().$input->item(utf8_ucfirst(__('filter')." : "));
		$form .= "</form></div>";
		return $form;
	}
	
	function filter_with() {
		$elements = func_get_args();
		foreach ($elements as  $element) {
			foreach ($element as $key => $value) {
				$this->filters[$key] = $value;
			}
		}
	}
	
	function balance_per_month_in_a_year_in_array($timestamp_max) {
		$values = array();
		for ($i = 0; $i < 12; $i++) {
			$timestamp_min = $timestamp_max;
			$timestamp_max = strtotime('+1 month', $timestamp_max);
			$values[] = $this->show_balance_between($timestamp_min, $timestamp_max);
		}
		return $values;
	}
	
	function balance_per_day_in_a_year_in_array($timestamp_max) {
		$values = array();
		for ($i = 0; $i <= 365; $i++) {
			$timestamp_min = $timestamp_max;
			$timestamp_max = strtotime('+1 day', $timestamp_max);
			$values[] = $this->show_balance_between($timestamp_min, $timestamp_max);
		}
		return $values;
	}
}
