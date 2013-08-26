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
	
	private $start = 0;
	private $stop = 0;
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
		
		return $join;
	}
	
	function get_columns() {
		$columns = parent::get_columns();
		$columns[] = $this->db->config['table_accounts'].".name as account_name, ".$this->db->config['table_sources'].".name as source_name, ".$this->db->config['table_types'].".name as type_name";

		return $columns;
	}
	
	function show_in_determined_order() {
		$this->determine_order();
		$this->select();
		return $this->show();
	}
	
	function determine_order() {
		if (!empty($_REQUEST['sort_by'])) {
			if ($_REQUEST['order_direction'] == 0) {
				$this->set_order($_REQUEST['sort_by'], 'ASC');
			} else {
				$this->set_order($_REQUEST['sort_by'], 'DESC');
			}
		}
	}
	
	function grid_header() {
		$grid = array(
			'header' => array(
				'class' => "grid_header",
				'cells' => array(
					array(
						'type' => "th",
						'class' => "sort",
						'id' => "delay",
						'value' => utf8_ucfirst(__("delay")),
					),
					array(
						'type' => "th",
						'class' => "sort",
						'id' => "account_name",
						'value' => utf8_ucfirst(__("account")),
					),
					array(
						'type' => "th",
						'class' => "sort",
						'id' => "source_name",
						'value' => utf8_ucfirst(__("source")),
						),
					array(
						'type' => "th",
						'class' => "sort",
						'id' => "type_name",
						'value' => utf8_ucfirst(__("type")),
					),
					array(
						'type' => "th",
						'class' => "sort",
						'id' => "amount_excl_vat",
						'value' => utf8_ucfirst(__("amount excluding tax")),
					),
					array(
						'type' => "th",
						'class' => "sort",
						'id' => "vat",
						'value' => __("VAT"),
					),
					array(
						'type' => "th",
						'class' => "sort",
						'id' => "amount_inc_vat",
						'value' => utf8_ucfirst(__("amount including tax")),
					),
					array(
						'type' => "th",
						'class' => "sort",
						'id' => "comment",
						'value' => utf8_ucfirst(__("comment")),
					),
					array(
						'type' => "th",
						'class' => "sort",
						'id' => "bank",
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
//		foreach ($this as $writing) {
//			$grid[$writing->id] = array(
//				'class' => "draggable",
//				'id' => $writing->id,
//				'cells' => array(
//					date("d", $writing->delay)."/".date("m", $writing->delay)."/".date("Y", $writing->delay),
//					isset($writing->account_id) && $writing->account_id > 0 ? $accounts_names[$writing->account_id] : "",
//					isset($writing->source_id) && $writing->source_id > 0 ? $sources_name[$writing->source_id] : "",
//					isset($writing->type_id) && $writing->type_id > 0 ? $types_name[$writing->type_id] : "",
//					round($writing->amount_excl_vat, 2),
//					$writing->vat,
//					round($writing->amount_inc_vat, 2),
//					$writing->comment.$writing->show_further_information(),
//					isset($writing->bank_id) && $writing->bank_id > 0 ? $banks_name[$writing->bank_id] : "",
//					$writing->paid_to_text($writing->paid),
//					$writing->form_split().$writing->form_edit().
//					$writing->form_duplicate().$writing->form_delete()
//				),
//			);
//		}
		
		foreach ($this as $writing) {
			$class = "";
			if (!is_int($writing->show_further_information())) {
				$class = "comment";
			}
			$grid[$writing->id] =  array(
					'class' => "draggable",
					'cells' => array(
						array(
							'type' => "td",
							'value' => date("d", $writing->delay)."/".date("m", $writing->delay)."/".date("Y", $writing->delay),
						),
						array(
							'type' => "td",
							'value' => isset($writing->account_id) && $writing->account_id > 0 ? $accounts_names[$writing->account_id] : "",
						),
						array(
							'type' => "td",
							'value' => isset($writing->source_id) && $writing->source_id > 0 ? $sources_name[$writing->source_id] : "",
							),
						array(
							'type' => "td",
							'value' => isset($writing->type_id) && $writing->type_id > 0 ? $types_name[$writing->type_id] : "",
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
							'value' => isset($writing->bank_id) && $writing->bank_id > 0 ? $banks_name[$writing->bank_id] : "",
						),
						array(
							'type' => "td",
							'value' => $writing->form_split().$writing->form_edit().$writing->form_duplicate().$writing->form_delete(),
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
		if (empty($_REQUEST) or (isset($_REQUEST['content']) and $_REQUEST['content'] != "lines.ajax.php")) {
			return "<div class=\"table_drag_drop\">".$html_table->show()."</div>";
		} else {
			return $html_table->show();
		}
	}
	
	function show_timeline($content="lines.php") {
		$grid = array();

		$encours = $_SESSION['month_encours'];
		$this->month = $encours;
		$this->start = strtotime('-2 months', $encours);
		$this->stop = strtotime('+10 months', $encours);
		$start = $this->start;
		while ($start <= $this->stop) {
			if ($this->month == $start) {
				$grid['leaves'][$start]['class'] = "timeline_month_encours";
			} else {
				$grid['leaves'][$start]['class'] = "timeline_month_navigation";
			}
			$grid['leaves'][$start]['value'] = "<a href=\"".link_content("content=".$content."&month=".$start)."\">".utf8_ucfirst($GLOBALS['array_month'][date("n",$start)])."<br />".date("Y", $start)."</a>";
			$start = mktime(0, 0, 0, date("m", $start) + 1, 1, date("Y", $start));
		}
		$timeline = "<span class=\"timeline\">";
		$list = new Html_List($grid);
		$timeline .= $list->show();
		$timeline .= "</span>";

		return $timeline;
	}
	
	function get_where() {
		if ($this->filter == "month") {
			if ($this->month == 0) {
				$this->month = $_SESSION['month_encours'];
			}
			$query_where[] = $this->db->config['table_writings'].".delay >= ".(int)$this->month;
			$query_where[] = $this->db->config['table_writings'].".delay < ".(int)strtotime('+1 months', $this->month);
			if(isset($query_where)) {
				return $query_where;
			} else {
				return array(1);
			}
		} else return array(1);
	}
	
	function show_balance_on_current_date() {
		$date = date("d", time())."/".date("m", time())."/".date("Y", time());
		$balance = $this->balance_on_date(time());
		$summary = utf8_ucfirst(__("accounting on"))." ".$date." : ".$balance." ".__("€");
		return $summary;
	}
	
	function balance_on_date($timestamp) {
		$amount = 0;
		foreach ($this->instances as $writing) {
			if($writing->delay < $timestamp) {
				$amount = $amount + $writing->amount_inc_vat;
			}
		}
		return round($amount, 2);
	}
	
	function form_import($title) {
		$banks = new Banks();
		$banks->select();
		$banks_name = $banks->names();
		$form = "<div class=\"import\"><form method=\"post\" name=\"import_writings\" id=\"import_writings\" action=\"\" enctype=\"multipart/form-data\">";
		$input_hidden_action = new Html_Input("action", "import");
		$input_file = new Html_Input("input_file", "", "file");
		$bank = new Html_Select("bank_id", $banks_name);
		$submit = new Html_Input("duplicate_submit", "Ok", "submit");
		$form .= $input_hidden_action->input_hidden().$input_file->item(utf8_ucfirst($title)).$bank->item(__('bank')).$submit->input();
		$form .= "</form></div>";
		return $form;
	}
	
	function import_cic($file) {
		if ($file_opened = fopen( $file['tmp_name'] , 'r') ) {
			$row = 0;
			$csv = array();

            while(($data = fgetcsv($file_opened, 1000, ';')) !== FALSE) {

                $csv[$row]['delay'] = $data[1];
                $csv[$row]['debit'] = $data[2];
                $csv[$row]['credit'] = $data[3];
                $csv[$row]['comment'] = $data[4];

                $row++;
            }
			fclose($file_opened);
			unset($csv[0]);
			foreach ($csv as $data) {
				$writing = new Writing();
				$time = explode("/", $data['delay']);
				$writing->delay = mktime(0, 0, 0, $time[1], $time[0], $time[2]);
				$writing->comment = $data['comment'];
				$writing->bank_id = (int)$_POST['bank_id'];
				if (!empty($data['debit'])) {
					$writing->amount_inc_vat = (float)str_replace(",", ".", $data['debit']);
				} else {
					$writing->amount_inc_vat = (float)str_replace(",", ".", $data['credit']);
				}
				$writing->save();
			}
		}
	}
	
	function import_coop($file) {
		if ($file_opened = fopen( $file['tmp_name'] , 'r') ) {
			$row = 0;
			$csv = array();

            while(($data = fgetcsv($file_opened, 1000, ';')) !== FALSE) {
				foreach ($data as $key => $value) {
					if ($key == 0) {
						$time = explode("/", $value);
						if (isset($time[1]) && $time[2]) {
							$value = mktime(0, 0, 0, $time[1], $time[0], $time[2]);
						}
					}
					if ($key == 3) {
						$value = (float)str_replace(",", ".", $value);
					}
					$csv[$row][$key] = trim($value);
				}
	              $row++;
            }
			fclose($file_opened);
			$row_names = $csv[0];
			unset($csv[0]);
			foreach ($csv as $data) {
				$information = "";
				for ($i = 0; $i < count($data); $i++) {
					if (!empty($data[$i]) && $i != 0 && $i != 1 && $i != 3 && $i != 4) {
						$information .= $row_names[$i]." : ".$data[$i]."\n";
					}
				}
				$writing = new Writing();
				$writing->delay = $data[0];
				$writing->comment = $data[1];
				$writing->bank_id = (int)$_POST['bank_id'];
				if (!empty($information)) {
					$writing->information = utf8_encode($information);
				}
				if ($data[4] == "DEBIT") {
					$data[3] = "-".$data[3];
				}
				$writing->amount_inc_vat = (float)$data[3];
				$writing->save();
			}
		}
	}
}
