<?php
/*
	lozeil
	$Author:  $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

class Import {
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
			$writings = new Writings();
			$writings->select();
			$writings_key = $writings->get_unique_key_in_array();
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
				$writing->unique_key = hash('md5', $writing->delay.$writing->comment.$writing->bank_id.$writing->amount_inc_vat);
				if (!in_array($writing->unique_key, $writings_key)) {
					$writing->save();
				}
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
			$writings = new Writings();
			$writings->select();
			$writings_key = $writings->get_unique_key_in_array();
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
				$writing->unique_key = hash('md5', $writing->delay.$writing->comment.$writing->bank_id.$writing->amount_inc_vat);
				if (!in_array($writing->unique_key, $writings_key)) {
					$writing->save();
				}
			}
		}
	}
	
	function form_import($label) {
		$banks = new Banks();
		$banks->select();
		$banks_name = $banks->names();
		$form = "<div class=\"import\"><form method=\"post\" name=\"import_writings\" id=\"import_writings\" action=\"\" enctype=\"multipart/form-data\">";
		$input_hidden_action = new Html_Input("action", "import");
		$input_file = new Html_Input("input_file", "", "file");
		$bank = new Html_Select("bank_id", $banks_name);
		$submit = new Html_Input("import_submit", "Ok", "submit");
		$form .= $input_hidden_action->input_hidden().$input_file->item(utf8_ucfirst($label)).$bank->item(__('bank')).$submit->input();
		$form .= "</form></div>";
		return $form;
	}
}