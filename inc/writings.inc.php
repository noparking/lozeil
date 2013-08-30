<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

class Writings extends Collector  {
	public $filter = null;
	
	private $month = 0;
	
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
		$join[] = "
			LEFT JOIN ".$this->db->config['table_accounts']."
			ON ".$this->db->config['table_accounts'].".id = ".$this->db->config['table_writings'].".account_id
		";
		$join[] = "
			LEFT JOIN ".$this->db->config['table_sources']."
			ON ".$this->db->config['table_sources'].".id = ".$this->db->config['table_writings'].".source_id
		";
		$join[] = "
			LEFT JOIN ".$this->db->config['table_types']."
			ON ".$this->db->config['table_types'].".id = ".$this->db->config['table_writings'].".type_id
		";
		$join[] = "
			LEFT JOIN ".$this->db->config['table_banks']."
			ON ".$this->db->config['table_banks'].".id = ".$this->db->config['table_writings'].".bank_id
		";
		
		return $join;
	}
	
	function get_columns() {
		$columns = parent::get_columns();
		$columns[] = $this->db->config['table_accounts'].".name as account_name, ".$this->db->config['table_sources'].".name as source_name, ".$this->db->config['table_types'].".name as type_name, ".$this->db->config['table_banks'].".name as bank_name";

		return $columns;
	}
	
	function grid_header() {
		$grid = array(
			'header' => array(
				'class' => "table_header",
				'cells' => array(
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("delay"),
						'id' => "delay",
						'value' => utf8_ucfirst(__("delay")),
					),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("account_name"),
						'id' => "account_name",
						'value' => utf8_ucfirst(__("account")),
					),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("source_name"),
						'id' => "source_name",
						'value' => utf8_ucfirst(__("source")),
						),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("type_name"),
						'id' => "type_name",
						'value' => utf8_ucfirst(__("type")),
					),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("amount_excl_vat"),
						'id' => "amount_excl_vat",
						'value' => utf8_ucfirst(__("amount excluding tax")),
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
						'value' => utf8_ucfirst(__("amount including tax")),
					),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("comment"),
						'id' => "comment",
						'value' => utf8_ucfirst(__("comment")),
					),
					array(
						'type' => "th",
						'class' => $this->determine_table_header_class("bank_name"),
						'id' => "bank_name",
						'value' => utf8_ucfirst(__("bank")),
					),
					array(
						'type' => "th",
						'id' => "split",
						'value' => utf8_ucfirst(__("split")),
					),
				),
			),
		);		
		return $grid;
	}
	
	function determine_table_header_class($header_column_name) {
		$class = "sort";
		if ($_SESSION['order_col_name'] == $header_column_name) {
			if ($_SESSION['order_direction'] == "ASC") {
				$class .= " sortedup";
			} else {
				$class .= " sorteddown";
			}
		}
		return $class;
	}

	function grid_body() {
		$accounts = new Accounts();
		$accounts->select();
		$accounts_names = $accounts->names();
		$types = new Types();
		$types->select();
		$types_name = $types->names();
		$sources = new Sources();
		$sources->select();
		$sources_name = $sources->names();
		$banks = new Banks();
		$banks->select();
		$banks_name = $banks->names();
		$grid = array();
		
		foreach ($this as $writing) {
			$class = "";
			$informations = $writing->show_further_information();
			if (!empty($informations)) {
				$class = "table_writings_comment";
			}
			$grid[$writing->id] =  array(
					'class' => "draggable",
					'id' => "table_".$writing->id,
					'cells' => array(
						array(
							'type' => "td",
							'value' => date("d", $writing->delay)."/".date("m", $writing->delay)."/".date("Y", $writing->delay),
						),
						array(
							'type' => "td",
							'value' => (isset($writing->account_id) && $writing->account_id > 0) ? $accounts_names[$writing->account_id] : "",
						),
						array(
							'type' => "td",
							'value' => (isset($writing->source_id) && $writing->source_id > 0) ? $sources_name[$writing->source_id] : "",
							),
						array(
							'type' => "td",
							'value' => (isset($writing->type_id) && $writing->type_id > 0) ? $types_name[$writing->type_id] : "",
						),
						array(
							'type' => "td",
							'value' => round($writing->amount_excl_vat, 2),
						),
						array(
							'type' => "td",
							'value' => $writing->vat,
						),
						array(
							'type' => "td",
							'value' => round($writing->amount_inc_vat, 2),
						),
						array(
							'type' => "td",
							'class' => $class,
							'value' => $writing->comment.$writing->show_further_information(),
						),
						array(
							'type' => "td",
							'value' => (isset($writing->bank_id) && $writing->bank_id > 0) ? $banks_name[$writing->bank_id] : "",
						),
						array(
							'type' => "td",
							'value' => $writing->form_split().
							"<div class=\"table_writings_modify\">".Html_Tag::a(link_content("content=lines.php&timestamp=".$_SESSION['timestamp']."&writings_id=".$writing->id)," ")."</div>".
							$writing->form_duplicate().$writing->form_delete(),
						),
					),
			);
		}
		return $grid;
	}

	function grid_footer() {
		return array();
	}

	function grid() {
		return $this->grid_header() + $this->grid_body() + $this->grid_footer();
	}
	
	function show() {
		$html_table = new Html_table(array('lines' => $this->grid()));
		return $html_table->show();
	}
	
	function display() {
		$html_table = new Html_table(array('lines' => $this->grid()));
		return "<div id=\"table_writings\">".$html_table->show()."</div>";
	}
	
	function show_timeline_at($timestamp) {
		$grid = array();
		$this->month = determine_first_day_of_month($timestamp);
		
		$timeline_iterator = strtotime('-2 months', $this->month);
		$timeline_stop = strtotime('+10 months', $this->month);
		
		$writings = new Writings();
		$writings->select();
		
		while ($timeline_iterator <= $timeline_stop) {
			$class = "navigation";
			if ($timeline_iterator == $this->month) {
				$class = "encours";
			} 
			$grid['leaves'][$timeline_iterator]['class'] = "heading_timeline_month_".$class;
			$next_month = determine_first_day_of_next_month($timeline_iterator);
			$balance = $writings->show_balance_at($next_month);
			if ($balance < 0) {
				$class = "negative_balance";
			} else {
				$class = "positive_balance";
			}
			$grid['leaves'][$timeline_iterator]['value'] = Html_Tag::a(link_content("content=lines.php&timestamp=".$timeline_iterator),
					utf8_ucfirst($GLOBALS['array_month'][date("n",$timeline_iterator)])."<br />".
					date("Y", $timeline_iterator))."<br /><br />
					<span class=\"".$class."\">".$balance."</span>";
			$timeline_iterator = $next_month;
		}
		$list = new Html_List($grid);
		$timeline = $list->show();

		return $timeline;
	}
	
	function display_timeline_at($timestamp) {
		return "<div id=\"heading_timeline\">".$this->show_timeline_at($timestamp)."</div>";
	}
	
	function get_where() {
		$query_where = parent::get_where();
		
		if (isset($this->filter['start'])) {
			$query_where[] = $this->db->config['table_writings'].".delay >= ".(int)$this->filter['start'];
		}
		if (isset($this->filter['stop'])) {
			$query_where[] = $this->db->config['table_writings'].".delay <= ".(int)$this->filter['stop'];
		}
		if (isset($this->filter['*']) && !empty($this->filter['*'])) {
			$query_where[] = $this->db->config['table_writings'].".search_index LIKE ".$this->db->quote("%".$this->filter['*']."%");
		}
		
		return $query_where;
	}
	
	function show_balance_on_current_date() {
		$summary = utf8_ucfirst(__("accounting on"))." ".get_time("d/m/Y")." : ".$this->show_balance_at(time())." ".__("€");
		return $summary;
	}
	
	function show_balance_at($timestamp) {
		$amount = 0;
		foreach ($this->instances as $writing) {
			if($writing->delay < $timestamp) {
				$amount = $amount + $writing->amount_inc_vat;
			}
		}
		return round($amount, 2);
	}
	
	function get_unique_key_in_array() {
		$this->select();
		$keys = array();
		foreach ($this as $writing) {
			if (!empty($writing->unique_key))
			$keys[] = $writing->unique_key;
		}
		return $keys;
	}
	
	function form_filter($value = "") {
		$form = "<div class=\"extra_filter_writings\"><form method=\"post\" name=\"extra_filter_writings_form\" action=\"\" enctype=\"multipart/form-data\">";
		$input_hidden_action = new Html_Input("action", "filter");
		$input = new Html_Input("extra_filter_writings_value",$value);
		$form .= $input_hidden_action->input_hidden().$input->item(utf8_ucfirst(__('filter')." : "));
		$form .= "</form></div>";
		return $form;
	}
	
	function filter_with() {
		$elements = func_get_args();
		foreach ($elements as  $element) {
			foreach ($element as $key => $value) {
			$this->filter[$key] = $value;
			}
		}
	}
}
