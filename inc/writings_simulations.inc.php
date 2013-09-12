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
		
		$timeline_iterator = strtotime('-2 months', $this->month);
		$writingssimulations = new Writings_Simulations();
		while ($timeline_iterator <= strtotime('+10 months', $this->month)) {
			$class = "navigation";
			if ($timeline_iterator == $this->month) {
				$class = "encours";
			} 
			$grid['leaves'][$timeline_iterator]['class'] = "heading_timeline_month_".$class;
			$next_month = determine_first_day_of_next_month($timeline_iterator);
			$balance = $writingssimulations->show_balance_at($next_month);
			
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
	
	function get_amounts_in_array() {
		$amounts = array();
		foreach ($this as $writingssimulation) {
			if ($writingssimulation->display == 1) {
				$first = determine_first_day_of_month($writingssimulation->date_start);
				$last = determine_first_day_of_month($writingssimulation->date_stop);
				$amount = $writingssimulation->amount_inc_vat;
				$periodicity = preg_split("/(q)|(y)|(a)|(t)|(m)/i", $writingssimulation->periodicity, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

				if (count($periodicity) == 1 and !is_numeric($periodicity[0])) {
					if(preg_match("/(m)/i", $periodicity[0])) {
						while ($first < $last) {
							$first = strtotime('+1 months', $first);
							$amounts[$first][] = $amount;
						}
					} elseif(preg_match("/(t)|(q)/i", $periodicity[0])) {
						while ($first < $last) {
							$first = strtotime('+3 months', $first);
							$amounts[$first][] = $amount;
						}
					} elseif(preg_match("/(y)|(a)/i", $periodicity[0])) {
						while ($first < $last) {
							$first = strtotime('+1 year', $first);
							$amounts[$first][] = $amount;
						}
					}
				} elseif (count($periodicity) == 2 and is_numeric($periodicity[0])) {
					if(preg_match("/(m)/i", $periodicity[1])) {
						while ($first < $last) {
							$first = strtotime('+'.$periodicity[0].' months', $first);
							$amounts[$first][] = $amount;
						}
					} elseif(preg_match("/(t)|(q)/i", $periodicity[1])) {
						while ($first < $last) {
							$first = strtotime('+'.($periodicity[0] * 3).' months', $first);
							$amounts[$first][] = $amount;
						}
					} elseif(preg_match("/(y)|(a)/i", $periodicity[1])) {
						while ($first < $last) {
							$first = strtotime('+'.$periodicity[0].' year', $first);
							$amounts[$first][] = $amount;
						}
					}
				}
			}
		}
		return $amounts;
	}
	
	function show_balance_at($timestamp) {
		$writings = new Writings();
		$writings->filter_with(array('stop' => strtotime('+11 months', determine_first_day_of_month($timestamp))));
		$writings->select_columns('amount_inc_vat', 'day');
		$writings->select();
		
		$this->select();
		$simulation_amounts = $this->get_amounts_in_array();
		
		$amount = 0;
		foreach ($writings->instances as $writing) {
			if($writing->day < $timestamp) {
				$amount += $writing->amount_inc_vat;
			}
		}
		
		foreach ($simulation_amounts as $month => $values) {
			if($month < $timestamp) {
				foreach ($values as $value) {
					$amount += $value;
				}
			}
		}
		return round($amount, 2);
	}
}
