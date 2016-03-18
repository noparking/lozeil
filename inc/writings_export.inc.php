<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

class Writings_Export  {
	public $from;
	public $to;
	
	function __construct(db $db = null) {
		if ($db === null) {
			$db = new db();
		}
		$this->db = $db;
	}
	
	function get_form() {
		list($from , $to) = determine_fiscal_year(time());
		$date_picker_from = new Html_Input_Date('date_picker_from', $from);
		$date_picker_to = new Html_Input_Date('date_picker_to', $to);
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
		} else {
			$this->from =  mktime(0, 0, 0, 1, 1, date('Y'));
		}
		if (is_datepicker_valid($post['date_picker_to'])) {
			$this->to = timestamp_from_datepicker($post['date_picker_to']);
		} else {
			$this->to =  mktime(0, 0, 0, 12, 31, date('Y'));
		}
	}
	
	function export() {
		$title = array(
			__("Date"),
			__("Journal"),
			__("Ledger code"),
			__("Number"),
			__("Details"),
			__("Debit"),
			__("Credit"),
			"E",
		);
		
		$writings = new Writings();
		$writings->filters['start'] = $this->from;
		$writings->filters['stop'] = $this->to;
		$writings->select();
		
		$accounting_codes = new Accounting_Codes();
		$accounting_codes->select();
		$accountingcodes_numbers = $accounting_codes->numbers();
		
		$values = array();
		foreach ($writings as $writing) {
			$values[] = array(
				'day' => $writing->day,
				'journal' => "BQC-".$writing->banks_id,
				'ledger' => isset($accountingcodes_numbers[$writing->accountingcodes_id]) ? $accountingcodes_numbers[$writing->accountingcodes_id] : "",
				'number' => $writing->number,
				'details' => $writing->comment,
				'debit' => ($writing->amount_inc_vat < 0) ? abs((float)$writing->amount_inc_vat) : 0,
				'credit' => ($writing->amount_inc_vat >= 0) ? (float)$writing->amount_inc_vat : 0,
				'E' => "E",
			);
		}

		export_excel($title, $values);
	}

	function export_synthese ($vat = true) {
		$reportings = new Reportings();
		$activities = new Activities();
		$activities->select();
		$global = $reportings->statbyreporting($this->from , $this->to, $vat);
		export_synthese_excel($this->from,$this->to,$global);
	}
}
