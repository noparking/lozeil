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
						'id' => "amount_excl_tax",
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
						'id' => "amount_inc_tax",
						'value' => utf8_ucfirst(__("amount including tax")),
					),
					array(
						'type' => "th",
						'class' => "sort",
						'id' => "paid",
						'value' => utf8_ucfirst(__("paid")),
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
		$grid = array();

		foreach ($this as $writing) {
			$grid[$writing->id] = array(
				'class' => "draggable",
				'id' => $writing->id,
				'cells' => array(
					date("d", $writing->delay)."/".date("m", $writing->delay)."/".date("Y", $writing->delay),
					$accounts_names[$writing->account_id],
					$sources_name[$writing->source_id],
					$types_name[$writing->type_id],
					round($writing->amount_excl_tax, 2),
					$writing->vat,
					round($writing->amount_inc_tax, 2),
					$writing->paid_to_text($writing->paid),
					$writing->form_split().$writing->form_edit().
					$writing->form_duplicate().$writing->form_delete()
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
		$grid['leaves']['date']['value'] = "<strong>".$GLOBALS['array_month'][date("n", $_SESSION['month_encours'])]."</strong>";
		$grid['leaves']['date']['class'] = "timeline_month";

		$encours = $_SESSION['month_encours'];
		$this->month = $encours;
		$this->start = strtotime('-2 months', $encours);
		$this->stop = strtotime('+10 months', $encours);
		$start = $this->start;
		while ($start <= $this->stop) {
			if ($this->month == $start) {
				$grid['leaves'][$start]['class'] = "timeline_month_encours";
			}
			$grid['leaves'][$start]['value'] = "<a href=\"".link_content("content=".$content."&month=".$start)."\">".date("m/Y", $start)."</a>";
			$start = mktime(0, 0, 0, date("m", $start) + 1, 1, date("Y", $start));
		}
		$timeline = "<span class=\"timeline\">";
		$list = new Html_List($grid);
		$timeline .= $list->show();
		$timeline .= "</span>";

		return $timeline;
	}
	
	function get_where() {
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
		
	}
}
