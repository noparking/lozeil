<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

class Balance extends Record {
	public $id = 0;
	public $accountingcodes_id = 0;
	public $period_id = 0;
	public $amount = 0;
	public $name = "";
	public $day = 0;
	public $number = "";
	public $timestamp = 0;

	function __construct($id = 0, db $db = null) {
		parent::__construct($db);
		$this->id = $id;
	}

	function db($db) {
		if ($db instanceof db) {
			$this->db = $db;
		}
	}
	
	function load(array $key = array(), $table = "balances", $columns = null) {
		return parent::load($key, $table, $columns);
	}

	function save() {
		if (is_numeric($this->id) and $this->id != 0) {
			$this->id = $this->update();

		} else {
			$this->id = $this->insert();
		}
		return $this->id;
	}

	function update() {
			$result = $this->db->query("UPDATE `".$this->db->config['table_balances']."`
				SET amount = ".(float)$this->amount.",
				number  = ".$this->db->quote($this->number).",
				name = ".$this->db->quote($this->name).",
				day = ".(int)$this->day.",	
				accountingcodes_id = ".(int)$this->accountingcodes_id.",
				period_id = ".(int)$this->period_id.",
				timestamp = ".time()."
				WHERE id = ".(int)$this->id
			);
		
		$this->db->status($result[1], "u", __('line'));

		return $this->id;
	}
	
	function insert() {
		$result = $this->db->id("
			INSERT INTO `".$this->db->config['table_balances']."`
			SET amount = ".(float)$this->amount.",
			number  = ".$this->db->quote($this->number).",
			name = ".$this->db->quote($this->name).",
			day = ".(int)$this->day.",
			accountingcodes_id = ".(int)$this->accountingcodes_id.",
			period_id = ".(int)$this->period_id.",
			timestamp = ".time()
		);
		$this->id = $result[2];
		$this->db->status($result[1], "i", __('line'));

		return $this->id;
	}

	function delete() {
		if ($this->id > 0) {
			$result = $this->db->query("DELETE FROM ".$this->db->config['table_balances']." WHERE id = '".$this->id."'");
			$this->db->status($result[1], "d", __('line'));
		}

		return $this->id;
	}

	function split_code($code, $j = 0) {
		$new_code = $this->get_new_code($code, $j);

		$balance = new Balance();
		$balance->load(array('number' => $new_code));
		if (!is_int($new_code) or $j >= 150) {
			return false;
		} else if ($balance->id == 0) {
			return $new_code;
		} else {
			$j++;
			return $this->split_code($code, $j);
		}
	}

	function get_new_code($code, $j) {
		$code = (($code + (9 * pow(10, 8))) / 10) + $j;

		return $code;
	}

	function split($amount, $option) {
		$amounts = array();
		if (!is_array($amount)) {
			$amounts[] = $amount;
		} else {
			$amounts = $amount;
		}
		$j = 1;
		
		$number = (int)$this->number;
		$amount = $this->amount;

		$current_affect = new Accounting_Code_Affectation();
		$current_affect->load(array('accountingcodes_id' => $this->accountingcodes_id));

		foreach ($amounts as $split_amount) {
			$split_amount = str_replace(",", ".", $split_amount);
			if (is_numeric($split_amount) and $split_amount != 0) {

				$balance = new Balance();
				$balance->name = $this->name." (split ".$j.")";
				if ($option == "ratio") {
					$balance->amount = $amount * ($split_amount / 100);
				} else {
					$balance->amount = $split_amount;				
				}

				$balance->day = $this->day;
				$balance->period_id = $this->period_id;
				$balance->number = $this->split_code($number, $j - 1);

				if (is_numeric($balance->number)) {
					$this->amount -= $balance->amount;
					
					$code = new Accounting_Code();
					$code->load(array('number' => $balance->number));
					if ($code->id == 0) {
						$code->number = $balance->number;
						$code->name = $balance->name;
						$code->save();
					}
					
					$affectation = new Accounting_Code_Affectation();
					$affectation->load(array('accountingcodes_id' => $code->id));
					if ($affectation->id == 0) {
						$affectation->accountingcodes_id = $code->id;
						$affectation->reportings_id = $current_affect->reportings_id;
						$affectation->save();

						$balance->accountingcodes_id = $code->id;
						$balance->save();
					} else {
						status(__("accounting code"), __("already exists"), -1);
					}
				} else {
					status(__("balance"), __("number not valid"), -1);
				}
				$j++;
			}
		}
		
		$this->save();
		return $this->id;
	}

