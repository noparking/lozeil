<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

class Writings extends Collector {
	public $filters = null;
	public $amounts = array();
	public $categories_id = null;
	
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
		if (!empty($this->order)) {
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
		}
		
		return $join;
	}
	
	function get_columns() {
		$columns = parent::get_columns();
		if(!empty($this->order)) {
			$columns[] = $this->db->config['table_categories'].".name as category_name, ".$this->db->config['table_sources'].".name as source_name, ".$this->db->config['table_banks'].".name as bank_name";
		}
		return $columns;
	}

	
	function get_where() {
		$query_where = parent::get_where();
		
		if (isset($this->id) and !empty($this->id)) {
			if (!is_array($this->id)) {
				$this->id = array((int)$this->id);
			}
			$query_where[] = $this->db->config['table_writings'].".id IN ".array_2_list($this->id);
		}
		if (isset($this->filters['timestamp_start'])) {
			$query_where[] = $this->db->config['table_writings'].".timestamp >= ".(int)$this->filters['timestamp_start'];
		}
		if (isset($this->filters['timestamp_stop'])) {
			$query_where[] = $this->db->config['table_writings'].".timestamp <= ".(int)$this->filters['timestamp_stop'];
		}
		if (isset($this->filters['start'])) {
			$query_where[] = $this->db->config['table_writings'].".day >= ".(int)$this->filters['start'];
		}
		if (isset($this->filters['stop'])) {
			$query_where[] = $this->db->config['table_writings'].".day <= ".(int)$this->filters['stop'];
		}
		if (isset($this->filters['search_index']) and !empty($this->filters['search_index'])) {
			$query_where[] = $this->db->config['table_writings'].".search_index LIKE ".$this->db->quote("%".$this->filters['search_index']."%");
		}
		if (isset($this->filters['categories_id'])) {
			$query_where[] = $this->db->config['table_writings'].".categories_id = ".(int)$this->filters['categories_id'];
		}
		if (isset($this->filters['sources_id'])) {
			$query_where[] = $this->db->config['table_writings'].".sources_id = ".(int)$this->filters['sources_id'];
		}
		if (isset($this->filters['banks_id'])) {
			$query_where[] = $this->db->config['table_writings'].".banks_id = ".(int)$this->filters['banks_id'];
		}
		if (isset($this->filters['accountingcodes_id'])) {
			$query_where[] = $this->db->config['table_writings'].".accountingcodes_id = ".(int)$this->filters['accountingcodes_id'];
		}
		if (isset($this->filters['amount_inc_vat'])) {
			$query_where[] = $this->db->config['table_writings'].".amount_inc_vat = ".(float)$this->filters['amount_inc_vat'];
		}
		if (isset($this->filters['number'])) {
			if ($this->filters['number'] == 'duplicate') {
				$query_where[] = $this->db->config['table_writings'].".number IN (
					SELECT number
					FROM ".$this->db->config['table_writings']."
					WHERE (day >= ".$_SESSION['filter']['start']." AND day <= ".$_SESSION['filter']['stop'].")
					GROUP BY number
					HAVING (COUNT(number) > 1 and number <> '')
				)";
			} else {
				$query_where[] = $this->db->config['table_writings'].".number LIKE ".$this->db->quote("%".$this->filters['number']."%");
			}
		}
		if (isset($this->filters['comment'])) {
			$query_where[] = $this->db->config['table_writings'].".comment LIKE ".$this->db->quote("%".$this->filters['comment']."%");
		}
		if (isset($this->filters['categories_id_min'])) {
			$query_where[] = $this->db->config['table_writings'].".categories_id >= ".(int)$this->filters['categories_id_min'];
		}
		if (isset($this->filters['duplicate'])) {
			$query_where[] = $this->db->config['table_writings'].".amount_inc_vat IN (
				SELECT amount_inc_vat
				FROM ".$this->db->config['table_writings']."
				WHERE (day >= ".$_SESSION['filter']['start']." AND day <= ".$_SESSION['filter']['stop'].")
				GROUP BY amount_inc_vat
				HAVING (COUNT(amount_inc_vat) > 1 AND MIN(banks_id) = 0)
			)";
		}
		if (isset($this->filters['last'])) {
			$query_where[] = $this->db->config['table_writings'].".timestamp >= (SELECT MAX(".$this->db->config['table_writings'].".timestamp) FROM ".$this->db->config['table_writings'].")";
		}
		
		return $query_where;
	}
	
	function filling_balance() {
		$this->select();
		foreach ($this as $writing) {
			$result = $this->db->query("SELECT SUM(`amount_inc_vat`) as sum FROM `".$this->db->config['table_writings']."` WHERE `accountingcodes_id` = ".$writing->accountingcodes_id);
			$d = $this->db->fetchArray($result[0]);
			debug::dump($d);
		}
	}

	function get_vat_amount($start, $stop) {
		$result = $this->db->query("
			SELECT SUM(amount_inc_vat) as sum_amount_inc_vat, SUM(amount_excl_vat) as sum_amount_excl_vat
			FROM ".$this->db->config['table_writings']."
			WHERE day >= ".$start." AND day <= ".$stop
		);
		
		$sum = $this->db->fetchArray($result[0]);
		
		return $sum['sum_amount_excl_vat'] - $sum['sum_amount_inc_vat'];
	}
		
	function grid_header() {
		if ($_SESSION['accountant_view']) {
			return $this->grid_header_accountant();
		} else {
			return $this->grid_header_normal();
		}
	}
	
	function grid_header_accountant() {
		$grid = $this->grid_header_normal();
		$grid['header']['cells'][3] = array(
						'type' => "th",
						'class' => $this->determine_table_header_class("accountingcodes_id"),
						'id' => "accountingcodes_id",
						'value' => utf8_ucfirst(__('accounting code')),
					);
		return $grid;
	}
	
	function grid_header_normal() {
		$checkbox = new Html_Checkbox("checkbox_all_up", "check");
		$grid = array(
			'header' => array(
				'class' => "table_header",
				'cells' => array(
					array(
						'type' => "th",
						'id' => "checkbox",
						'value' => $checkbox->input()
					),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("day"),
						'id' => "day",
						'value' => utf8_ucfirst(__("date")),
					),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("number"),
						'id' => "number",
						'value' => utf8_ucfirst(__('piece nb')),
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
						'class' => $this->determine_table_header_class("bank_name"),
						'id' => "bank_name",
						'value' => utf8_ucfirst(__("bank")),
					),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("comment"),
						'id' => "comment",
						'value' => utf8_ucfirst(__("comment")),
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
						'value' => utf8_ucfirst(__("debit")),
					),
					array(
						'type' => "th",
						'class' => "sort",
						'id' => "amount_inc_vat",
						'value' => utf8_ucfirst(__("credit")),
					),
					array(
						'type' => "th",
						'id' => "operations",
						'value' => "",
					),
				),
			),
		);		
		return $grid;
	}
	
	function determine_table_header_class($header_column_name) {
		$class = "sort";
		if ($_SESSION['order']['name'] == $header_column_name) {
			if ($_SESSION['order']['direction'] == "ASC") {
				$class .= " sortedup";
			} else {
				$class .= " sorteddown";
			}
		}
		return $class;
	}
		
	function distribution($table,$nameid,$title,$date,$date_end,$positive)
	{
		if($positive == true) {
			$operateur = ">=";	
		}
		else {
			$operateur = "<";
		}
		$data = array();
		$twriting = $GLOBALS['dbconfig']['table_writings'];
		$query = "SELECT SUM(`amount_inc_vat`) as `sum` ,`".$table."`.`".$title."` FROM ".$twriting." , ".$table." WHERE `".$twriting."`.`".$nameid."` = `".$table."`.`id` AND `day` >= '".$date."' AND `day` < '".$date_end."' AND `".$twriting."`.`amount_inc_vat` ".$operateur." 0 GROUP BY `".$table."`.`".$title."` ;";
		$db = new db();
		$result =  $db->query($query);
		while(($d = $db->fetchArray($result[0]))){
			$data[addslashes($d[$title])] = $d['sum'];
		}
		return $data;
	}
			
	function get_amount_per($date,$date_end)
	{
		$data = array();
		for($i = 0;$i<=11;$i++) {
			$time = strtotime("+".$i." months ".date("m/d/Y",$date));
			$data[date('m/Y',$time)] = 0;
		}
		
		$result = $this->db->query("
			SELECT `amount_inc_vat` ,`day` 
			FROM ".$this->db->config['table_writings']."
			WHERE `day` >= '".$date."'
                        AND `day` < '".$date_end."'"
		);
		while(($d = $this->db->fetchArray($result[0]))) {
			$data[date('m/Y',$d['day'])] += $d['amount_inc_vat'];
		}
		return $data;
	}
	
	
	function grid_body() {
		if ($_SESSION['accountant_view']) {
			return $this->grid_body_accountant();
		} else {
			return $this->grid_body_normal();
		}
	}
	
	function grid_body_accountant() {
		$sources = new Sources();
		$sources->select();
		$sources_name = $sources->names();
		$banks = new Banks();
		$banks->select();
		$banks_name = $banks->names();
		
		$accounting_codes = new Accounting_Codes();
		$accounting_codes->select();
		$accounting_codes_number = $accounting_codes->numbers();
		$grid = array();
		
		if (isset($this->filters['duplicate']) and $_SESSION['order']['name'] == 'day') {
			$duplicate = $this->get_duplicate_color_classes();
		}
		
		$debit = 0;
		$credit = 0;
		foreach ($this as $writing) {
			if ($writing->amount_inc_vat < 0) {
				$debit += $writing->amount_inc_vat;
			} else {
				$credit += $writing->amount_inc_vat;
			}
			$class = "draggable droppable";
			if ($writing->is_recently_modified()) {
				$class .= " modified";
			}
			if ($writing->attachment) {
				$class .= " file_attached";
			}
			
			if (isset($this->filters['duplicate']) and $_SESSION['order']['name'] == 'day') {
				if (isset($duplicate[$writing->day][$writing->amount_inc_vat]) and $duplicate[$writing->day][$writing->amount_inc_vat]) {
					$class .= $duplicate[$writing->day][$writing->amount_inc_vat];
				}
			}
			$informations = $writing->show_further_information();
			$checkbox = new Html_Checkbox("checkbox_".$writing->id, $writing->id);
			$checkbox->properties = array("class" => "table_checkbox");
			$grid[] = array(
				'class' => $class,
				'id' => "table_".$writing->id,
				'cells' => array(
					array(
						'type' => "td",
						'value' => $checkbox->input(),
					),
					array(
						'type' => "td",
						'value' => date("d/m/Y", $writing->day),
					),
					array(
						'type' => "td",
						'value' => $writing->number,
					),
					array(
						'type' => "td",
						'value' => isset($accounting_codes_number[$writing->accountingcodes_id]) ? $accounting_codes_number[$writing->accountingcodes_id] : "",
					),
					array(
						'type' => "td",
						'value' => isset($sources_name[$writing->sources_id]) ? $sources_name[$writing->sources_id] : "",
					),
					array(
						'type' => "td",
						'value' => isset($banks_name[$writing->banks_id]) ? $banks_name[$writing->banks_id] : "",
					),
					array(
						'type' => "td",
						'class' => empty($informations) ? "" : "table_writings_comment",
						'value' => $writing->comment.$informations,
					),
					array(
						'type' => "td",
						'value' => ($writing->vat != 0) ? $writing->vat : "",
					),
					array(
						'type' => "td",
						'style' => "text-align: right;",
						'value' => $writing->amount_inc_vat < 0 ? number_adjust_format($writing->amount_inc_vat) : "",
					),
					array(
						'type' => "td",
						'style' => "text-align: right;",
						'value' => $writing->amount_inc_vat >= 0 ? number_adjust_format($writing->amount_inc_vat) : "",
					),
					array(
						'type' => "td",
						'class' => "operations",
						'value' => $writing->show_operations(),
					),
				),
			);
		}
		$grid[] = array(
			'class' => "table_total",
			'cells' => array(
					array(
						'colspan' => "8",
						'type' => "th",
						'value' => "",
					),
				array(
						'type' => "th",
						'value' => $debit." ".$GLOBALS['param']['currency'],
					),
				array(
						'type' => "th",
						'value' => $credit." ".$GLOBALS['param']['currency'],
					),
				array(
						'type' => "th",
						'value' => "",
					),
				)
			);
		return $grid;
	}
	
	function grid_body_normal() {
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
		
		if (isset($this->filters['duplicate']) and $_SESSION['order']['name'] == 'day') {
			$duplicate = $this->get_duplicate_color_classes();
		}
		
		
		$debit = 0;
		$credit = 0;
		foreach ($this as $writing) {
			if ($writing->amount_inc_vat < 0) {
				$debit += $writing->amount_inc_vat;
			} else {
				$credit += $writing->amount_inc_vat;
			}
			$class = "draggable droppable";
			if ($writing->is_recently_modified()) {
				$class .= " modified";
			}
			if ($writing->attachment) {
				$class .= " file_attached";
			}
			
			if (isset($this->filters['duplicate']) and $_SESSION['order']['name'] == 'day') {
				if (isset($duplicate[$writing->day][$writing->amount_inc_vat]) and $duplicate[$writing->day][$writing->amount_inc_vat]) {
					$class .= $duplicate[$writing->day][$writing->amount_inc_vat];
				}
			}
			$informations = $writing->show_further_information();
			$checkbox = new Html_Checkbox("checkbox_".$writing->id, $writing->id);
			$checkbox->properties = array("class" => "table_checkbox");
			$grid[] = array(
				'class' => $class,
				'id' => "table_".$writing->id,
				'cells' => array(
					array(
						'type' => "td",
						'value' => $checkbox->input(),
					),
					array(
						'type' => "td",
						'value' => date("d/m/Y", $writing->day),
					),
					array(
						'type' => "td",
						'value' => $writing->number,
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
						'value' => isset($banks_name[$writing->banks_id]) ? $banks_name[$writing->banks_id] : "",
					),
					array(
						'type' => "td",
						'class' => empty($informations) ? "" : "table_writings_comment",
						'value' => $writing->comment.$informations,
					),
					array(
						'type' => "td",
						'value' => ($writing->vat != 0) ? $writing->vat : "",
					),
					array(
						'type' => "td",
						'value' => $writing->amount_inc_vat < 0 ? round($writing->amount_inc_vat, 2) : "",
					),
					array(
						'type' => "td",
						'value' => $writing->amount_inc_vat >= 0 ? round($writing->amount_inc_vat, 2) : "",
					),
					array(
						'type' => "td",
						'class' => "operations",
						'value' => $writing->show_operations(),
					),
				),
			);
		}
		
		$grid[] = array(
			'class' => "table_total",
			'cells' => array(
					array(
						'colspan' => "8",
						'type' => "td",
						'value' => "",
					),
				array(
						'type' => "td",
						'value' => round($debit, 2)." ".$GLOBALS['param']['currency'],
					),
				array(
						'type' => "td",
						'value' => round($credit, 2)." ".$GLOBALS['param']['currency'],
					),
				array(
						'type' => "td",
						'value' => "",
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
		return "<div id=\"table_writings\">".$this->show()."</div>";
	}
	
	function show_timeline_at($timestamp) {
		$writings = new Writings();
		
		list($start, $stop) = determine_fiscal_year($timestamp);
		$writings->month = $start;
		$writings->filter_with(array('stop' => $stop));
		$writings->select_columns('amount_inc_vat', 'day');
		$writings->select();
		
		$cubismchart = new Html_Cubismchart("writings");
		$cubismchart->data = $writings->get_balance_per_day_all_categories($timestamp);
		$cubismchart->start = $timestamp;
		return $cubismchart->show();
	}
	
	function display_timeline_at($timestamp) {
		return "<div id=\"heading_timeline\">".$this->show_timeline_at($timestamp)."</div>";
	}
	
	function display_balance_on_current_date() {
		list($start, $stop) = determine_fiscal_year(time());
		
		return Html_Tag::a(link_content("content=writings.php&start=".$start."&stop=".$stop),utf8_ucfirst(__("accounting on"))." ".get_time("d/m/Y")." : ".$this->show_balance_at(time())." ".__("€"));
	}
	
	function show_balance_at($timestamp_max) {
		$amount = 0;
		foreach ($this->instances as $writing) {
			if ($writing->day < $timestamp_max) {
				$amount += $writing->amount_inc_vat;
			}
		}
		
		return round($amount, 2);
	}
	
	function form_filter($start, $stop, $value = "") {
		
		$categories = new Categories();
		$categories->select();
		$sources = new Sources();
		$sources->select();
		$banks = new Banks();
		$banks->select();
		if (isset($_SESSION['filter']['accountingcodes_id'])) {
			$accountingcode = new Accounting_Code();
			$accountingcode->load(array('id' => (int)$_SESSION['filter']['accountingcodes_id']));
		}
		
		$categories_names = $categories->names();
		$categories_names['none'] = __('&#60none&#62');
		$banks_names = $banks->names_of_selected_banks();
		$banks_names['none'] = __('&#60none&#62');
		$sources_names = $sources->names();
		$sources_names['none'] = __('&#60none&#62');
		
		$input_hidden_action = new Html_Input("action", "filter");
		$input = new Html_Input("extra_filter_writings_value",$value);
		
		$category = new Html_Select("filter_categories_id", $categories_names);
		if (isset($_SESSION['filter']['categories_id'])) {
			$category->selected = $_SESSION['filter']['categories_id'];
			$category_class = "filter_show";
		} else {
			$category_class = "filter_hide";
		}
		
		$source = new Html_Select("filter_sources_id", $sources_names);
		if (isset($_SESSION['filter']['sources_id'])) {
			$source->selected = $_SESSION['filter']['sources_id'];
			$source_class = "filter_show";
		} else {
			$source_class = "filter_hide";
		}
		
		$bank = new Html_Select("filter_banks_id", $banks_names);
		if (isset($_SESSION['filter']['banks_id'])) {
			$bank->selected = $_SESSION['filter']['banks_id'];
			$bank_class = "filter_show";
		} else {
			$bank_class = "filter_hide";
		}
		
		$accountingcode_input = new Html_Input_Ajax("filter_accountingcodes_id", link_content("content=writings.ajax.php"));
		$accountingcode_checkbox = new Html_Checkbox("filter_accountingcodes_none", "1");
		if (!isset($_SESSION['filter']['accountingcodes_id'])) {
			$accountingcode_input_class = "filter_hide";
			$accountingcode_checkbox_class = "";
		} else {
			$accountingcode_input_class = "filter_show";
			if ($_SESSION['filter']['accountingcodes_id']) {
				$accountingcode_input->element = array($_SESSION['filter']['accountingcodes_id'] => $accountingcode->fullname());
				$accountingcode_input_class = "filter_show";
				$accountingcode_checkbox_class = "filter_hide";
			} else {
				$accountingcode_input->properties = array('class' => 'filter_hide');
				$accountingcode_checkbox_class = "filter_sow";
				$accountingcode_checkbox->selected = 1;
			}
		}
		
		$number = new Html_Input("filter_number");
		$number_checkbox = new Html_Checkbox("filter_number_duplicate", "1");
		if (!isset($_SESSION['filter']['number'])) {
			$number_class = "filter_hide";
			$number_checkbox_class = "";
		} elseif ($_SESSION['filter']['number'] == 'duplicate') {
			$number_class = "filter_show";
			$number->properties = array('class' => 'filter_hide');
			$number_checkbox_class = "filter_show";
			$number_checkbox->selected = 1;
			
		} else {
			$number_class = "filter_show";
			$number->properties = array('class' => 'filter_show');
			$number->value = $_SESSION['filter']['number'];
			$number_checkbox_class = "filter_hide";
			$number_checkbox->selected = 0;
		}
		
		$amount_inc_vat = new Html_Input("filter_amount_inc_vat");
		if (isset($_SESSION['filter']['amount_inc_vat'])) {
			$amount_inc_vat->value = $_SESSION['filter']['amount_inc_vat'];
			$amount_inc_vat_class = "filter_show";
		} else {
			$amount_inc_vat_class = "filter_hide";
		}
		
		$comment = new Html_Textarea("filter_comment");
		if (isset($_SESSION['filter']['comment'])) {
			$comment->value = $_SESSION['filter']['comment'];
			$comment_class = "filter_show";
		} else {
			$comment_class = "filter_hide";
		}
		
		$checkbox = new Html_Checkbox("filter_duplicate", "duplicate");
		if (isset($_SESSION['filter']['duplicate'])) {
			$checkbox->selected = $_SESSION['filter']['duplicate'];
			$checkbox_class = "filter_show";
		} else {
			$checkbox_class = "filter_hide";
		}
		
		$checkbox_last = new Html_Checkbox("filter_last", "last");
		if (isset($_SESSION['filter']['last'])) {
			$checkbox_last->selected = $_SESSION['filter']['last'];
			$checkbox_last_class = "filter_show";
		} else {
			$checkbox_last_class = "filter_hide";
		}
		
		$date_start = new Html_Input_Date("filter_day_start", $start);
		$date_stop = new Html_Input_Date("filter_day_stop", $stop);
		if (preg_match("/show/", $category_class.$source_class.$bank_class.$accountingcode_input_class.$number_class.$amount_inc_vat_class.$comment_class.$checkbox_class)) {
			$date_class = "filter_show";
		} else {
			$date_class = "filter_hide";
		}
		$submit = new Html_Input("submit_hidden", "", "submit");
		$grid = array(
			'class' => 'itemsform',
			'leaves' => array(
				'*' => array(
					'value' => $input_hidden_action->input_hidden().$input->item(utf8_ucfirst(__('filter')." : "))."<span id =\"extra_filter_writings_toggle\"> + </span>"
				),
				'date' => array(
					'class' => "extra_filter_item ".$date_class,
					'value' => $date_start->item(__('date')).$date_stop->input()
				),
				'category' => array(
					'class' => "extra_filter_item ".$category_class,
					'value' => $category->item(__('category'))
				),
				'source' => array(
					'class' => "extra_filter_item ".$source_class,
					'value' => $source->item(__('source')),
				),
				'bank' => array(
					'class' => "extra_filter_item ".$bank_class,
					'value' => $bank->item(__('bank')),
				),
				'accountingcode' => array(
					'class' => "extra_filter_item ".$accountingcode_input_class,
					'value' => $accountingcode_input->item(__('accounting code'), "",
					           "<span class=\"".$accountingcode_checkbox_class."\">".$accountingcode_checkbox->item(__('not any'))."</span>"),
				),
				'number' => array(
					'class' => "extra_filter_item ".$number_class,
					'value' => $number->item(__('piece nb'), "", "<span class=\"".$number_checkbox_class."\">".$number_checkbox->item(__('duplicates'))."</span>"),
				),
				'amount_inc_vat' => array(
					'class' => "extra_filter_item ".$amount_inc_vat_class,
					'value' => $amount_inc_vat->item(__('amount including vat')),
				),
				'comment' => array(
					'class' => "extra_filter_item ".$comment_class,
					'value' => $comment->item(__('comment')),
				),
				'checkbox' => array(
					'class' => "extra_filter_item ".$checkbox_class,
					'value' => $checkbox->item(__('duplicates')),
				),
				'last' => array(
					'class' => "extra_filter_item ".$checkbox_last_class,
					'value' => $checkbox_last->item(__('last modified')),
				),
			)
		);
		if (!$_SESSION['accountant_view']) {
			unset($grid['leaves']['accountingcode']);
		}
		$list = new Html_List($grid);
		$form = "<div class=\"extra_filter_writings\">
					<form method=\"post\" name=\"extra_filter_writings_form\" action=\"\" enctype=\"multipart/form-data\">
						<div class=\"extra_filter_writings\">".
							$list->show().$submit->input()."
						</div>
					</form>
				</div>";
		
		return $form;
	}
	
	function filter_with() {
		$elements = func_get_args();
		foreach ($elements as $element) {
			foreach ($element as $key => $value) {
				$this->filters[$key] = $value;
			}
		}
	}
	
	function modify_options() {
		$options = array(
			"null" => "--",
			"change_category" => __('change category to')." ...",
			"change_source" => __('change source to')." ...",
			"change_accounting_code" => __('change accounting code to')." ...",
			"change_amount_inc_vat" => __('change amount including vat to')." ...",
			"change_vat" => __('change vat to')." ...",
			"change_day" => __('change date to')." ...",
			"duplicate" => __('duplicate over')." ...",
			"estimate_accounting_code" => __('estimate accounting code'),
			"estimate_category" => __('estimate category'),
			"delete" => __('delete')
		);
		if (!$_SESSION['accountant_view']) {
			unset($options['change_accounting_code']);
			unset($options['estimate_accounting_code']);
		}
		$select = new Html_Select("options_modify_writings", $options);
		$select->properties = array(
				'onchange' => "confirm_option('".utf8_ucfirst(__('are you sure?'))."')"
			);
		$checkbox = new Html_Checkbox("checkbox_all_down", "check");
		
		$form = "<div id=\"select_writings\">".
					$checkbox->input().$select->item("")."
					<div id=\"form_modify_writings\">
					</div>
				</div>";
		return $form;
	}
	
	function determine_show_form_modify($target) {
		$form = "<form method=\"post\" name=\"writings_modify_form\" action=\"\" enctype=\"multipart/form-data\" onsubmit=\"return confirm_modify('".utf8_ucfirst(__('are you sure?'))."')\">";
		$submit = new Html_Input("submit_writings_modify_form", __('ok'), "submit");
		switch($target) {
			case 'change_category':
				$categories = new Categories();
				$categories->select();
				$category = new Html_Select("categories_id", $categories->names());
				$category->properties = array(
					'onsubmit' => "confirm_modify('".utf8_ucfirst(__('are you sure?'))."')"
				);
				$form .= $category->item("");
				break;
			case 'change_source':
				$sources = new Sources();
				$sources->select();
				$source = new Html_Select("sources_id", $sources->names());
				$source->properties = array(
					'onsubmit' => "confirm_modify('".utf8_ucfirst(__('are you sure?'))."')"
				);
				$form .= $source->item("");
				break;
			case 'change_accounting_code':
				$accountingcodes = new Accounting_Codes();
				$accountingcodes->select();
				$accountingcode = new Html_Input_Ajax("accountingcodes_id", link_content("content=writings.ajax.php"), $accountingcodes->numbers());
				$accountingcode->properties = array(
					'onsubmit' => "confirm_modify('".utf8_ucfirst(__('are you sure?'))."')"
				);
				$form .= $accountingcode->item("");
				break;
			case 'change_amount_inc_vat':
				$amount_inc_vat = new Html_Input("amount_inc_vat");
				$form .= $amount_inc_vat->input();
				break;
			case 'change_vat':
				$vat = new Html_Input("vat");
				$form .= $vat->input();
				break;
			case 'change_day':
				$datepicker = new Html_Input_Date("day");
				$datepicker->properties = array(
					'onsubmit' => "confirm_modify('".utf8_ucfirst(__('are you sure?'))."')"
				);
				$form .= $datepicker->item("");
				break;
			case 'duplicate':
				$vat = new Html_Input("duplicate");
				$form .= $vat->input();
				break;
			default :
				break;
		}
		$form .= $submit->input();
		$form .= "</form>";
		
		return $form;
	}
	
	function clean_from_ajax($post) {
		$parameters = array();
		$parameters['operation'] = $post['operation'];
		$ids = json_decode($post['ids']);
		if (!empty($ids)) {
			switch ($parameters['operation']) {
				case 'change_category':
					$parameters['value'] = $post['categories_id'];
					if (!empty($parameters['value']) or $parameters['value'] == 0) {
						$parameters['id'] = $ids;
					}
					break;
				case 'change_source':
					$parameters['value'] = (int)$post['sources_id'];
					if (!empty($parameters['value']) or $parameters['value'] == 0) {
						$parameters['id'] = $ids;
					}
					break;
				case 'change_accounting_code':
					if(isset($post['accountingcodes_id'])) {
						$parameters['value'] = (int)$post['accountingcodes_id'];
					}
					else {
						$parameters['value'] = (int)$post[md5('accountingcodes_id')];
					}
					if (!empty($parameters['value']) or $parameters['value'] == 0) {
						$parameters['id'] = $ids;
					}
					break;
				case 'change_vat':
					$parameters['value'] = str_replace(",", ".", trim($post['vat']));
					if (is_numeric($parameters['value'])) {
						$parameters['id'] = $ids;
					}
					break;
				case 'change_amount_inc_vat':
					$parameters['value'] = str_replace(",", ".", trim($post['amount_inc_vat']));
					if (is_numeric($parameters['value'])) {
						$parameters['id'] = $ids;
					}
					break;
				case 'change_day':
					if(is_datepicker_valid($post['day'])) {
						$parameters['value'] = timestamp_from_datepicker($post['day']);
						if (!empty($parameters['value'])) {
							$parameters['id'] = $ids;
						}
					}
					break;
				case 'duplicate':
					$parameters['value'] = trim($post['duplicate']);
					if (!empty($parameters['value'])) {
						$parameters['id'] = $ids;
					}
					break;
				default :
					break;
			}
		}
		return $parameters;
	}
	
	function apply($operation, $value) {
		switch ($operation) {
			case 'change_category':
				$this->change_category($value);
				break;
			case 'change_source':
				$this->change_source($value);
				break;
			case 'change_accounting_code':
				$this->change_accounting_code($value);
				break;
			case 'change_vat':
				$this->change_vat($value);
				break;
			case 'change_amount_inc_vat':
				$this->change_amount_inc_vat($value);
				break;
			case 'change_day':
				$this->change_day($value);
				break;
			case 'duplicate':
				$this->duplicate_over_from_ids($value);
				break;
			default :
				break;
		}
	}
	
	function delete() {
		$this->select();
		foreach ($this as $writing) {
			$writing->delete();
		}
	}

	function delete_from_ids($ids) {
		foreach($ids as $id) {
			$writing = new Writing();
			$writing->id = $id;
			$writing->delete();
		}
	}
	
	function change_category($id) {
		$bayesianelements = new Bayesian_Elements();
		$category = new Category();
		$category->load(array('id' => $id));
		foreach ($this as $writing) {
			$writing_before = clone $writing;
			$writing->categories_id = $id;
			if($writing->vat == 0) {
				$writing->vat = $category->vat;
			}
			$bayesianelements->increment_decrement($writing_before, $writing);
			$writing->update();
		}
	}
	
		
	function change_accounting_code($id) {
		$bayesianelements = new Bayesian_Elements();
		$accounting_code = new Accounting_Code();
		$accounting_code->load(array('id' => $id));
		foreach ($this as $writing) {
			$writing_before = clone $writing;
			$writing->accountingcodes_id = $id;
			$bayesianelements->increment_decrement($writing_before, $writing);
			$writing->update();
		}
	}
	
	function change_source($value) {
		foreach ($this as $writing) {
			$writing->sources_id = $value;
			$writing->update();
		}
	}
	
	function change_vat($amount) {
		foreach($this as $writing) {
			$writing->vat = $amount;
			$writing->update();
		}
	}
	
	function change_amount_inc_vat($amount) {
		foreach($this as $writing) {
			if ($writing->banks_id == 0) {
				$writing->amount_inc_vat = $amount;
				$writing->update();
			}
		}
	}
	
	function change_day($value) {
		foreach ($this as $writing) {
			if ($writing->banks_id == 0) {
				$writing->day = $value;
				$writing->update();
			}
		}
	}

	function duplicate_over_from_ids($amount) {
		foreach($this as $writing) {
			$writing->duplicate($amount);
		}
	}
		
	function clean_filter_from_ajax($post) {
		$cleaned = array ();
		if (!empty($post['extra_filter_writings_value'])) {
			$cleaned['search_index'] = $post['extra_filter_writings_value'];
		}
		list($cleaned['start'], $cleaned['stop']) = determine_start_stop($post['filter_day_start'], $post['filter_day_stop']);
		if ($post['filter_categories_id']) {
			if ($post['filter_categories_id'] == 'none') {
				$post['filter_categories_id'] = 0;
			}
			$cleaned['categories_id'] = $post['filter_categories_id'];
		}
		if ($post['filter_sources_id']) {
			if ($post['filter_sources_id'] == 'none') {
				$post['filter_sources_id'] = 0;
			}
			$cleaned['sources_id'] = $post['filter_sources_id'];
		}
		if ($post['filter_banks_id']) {
			if ($post['filter_banks_id'] == 'none') {
				$post['filter_banks_id'] = 0;
			}
			$cleaned['banks_id'] = $post['filter_banks_id'];
		}
		if (isset($post['filter_accountingcodes_none'])) {
			$cleaned['accountingcodes_id'] = 0;
		} elseif (isset($post['filter_accountingcodes_id'])) {
			$cleaned['accountingcodes_id'] = $post['filter_accountingcodes_id'];
		}
		if (isset($post['filter_accountingcodes_none'])) {
			$cleaned['accountingcodes_id'] = 0;
		}
		if (isset($post['filter_number_duplicate'])) {
			$cleaned['number'] = 'duplicate';
		} elseif (!empty($post['filter_number'])) {
			$cleaned['number'] = $post['filter_number'];
		}
		if (!empty($post['filter_amount_inc_vat'])) {
			$cleaned['amount_inc_vat'] = (float)str_replace(",", ".", $post['filter_amount_inc_vat']);
		}
		if (!empty($post['filter_comment'])) {
			$cleaned['comment'] = $post['filter_comment'];
		}
		if (isset($post['filter_duplicate'])) {
			$cleaned['duplicate'] = 1;
		}
		if (isset($post['filter_last'])) {
			$cleaned['last'] = 1;
		}
		return $cleaned;
	}
	
	function get_balance_per_day_per_category($timestamp) {
		$balance = array();
		foreach ($this as $writing) {
			$day = mktime(0, 0, 0, date('m', $writing->day), date('d', $writing->day), date('Y', $writing->day));
			$balance[$writing->categories_id][$day] = isset($balance[$writing->categories_id][$day]) ? $balance[$writing->categories_id][$day] + round($writing->amount_inc_vat, 2) : round($writing->amount_inc_vat, 2);
		}
		
		$nb_day = is_leap(date('Y',$timestamp) + 1) ? 366 : 365;
		
		foreach($balance as $id => $category) {
			$timestamp_start = $timestamp;
			$previous = 0;
			for ($i = 0; $i < $nb_day; $i++) {
				if (!isset($category[$timestamp_start])) {
					$category[$timestamp_start] = 0 + $previous;
				} else {
					$category[$timestamp_start] += $previous;
				}
				$previous = $category[$timestamp_start];
				$timestamp_start = strtotime('+1 day', $timestamp_start);
			}
			ksort($category);
			$balance[$id] = $category;
		}
		
		ksort($balance);
		
		return $balance;
	}
	
	function get_balance_per_day_per_bank($timestamp) {
		$balance = array();
		
		foreach ($this as $writing) {
			$day = mktime(0, 0, 0, date('m', $writing->day), date('d', $writing->day), date('Y', $writing->day));
			$balance[$writing->banks_id][$day] = isset($balance[$writing->banks_id][$day]) ? $balance[$writing->banks_id][$day] + round($writing->amount_inc_vat, 2) : round($writing->amount_inc_vat, 2);
		}
		$nb_day = is_leap(date('Y',$timestamp) + 1) ? 366 : 365;
		
		foreach($balance as $id => $bank) {
			$timestamp_start = determine_first_day_of_year($timestamp);
			$previous = 0;
			for ($i = 0; $i < $nb_day; $i++) {
				if (!isset($bank[$timestamp_start])) {
					$bank[$timestamp_start] = 0 + $previous;
				} else {
					$bank[$timestamp_start] += $previous;
				}
				$previous = $bank[$timestamp_start];
				$timestamp_start = strtotime('+1 day', $timestamp_start);
			}
			ksort($bank);
			$balance[$id] = $bank;
		}
		
		ksort($balance);
		
		return $balance;
	}
	
	function get_balance_per_day_all_categories($timestamp) {
		$balance = array();
		
		list($start,) = determine_fiscal_year($timestamp);
		
		foreach ($this as $writing) {
			$day = mktime(0, 0, 0, date('m', $writing->day), date('d', $writing->day), date('Y', $writing->day));
			if ($day < $start) {
				$balance[$start] = isset($balance[$start]) ? $balance[$start] + round($writing->amount_inc_vat, 2) : round($writing->amount_inc_vat, 2);
			} else {
				$balance[$day] = isset($balance[$day]) ? ($balance[$day] + round($writing->amount_inc_vat, 2)) : round($writing->amount_inc_vat, 2);
			}
		}
		
		$nb_day = is_leap(date("Y", (int)$timestamp) + 1) ? 366 : 365;
		$previous = 0;
		$timestamp_start = $start;
		
		for ($i = 0; $i < $nb_day; $i++) {
			if (!isset($balance[$timestamp_start])) {
				$balance[$timestamp_start] = 0 + $previous;
			} else {
				$balance[$timestamp_start] += $previous;
			}
			$previous = $balance[$timestamp_start];
			$timestamp_start = strtotime('+1 day', $timestamp_start);
		}
		
		ksort($balance);
		
		return $balance;
	}
	
	function get_amount_monthly_per_category($timestamp) {
		$balance = array();
		
		foreach ($this as $writing) {
			$month = mktime(0, 0, 0, date('m', $writing->day), 1, date('Y', $writing->day));
			$balance[$writing->categories_id][$month] = isset($balance[$writing->categories_id][$month]) ? $balance[$writing->categories_id][$month] + $writing->amount_inc_vat : $writing->amount_inc_vat;
		}
		
		$nb_day = is_leap(date('Y',$timestamp) + 1) ? 366 : 365;
		
		foreach($balance as $id => $category) {
			$timestamp_start = determine_first_day_of_year($timestamp);
			$previous_month = 0;
			for ($i = 0; $i < $nb_day; $i++) {
				if ($previous_month != date('m', $timestamp_start)) {
					$previous = 0;
				}
				if (!isset($category[$timestamp_start])) {
					$category[$timestamp_start] = 0 + $previous;
				} else {
					$category[$timestamp_start] += $previous;
				}
				$previous = $category[$timestamp_start];
				$previous_month = date('m', $timestamp_start);
				$timestamp_start = strtotime('+1 day', $timestamp_start);
			}
			ksort($category);
			$balance[$id] = $category;
		}
		
		ksort($balance);
		
		return $balance;
	}
	
	function get_amount_monthly_per_bank($timestamp) {
		$balance = array();
		
		foreach ($this as $writing) {
			$month = mktime(0, 0, 0, date('m', $writing->day), 1, date('Y', $writing->day));
			$balance[$writing->banks_id][$month] = isset($balance[$writing->banks_id][$month]) ? $balance[$writing->banks_id][$month] + $writing->amount_inc_vat : $writing->amount_inc_vat;
		}
		
		$nb_day = is_leap(date('Y',$timestamp) + 1) ? 366 : 365;
		
		foreach($balance as $id => $bank) {
			$timestamp_start = determine_first_day_of_year($timestamp);
			$previous_month = 0;
			for ($i = 0; $i < $nb_day; $i++) {
				if ($previous_month != date('m', $timestamp_start)) {
					$previous = 0;
				}
				if (!isset($bank[$timestamp_start])) {
					$bank[$timestamp_start] = 0 + $previous;
				} else {
					$bank[$timestamp_start] += $previous;
				}
				$previous = $bank[$timestamp_start];
				$previous_month = date('m', $timestamp_start);
				$timestamp_start = strtotime('+1 day', $timestamp_start);
			}
			ksort($bank);
			$balance[$id] = $bank;
		}
		
		ksort($balance);
		
		return $balance;
	}
	
	function get_amount_weekly_per_bank($timestamp) {
		$balance = array();
		
		foreach ($this as $writing) {
			$week = mktime(0, 0, 0, date('m', $writing->day), date('d', $writing->day) - date('N', $writing->day) + 1, date('Y', $writing->day));
			$balance[$writing->banks_id][$week] = isset($balance[$writing->banks_id][$week]) ? $balance[$writing->banks_id][$week] + $writing->amount_inc_vat : $writing->amount_inc_vat;
		}
		
		$nb_day = is_leap(date('Y',$timestamp) + 1) ? 366 : 365;
		
		foreach($balance as $id => $bank) {
			$timestamp_start = determine_first_day_of_year($timestamp);
			$previous_month = 0;
			for ($i = 0; $i < $nb_day; $i++) {
				if ($previous_month != date('W', $timestamp_start)) {
					$previous = 0;
				}
				if (!isset($bank[$timestamp_start])) {
					$bank[$timestamp_start] = 0 + $previous;
				} else {
					$bank[$timestamp_start] += $previous;
				}
				$previous = $bank[$timestamp_start];
				$previous_month = date('W', $timestamp_start);
				$timestamp_start = strtotime('+1 day', $timestamp_start);
			}
			ksort($bank);
			$balance[$id] = $bank;
		}
		
		ksort($balance);
		
		return $balance;
	}
	
	function get_amount_weekly_per_category($timestamp) {
		$balance = array();
		
		foreach ($this as $writing) {
			$week = mktime(0, 0, 0, date('m', $writing->day), date('d', $writing->day) - date('N', $writing->day) + 1, date('Y', $writing->day));
			$balance[$writing->categories_id][$week] = isset($balance[$writing->categories_id][$week]) ? $balance[$writing->categories_id][$week] + $writing->amount_inc_vat : $writing->amount_inc_vat;
		}
		
		$nb_day = is_leap(date('Y',$timestamp) + 1) ? 366 : 365;
		
		foreach($balance as $id => $category) {
			$timestamp_start = determine_first_day_of_year($timestamp);
			$previous_month = 0;
			for ($i = 0; $i < $nb_day; $i++) {
				if ($previous_month != date('W', $timestamp_start)) {
					$previous = 0;
				}
				if (!isset($category[$timestamp_start])) {
					$category[$timestamp_start] = 0 + $previous;
				} else {
					$category[$timestamp_start] += $previous;
				}
				$previous = $category[$timestamp_start];
				$previous_month = date('W', $timestamp_start);
				$timestamp_start = strtotime('+1 day', $timestamp_start);
			}
			ksort($category);
			$balance[$id] = $category;
		}
		
		ksort($balance);
		
		return $balance;
	}
	
	function estimate_accounting_code_from_ids($ids) {
		$bayesianelements_accounting_codes_id = new Bayesian_Elements();
		$bayesianelements_accounting_codes_id->prepare_id_estimation($GLOBALS['dbconfig']['table_accountingcodes']);
		
		foreach($ids as $id) {
			$writing = new Writing();
			$writing->load(array('id' => $id));
			$writing->accountingcodes_id = $bayesianelements_accounting_codes_id->fisher_element_id_estimated($writing);
			$writing->update();
		}
	}
	
	function estimate_category_from_ids($ids) {
		$bayesianelements_categories_id = new Bayesian_Elements();
		$bayesianelements_categories_id->prepare_id_estimation($GLOBALS['dbconfig']['table_categories']);
		foreach($ids as $id) {
			$writing = new Writing();
			$writing->load(array('id' => $id));
			$writing->categories_id = $bayesianelements_categories_id->fisher_element_id_estimated($writing);
			$writing->update();
		}
	}
	
	function get_duplicate_color_classes() {
		$duplicate = array();
		foreach ($this as $writing) {
			if (isset($duplicate[$writing->day][$writing->amount_inc_vat])) {
				$duplicate[$writing->day][$writing->amount_inc_vat] = 1;
			} else {
				$duplicate[$writing->day][$writing->amount_inc_vat] = 0;
			}
		}
		$color_class = " duplicate_brown";
		foreach ($duplicate as $timestamp => $amount) {
			$values = array_values($amount);
			if (array_shift($values)) {
				$keys = array_keys($amount);
				$duplicate[$timestamp][array_shift($keys)] = $color_class;
				$color_class = ($color_class == " duplicate_brown") ? " duplicate_green" : " duplicate_brown";
			}
		}
		return $duplicate;
	}
	
	function calculate_quarterly_vat($timestamp) {
		list($start, $stop) = determine_month(strtotime("-1 month", $timestamp));
		$start = strtotime("-2 month", $start);
		$writings = new Writings();
		$vat = $writings->get_vat_amount($start, $stop);
		
		$categories = new Categories();
		$categories->filter_with(array('vat_category' => 1));
		$categories->select();
		if (count($categories) == 1) {
			$category = $categories[0];
			list($month_start, $month_stop) = determine_month($timestamp);
			$writings->filter_with(array('start' => $month_start, 'stop' => $month_stop, 'categories_id' => $category->id, 'banks_id' => 0));
			$writings->select();
			
			$writing = new Writing();
			if (count($writings) == 1) {
				$writing->load(array('id' => $writings[0]->id));
			}
			$writing->categories_id = $category->id;
			$writing->day = $timestamp;
			$writing->amount_inc_vat = $vat;
			$writing->comment = utf8_ucfirst(__("automatically calculated vat"))." ".__('from')." ".date('d/m/Y', $start)." ".__('to')." ".date('d/m/Y', $stop);
			$writing->save();
		}
	}
}
