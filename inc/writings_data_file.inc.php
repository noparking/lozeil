<?php
/*
	lozeil
	$Author:  $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

class Writings_Data_File {
	public $file_name = "";
	public $tmp_name = "";
	public $banks_id = 0;
	public $csv_data = array();
	public $unique_keys = array();
	
	function __construct($tmp_name ="", $banks_id = null, $file_name = "") {
		$this->tmp_name = $tmp_name;
		$this->file_name = $file_name;
		$this->banks_id = $banks_id;
		$this->csv_data = array();
		
		$writings = new Writings();
		$writings->select_columns('unique_key');
		$writings->select();
		foreach ($writings as $writing) {
		$this->unique_keys[] = $writing->unique_key;
		}
	}
	
	function prepare_csv_data() {
		if ($file_opened = fopen( $this->tmp_name , 'r') ) {
			$row = 0;

            while(($data = fgetcsv($file_opened, 1000, ';')) !== FALSE) {
				foreach ($data as $key => $value) {
					$this->csv_data[$row][$key] = trim($value);
				}
	              $row++;
            }
			fclose($file_opened);
		} else {
			log_status(__('can not open file')." : ".$this->file_name);
		}
	}
	
	function import_as_cic() {
		$nb_records = 0;
		unset($this->csv_data[0]);
		foreach ($this->csv_data as $line) {
			if ($this->is_line_cic($line)) {
				$writing = new Writing();
				$time = explode("/", $line[1]);
				$writing->day = mktime(0, 0, 0, $time[1], $time[0], $time[2]);
				$writing->comment = $line[4];
				$writing->banks_id = $this->banks_id;
				if (!empty($line[2])) {
					$writing->amount_inc_vat = (float)str_replace(",", ".", $line[2]);
					$writing->amount_excl_vat = (float)str_replace(",", ".", $line[2]);
				} else {
					$writing->amount_inc_vat = (float)str_replace(",", ".", $line[3]);
					$writing->amount_excl_vat = (float)str_replace(",", ".", $line[3]);
				}
				$writing->paid = 1;
				$writing->unique_key = hash('md5', $writing->day.$writing->comment.$writing->banks_id.$writing->amount_inc_vat);
				if (!in_array($writing->unique_key, $this->unique_keys)) {
					$writing->save();
					$nb_records++;
				} else {
					log_status(__('line %s of file %s already exists', array(implode(' - ', $line), $this->file_name)));
				}
			} else {
				log_status(__('line %s of file %s is not in cic format', array(implode(' - ', $line), $this->file_name)));
			}
		}
		log_status(__(('%s new records for %s'), array(strval($nb_records), $this->file_name)));
	}
	
	function import_as_coop() {
		$nb_records = 0;
		$row_names = $this->csv_data[0];
		unset($this->csv_data[0]);

		foreach ($this->csv_data as $line) {
			if ($this->is_line_coop($line)) {
				$information = "";
				for ($i = 0; $i < count($line); $i++) {
					if (!empty($line[$i]) and $i != 0 and $i != 1 and $i != 3 and $i != 4) {
						$information .= $row_names[$i]." : ".$line[$i]."\n";
					}
				}
				$writing = new Writing();
				$time = explode("/", $line[0]);
				$writing->day = mktime(0, 0, 0, $time[1], $time[0], $time[2]);
				$writing->comment = $line[1];
				$writing->banks_id = $this->banks_id;
				if (!empty($information)) {
					$writing->information = utf8_encode($information);
				}
				if ($line[4] == "DEBIT") {
					$line[3] = "-".$line[3];
				}
				$writing->amount_inc_vat = (float)str_replace(",", ".", $line[3]);
				$writing->amount_excl_vat = (float)str_replace(",", ".", $line[3]);
				$writing->paid = 1;
				$writing->unique_key = hash('md5', $writing->day.$writing->comment.$writing->banks_id.$writing->amount_inc_vat);
				if (!in_array($writing->unique_key, $this->unique_keys)) {
					$writing->save();
					$nb_records++;
				} else {
					log_status(__('line %s of file %s already exists', array(implode(' - ', $line), $this->file_name)));
				}
			} else {
				log_status(__('line %s of file %s is not in coop format', array(implode(' - ', $line), $this->file_name)));
			}
		}
		log_status(__(('%s new records for %s'), array(strval($nb_records), $this->file_name)));
	}
	
	function form_import() {
		$form = "<div id=\"menu_actions_import\"><form method=\"post\" name=\"menu_actions_import_form\" action=\"".link_content("content=writingsimport.php")."\" enctype=\"multipart/form-data\">";
		$import_file = new Html_Input("menu_actions_import_file", "", "file");
		$submit = new Html_Input("menu_actions_import_submit", "Ok", "submit");
		$form .= "<a id=\"menu_actions_import_label\" href=\"\">".utf8_ucfirst(__("import bank statement"))."</a>".$import_file->item("").$submit->input();
		$form .= "</form></div>";
		return $form;
	}
	
	function form_export() {
		$date_picker_from = new Html_Input_Date('date_picker_from');
		$date_picker_to = new Html_Input_Date('date_picker_to');
		$date_picker_from->img_src = "medias/images/link_calendar_white.png";
		$date_picker_to->img_src = "medias/images/link_calendar_white.png";
		$form = "<div id=\"menu_actions_export\"><form method=\"post\" name=\"menu_actions_export_form\" action=\"".link_content("content=writingsexport.php")."\" enctype=\"multipart/form-data\">";
		$submit = new Html_Input("menu_actions_export_submit", "Ok", "submit");
		$form .= $date_picker_from->input().$date_picker_to->input().$submit->input();
		$form .= "</form></div>";
		return $form;
	}
	
	function import() {
		$banks = new Banks();
		$this->prepare_csv_data();
		if ($this->is_cic($this->csv_data)) {
			$this->banks_id = $banks->get_id_from_name("cic");
			if ($this->banks_id) {
				$this->import_as_cic();
			}
		} elseif ($this->is_coop($this->csv_data)) {
			$this->banks_id = $banks->get_id_from_name("coop");
			if ($this->banks_id) {
				$this->import_as_coop();
			}
		} else {
			log_status(__(('file %s is not in supported format'),  $this->file_name));
		}
	}
	
	function is_cic($data) {
		switch (true) {
			case $data[0][1] != "Date de valeur":
			case $data[0][5] != "Solde":
				return false;
			default :
				return true;
		}
	}
	
	function is_coop($data) {
		switch (true) {
			case $data[0][0] != "Date" :
			case $data[0][3] != "Montant" :
			case $data[0][4] != "Sens" :
				return false;
			default :
				return true;
		}
	}
	
	function is_line_cic($line) {
		$time = explode("/", $line[1]);
		
		switch (true) {
			case (!isset($time[1]) OR !isset($time[2])) :
			case !(empty($line[2]) XOR empty($line[3])) :
				return false;
			default :
				return true;
		}
	}
	
	function is_line_coop($line) {
		$day = str_replace("/", "", $line[0]);
		switch (true) {
		case strlen($day) != 8 :
		case empty($line[3]) :
		case ($line[4] != "DEBIT" AND $line[4] != "CREDIT") :
			return false;
		default :
			return true;
		}
	}
}