	function name_already_exists() {
		$balance = new Balance();
		$balance->load(array('name' => $this->name));
		if ($balance->id > 0) {
			return true;
		} else {
			return false;
		}
	}

	function clean($data) {
		if (isset($data['accountingcodes_id'])) {
			$data['accountingcodes_id'] = (int)$data['accountingcodes_id'];
		}
		if (isset($data['name'])) {
			$data['name'] = strip_tags($data['name']);
			$data['name'] = trim(preg_replace('/\s+/', ' ', $data['name']));
		}
		if (isset($data['amount'])) {
			$data['amount'] = (int)$data['amount'];
		}
		return $data;
	}

	function get_insert_data($info) {
		$accountingcode_id = $info['accountingcodes_id'];
		if ($accountingcode_id <= 0) {
			$accountingcode_id = $info['hidden_code'];
		}

		$balances = new Balances();
		$balances->filter_with(array('accountingcodes_id' => $accountingcode_id, 'start' => $_SESSION['filter']['start'], 'stop' => $_SESSION['filter']['stop']));
		$balances->select();

		if (count($balances) > 0 and $balances->current()->id != $info['balance_id']) {
			status("balance", __("accounting code number already exists"), -1);
		} else {
			$code = new Accounting_Code();
			$code->load(array('id' => $accountingcode_id));
			
			$affectation = new Accounting_Code_affectation();
			$affectation->load(array('accountingcodes_id' => $code->id));
			
			if ($info['include'] == 0) {
				$affectation->desaffect();
			} else {
				if ($affectation->id == 0) {
					$affectation->accountingcodes_id = $code->id;
				}
				$affectation->reportings_id = $info['include'];
				$affectation->save();
			}

			$data = array(
				'accountingcodes_id' => $code->id,
				'number' => $code->number,
				'name' => $info['name'],
				'amount' => !empty($info['amount']) ? $info['amount'] : 0,
				'day' => mktime(0, 0, 0, $info['datepicker']['m'], $info['datepicker']['d'], $info['datepicker']['Y']),
			);
		}

		return $data;
	}

	function is_recently_modified(){
		if ($this->timestamp > (time() - 10)) {
			return true;
		}
		return false;
	}

	function clean_amounts_from_ajax(array $amounts) {
		$cleaned = array();
		foreach ($amounts as $amount) {
			if (!empty($amount)) {
				$cleaned[] = (float)str_replace(",", ".", $amount);
			}
		}
		return $cleaned;
	}

	function clean_ratios_from_ajax(array $amounts) {
		$cleaned = array();
		foreach ($amounts as $ratio) {
			if (!empty($ratio)) {
				$ratio = (float)str_replace(",", ".", $ratio);
				$cleaned[] = $this->amount * ($ratio / 100);
			}
		}
		return $cleaned;
	}

