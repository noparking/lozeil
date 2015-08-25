<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Writings_Simulations extends Collector  {
	public $amounts = array();
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
		list($this->start,$this->stop) = determine_fiscal_year($timestamp);
		
		$cubismchart = new Html_Cubismchart("writingssimulations");
		$cubismchart->data = $this->balance_per_day_in_a_year_in_array($this->start);
		$cubismchart->start = $this->start;
		return $cubismchart->show();
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
						'value' => utf8_ucfirst(__("evolution")),
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
						'id' => "operations",
						'value' => "",
					)
				)
			)
		);
		return $grid;
	}
	
	function grid_body() {
		$grid = array();
		foreach ($this as $simulation) {
			
			$evolution = explode(":", $simulation->evolution);
			$evolution_to_display = __($evolution[0]);
			if (isset($evolution[1])) {
				$evolution_to_display .= " : ".$evolution[1];
			}
			
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
						'value' => $evolution_to_display,
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
		return "<div id=\"table_simulations\">".$this->show()."</div>";
	}
	
	function get_amounts_in_array() {
		$amounts = array();
		foreach ($this as $writingssimulation) {
			if ($writingssimulation->display == 1) {
				$first = $writingssimulation->date_start;
				$last = $writingssimulation->date_stop;
				$amount = $writingssimulation->amount_inc_vat;
				$periodicity = preg_split("/(q)|(y)|(a)|(t)|(m)/i", $writingssimulation->periodicity, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
				$evolution = explode(":", $writingssimulation->evolution);
				if ($first == $last) {
					$amounts[$first][] = $amount;
				} elseif (count($periodicity) == 1 and !is_numeric($periodicity[0])) {
					if(preg_match("/(m)/i", $periodicity[0])) {
						while ($first < $last) {
							$first = strtotime('+1 months', $first);
							$amounts[$first][] = $amount;
							if ($evolution[0] == "linear") {
								$amount = $amount + $evolution[1];
							}
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
		$amount = 0;
		foreach ($this->amounts as $month => $values) {
			if($month < $timestamp) {
				foreach ($values as $value) {
					$amount += $value;
				}
			}
		}
		return round($amount, 2);
	}
	
	function balance_per_day_in_a_year_in_array($timestamp_max) {
		$nb_day = intval(($this->stop -$this->start)/60/60/24);
		$writings = new Writings();
		$writings->filter_with(array('stop' => $this->stop));
		$writings->select_columns('amount_inc_vat', 'day');
		$writings->select();
		$this->select();
		$this->amounts = $this->get_amounts_in_array();
		
		$values = array();
		for ($i = 0; $i <= $nb_day; $i++) {
			$timestamp_max = strtotime('+1 day', $timestamp_max);
			$value = $writings->show_balance_at($timestamp_max) + $this->show_balance_at($timestamp_max);
			$values[] = $value;
		}
		return $values;
	}
}
