<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

class Writings_Simulations extends Collector  {
	
	public $filters = null;
	
	function __construct($class = null, $table = null, $db = null) {
		if ($class === null) {
			$class = substr(__CLASS__, 0, -1);
		}
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_writingssimulations'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
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
			
			$balance += 5000;
			
			$balance_class = $balance > 0 ? "positive_balance" : "negative_balance";
			
			$grid['leaves'][$timeline_iterator]['value'] = Html_Tag::a(link_content("content=writingssimulations.php&timestamp=".$timeline_iterator),
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
	
	
	function grid_header() {
		$grid =  array(
			'header' => array(
				'cells' => array(
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__("name")),
					),
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__("amount including vat")),
					),
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__("start date")),
					),
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__("end date")),
					),
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__("periodicity")),
					),
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__("display")),
					)
				)
			)
		);
		return $grid;
	}
	
	function grid_body() {
		foreach ($this as $simulation) {
			$name = new Html_Input("simulation[".$simulation->id."][name]", $simulation->name);
			$amount_inc_vat = new Html_Input("simulation[".$simulation->id."][amount_inc_vat]", $simulation->amount_inc_vat);
			$datepicker_start = new Html_Input_Date("datepicker_start__".$simulation->id, $simulation->date_start);
			$datepicker_stop = new Html_Input_Date("datepicker_stop__".$simulation->id, $simulation->date_stop);
			$periodicity = new Html_Input("periodicity__".$simulation->id, $simulation->periodicity);
			$display = new Html_Checkbox("display__".$simulation->id, " ", $simulation->display);
		
			$grid[$simulation->id] =  array(
			'cells' => array(
					array(
						'type' => "td",
						'value' => $name->item(""),
					),
					array(
						'type' => "td",
						'value' => $amount_inc_vat->item(""),
					),
					array(
						'type' => "td",
						'value' => $datepicker_start->input(),
					),
					array(
						'type' => "td",
						'value' => $datepicker_stop->input(),
					),
					array(
						'type' => "td",
						'value' => $periodicity->item(""),
					),
					array(
						'type' => "td",
						'value' => $display->item(""),
					)
				)
			);
		}

		$name = new Html_Input("name__0");
		$amount_inc_vat = new Html_Input("amount_inc_vat__0");
		$datepicker_start = new Html_Input_Date("datepicker_start__0");
		$datepicker_stop = new Html_Input_Date("datepicker_stop__0");
		$periodicity = new Html_Input("periodicity__0");
		$display = new Html_Checkbox("display__0", " ");
		$grid[0] =  array(
			'id' => 0,
			'cells' => array(
				array(
					'type' => "td",
					'value' => $name->item(""),
				),
				array(
					'type' => "td",
					'value' => $amount_inc_vat->item(""),
				),
				array(
					'type' => "td",
					'value' => $datepicker_start->input(),
				),
				array(
					'type' => "td",
					'value' => $datepicker_stop->input(),
				),
				array(
					'type' => "td",
					'value' => $periodicity->item(""),
				),
				array(
					'type' => "td",
					'value' => $display->item(""),
				)
			)
		);
	return $grid;
	}
	
	function grid() {
		return $this->grid_header() + $this->grid_body();
	}
	
	function show() {
		$html_table = new Html_table(array('lines' => $this->grid()));
		return $html_table->show();
	}
	
	function show_form() {
		$submit = new Html_Input("submit", __('save'), "submit");
		return "<div id=\"simulation\"><form method=\"post\" name=\"simulation\" action=\"\" enctype=\"multipart/form-data\">".
				$this->show().$submit->item("")."</form></div>";
	}
	
	function prepare_results_from_post($posts) {
		$results = array();
		foreach ($posts as $name => $post) {
			$id = explode("__", $name);
			if(is_array($post)) {
				foreach($post as $key => $value) {
					if (!empty($value)) {
						$results[$id[1]][$key] = $value;
					}
				}
			} else {
				if (!empty($post)) {
					$results[$id[1]][$id[0]] = $post;
				}
			}
		}
	}
}
