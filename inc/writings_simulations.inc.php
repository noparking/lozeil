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
					),
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__("operations")),
					)
				)
			)
		);
		return $grid;
	}
	
	function grid_body() {
		$grid = array();
		foreach ($this as $simulation) {
			
			if ($simulation->is_recently_modified()) {
				$class = "modified";
			} else {
				$class = "";
			}
			
			$grid[$simulation->id] =  array(
			'id' => 'table_'.$simulation->id,
			'class' => $class,
			'cells' => array(
					array(
						'type' => "td",
						'value' => $simulation->name,
					),
					array(
						'type' => "td",
						'value' => round($simulation->amount_inc_vat, 2),
					),
					array(
						'type' => "td",
						'value' => date("d/m/Y", $simulation->date_start),
					),
					array(
						'type' => "td",
						'value' => date("d/m/Y", $simulation->date_stop),
					),
					array(
						'type' => "td",
						'value' => $simulation->periodicity,
					),
					array(
						'type' => "td",
						'value' => $simulation->display,
					),
					array(
						'type' => "td",
						'class' => 'operations',
						'value' => $simulation->show_operations()
					)
				)
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
		return "<div id=\"simulation\">".$this->show()."</div>";
	}
}
