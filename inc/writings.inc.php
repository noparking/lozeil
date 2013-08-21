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
	
	function __construct($class = null, $table = null, $db = null) {
		$this->account_id = 1;
		$this->source_id = 1;
		$this->type_id = 1;
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

		if (isset($this->account_id)) {
			$join[] = "
				LEFT JOIN ".$this->db->config['table_accounts']."
				ON ".$this->db->config['table_accounts'].".id = ".$this->db->config['table_writings'].".account_id
			";	
	}
		if (isset($this->source_id)) {
			$join[] = "
				LEFT JOIN ".$this->db->config['table_sources']."
				ON ".$this->db->config['table_sources'].".id = ".$this->db->config['table_writings'].".source_id
			";
		}
		if (isset($this->type_id)) {
			$join[] = "
				LEFT JOIN ".$this->db->config['table_types']."
				ON ".$this->db->config['table_types'].".id = ".$this->db->config['table_writings'].".type_id
			";
		}
		
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
						'value' => __("Delay"),
					),
					array(
						'type' => "th",
						'class' => "sort",
						'id' => "account_name",
						'value' => __("Account"),
					),
					array(
						'type' => "th",
						'class' => "sort",
						'id' => "source_name",
						'value' => __("Source"),
						),
					array(
						'type' => "th",
						'class' => "sort",
						'id' => "type_name",
						'value' => __("Type"),
					),
					array(
						'type' => "th",
						'class' => "sort",
						'id' => "amount_excl_tax",
						'value' => __("Amount_excl_tax"),
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
						'value' => __("Amount_inc_tax"),
					),
					array(
						'type' => "th",
						'class' => "sort",
						'id' => "paid",
						'value' => __("Paid"),
					),
					array(
						'type' => "th",
						'id' => "split",
						'value' => __("Split"),
					),
				),
			),
		);		
		return $grid;
	}

	function grid_body() {
		$grid = array();

		foreach ($this as $writing) {
			$grid[$writing->id] = array(
				'class' => "draggable",
				'id' => $writing->id,
				'cells' => array(
					date("d", $writing->delay)."/".date("m", $writing->delay)."/".date("Y", $writing->delay),
					$writing->get_name_from_table("account"),
					$writing->get_name_from_table("source"),
					$writing->get_name_from_table("type"),
					round($writing->amount_excl_tax, 2),
					$writing->vat,
					round($writing->amount_inc_tax, 2),
					$writing->paid_to_text($writing->paid),
					"<button class=\"split\">".__("Split")."</button>"
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
		if (empty($_REQUEST)) {
			return "<div class=\"table_drag_drop\">".show_table(array('lines' => $this->grid()))."</div>";
		} else {
			return show_table(array('lines' => $this->grid()));
		}
	}
}
