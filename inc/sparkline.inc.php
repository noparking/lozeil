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
			$this->year = date('Y');
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
	
	function show() {
		$show = "<tr class=\"sparkline\" >";
		$show .= "<td class=\"sparkline-values\" sparkType=\"".$this->type."\">".join(",", $this->values)."</td>";
		$show .= "<td class=\"sparkline-min\">".min($this->values)."</td>";
		$show .= "<td class=\"sparkline-max\">".max($this->values)."</td>";
		$show .= "<td class=\"sparkline-last\">".end($this->values)."</td>";
		$show .= "<td class=\"sparkline-name\">".$this->name."</td>";
		$show .= "</tr>";
		return $show;
	}
	
	function show_header() {
		$show = "<tr class=\"sparkline_header\">";
		$show .= "<th>".utf8_ucfirst(__('sparkline'))."</th>";
		$show .= "<th>".utf8_ucfirst(__('min'))."</th>";
		$show .= "<th>".utf8_ucfirst(__('max'))."</th>";
		$show .= "<th>".utf8_ucfirst(__('last'))."</th>";
		$show .= "<th>".utf8_ucfirst(__('category'))."</th>";
		$show .= "</tr>";
		return $show;
	}
	
	function display() {
		$html = $this->show_header();
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
				$html .= $this->show();
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
		$html .= $this->show();
		$year = new Html_Select("year", close_years_in_array(), (int)date('Y', $this->year));
		$type = new Html_Select("type", array('bar' => 'bar','line' => 'line','tristate' => 'tristate','box' => 'box'), $this->type);
		$period = new Html_Select("period", array('month' => __('month'),'day' => __('day')), $this->period);
		$submit = new Html_Input("submit", "", "submit");
		$submit->value =__('ok');
		
		return "<form method=\"post\" id=\"followupwritings_year\" action=\"\">".$type->selectbox().$period->selectbox().$year->selectbox().$submit->item("")."</form>
			<table id =\"table_followupwritings\">".$html."</table>";
	}
}
