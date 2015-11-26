<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

class Reportings extends Collector {
	public $filters = null;
	public $fullname = "";
	public $activities_id = 0;
	public $reportings_id = 0;

	function __construct($db = null, $class = null, $table = null) {
		if ($class === null) {
			$class = substr (__CLASS__ , 0, -1);
		}
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_reportings'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}

	function delete() {
		foreach ($this as $reporting) {
			$reporting->delete();
		}
	}

	function get_where() {
		$query_where = parent::get_where();

		if (isset($this->id) and !empty($this->id)) {
			if (!is_array($this->id)) {
				$this->id = array((int)$this->id);
			}
			$query_where[] = $this->db->config['table_reportings'].".id IN ".array_2_list($this->id);
		}
		if (isset($this->filters['activities_id'])) {
			$query_where[] = $this->db->config['table_reportings'].".activities_id = ".(int)$this->filters['activities_id'];
		}
		if (isset($this->filters['reportings_id'])) {
			$query_where[] = $this->db->config['table_reportings'].".reportings_id = ".(int)$this->filters['reportings_id'];
		}

		return $query_where;
	}

	function filter_with() {
		$elements = func_get_args();
		foreach ($elements as $element) {
			foreach ($element as $key => $value) {
				$this->filters[$key] = $value;
			}
		}
	}

	function fullnames() {
		$reportings = array();
		foreach ($this as $element) {
			$activity = new activity();
			$activity->load(array('id'=> $element->activities_id));
			$reportings[$element->id] = $element->name ." <span style=\"font-size:12px;\">".$activity->name."</span>";
		}
		return $reportings;
	}

	function instances() {
		$reportings = array ();
		foreach ( $this as $element ) {
			$reportings[$element->id] = $element;
		}
		return $reportings;
	}

	function save_reportings_default($reportings, $activity_id) {
		foreach ($reportings as $name => $childs) {
			$reporting = new Reporting();
			$reporting->name = utf8_ucfirst($name);
			$reporting->activities_id = $activity_id;
			if (array_key_exists("base", $childs)) {
				$reporting->base = 1;
			}
			$reporting->save();

			foreach ($childs as $key => $name) {
				if ($key != "base") {
					$reporting_child = new Reporting();
					$reporting_child->reportings_id = $reporting->id;
					$reporting_child->activities_id = $activity_id;
					$reporting_child->name = utf8_ucfirst($name);
					$reporting_child->norm = $key;
					$reporting_child->save();
				}
			}
		}
	}

	function get_grid($id = 0, $level = 0) {
		$reportings = array();
		$retour = array();

		$this->select();
		foreach ($this as $i) {
			if ($i->reportings_id == $id) {
				$reportings[$i->id] = array(
					'id' => $i->id,
					'name' => $i->name,
					'level' => $level,
					'parent' => $i->reportings_id,
					'reportings_id' => $i->reportings_id,
					'activities_id' => $i->activities_id,
					'base' => $i->base,
					'real' => array('value' => "", 'ratio' => ""),
					'n-1' => array('value' => "", 'ratio' => ""),
					'n-2' => array('value' => "", 'ratio' => ""),
					'ecart' => array('value' => "", 'ratio' => ""),
					'ecart2' => array('value' => "", 'ratio' => ""),
					'sort'=> $i->sort
				);
			}
		}
		uasort($reportings,"sortcmp");
		foreach ($reportings as $id => $reporting) {
			$nextlevel = $this->get_grid($id, $level + 1);
			$retour[$id] = $reporting;
			if (!empty($nextlevel)) {
				$retour = $retour + $nextlevel;
			}
		}
		return $retour;
	}
	
	function get_reportings_by_sort($id = 0, $level = 0) {
		$reportings = array();
		$retour = array();

		$this->select();
		foreach ($this as $i) {
			if ($i->reportings_id == $id) {
				$reportings[$i->id] = $i;
				$i->level = $level;
				$i->parent = $i->reportings_id;
			}
		}

		foreach ($reportings as $id => $reporting) {
			$nextlevel = $this->get_reportings_by_sort($id, $level + 1);
			$retour[$id] = $reporting;
			if (!empty($nextlevel)) {
				$retour = $retour + $nextlevel;
			}
		}
		return $retour;
	}

	function display_balancescustom($activities_id, $date_info) {
		return "<center><div id=\"master\">".$this->show_table_balancescustom($activities_id, $date_info)."</div></center>";
	}

	function show_table_balancescustom($activities_id, $date_info) {
		$html_table = new Html_Table(array('lines' => $this->show_form($activities_id, $date_info)));
		return $html_table->show();
	}

	function show_form($activities_id, $date_info) {
		return $this->show_form_header() + $this->show_form_body($activities_id, $date_info) + $this->show_form_delimiter() + $this->show_form_other($activities_id, $date_info);
	}

	function show_form_header() {
		$reporting = new Reporting();
		$checkbox = new Html_Checkbox("checkbox_all_reporting", "check");
		$checkbox->properties['style'] = "position: relative; left: 9px;";
		$checkbox_view = new Html_Checkbox("checkbox_all_view", "check");
		$checkbox_view->properties = array('class' => "checkbox_styled", 'more' => __("more"), 'less' => __("less"));
		$grid = array(
			'cells' => array(
				array(
					'type' => "th",
					'class' => "table_checkbox_header",
					'value' => $checkbox->item("")
				),
				array(
					'type' => "th",
					'value' => ucfirst(__("reporting name")),
				),
				array(
					'type' => "th",
					'style' => 'text-align: center; width: 100px;',
					'value' => ucfirst(__("accounting plan number")),
				),
				array(
					'type' => "th",
					'style' => 'text-align: center; width: 350px',
					'value' => ucfirst(__("accounting plan name")),
				),
				array(
					'type' => "th",
					'style' => 'text-align: center; width: 100px',
					'value' => ucfirst(__("value")),
				),
				array(
					'type' => "th",
					'style' => "width: 120px;",
					'value' => $reporting->get_view($checkbox_view).ucfirst(__("operation")),
				),
			)
		);

		return $grid;
	}

	function show_form_body($activities_id = 1, $date_info = array()) {
		$from = $date_info['start'];
		$to = $date_info['stop'];
		$this->filter_with(array('activities_id' => $activities_id));
		$this->activities_id = $activities_id;

		$reportings = $this->get_reportings_by_sort();
		$form = array();

		$periods = $this->get_periods($from, $to);
		$period = $periods['n'];

		foreach ($reportings as $reporting) {
			$check = "";
			($reporting->base == "1") ? $class = " base_reporting" : $class = "";
			($reporting->parent != 0) ? $parent = $reporting->parent : $parent = "";

			if (isset($_SESSION['checkbox_reporting'][$reporting->id]) && $_SESSION['checkbox_reporting'][$reporting->id] == "true") {
				$check = "checked";
			}
			$checker = new Html_Checkbox("checkbox_reporting[".$reporting->id."][checked]", $reporting->id);
			$checkbox_view = new Html_Checkbox("checkbox_view", $reporting->id, $check);
			$checkbox_view->properties = array('class' => "checkbox_styled", 'more' => __("more"), 'less' => __("less"));

			if ($reporting->is_recently_modified()) {
				$class .= " modified";
			}

			$form[md5($reporting->id)] = array(
				'class' => "reporting droppable actionnable ".$class,
				'level' => $reporting->level,
				'parent' => $reporting->parent,
				'style' => "text-align: center;",
				'name' => "reporting",
				'id' => "reporting_".$reporting->id,
				'value' => $reporting->id,
				'cells' => array(
					array(
						'type' => "td",
						'class' => "table_checkbox_reporting",
						'value' => $checker->item("")
						),
					array(
						'type' => "td",
						'class' => "reporting_name_".$reporting->id,
						'style' => "text-align: left; padding-left: 10px;",
						'value' => $reporting->name
						),
					array(
						'type' => "td",
						'colspan' => 1,
						'value' => "---"
						),
					array(
						'type' => "td",
						'colspan' => 1,
						'value' => "---"
						),
					array(
						'type' => "td",
						'colspan' => 1,
						'value' => "---"
						),
					array(
						'type' => "td",
						'class' => "op",
						'style' => "text-align: left; width: 100px;",
						'value' => $reporting->get_view($checkbox_view).$reporting->get_form_modify_reporting($reporting->id).
									$reporting->get_form_add_reporting($reporting->id).$reporting->get_form_delete_reporting($reporting->id)
						)
					));

			$affectations = new Accounting_Codes_Affectation();
			$affectations = $affectations->order_by_number($reporting->id);
			
			foreach ($affectations as $affectation) {
				$code = new Accounting_Code();
				$code->load(array('id' => $affectation->accountingcodes_id));

				$balance = new Balance();
				$balance->load(array('accountingcodes_id' => $code->id, 'period_id' => $period->id));

				$checker = new Html_Checkbox("checkbox_accountingcode[".$code->id."][checked]", $code->id);
				$checker->properties['data'] = $reporting->id;
				$class = "";
				if ($affectation->is_recently_modified()) {
					$class = "modified";
				}

				$form[sha1($code->id)] = array(
					'class' => "accountingcode actionnable draggable ".$class,
					'value' => $code->id,
					'data' => $reporting->id,
					'name' => "account_".$reporting->id,
					'cells' => array(
						array(
							'type' => "td",
							'colspan' => 1
						),
						array(
							'type' => "td",
							'style' => "text-align: right;",
							'class' => "table_checkbox_accountingcode column",
							'value' => $checker->input()
						),
						array(
							'type' => "td",
							'class' => "column",
							'value' => $code->number
						),
						array(
							'type' => "td",
							'class' => "column",
							'value' => $code->name
						),
						array(
							'type' => "td",
							'class' => "column",
							'style' => "text-align: right; white-space: nowrap",
							'value' => number_adjust_format($balance->amount).$GLOBALS['param']['currency']
						),
						array(
							'type' => "td",
							'style' => "position: relative; left: 12px;",
							'value' => $reporting->get_form_modify_accountingcode($code->id, $reporting->id).
										$reporting->get_form_delete_accountingcode($code->id, $reporting->id)
						)
					)
				);
			}

			$options = array(
				"none" => "--",
				"include" => ucfirst(__("change to")),
				"delete" => ucfirst(__("delete")),
			);

			$select = new Html_Select("action", $options, "none");
			$includeinto = new Html_Select("include_into_reporting", $reporting->form_include_accountingcode(), $reporting->id);
			$includeinto->properties['class'] = "include_into_reporting_".$reporting->id;
			$checkbox = new Html_Checkbox("checkbox_all_accountingcode", $reporting->id);
			$checkbox->properties['style'] = "position: relative; right: 38px;";
			$submit = new Html_Input("submit", __('ok'), "submit");

			$form[$reporting->id] = array(
				'data' => $reporting->id,
				'name' => "account_".$reporting->id,
				'style' => "position: relative;",
				'cells' => array(
					array(
						'type' => "td",
						'colspan' => 2
					),
					array(
						'type' => "td",
						'colspan' => 4,
						'style' => "text-align: left;",
						'value' => "<form method=\"post\" action=\"\" name=\"form_accountingcode\">".
									$checkbox->input().$select->item("").$includeinto->item("").$submit->input().
								"</form>"
					),
					array(
						'type' => "td",
						'colspan' => 4
					)
				)
			);
		}

		$options = array(
			"none" => "--",
			"desaffect" => ucfirst(__('reset accounting codes')),
			"desaffect_in_cascade" => ucfirst(__('reset accounting codes in cascade')),
			"delete" => ucfirst(__('delete')),
		);

		$select = new Html_Select("action", $options, "none");
		$select->properties['style'] = "width: 200px; white-space: pre-wrap;";
		$submit = new Html_Input("submit", __('ok'), "submit");

		$checkbox = new Html_Checkbox("checkbox_all_reporting", "check");
		$checkbox->properties['style'] = "position: relative; left: 9px;";
		$form[uniqid()] = array(
				'cells' => array(
					array(
						'type' => "th",
						'value' => $checkbox->item("")
						),
					array(
						'type' => "th",
						'value' => "<form method=\"post\" action=\"\" name=\"form_reporting\">".$select->item("").$submit->input()."</form>"
					),
					array(
						'type' => 'th',
						'colspan' => 4
						)
					));

		return $form;
	}

	function show_form_delimiter() {
		$form = array();
		$form[] = array(
			'style' => 'background-color: #eee;
						background-image: repeating-linear-gradient(50deg, transparent, transparent 2px, rgba(255,255,255,255) 2px, rgba(255,255,255,255) 4px);',
			'cells' => array(
					array(
						'type' => 'td',
						'style' => 'height: 20px;',
						'colspan' => 6
						)
			));

		return $form;
	}

	function show_form_other($activities_id = 1, $date_info = array()) {
		$periods = $this->get_periods($date_info['start'], $date_info['stop']);
		$period = $periods['n'];

		$balances = new Balances();
		$other = $balances->get_accoutingcode_affectable();
		asort($other);
		$form = array();

		if (count($other) > 0) {
			$form[uniqid()] = array(
				'id' => 'other_accountingcodes',
				'style' => 'background-color: #d75a62;',
				'level' => 0,
				'value' => 0,
				'cells' => array(
					array(
						'type' => 'td',
						'colspan' => 2,
						'style' => 'font-weight: bold;',
						'value' => ucfirst(__('non affected'))
						),
					array(
						'type' => 'td',
						'colspan' => 4
						),
					));

			foreach ($other as $id => $d) {
				$reporting = new Reporting();

				$code = new Accounting_Code();
				$code->load(array('id' => $id));

				$balance = new Balance();
				$balance->load(array('accountingcodes_id' => $code->id, 'period_id' => $period->id));

				$checker = new Html_Checkbox("checkbox_accountingcode[".$id."][checked]", $id);
				$checker->properties['data'] = "0";
				$form[md5($id)] = array(
					'data' => 0,
					'value' => $id,
					'name' => 'account_0',
					'class' => 'draggable non_affected',
					'style' => 'position: relative;',
					'cells' => array(
						array(
							'type' => 'td',
							'colspan' => 1
						),
						array(
							'type' => 'td',
							'class' => "table_checkbox_accountingcode",
							'style' => 'text-align: right;',
							'value' => $checker->input()
						),
						array(
							'type' => 'td',
							'value' => $d['number']
						),
						array(
							'type' => 'td',
							'value' => $d['name']
						),
						array(
							'type' => 'td',
							'style' => 'text-align: right;',
							'value' => number_adjust_format($balance->amount).$GLOBALS['param']['currency']
						),
						array(
							'type' => 'td',
							'style' => 'position: relative; left: 10px;',
							'value' => $reporting->get_form_modify_accountingcode_non_affected($id)
						)
					)
				);
			}

			$options = array(
				"none" => "--",
				"reaffect" => ucfirst(__("reaffect by default")),
				"include" => ucfirst(__("included in")),
			);

			$reporting = new Reporting();

			$includeinto = new Html_Select("include_into_reporting", $reporting->form_include_accountingcode());
			$includeinto->properties['class'] = "include_into_reporting_0";
			$includeinto->properties["tip"] = true;
			$select = new Html_Select("action", $options, "none");
			$checkbox = new Html_Checkbox("checkbox_all_accountingcode", "0");
			$checkbox->properties['style'] = "position: relative; right: 38px;";
			$submit = new Html_Input("submit", __("ok"), "submit");
			$form[$id] = array(
				'data' => 0,
				'name' => "account_0",
				'class' => "accoungtingcode actionnable",
				'style' => "position: relative",
				'cells' => array(
					array(
						'type' => "td",
						'colspan' => 2
					),
					array(
						'type' => "td",
						'colspan' => 4,
						'style' => "text-align: left;",
						'value' => "<form method=\"post\" action=\"\" name=\"form_accountingcode\">".
								$checkbox->input().$select->item("").$includeinto->item("").$submit->input().
							"</form>"
					)
				)
			);
		}

		return $form;
	}

	function determine_year($timestamp = null) {
		if ($timestamp == null) {
			$timestamp = time();
		}
		list($start, $stop) = determine_fiscal_year($timestamp);
		return array('start' => $start, 'stop' => $stop);
	}

	function display_reportings_detail($period_option, $start) {
		$reporting = new Reporting();
		$year = $this->determine_year($start);
		return "<center><div id=\"master_reportings\">".$this->show_view($period_option, $year['start'], $year['stop'])."</div></center>";
	}

	function show_view($period_option, $start, $stop) {
		$tables = "";
		$activities = new Activities();
		$activities->add_order("global");
		$activities->select();

		$i = 0;
		$_SESSION['global_ca'] = array('n' => 0, 'n-1' => 0, 'n-2' => 0);
		$_SESSION['global_result'] = array('n' => 0, 'n-1' => 0, 'n-2' => 0);
		foreach ($activities as $activity) {
			$i++;
			$view = $this->show_view_header($activity->name, $start, $stop) + $this->show_view_body($period_option, $activity, $start, $stop) + $this->show_view_bottom($start, $stop);
			$html_table = new Html_Table(array('lines' => $view));
			$tables .= $html_table->show();
			if ($i < count($activities)) {
				$tables .= "<div class=\"master_reportings_delimiter\"></div>";
			}
		}
		unset($_SESSION['global_ca']);
		unset($_SESSION['global_result']);

		return $tables;
	}

	function show_view_header($activities_name, $start, $stop) {
		$reporting = new Reporting();
		$grid = array();
		$grid[uniqid()] = array(
			'class' => "header",
			'style' => "border: 1px solid #ddd;",
			'cells' => array(
				array(
				'type' => "th",
				'colspan' => 13,
				'style' => "text-align: center; border-bottom: 1px solid #ddd;",
				'value' => "<h3>".$activities_name."</h3>"
					)
				));
		$periods = $this->get_periods($start, $stop);

		$checkbox_view = new Html_Checkbox("checkbox_all_view", "check");
		$checkbox_view->properties = array('class' => "checkbox_styled", 'more' => __("more"), 'less' => __("less"));

		$years = array(
			'n' => ($periods['n']->start == 0 or $periods['n']->stop == 0) ? ucfirst(__("made")) : ucfirst(__("made")).": <br>".__("from"). " ".date("d/m/Y", $periods['n']->start)." ".__("to")." ".date("d/m/Y", $periods['n']->stop),
			'n-1' => ($periods['n-1']->start == 0 or $periods['n-1']->stop == 0) ? "N-1" : "N-1: <br>".__("from"). " ".date("d/m/Y", $periods['n-1']->start)." ".__("to")." ".date("d/m/Y", $periods['n-1']->stop),
			'n-2' => ($periods['n-2']->start == 0 or $periods['n-2']->stop == 0) ? "N-2" : "N-2: <br>".__("from"). " ".date("d/m/Y", $periods['n-2']->start)." ".__("to")." ".date("d/m/Y", $periods['n-2']->stop)
		);

		$grid[uniqid()] = array(
			'class' => "header",
			'cells' => array(
				array(
					'type' => "th",
					'style' => "border: 1px solid #ddd;",
					'class' => "table_checkbox",
					'value' => $reporting->get_view($checkbox_view)
					),
				array(
					'type' => "th",
					'value' => ucfirst(__("accounting code"))
					),
				array(
					'type' => "th",
					'value' => ucfirst(__("name"))
					),
				array(
					'type' => "th",
					'colspan' => 2,
					'value' => $years['n']
					),
				array(
					'type' => "th",
					'colspan' => 2,
					'value' => $years['n-1']
					),
				array(
					'type' => "th",
					'colspan' => 2,
					'value' => $years['n-2']
					),
				array(
					'type' => "th",
					'colspan' => 2,
					'value' => ucfirst(__("difference"))." N & N-1"
					),
				array(
					'type' => "th",
					'colspan' => 2,
					'value' => ucfirst(__("difference"))." N-1 & N-2"
					)
				));

		return $grid;
	}

	function show_view_body($period_option, $activity, $start, $stop) {
		$activities = new Activities();
		$activities->select();

		$form = array();
		$this->filter_with(array('activities_id' => $activity->id));
		$grid = $this->get_grid();

		$periods = $this->get_periods($start, $stop);

		if ($activity->global == 1 and count($activities) > 1) {
			$previous_amount = [
				'n' => $_SESSION['global_result']['n'],
				'n-1' => $_SESSION['global_result']['n-1'],
				'n-2' => $_SESSION['global_result']['n-2'],
			];
			$amount_base = [
				'n' => $_SESSION['global_ca']['n'],
				'n-1' => $_SESSION['global_ca']['n-1'],
				'n-2' => $_SESSION['global_ca']['n-2'],
			];
			$form[uniqid()] = $this->show_view_global($period_option, $start, $stop);
		} else {
			$previous_amount = [
				'n' => "",
				'n-1' => "",
				'n-2' => ""
			];
			$amount_base = [
				'n' => "",
				'n-1' => "",
				'n-2' => ""
			];
		}

		foreach ($grid as $id => $data) {
			$amount = array(
				'n' => !$this->is_empty($start, $stop) ? $this->reporting_amount_by_year($id, $periods['n']) : "", 
				'n-1' => !$this->is_empty(strtotime("-1 year", $start), strtotime("-1 year", $stop)) ? $this->reporting_amount_by_year($id, $periods['n-1']) : "",
				'n-2' => !$this->is_empty(strtotime("-2 years", $start), strtotime("-2 years", $stop)) ? $this->reporting_amount_by_year($id, $periods['n-2']): "",
				'ecart' => "",
				'ecart2' => ""
			);

			$class = "reporting";
			if ($data['base'] == 1) {
				$_SESSION['global_ca']['n'] += $amount['n'];
				$_SESSION['global_ca']['n-1'] += $amount['n-1'];
				$_SESSION['global_ca']['n-2'] += $amount['n-2'];
				$amount_base = $amount;
				$class = "base_reporting";
			}

			$sum = $amount['n'] + $amount['n-1'] + $amount['n-2'] + $amount['ecart'] + $amount['ecart2'];

			if ($data['level'] == 0 and $sum == 0) {
				$_SESSION['global_result']['n'] += $amount['n'];
				$_SESSION['global_result']['n-1'] += $amount['n-1'];
				$_SESSION['global_result']['n-2'] += $amount['n-2'];
				!$this->is_empty($start, $stop) ? $amount['n'] += $previous_amount['n'] : "";
				!$this->is_empty(strtotime("-1 year", $start), strtotime("-1 year", $stop)) ? $amount['n-1'] += $previous_amount['n-1'] : "";
				!$this->is_empty(strtotime("-2 years", $start), strtotime("-2 years", $stop)) ? $amount['n-2'] += $previous_amount['n-2'] : "";
				$previous_amount = array('n' => $amount['n'], 'n-1' => $amount['n-1'], 'n-2' => $amount['n-2']);
			}

			if ($period_option == "variable") {
				$amount['ecart'] = !$this->verify_empty_year($start, $stop) ? ($amount['n'] * (12 / month_from_timestamp($periods['n']->start, $periods['n']->stop))) - ($amount['n-1'] * (12 / month_from_timestamp($periods['n-1']->start, $periods['n-1']->stop))) : "";
				$amount['ecart2'] = !$this->verify_empty_year(strtotime("-1 year", $start), strtotime("-1 year", $stop)) ? ($amount['n-1'] * (12 / month_from_timestamp($periods['n-1']->start, $periods['n-1']->stop))) - ($amount['n-2'] * (12 / month_from_timestamp($periods['n-2']->start, $periods['n-2']->stop))): "";
			} else {
				$amount['ecart'] = !$this->verify_empty_year($start, $stop) ? ($amount['n'] - $amount['n-1']) : "";
				$amount['ecart2'] = !$this->verify_empty_year(strtotime("-1 year", $start), strtotime("-1 year", $stop)) ? $amount['ecart2'] = $amount['n-1'] - $amount['n-2'] : "";
			}

			$ratio = array(
				'n' => pourcentage($amount['n'], abs($amount_base['n']), 3),
				'n-1' => pourcentage($amount['n-1'], abs($amount_base['n-1']), 3),
				'n-2' => pourcentage($amount['n-2'], abs($amount_base['n-2']), 3),
				'ecart' => pourcentage($amount['ecart'], abs($amount['n-1']), 3),
				'ecart2' => pourcentage($amount['ecart2'], abs($amount['n-2']), 3)
			);

			$color1 = "";
			$color2 = "";
			if ($ratio['ecart'] > 0) {
				$color1 = "#2fa643;";
			} else if ($ratio['ecart'] < 0) {
				$color1 = "#d75a62;";
			}
			if ($ratio['ecart2'] > 0) {
				$color2 = "#2fa643;";
			} else if ($ratio['ecart2'] < 0) {
				$color2 = "#d75a62;";
			}

			($data['parent'] != "0") ? $parent = $data['parent'] : $parent = "";
			(isset($_SESSION['checkbox_reporting'][$id]) and $_SESSION['checkbox_reporting'][$id] == "true") ? $check = "checked" : $check = "";
			$checkbox = new Html_Checkbox("checkbox_reporting", $id, $check);
			$checkbox->properties = array('class' => "checkbox_styled", 'more' => __("more"), 'less' => __("less"));
			
			$form[md5($id)] = array(
				'class' => $class,
				'parent' => $parent,
				'level' => $data['level'],
				'value' => $id,
				'id' => "reporting_".$id,
				'cells' => array(
					array(
						'type' => "td",
						'class' => "table_checkbox",
						'value' => $checkbox->item("")."<span class=\"acronym\"></span>"
					),
					array(
						'type' => "td",
						'style' => "width: 50px; text-align: center;",
						'value' => "---"
					),
					array(
						'type' => "td",
						'style' => "width: 100px;",
						'value' => $data['name']
					),
					array(
						'type' => "td",
						'class' => "classic-col",
						'style' => "white-space: nowrap",
						'value' => number_adjust_format($amount['n']).currency_if_exists($amount['n'])
					),
					array(
						'type' => "td",
						'class' => "ratio-col",
						'style' => "white-space: nowrap;",
						'value' => number_adjust_format($ratio['n']).ratio_if_exists($ratio['n'])
					),
					array(
						'type' => "td",
						'class' => "classic-col",
						'style' => "white-space: nowrap",
						'value' => number_adjust_format($amount['n-1']).currency_if_exists($amount['n-1'])
					),
					array(
						'type' => "td",
						'class' => "ratio-col",
						'style' => "white-space: nowrap;",
						'value' => number_adjust_format($ratio['n-1']).ratio_if_exists($ratio['n-1'])
					),
					array(
						'type' => "td",
						'class' => "classic-col",
						'style' => "white-space: nowrap",
						'value' => number_adjust_format($amount['n-2']).currency_if_exists($amount['n-2'])
					),
					array(
						'type' => "td",
						'class' => "ratio-col",
						'style' => "white-space: nowrap;",
						'value' => number_adjust_format($ratio['n-2']).ratio_if_exists($ratio['n-2'])
					),
					array(
						'type' => "td",
						'class' => "classic-col",
						'style' => "white-space: nowrap; color: ".$color1,
						'value' => number_difference(number_adjust_format($amount['ecart'])).currency_if_exists($amount['ecart'])
					),
					array(
						'type' => "td",
						'class' => "ratio-col",
						'style' => "white-space: nowrap; color: ".$color1,
						'value' => number_difference(number_adjust_format($ratio['ecart'])).ratio_if_exists($ratio['ecart'])
					),
					array(
						'type' => "td",
						'class' => "classic-col",
						'style' => "white-space: nowrap; color: ".$color2,
						'value' => number_difference(number_adjust_format($amount['ecart2'])).currency_if_exists($amount['ecart2'])
					),
					array(
						'type' => "td",
						'class' => "ratio-col",
						'style' => "white-space: nowrap; color: ".$color2,
						'value' => number_difference(number_adjust_format($ratio['ecart2'])).ratio_if_exists($ratio['ecart2'])
					)
			));
			unset($amount);
			unset($ratio);

			$affectations = new Accounting_Codes_Affectation();
			$affectations = $affectations->order_by_number($id);
			foreach ($affectations as $affectation) {
				$code = new Accounting_Code();
				$code->load(array('id' => $affectation->accountingcodes_id));
				
				$amount = array(
					'n' => $this->accountingcode_amount_by_year($code->id, $periods['n']), 
					'n-1' => $this->accountingcode_amount_by_year($code->id, $periods['n-1']),
					'n-2' => $this->accountingcode_amount_by_year($code->id, $periods['n-2']),
				);

				if ($period_option == "variable") {
					$amount['ecart'] = (is_numeric($amount['n']) and is_numeric($amount['n-1'])) ? $amount['ecart'] = ($amount['n'] * (12 / month_from_timestamp($periods['n']->start, $periods['n']->stop))) - ($amount['n-1'] * (12 / month_from_timestamp($periods['n-1']->start, $periods['n-1']->stop))) : "";
					$amount['ecart2'] = (is_numeric($amount['n-1']) and is_numeric($amount['n-2'])) ? $amount['ecart2'] = ($amount['n-1'] * (12 / month_from_timestamp($periods['n-1']->start, $periods['n-1']->stop))) - ($amount['n-2'] * (12 / month_from_timestamp($periods['n-2']->start, $periods['n-2']->stop))): "";
				} else {
					$amount['ecart'] = (is_numeric($amount['n']) and is_numeric($amount['n-1'])) ? $amount['ecart'] = $amount['n'] - $amount['n-1'] : "";
					$amount['ecart2'] = (is_numeric($amount['n-1']) and is_numeric($amount['n-2'])) ? $amount['ecart2'] = $amount['n-1'] - $amount['n-2'] : "";
				}

				$ratio = array(
					'n' => pourcentage($amount['n'], $amount_base['n'], 3),
					'n-1' => pourcentage($amount['n-1'], $amount_base['n-1'], 3),
					'n-2' => pourcentage($amount['n-2'], $amount_base['n-2'], 3),
					'ecart' => pourcentage($amount['ecart'], abs($amount['n-1']), 3),
					'ecart2' => pourcentage($amount['ecart2'], abs($amount['n-2']), 3)
				);

				$color1 = "";
				$color2 = "";
				if ($ratio['ecart'] > 0) {
					$color1 = "#2fa643;";
				} else if ($ratio['ecart'] < 0) {
					$color1 = "#d75a62;";
				}
				if ($ratio['ecart2'] > 0) {
					$color2 = "#2fa643;";
				} else if ($ratio['ecart2'] < 0) {
					$color2 = "#d75a62;";
				}

				$form[sha1($code->id)] = array(
					'class' => "sub_table",
					'name' => "sub_table_".$id,
					'cells' => array(
						array(
							'type' => "td",
							'class' => "table_checkbox",
							'colspan' => 1
						),
						array(
							'type' => "td",
							'class' => "number",
							'value' => $code->number
						),
						array(
							'type' => "td",
							'style' => "width: 100px;",
							'value' => mb_strtoupper($code->name)
						),
						array(
							'type' => "td",
							'class' => "classic-col",
							'style' => "white-space: nowrap",
							'value' => number_adjust_format($amount['n']).currency_if_exists($amount['n'])
						),
						array(
							'type' => "td",
							'class' => "ratio-col",
							'style' => "white-space: nowrap;",
							'value' => number_adjust_format($ratio['n']).ratio_if_exists($ratio['n'])
						),
						array(
							'type' => "td",
							'class' => "classic-col",
							'style' => "white-space: nowrap",
							'value' => number_adjust_format($amount['n-1']).currency_if_exists($amount['n-1'])
						),
						array(
							'type' => "td",
							'class' => "ratio-col",
							'style' => "white-space: nowrap;",
							'value' => number_adjust_format($ratio['n-1']).ratio_if_exists($ratio['n-1'])
						),
						array(
							'type' => "td",
							'class' => "classic-col",
							'style' => "white-space: nowrap",
							'value' => number_adjust_format($amount['n-2']).currency_if_exists($amount['n-2'])
						),
						array(
							'type' => "td",
							'class' => "ratio-col",
							'style' => "white-space: nowrap;",
							'value' => number_adjust_format($ratio['n-2']).ratio_if_exists($ratio['n-2'])
						),
						array(
							'type' => "td",
							'class' => "classic-col",
							'style' => "white-space: nowrap; color: ".$color1,
							'value' => number_difference(number_adjust_format($amount['ecart'])).currency_if_exists($amount['ecart'])
						),
						array(
							'type' => "td",
							'class' => "ratio-col;",
							'style' => "white-space: nowrap; color: ".$color1,
							'value' => number_difference(number_adjust_format($ratio['ecart'])).ratio_if_exists($ratio['ecart'])
						),
						array(
							'type' => "td",
							'class' => "classic-col",
							'style' => "white-space: nowrap; color: ".$color2,
							'value' => number_difference(number_adjust_format($amount['ecart2'])).currency_if_exists($amount['ecart2'])
						),
						array(
							'type' => "td",
							'class' => "ratio-col",
							'style' => "white-space: nowrap; color: ".$color2,
							'value' => number_difference(number_adjust_format($ratio['ecart2'])).ratio_if_exists($ratio['ecart2'])
						)
				));
				unset($amount);
				unset($ratio);
			}
		}

		return $form;
	}

	function show_view_global($period_option, $start, $stop) {
		$amount = array(
			'n' => !$this->is_empty($start, $stop) ? floatval($_SESSION['global_ca']['n']) : "",
			'n-1' => !$this->is_empty(strtotime("-1 year", $start), strtotime("-1 year", $stop)) ? floatval($_SESSION['global_ca']['n-1']) : "",
			'n-2' => !$this->is_empty(strtotime("-2 years", $start), strtotime("-2 years", $stop)) ? floatval($_SESSION['global_ca']['n-2']) : "",
			'ecart' => "",
			'ecart2' => ""
		);

		$periods = $this->get_periods($start, $stop);

		if ($period_option == "variable") {
			$amount['ecart'] = (is_numeric($amount['n']) and is_numeric($amount['n-1'])) ? $amount['ecart'] = ($amount['n'] * (12 / month_from_timestamp($periods['n']->start, $periods['n']->stop))) - ($amount['n-1'] * (12 / month_from_timestamp($periods['n-1']->start, $periods['n-1']->stop))) : "";
			$amount['ecart2'] = (is_numeric($amount['n-1']) and is_numeric($amount['n-2'])) ? $amount['ecart2'] = ($amount['n-1'] * (12 / month_from_timestamp($periods['n-1']->start, $periods['n-1']->stop))) - ($amount['n-2'] * (12 / month_from_timestamp($periods['n-2']->start, $periods['n-2']->stop))): "";
		} else {
			$amount['ecart'] = (is_numeric($amount['n']) and is_numeric($amount['n-1'])) ? $amount['ecart'] = $amount['n'] - $amount['n-1'] : "";
			$amount['ecart2'] = (is_numeric($amount['n-1']) and is_numeric($amount['n-2'])) ? $amount['ecart2'] = $amount['n-1'] - $amount['n-2'] : "";
		}

		$ratio = array(
			'n' => is_numeric($amount['n']) ? "100" : "",
			'n-1' => is_numeric($amount['n-1']) ? "100" : "",
			'n-2' => is_numeric($amount['n-2']) ? "100" : "",
			'ecart' => pourcentage($amount['ecart'], abs($_SESSION['global_ca']['n-1']), 3),
			'ecart2' => pourcentage($amount['ecart2'], abs($_SESSION['global_ca']['n-2']), 3)
		);

		$color1 = "";
		$color2 = "";
		if ($ratio['ecart'] > 0) {
			$color1 = "#2fa643;";
		} else if ($ratio['ecart'] < 0) {
			$color1 = "#d75a62;";
		}
		if ($ratio['ecart2'] > 0) {
			$color2 = "#2fa643;";
		} else if ($ratio['ecart2'] < 0) {
			$color2 = "#d75a62;";
		}

		return array(
				'class' => "base_reporting",
				'level' => "0",
				'cells' => array(
					array(
						'type' => "td",
						'style' => "border: none;",
						'colspan' => 1
					),
					array(
						'type' => "td",
						'style' => "width: 50px; text-align: center;",
						'value' => "---"
					),
					array(
						'type' => "td",
						'style' => "width: 100px;",
						'value' => ucfirst(__("total turnover"))
					),
					array(
						'type' => "td",
						'class' => "classic-col",
						'style' => "white-space: nowrap",
						'value' => number_adjust_format($amount['n']).currency_if_exists($amount['n'])
					),
					array(
						'type' => "td",
						'class' => "ratio-col",
						'value' => $ratio['n'].ratio_if_exists($ratio['n'])
					),
					array(
						'type' => "td",
						'class' => "classic-col",
						'style' => "white-space: nowrap",
						'value' => number_adjust_format($amount['n-1']).currency_if_exists($amount['n-1'])
					),
					array(
						'type' => "td",
						'class' => "ratio-col",
						'value' => $ratio['n-1'].ratio_if_exists($ratio['n-1'])
					),
					array(
						'type' => "td",
						'class' => "classic-col",
						'style' => "white-space: nowrap",
						'value' => number_adjust_format($amount['n-2']).currency_if_exists($amount['n-2'])
					),
					array(
						'type' => "td",
						'class' => "ratio-col",
						'value' => $ratio['n-2'].ratio_if_exists($ratio['n-2'])
					),
					array(
						'type' => "td",
						'class' => "classic-col",
						'style' => "white-space: nowrap; color: ".$color1,
						'value' => number_difference(number_adjust_format($amount['ecart'])).currency_if_exists($amount['ecart'])
					),
					array(
						'type' => "td",
						'class' => "ratio-col",
						'style' => "white-space: nowrap; color: ".$color1,
						'value' => number_difference(number_adjust_format($ratio['ecart'])).ratio_if_exists($ratio['ecart'])
					),
					array(
						'type' => "td",
						'class' => "classic-col",
						'style' => "white-space: nowrap; color: ".$color2,
						'value' => number_difference(number_adjust_format($amount['ecart2'])).currency_if_exists($amount['ecart2'])
					),
					array(
						'type' => "td",
						'class' => "ratio-col",
						'style' => "white-space: nowrap; color: ".$color2,
						'value' => number_difference(number_adjust_format($ratio['ecart2'])).ratio_if_exists($ratio['ecart2'])
					)
			));
		unset($_SESSION['global_ca']);
	}

	function show_view_bottom($start, $stop) {
		$reporting = new Reporting();
		$checkbox_view = new Html_Checkbox("checkbox_all_view", "check");
		$checkbox_view->properties = array('class' => "checkbox_styled", 'more' => __("more"), 'less' => __("less"));

		$periods = $this->get_periods($start, $stop);

		$years = array(
			'n' => ($periods['n']->start == 0 or $periods['n']->stop == 0) ? "" : __("span at import").": <strong>".month_from_timestamp($periods['n']->start, $periods['n']->stop).__("month")."</strong>",
			'n-1' => ($periods['n-1']->start == 0 or $periods['n-1']->stop == 0) ? "" : __("span at import").": <strong>".month_from_timestamp($periods['n-1']->start, $periods['n-1']->stop).__("month")."</strong>",
			'n-2' => ($periods['n-2']->start == 0 or $periods['n-2']->stop == 0) ? "" : __("span at import").": <strong>".month_from_timestamp($periods['n-2']->start, $periods['n-2']->stop).__("month")."</strong>"
		);

		$grid = array(
			'cells' => array(
				array(
					'type' => "th",
					'style' => "border: 1px solid #ddd;",
					'class' => "table_checkbox",
					'value' => $reporting->get_view($checkbox_view)
				),
				array(
					'type' => "th",
					'colspan' => 2
				),
				array(
					'type' => "th",
					'colspan' => 2,
					'value' => $years['n']
				),
				array(
					'type' => "th",
					'colspan' => 2,
					'value' => $years['n-1']
				),
				array(
					'type' => "th",
					'colspan' => 2,
					'value' => $years['n-2']
				),
				array(
					'type' => "th",
					'colspan' => 4
				)
		));

		return $grid;
	}

	function is_empty($from, $to) {
		$balances = new Balances();
		$balances->filter_with(array('start' => $from, 'stop' => $to));
		$balances->select();

		if (count($balances) == 0) {
			return true;
		} else {
			return false;
		}
	}

	function verify_empty_year($from, $to) {
		$balances = new Balances();
		$balances->filter_with(array('start' => $from, 'stop' => $to));
		$balances->select();

		if (count($balances) == 0) {
			return true;
		}

		$balances->filter_with(array('start' => strtotime("-1 year", $from), 'stop' => strtotime("-1 year", $to)));
		$balances->select();

		if (count($balances) == 0) {
			return true;
		} else {
			return false;
		}
	}

	function get_periods($start, $stop) {
		$periods = array();

		$balances = new Balances;
		$balances->filter_with(array('start' => $start, 'stop' => $stop));
		$balances->select();
		$balance_year_one = $balances->current() != false ? $balances->current() : new Balance();

		$balances->filter_with(array('start' => strtotime("-1 year", $start), 'stop' => strtotime("-1 year", $stop)));
		$balances->select();
		$balance_year_two = $balances->current() != false ? $balances->current() : new Balance();

		$balances->filter_with(array('start' => strtotime("-2 years", $start), 'stop' => strtotime("-2 years", $stop)));
		$balances->select();
		$balance_year_three = $balances->current() != false ? $balances->current() : new Balance();

		$period = new Balance_Period();
		$period->load(array('id' => $balance_year_one->period_id));
		$periods['n'] = $period;

		$period = new Balance_Period();
		$period->load(array('id' => $balance_year_two->period_id));
		$periods['n-1'] = $period;

		$period = new Balance_Period();
		$period->load(array('id' => $balance_year_three->period_id));
		$periods['n-2'] = $period;
		
		return $periods;
	}

	function reporting_amount_by_year($id, $period) {
		$affectations = new Accounting_Codes_Affectation();
		$affectations->filter_with(array('reportings_id' => $id));
		$affectations->select();

		$amount = 0;
		foreach ($affectations as $affectation) {
			$balance = new Balance();
			$balance->load(array('accountingcodes_id' => $affectation->accountingcodes_id, 'period_id' => $period->id));
			$amount += $balance->amount;
		}

		$reportings = new Reportings();
		$reportings->filter_with(array('reportings_id' => $id));
		$reportings->select();

		foreach ($reportings as $reporting) {
			$amount += $this->reporting_amount_by_year($reporting->id, $period);
		}

		return $amount;
	}

	function accountingcode_amount_by_year($accountingcode_id, $period) {
		$balance = new Balance();
		$balance->load(array('accountingcodes_id' => $accountingcode_id, 'period_id' => $period->id));
		
		return $balance->id > 0 ? $balance->amount : "";
	}
}
