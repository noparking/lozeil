<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Writings_Export  {
	public $from;
	public $to;
	
	function __construct(db $db = null) {
		if ($db === null) {
			$db = new db();
		}
		$this->db = $db;
	}
	
	static function get_form()
	{
		list($from , $to) = determine_fiscal_year(time());
		$date_picker_from = new Html_Input_Date('date_picker_from',$from);
		$date_picker_to = new Html_Input_Date('date_picker_to',$to);
		$date_picker_from->img_src = $GLOBALS['config']['layout_mediaserver']."medias/images/link_calendar.png";
		$date_picker_to->img_src = $GLOBALS['config']['layout_mediaserver']."medias/images/link_calendar.png";

		$form =  "<center><div class=\"form\" ><form method=\"post\"  action=\"\" enctype=\"multipart/form-data\"> <table>";
		$submit = new Html_Input("menu_actions_export_submit", __('export'), "submit");
		$form .= "<tr><td>".utf8_ucfirst(__('date picker from'))." : </td><td>".$date_picker_from->input()."</td></tr>";
		$form .= "<tr><td>".utf8_ucfirst(__('date picker to'))."   : </td><td>".$date_picker_to->input()."</td></tr>";
		$form .= "<tr><td>".$submit->input()."</td></tr>";
		$form .= "</table></form></div></center>";
		return $form;
	}
	
	function clean_and_set($post) {
		if (is_datepicker_valid($post['date_picker_from'])) {
			$this->from = timestamp_from_datepicker($post['date_picker_from']);
		}
		else {
			$this->from =  mktime(0, 0, 0, 1, 1, date('Y'));
		}
		if (is_datepicker_valid($post['date_picker_to'])) {
			$this->to = timestamp_from_datepicker($post['date_picker_to']);
		}
		else {
			$this->to =  mktime(0, 0, 0, 12, 31, date('Y'));
		}
	}
	
	function export() {
		$querywhere = "";
	  
		if (isset($this->from)) {
			$querywhere .= " AND ".$this->db->config['table_writings'].".day >= ".$this->from;
		}
		if (!isset($this->to)) {
			$this->to = time();
		}
		$querywhere .= " AND ".$this->db->config['table_writings'].".day <= ".$this->to;
	  
		$result_export = $this->db->query("SELECT ".
			$this->db->config['table_writings'].".day as '0', ".
			$this->db->config['table_writings'].".banks_id as '1', ".
			$this->db->config['table_accountingcodes'].".number as '2', ".
			$this->db->config['table_writings'].".number as '3', ".
			$this->db->config['table_writings'].".comment as '4', ".
			$this->db->config['table_writings'].".information, ".
			$this->db->config['table_writings'].".amount_inc_vat as '5' 
			FROM ".$this->db->config['table_writings'].
			" LEFT JOIN ".$this->db->config['table_accountingcodes'].
			" ON ".$this->db->config['table_accountingcodes'].".id = ".$this->db->config['table_writings'].".accountingcodes_id".
			" WHERE (1=1)".
			$querywhere.
			" ORDER BY day ASC"
		);
		if ($result_export[1] > 0) {
			while ($row_export = $this->db->fetch_array($result_export[0])) {
				$value[] = $row_export;
			}
	    
			for ($i = 0; $i < count($value); $i++) {
				$value[$i][0] = date("d/m/Y", $value[$i][0]);
	      
				$value[$i][1] = "BQC-".$value[$i][1];
	      
				$value[$i][4] .= " ".$value[$i]["information"];
				unset($value[$i]["information"]);
	      
				if ($value[$i][5] >= 0) {
					$value[$i][5] = (float)$value[$i][5];
					$value[$i][6] = 0;
				} else {
					$value[$i][6] = -(float)$value[$i][5];
					$value[$i][5] = 0;
				}
	      
				$value[$i][7] = "E";
	      
				ksort($value[$i]);
			}
			export_excel("", $value);
		}
	}
	
	

	function export_synthese ($vat = true) {
		$reportings = new Reportings();
		$activities = new Activities();
		$activities->select();
		$global = $reportings->statbyreporting($this->from , $this->to, $vat);
		export_synthese_excel($this->from,$this->to,$global);
	}
  }
