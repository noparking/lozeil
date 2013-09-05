<?php
/*
	lozeil
	$Author: $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

class Sparkline {
	public $name = "";
	public $values = array();
	public $type = "";
	public $period = "";
	public $year = 0;
	
	function __construct($type = "line", $period = "month", $year = 0) {
		$this->type = $type;
		$this->period = $period;
		if ($year == 0) {
			$this->year = mktime(0, 0, 0, 1, 1, date('Y'));
		}
	}
	
	function name($name="") {
		if (!empty($name)) {
			$this->name = $name;
		}
		
		return $this->name;
	}
	
	function values($values) {
		if (is_array($values) and count($values) > 0) {
			$this->values = $values;
		}
		
		return $this->values;
	}
	
	function grid_header() {
		$grid = array(
			'header' => array(
				'class' => "sparkline_header",
				'cells' => array(
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__('sparkline')),
					),
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__('min')),
					),
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__('max')),
					),
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__('last')),
					),
					array(
						'type' => "th",
						'value' => utf8_ucfirst(__('category')),
					)
				)
			)
		);
		return $grid;
	}
	
	function grid_body() {
		$writings = new Writings();
		$categories = new Categories();
		$categories->select();
		foreach ($categories as $category) {
			if($category->is_in_use()) {
				$writings->filter_with(array('categories_id' => $category->id));
				$writings->select();
				if ($this->period == 'day') {
					$values = $writings->balance_per_day_in_a_year_in_array($this->year);
				} elseif ($this->period == 'month') {
					$values = $writings->balance_per_month_in_a_year_in_array($this->year);
				}
				$this->name = $category->name;
				$this->values($values);
				
				$grid[$category->id] = array(
						'class' => "sparkline",
						'cells' => array(
							array(
								'type' => "td",
								'class' => "sparkline-values",
								'sparkType' => $this->type,
								'value' => join(",", $this->values),
							),
							array(
								'type' => "td",
								'class' => "sparkline-min",
								'value' => min($this->values),
							),
							array(
								'type' => "td",
								'class' => "sparkline-max",
								'value' => max($this->values),
							),
							array(
								'type' => "td",
								'class' => "sparkline-last",
								'value' => end($this->values),
							),
							array(
								'type' => "td",
								'class' => "sparkline-name",
								'value' => $this->name,
							),
						)
				);
			}
		}
		
		$writings->filter_with(array('categories_id' => 0));
		$writings->select();
		if ($this->period == 'day') {
			$values = $writings->balance_per_day_in_a_year_in_array($this->year);
		} elseif ($this->period == 'month') {
			$values = $writings->balance_per_month_in_a_year_in_array($this->year);
		}
		$this->name = __('without category');
		$this->values($values);
		$grid[0] = array(
				'class' => "sparkline",
				'cells' => array(
					array(
						'type' => "td",
						'class' => "sparkline-values",
						'sparkType' => $this->type,
						'value' => join(",", $this->values),
					),
					array(
						'type' => "td",
						'class' => "sparkline-min",
						'value' => min($this->values),
					),
					array(
						'type' => "td",
						'class' => "sparkline-max",
						'value' => max($this->values),
					),
					array(
						'type' => "td",
						'class' => "sparkline-last",
						'value' => end($this->values),
					),
					array(
						'type' => "td",
						'class' => "sparkline-name",
						'value' => $this->name,
					),
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
	
	function display() {
		$year = new Html_Select("year", close_years_in_array(), (int)date('Y', $this->year));
		$type = new Html_Select("type", array('bar' => 'bar','line' => 'line','tristate' => 'tristate','box' => 'box'), $this->type);
		$period = new Html_Select("period", array('month' => __('month'),'day' => __('day')), $this->period);
		$submit = new Html_Input("submit", "", "submit");
		$submit->value =__('ok');
		
		return "<form method=\"post\" id=\"followupwritings_year\" action=\"\">".$type->selectbox().$period->selectbox().$year->selectbox().$submit->item("")."</form>
			<div id =\"table_followupwritings\">".$this->show()."</div>";
	}
}
