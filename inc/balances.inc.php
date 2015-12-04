<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

class Balances extends Collector  {
	public $filters = null;
	
	function __construct($class = null, $table = null, $db = null) {
		if ($class === null) {
			$class = substr(__CLASS__, 0, -1);
		}
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_balances'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}

	function delete() {
		foreach ($this as $balance) {
			$balance->delete();
		}
	}

	function get_where() {
		$query_where = parent::get_where();
		
		if (isset($this->id) and !empty($this->id)) {
			if (!is_array($this->id)) {
				$this->id = array((int)$this->id);
			}
			$query_where[] = $this->db->config['table_balances'].".id IN ".array_2_list($this->id);
		}
		if (isset($this->filters['search']) and !empty($this->filters['search'])) {
			$query_where[] = "(".$this->db->config['table_balances'].".number LIKE '%".$this->filters['search']."%' OR ".
								$this->db->config['table_balances'].".name LIKE '%".$this->filters['search']."%' OR ".
								$this->db->config['table_balances'].".amount LIKE '%".$this->filters['search']."%')";
		}
		if (isset($this->filters['start'])) {
			$query_where[] = $this->db->config['table_balances'].".day >= ".(int)$this->filters['start'];
		}
		if (isset($this->filters['stop'])) {
			$query_where[] = $this->db->config['table_balances'].".day <= ".(int)$this->filters['stop'];
		}
		if (isset($this->filters['accountingcodes_id'])) {
			if (!is_array($this->filters['accountingcodes_id'])) {
				$this->filters['accountingcodes_id'] = array((int)$this->filters['accountingcodes_id']);
			}
			$query_where[] = $this->db->config['table_balances'].".accountingcodes_id IN ".array_2_list($this->filters['accountingcodes_id']);
		}
		if (isset($this->filters['!accountingcodes_id'])) {
			if (!is_array($this->filters['!accountingcodes_id'])) {
				$this->filters['!accountingcodes_id'] = array((int)$this->filters['!accountingcodes_id']);
			}
			$query_where[] = $this->db->config['table_balances'].".accountingcodes_id NOT IN ".array_2_list($this->filters['!accountingcodes_id']);
		}
		if (isset($this->filters['amount'])) {
			$query_where[] = $this->db->config['table_balances'].".amount = ".(float)$this->filters['amount'];
		}
		if (isset($this->filters['number'])) {
			$query_where[] = $this->db->config['table_balances'].".number LIKE ".$this->db->quote("%".$this->filters['number']."%");
		}
		if (isset($this->filters['name'])) {
			$query_where[] = $this->db->config['table_balances'].".name LIKE ".$this->db->quote("%".$this->filters['name']."%");
		}
		if (isset($this->filters['parent_id'])) {
			$query_where[] = $this->db->config['table_balances'].".parent_id = ".$this->db->quote($this->filters['parent_id']);
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
	
	function get_accoutingcode_affectable() {
		$affectations = new Accounting_Codes_Affectation();
		$affectations->filter_with(array('reportings_id' => 0));
		$affectations->select();

		$data = array();
		foreach ($affectations as $affectation) {
			$code = new Accounting_Code();
			$code->load(array('id' => $affectation->accountingcodes_id));

			$balances = new Balances();
			$balances->filter_with(array('accountingcodes_id' => $code->id, 'start' => $_SESSION['filter']['start'], 'stop' => $_SESSION['filter']['stop']));
			$balances->select();
			$balances->current() != false ? $balance = $balances->current() : $balance = new Balance();  
			$data[$code->id] = array('number' => $code->number, 'name' => $code->name, 'amount' => $balance->amount);
		}
		return $data;
	}

	function adapt_string($data) {
		$form = "";
		foreach ($data as $value) {
			$form .= "- ".$value."<br>";
		}

		return $form;
	}

	function get_affectations_location($accountingcode_id) {
		$affectation = new Accounting_Code_Affectation();
		$affectation->load(array('accountingcodes_id' => $accountingcode_id));

		$reporting = new Reporting();
		$reporting->load(array('id' => $affectation->reportings_id));

		$activity = new Activity();
		$activity->load(array('id' => $reporting->activities_id));

		return array('reporting' => $reporting->name, 'activity' => $activity->name);
	}

	function sum($start, $stop) {
		$this->filter_with(array('start' => $start, 'stop' => $stop));
		$this->select();

		$amount = 0;
		foreach ($this as $balance) {
			$amount += $balance->amount;
		}

		return $amount;
	}

	function period($start, $stop) {
		$this->filter_with(array('start' => $start, 'stop' => $stop));
		$this->select();

		$period_ids = array();
		foreach ($this as $balance) {
			$period_ids[] = $balance->period_id;
		}

		return array_unique($period_ids);
	}

	function display() {
		$balance = new Balance();

		return "<div id=\"balance\">".
			$balance->form_balance($_SESSION['filter']['start']).$balance->get_form_add().$balance->form_filter()."
			<form method=\"post\" name=\"form_balance\" action=\"\">".$this->show()."</form>
		</div>";
	}

	function show() {
		$filter =  isset($this->filters['search']) ? $this->filters['search'] : "";
		$html_table = new Html_Table(array('lines' => $this->show_header() + $this->show_body() + $this->show_footer(), 'filter' => $filter));
		return $html_table->show();
	}

	function show_header() {
		$checkbox = new Html_Checkbox("checkbox_all_up", "check");
		$grid = array(
			'cells' => array(
				array(
					'type' => "th",
					'value' => $checkbox->item("")
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
					'value' => ucfirst(__("amount"))
					),
				array(
					'type' => "th",
					'value' => ucfirst(__("reporting"))
					),
				array(
					'type' => "th",
					'value' => ucfirst(__("activity"))
					),
				array(
					'type' => "th",
					'value' => ucfirst(__("date"))
					),
				array(
					'type' => "th",
					'value' => ucfirst(__("operation"))
					)
				));
		return $grid;
	}

	function show_body() {
		$grid = array();
		$this->add_order("number");
		$this->select();
		foreach ($this as $id => $balance) {
			$class = "";
			if ($balance->is_recently_modified()) {
				$class = "modified";
			}

			$child = new Balance();
			$child->parent_id = $balance->id;
			$child->load(array("parent_id" => $balance->id));

			$data = $this->get_affectations_location($balance->accountingcodes_id);

			$color = "#000";
			if ($data['reporting'] == "0" or $data['activity'] == "0") {
				$data['reporting'] = __("non affected");
				$data['activity'] = __("non affected");
				$color = "#d75a62";
			}

			$checker = new Html_Checkbox("checkbox_balance[".$balance->id."][checked]", $balance->id);
			$grid[$id] = array(
				'class' => $class,
				'style' => "color: ".$color,
				'cells' => array(
					array(
						'type' => "td",
						'value' => $checker->item("")
						),
					array(
						'type' => "td",
						'name' => "number",
						'style' => "width: 150px;",
						'value' => $balance->number
						),
					array(
						'type' => "td",
						'name' => "name",
						'style' => "width: 300px;",
						'value' => $balance->name
						),
					array(
						'type' => "td",
						'name' => "amount",
						'style' => "text-align: right; white-space: nowrap",
						'value' => number_adjust_format($balance->amount).$GLOBALS['param']['currency']
						),
					array(
						'type' => "td",
						'value' => $data['reporting']
						),
					array(
						'type' => "td",
						'value' => $data['activity']
						),
					array(
						'class' => "date",
						'type' => "td",
						'value' => date("d-m-Y", $balance->day)
						),
				));
			if ($balance->split != 1 and $child->id == 0) {
				$grid[$id]['cells'][] = array(
					'class' => "op",
					'type' => "td",
					'value' => $balance->get_form_modify().$balance->get_form_split().$balance->get_form_delete(),
				);
			} else {
				$grid[$id]['cells'][] = array(
					'class' => "op",
					'type' => "td",
					'value' => $balance->get_form_modify().$balance->get_form_merge().$balance->get_form_delete(),
				);
			}
		}
		return $grid;
	}

	function show_footer() {
		$reporting = new Reporting();

		$options = array(
			'none' => "--",
			'affected' => __("affected to"),
			'reaffect' => __("reaffect by default"),
			'split' => __("split at"),
			'delete only' => __("delete balance only"),
			'delete plus' => __("delete balance and its imported"),
		);

		$includeinto = new Html_Select("include", $reporting->form_include_accountingcode());
		$includeinto->properties['style'] = "display: none;";

		$ratio = new Html_Input("ratio_input", "0", "number");
		$ratio->properties['style'] = "width: 50px; display: none;";
		$label = "<span class=\"ratio_label\" style=\"display: none\">%</span>";

		$checkbox = new Html_Checkbox("checkbox_all_down", "check");
		$select = new Html_Select("action", $options, "none");
		$select->properties['style'] = "width: 150px; white-space: pre-wrap;";
		$submit = new Html_Input("submit", __('ok'), "submit");
		$grid[uniqid()] = array(
			'cells' => array(
				array(
					'type' => "th",
					'value' => $checkbox->item("")
					),
				array(
					'type' => "th",
					'value' => $select->item("")
					),
				array(
					'type' => "th",
					'value' => $includeinto->item("").$ratio->item("").$label.$submit->input()
					),
				array(
					'type' => "th",
					'colspan' => 5
					)
				));
		return $grid;
	}
	
	function grid_period($start, $stop) {
		$grid = array(
			'variable' => __("pro rata over 12 months"),
			'absolute' => __("absolute"),
		);

		return $grid;
	}
}