	function form_balance($from = null) {
		if ($from == null)
			$from = time();
		list($from, $to) = determine_fiscal_year($from);
		$years = array();
		$begin_year = date("Y", $from);
		for ($i = intval($begin_year) - 7; $i <= intval($begin_year) + 7;$i++) {
			$years[$i]= $i;
		}

		$balances = new Balances();
		$date_picker_from = new Html_Select("date_picker_from", $years, date("Y", $from));
		$submit = new Html_Input("show_submit",__('show'), "submit");
		$month = $GLOBALS['param']['fiscal year begin'];
		if (strlen($month) == 1)  {
			$month = "0".$month;
		}

		$ids = $balances->period($from, $to);
		if (!empty($ids)) {
			$balance_period = new Balance_Period();
			$balance_period->load(array('id' => $ids[0]));
			$span = month_from_timestamp($balance_period->start, $balance_period->stop);
		} else {
			$span = 0;
		}

		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'year' => array(
					'value' => ucfirst(__("fiscal year begin"))." 01/".$month."/".$date_picker_from->selectBox()
				),
				'result' => array(
					'value' => ucfirst(__("result")).": ".number_adjust_format($balances->sum($from, $to)).$GLOBALS['param']['currency']
				),
				'span' => array(
					'value' => ucfirst(__("span")).": ".$span." ".__("months")
				),
				'submit' => array(
					'value' => $submit->input()
				),
			)
		);

		$list = new Html_List($grid);

		$form =  "<center><div class=\"form\" >
			<form method=\"post\"  action=\"\" enctype=\"multipart/form-data\">".$list->show()."</form>
		</div></center>";

		return $form;
	}

	function form_filter() {
		$filter = new Html_Input("search_filter", isset($_SESSION['filter']['search']) ? $_SESSION['filter']['search'] : "");
		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'filter' => array(
					'value' => $filter->item(ucfirst(__("filter")).": ")
				)
			)
		);

		$list = new Html_List($grid);

		$form = "<div class=\"form_filter_balances\" >
			<form method=\"post\" name=\"filter_balances\" action=\"\" enctype=\"multipart/form-data\">".$list->show()."</form>
		</div>";

		return $form;
	}

	function form() {
		$reporting = new Reporting();
		
		$affectation = new Accounting_Code_affectation();
		$affectation->load(array('accountingcodes_id' => $this->accountingcodes_id));

		$accountingcode = new Accounting_Code();
		$currentcode = array();
		if ($accountingcode->load(array('id' => $this->accountingcodes_id))) {
			$currentcode[] = $accountingcode->fullname();
		}
		
		$id_hidden = new Html_Input("balance_id", $this->id, "hidden");
		$code_hidden = new Html_Input("hidden_code", $this->accountingcodes_id, "hidden");
		$input_hidden = new Html_Input("action", "insert", "hidden");
		($this->day == 0) ? $this->day = $_SESSION['filter']['start'] : $this->day;
		$datepicker = new Html_Input_Date("datepicker", $this->day);
		$accountingcode = new Html_Input_Ajax("accountingcodes_id", link_content("content=writings.ajax.php"), $currentcode);
		$name = new Html_Input("name", $this->name);
		$amount = new Html_Input("amount", $this->amount);
		$include = new Html_Select("include", $reporting->form_include(), $affectation->reportings_id);
		$submit = new Html_Input("submit", __('save'), "submit");
		
		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'title' => array(
					'value' => "<h3>".ucfirst(__("add new balance"))."</h3>"
				),
				'accountingcode' => array(
					'value' => $accountingcode->item(ucfirst(__("accounting code"))),
				),
				'name' => array(
					'value' => $name->item(ucfirst(__("name"))),
				),
				'amount' => array(
					'value' => $amount->item(ucfirst(__("amount"))),
				),
				'include' => array(
					'value' => $include->item(ucfirst(__("included in"))),
				),
				'date' => array(
					'value' => $datepicker->item(ucfirst(__("date"))),
				),
				'submit' => array(
					'value' => $submit->item(""),
				)
			)
		);

		$list = new Html_List($grid);

		$form = "<div class=\"modify_balances_form\">
			<form method=\"post\" name=\"table_balances_modify\" action=\"\" enctype=\"multipart/form-data\">".
			$id_hidden->input().$code_hidden->input().$input_hidden->input().$list->show()."
		</form></div>";

		return $form;
	}

	function form_split() {
		$input_split_hidden = new Html_Input("input_split", "ratio", "text");
		$input_hidden_id = new Html_Input("balance_id", $this->id, "text");
		$input_hidden_action = new Html_Input("action", "split");
		$input_value = new Html_Input("table_balances_split_amount[new]", "", "number");
		$input_value_clone = new Html_Input("table_balances_split_amount[new0]", "", "number");
		$submit = new Html_Input("table_balances_split_submit", utf8_ucfirst(__('save')), "submit");

		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'title' => array(
					'value' => "<h2>".ucfirst(__("split"))."</h2>"
				),
				'choice' => array(
					'value' => ucfirst(__("split type")).": <span id=\"ratio\" class=\"split-active\">%</span> / <span id=\"amount\">".$GLOBALS['param']['currency']."</span>",
				),
				'subtitle' => array(
					'value' => "<h3>".ucfirst(__("amount")).": ".number_adjust_format($this->amount).$GLOBALS['param']['currency']."</h3>".
								"<h3>".ucfirst(__("accounting code")).": ".$this->number."</h3>"
				),
				'duplicate' => array(
					'value' => $input_value->item("")
				),
				'duplicate_clone' => array(
					'value' => $input_value_clone->item("")
				),
				'submit' => array(
					'value' => $submit->item(""),
				),
			)
		);
		
		$list = new Html_List($grid);

		$form = "<div class=\"form_split\">
					<form method=\"post\" name=\"table_balances_split\" action=\"\" enctype=\"multipart/form-data\">".
						$input_split_hidden->input_hidden().$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$list->show()."
					</form>
				</div>";
		
		return $form."<div class=\"preview_changes\">".$this->preview_split()."</div>";
	}


	function grid_preview_split($amounts) {
		$grid = array(
			'lines_header' => array(
				'cells' => array(
					array(
						'type' => "th",
						'value' => ucfirst(__('New amounts'))
					),
					array(
						'type' => "th",
						'value' => ucfirst(__('accounting code'))
					)
				)
			),
		);
		
		$i = 1;
		$sum = 0;
		$number = $this->number;
		$code = $this->split_code($number);
		foreach ($amounts as $key => $amount) {
			$grid["lines_".$i]["cells"][] = array(
				'type' => "td",
				'value' => __("amount")." ".$i.": ".number_adjust_format($amount).$GLOBALS['param']['currency']
			);
			$grid["lines_".$i]["cells"][] = array(
				'type' => "td",
				'value' => __("accounting code")." ".($code + ($i - 1))
			);
			$i++;
			$sum = $sum + $amount;
		}
		$grid["lines_last"]["cells"][] = array(
			'type' => "td",
			'value' => __("result").": ".number_adjust_format($this->amount - $sum)." ".$GLOBALS['param']['currency']
		);
		return $grid;
	}

	function preview_split($request = "") {
		$amounts = array();
		parse_str(urldecode($request), $parsed);
		if (!empty($parsed)) {
			if ($parsed['input_split'] == "amount") {
				$amounts = $this->clean_amounts_from_ajax($parsed['table_balances_split_amount']);
			} else {
				$amounts = $this->clean_ratios_from_ajax($parsed['table_balances_split_amount']);				
			}
		}
		$html_table = new Html_table(array('lines' => $this->grid_preview_split($amounts)));
		
		return $html_table->show();
	}

	function verify_amounts(array $amounts, $option) {
		$sum = $this->amount;
		foreach ($amounts as $split_amount) {
			if ($option == "ratio") {
				if ($split_amount < 0) {
					return false;
				} else {
					$sum -= abs($this->amount) * ($split_amount / 100);				
				}
			} else {
				if ((is_positive($this->amount) and is_negative($split_amount)) or (is_negative($this->amount) and is_positive($split_amount))) {
					return false;
				} else {
					$sum -= $split_amount;				
				}
			}
		}

		if ((is_positive($sum) and is_negative($this->amount)) or (is_negative($sum) and is_positive($this->amount))) {
			return false;
		} else {
			return true;
		}
	}

	function form_import_balance() {
		$import_file = new Html_Input("file_balance", "", "file");
		$submit = new Html_Input("menu_actions_import_submit", "Ok", "submit");

		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'import' => array(
					'value' => ucfirst(__("file")).": ".$import_file->item("")
				),
				'submit' => array(
					'value' => $submit->input()
				),
			)
		);

		$list = new Html_List($grid);

		$form = "<center><div class=\"form\">
			<form method=\"post\" name=\"menu_actions_import_balance_form\" action=\"\" enctype=\"multipart/form-data\">".$list->show()."</form>
		</div></center>";

		return $form;
	}

	function get_form_modify() {
		return "<div class=\"modify show_acronym\">
					<input type=\"button\" class=\"modif modify_balance\" onclick=\"form_insert('".$this->id."');\" /><br>
					<span class=\"acronym\">".__("modify")."</span>
				</div>";
	}

	function get_form_add() {
		return "<div id=\"add_balance\">
			<div class=\"duplicate show_acronym\">
				<span class=\"opeaffectationn\"> <input class=\"add\" type=\"button\" onclick=\"form_insert(0);\" id=\"".$this->id."\"/> </span><br>
				<span class=\"acronym\">".__('add')."</span>
			</div>".ucfirst(__("add new line"))."
		</div>";
	}

	function get_form_split() {
		return "<div class=\"split show_acronym\">
					<input type=\"button\" class=\"balance_split split_balance\" onclick=\"form_split('".$this->id."');\" /><br>
					<span class=\"acronym\">".__("split")."</span>
				</div>";
	}

	function get_form_delete() {
		return "<div class=\"delete show_acronym\">
					<input type=\"button\" class=\"del delete_balance\" onclick=\"delete_balance('".$this->id."');\" /><br>
					<span class=\"acronym\">".__("delete")."</span>
				</div>";
	}
}
