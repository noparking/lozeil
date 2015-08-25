<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

class Import_Writings extends Import_Data {
	public $nb_new_records = 0;
	public $nb_ignored_records = 0;
	public $unique_keys = array();

	function __construct($tmp_name = "", $file_name = "", $type = "") {
		parent::__construct($tmp_name, $file_name, $type);
	}

	function import_as_xlsx()  {
		$writings_imported = new Writings_Imported();

		$writings_imported->filter_with(array("sources_id" => $this->sources_id));
		$writings_imported->select();
		foreach ($writings_imported as $writing_imported) {
			$this->unique_keys[] = $writing_imported->hash;
		}

		foreach ($this->csv_data as $line) {
			if($this->is_line_koala($line)) {
				$writing = new Writing();
				$time = explode("/", $line[0]);
				$writing->day = mktime(0, 0, 0, $time[1], $time[0], $time[2]);
				if ($this->start == null or $this->start > $writing->day ) {
					$this->start = $writing->day;
				}
				$writing->comment = $line[2];
				$source = new source();
				$source->load(array('name'=> "koala"));
				if( $source->id == 0 ) {
					$source->name = "koala";
					$source->save();
				}
				$writing->sources_id = $source->id;
				$writing->information = $line[2];
				$writing->amount_inc_vat = (float)($line[3]);
				$accountingcode = new Accounting_Code();
				$accountingcode->load(array('number' => $line[1]));
				$writing->banks_id = 0;
				if ($accountingcode->id == 0) {
					$accountingcode->number = $line[1];
					$accountingcode->name = $line[2];
					$accountingcode->save();
				}
				$writing->accountingcodes_id = $accountingcode->id;
				$writing->paid = 1;

				$hash = hash('md5', $writing->day.$writing->comment.$writing->sources_id.$writing->amount_inc_vat.$writing->information);
				if (!in_array($hash, $this->unique_keys)) {
					$this->determine_start_stop($writing->day);
					$writing_imported = new Writing_Imported();
					$writing_imported->hash = $hash;
					$writing_imported->sources_id = $this->sources_id;
					$writing_imported->banks_id = $this->banks_id;
					$writing_imported->save();
					$writing->save();
					$this->nb_new_records++;
				} else {
					$this->nb_ignored_records++;
					log_status(__('line %s of file %s already exists', array(implode(' - ', $line), $this->file_name)));
				}
			}
		}
		log_status(__(('%s record(s) inserted for %s'), array(strval($this->nb_new_records), $this->file_name)));
	}

	function import_as_paybox() {
		$bayesianelements_categories_id = new Bayesian_Elements();
		$bayesianelements_categories_id->prepare_id_estimation($GLOBALS['dbconfig']['table_categories']);
		
		$bayesianelements_accounting_codes_id = new Bayesian_Elements();
		$bayesianelements_accounting_codes_id->prepare_id_estimation($GLOBALS['dbconfig']['table_accountingcodes']);
		
		$writings_imported = new Writings_Imported();
		$writings_imported->filter_with(array("sources_id" => $this->sources_id));
		$writings_imported->select();
		foreach ($writings_imported as $writing_imported) {
			$this->unique_keys[] = $writing_imported->hash;
		}
		
		$row_names = $this->csv_data[0];
		unset($this->csv_data[0]);
		
		foreach ($this->csv_data as $line) {
			if ($this->is_line_paybox($line)) {
				$information = "";
				foreach (array(4, 7, 15, 21, 23, 28, 30) as $nb) {
					if (!empty($line[$nb])) $information .= $row_names[$nb]." : ".$line[$nb]."\n";
				}
				$writing = new Writing();
				$time = explode("/", $line[6]);
				$writing->day = mktime(0, 0, 0, $time[1], $time[0], $time[2]);
				if ($this->start == null or $this->start > $writing->day ) {
					$this->start = $writing->day;
				}
				$writing->comment = $line[12]." ".$line[13];
				$writing->sources_id = $this->sources_id;
				$writing->information = utf8_encode($information);
				$writing->amount_inc_vat = (float)(substr($line[17], 0, -2).".".substr($line[17], -2));
				$writing->paid = 1;

				$hash = hash('md5', $writing->day.$writing->comment.$writing->sources_id.$writing->amount_inc_vat.$writing->information);
				
				if (!in_array($hash, $this->unique_keys)) {
					$this->determine_start_stop($writing->day);
					$writing_imported = new Writing_Imported();
					$writing_imported->hash = $hash;
					$writing_imported->sources_id = $this->sources_id;
					$writing_imported->save();
					$writing->categories_id = $bayesianelements_categories_id->fisher_element_id_estimated($writing);
					if ($writing->categories_id > 0) {
						$category = new Category();
						$category->load(array('id' => $writing->categories_id));
						$writing->vat = $category->vat;
					}
					$writing->accountingcodes_id = $bayesianelements_accounting_codes_id->fisher_element_id_estimated($writing);
					$writing->save();
					$this->nb_new_records++;
				} else {
					$this->nb_ignored_records++;
					log_status(__('line %s of file %s already exists', array(implode(' - ', $line), $this->file_name)));
				}
			} else {
				$this->nb_ignored_records++;
				log_status(__('line %s of file %s is not in coop format', array(implode(' - ', $line), $this->file_name)));
			}
		}
		log_status(__(('%s record(s) inserted for %s'), array(strval($this->nb_new_records), $this->file_name)));
	}

	function import_as_cic() {
		$bayesianelements_categories_id = new Bayesian_Elements();
		$bayesianelements_categories_id->prepare_id_estimation($GLOBALS['dbconfig']['table_categories']);
		
		$bayesianelements_accounting_codes_id = new Bayesian_Elements();
		$bayesianelements_accounting_codes_id->prepare_id_estimation($GLOBALS['dbconfig']['table_accountingcodes']);
		
		$writings_imported = new Writings_Imported();
		$writings_imported->filter_with(array("banks_id" => $this->banks_id));
		$writings_imported->select();
		foreach ($writings_imported as $writing_imported) {
			$this->unique_keys[] = $writing_imported->hash;
		}
		
		unset($this->csv_data[0]);
		foreach ($this->csv_data as $line) {
			if ($this->is_line_cic($line)) {
				$writing = new Writing();
				$time = explode("/", $line[1]);
				$writing->day = mktime(0, 0, 0, $time[1], $time[0], $time[2]);
				if ($this->start == null or $this->start > $writing->day ) {
					$this->start = $writing->day;
				}
				$writing->comment = $line[4];
				$writing->banks_id = $this->banks_id;
				$writing->amount_inc_vat = !empty($line[2]) ? (float)str_replace(",", ".", $line[2]) : (float)str_replace(",", ".", $line[3]);
				$writing->paid = 1;

				$hash = hash('md5', $writing->day.$writing->comment.$writing->banks_id.$writing->amount_inc_vat);
			
				if (!in_array($hash, $this->unique_keys)) {
					$this->determine_start_stop($writing->day);

					$writing_imported = new Writing_Imported();
					$writing_imported->hash = $hash;
					$writing_imported->banks_id = $this->banks_id;
					$writing_imported->save();
					$writing->categories_id = $bayesianelements_categories_id->fisher_element_id_estimated($writing);
					if ($writing->categories_id > 0) {
						$category = new Category();
						$category->load(array('id' => $writing->categories_id));
						$writing->vat = $category->vat;
					}
					$writing->accountingcodes_id = $bayesianelements_accounting_codes_id->fisher_element_id_estimated($writing);
					$writing->save();
					$this->nb_new_records++;
				} else {
					$this->nb_ignored_records++;
					log_status(__('line %s of file %s already exists', array(implode(' - ', $line), $this->file_name)));
				}
			} else {
				$this->nb_ignored_records++;
				log_status(__('line %s of file %s is not in cic format', array(implode(' - ', $line), $this->file_name)));
			}
		}
		log_status(__(('%s record(s) inserted for %s'), array(strval($this->nb_new_records), $this->file_name)));
	}
	
	
	function import_as_slk() {
		
		$bayesianelements_categories_id = new Bayesian_Elements();
		$bayesianelements_categories_id->prepare_id_estimation($GLOBALS['dbconfig']['table_categories']);
		
		$bayesianelements_accounting_codes_id = new Bayesian_Elements();
		$bayesianelements_accounting_codes_id->prepare_id_estimation($GLOBALS['dbconfig']['table_accountingcodes']);
		
		$writings_imported = new Writings_Imported();
		$writings_imported->filter_with(array("banks_id" => $this->banks_id));
		$writings_imported->select();
		foreach ($writings_imported as $writing_imported) {
			$this->unique_keys[] = $writing_imported->hash;
		}
		
		foreach ($this->csv_data as $line) {
				$writing = new Writing();
				$writing->day = $line[0];
				if ($this->start == null or $this->start > $writing->day ) {
					$this->start = $writing->day;
				}
				$writing->comment = $line[1];
				$writing->banks_id = $this->banks_id;
				$writing->amount_inc_vat = $line[2];
				$writing->paid = 1;
				$hash = hash('md5', $writing->day.$writing->comment.$writing->banks_id.$writing->amount_inc_vat);
				if (!in_array($hash, $this->unique_keys)) {
					$this->determine_start_stop($writing->day);

					$writing_imported = new Writing_Imported();
					$writing_imported->hash = $hash;
					$writing_imported->banks_id = $this->banks_id;
					$writing_imported->save();
					$writing->categories_id = $bayesianelements_categories_id->fisher_element_id_estimated($writing);
					if ($writing->categories_id > 0) {
						$category = new Category();
						$category->load(array('id' => $writing->categories_id));
						$writing->vat = $category->vat;
					}
					$writing->accountingcodes_id = $bayesianelements_accounting_codes_id->fisher_element_id_estimated($writing);
					$writing->save();
					$this->nb_new_records++;
				} else {
					$this->nb_ignored_records++;
					log_status(__('line %s of file %s already exists', array(implode(' - ', $line), $this->file_name)));
				}
			
		}
		log_status(__(('%s record(s) inserted for %s'), array(strval($this->nb_new_records), $this->file_name)));
	}
	
	
	
	function import_as_coop() {
		$bayesianelements_categories_id = new Bayesian_Elements();
		$bayesianelements_categories_id->prepare_id_estimation($GLOBALS['dbconfig']['table_categories']);
		
		$bayesianelements_accounting_codes_id = new Bayesian_Elements();
		$bayesianelements_accounting_codes_id->prepare_id_estimation($GLOBALS['dbconfig']['table_accountingcodes']);
		
		$writings_imported = new Writings_Imported();
		$writings_imported->filter_with(array("banks_id" => $this->banks_id));
		$writings_imported->select();
		foreach ($writings_imported as $writing_imported) {
			$this->unique_keys[] = $writing_imported->hash;
		}
		
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
				if ($this->start == null or $this->start > $writing->day ) {
					$this->start = $writing->day;
				}
				$writing->comment = $line[1];
				$writing->banks_id = $this->banks_id;
				$writing->information = utf8_encode($information);
				if ($line[4] == "DEBIT") {
					$line[3] = "-".$line[3];
				}
				$writing->amount_inc_vat = (float)str_replace(",", ".", $line[3]);
				$writing->paid = 1;

				$hash = hash('md5', $writing->day.$writing->comment.$writing->banks_id.$writing->amount_inc_vat);
				
				if (!in_array($hash, $this->unique_keys)) {
					$this->determine_start_stop($writing->day);

					$writing_imported = new Writing_Imported();
					$writing_imported->hash = $hash;
					$writing_imported->banks_id = $this->banks_id;
					$writing_imported->save();
					$writing->categories_id = $bayesianelements_categories_id->fisher_element_id_estimated($writing);
					if ($writing->categories_id > 0) {
						$category = new Category();
						$category->load(array('id' => $writing->categories_id));
						$writing->vat = $category->vat;
					}
					$writing->accountingcodes_id = $bayesianelements_accounting_codes_id->fisher_element_id_estimated($writing);
					$writing->save();
					$this->nb_new_records++;
				} else {
					$this->nb_ignored_records++;
					log_status(__('line %s of file %s already exists', array(implode(' - ', $line), $this->file_name)));
				}
			} else {
				$this->nb_ignored_records++;
				log_status(__('line %s of file %s is not in coop format', array(implode(' - ', $line), $this->file_name)));
			}
		}
		log_status(__(('%s record(s) inserted for %s'), array(strval($this->nb_new_records), $this->file_name)));
	}
	
	function import_as_ofx() {
		$bayesianelements_categories_id = new Bayesian_Elements();
		$bayesianelements_categories_id->prepare_id_estimation($GLOBALS['dbconfig']['table_categories']);
		
		$bayesianelements_accounting_codes_id = new Bayesian_Elements();
		$bayesianelements_accounting_codes_id->prepare_id_estimation($GLOBALS['dbconfig']['table_accountingcodes']);
		
		$writings_imported = new Writings_Imported();
		$writings_imported->filter_with(array("banks_id" => $this->banks_id));
		$writings_imported->select();
		foreach ($writings_imported as $writing_imported) {
			$this->unique_keys[] = $writing_imported->hash;
		}
		
		$blocks = preg_split("/<STMTTRN>/", file_get_contents($this->tmp_name));
		
		foreach($blocks as $block) {
			$block = strstr($block, "</STMTTRN>", true);
			
			$amount_inc_vat = 0;
			$day = 0;
			$comment = "";
			$information = "";
			
			if ($block) {
				$lines = explode("\n", $block);
				foreach($lines as $line) {
					if (strstr($line, "<TRNAMT>") !== false) {
						$amount_inc_vat = (float)str_replace("<TRNAMT>", "", $line);
					}
					if (strstr($line, "<DTPOSTED>") !== false) {
						$day = (int)strtotime(str_replace("<DTPOSTED>", "", $line));
					}
					if (strstr($line, "<NAME>") !== false) {
						$comment = trim(preg_replace('/\t+/', '', str_replace("<NAME>", "", $line)));
					}
					if (strstr($line, "<MEMO>") !== false) {
						$information = trim(preg_replace('/\t+/', '', str_replace("<MEMO>", "", $line)));
					}
				}
				$writing = new Writing();
				$writing->amount_inc_vat = $amount_inc_vat;
				$writing->day = $day;
				if ($this->start == null or $this->start > $writing->day ) {
					$this->start = $writing->day;
				}
				$writing->comment = $comment;
				$writing->information = $information;
				$writing->banks_id = $this->banks_id;
				$writing->paid = 1;

				$hash = hash('md5', $writing->day.$writing->comment.$writing->banks_id.$writing->amount_inc_vat);

				if (!in_array($hash, $this->unique_keys)) {
					$this->determine_start_stop($writing->day);

					$writing_imported = new Writing_Imported();
					$writing_imported->hash = $hash;
					$writing_imported->banks_id = $this->banks_id;
					$writing_imported->save();
					$writing->categories_id = $bayesianelements_categories_id->fisher_element_id_estimated($writing);
					if ($writing->categories_id > 0) {
						$category = new Category();
						$category->load(array('id' => $writing->categories_id));
						$writing->vat = $category->vat;
					}
					$writing->accountingcodes_id = $bayesianelements_accounting_codes_id->fisher_element_id_estimated($writing);
					$writing->save();
					$this->nb_new_records++;
				} else {
					$this->nb_ignored_records++;
					log_status(__('line %s of file %s already exists', array(implode(' - ', $lines), $this->file_name)));
				}
			}
		}
		log_status(__(('%s record(s) inserted for %s'), array(strval($this->nb_new_records), $this->file_name)));
	}
	
	function import_as_qif() {
		$bayesianelements_categories_id = new Bayesian_Elements();
		$bayesianelements_categories_id->prepare_id_estimation($GLOBALS['dbconfig']['table_categories']);
		
		$bayesianelements_accounting_codes_id = new Bayesian_Elements();
		$bayesianelements_accounting_codes_id->prepare_id_estimation($GLOBALS['dbconfig']['table_accountingcodes']);
		
		$writings_imported = new Writings_Imported();
		$writings_imported->filter_with(array("banks_id" => $this->banks_id));
		$writings_imported->select();
		foreach ($writings_imported as $writing_imported) {
			$this->unique_keys[] = $writing_imported->hash;
		}
		
		$blocks = explode("^", file_get_contents($this->tmp_name));
		
		foreach($blocks as $block) {
			$block = rtrim($block);
			if (!empty($block)) {
				$lines = explode("\n", $block);
				$amount_inc_vat = 0;
				$day = 0;
				$comment = "";
				$information = "";
				$goodline = false;
				foreach ($lines as $line) {
					if (!empty($line)) {
						$date_supposed = preg_split("/^D/", $line);
						$amount_supposed = preg_split("/^T/", $line);
						$comment_supposed = preg_split("/^P/", $line);
						$goodline |= (preg_match("/^N(.)*/", $line) > 0 )?true:false;
						if (isset($date_supposed[1])) {
							$date = explode("/", $date_supposed[1]);
							$date[2] = (strlen($date[2]) < 4) ? ($date[2] + 2000) : $date[2];
							$day = mktime(0, 0, 0, $date[1], $date[0], $date[2]);
							
						} elseif (isset($amount_supposed[1])) {
							$amount_inc_vat = (float)$amount_supposed[1];
						} elseif (isset($comment_supposed[1])) {
							$comment = $comment_supposed[1];
						} elseif (preg_match("/^N(.)*/", $line) <= 0 ) {
							$header = preg_split("/^!/", $line);
							if (!isset($header[1])) {
								$information .= substr($line, 1)."\n";
							}
						}
					}
				}

				if($goodline) {
					$writing = new Writing();
					$writing->amount_inc_vat = $amount_inc_vat;
					$writing->day = $day;
					if ($this->start == null or $this->start > $writing->day ) {
						$this->start = $writing->day;
					}
					$writing->comment = $comment;
					$writing->information = $information;
					$writing->banks_id = $this->banks_id;
					$writing->paid = 1;
					

					$hash = hash('md5', $writing->day.$writing->comment.$writing->banks_id.$writing->amount_inc_vat);

					if (!in_array($hash, $this->unique_keys)) {
						$this->determine_start_stop($writing->day);

						$writing_imported = new Writing_Imported();
						$writing_imported->hash = $hash;
						$writing_imported->banks_id = $this->banks_id;
						$writing_imported->save();
						$writing->categories_id = $bayesianelements_categories_id->fisher_element_id_estimated($writing);
						if ($writing->categories_id > 0) {
							$category = new Category();
							$category->load(array('id' => $writing->categories_id));
							$writing->vat = $category->vat;
						}
						$writing->accountingcodes_id = $bayesianelements_accounting_codes_id->fisher_element_id_estimated($writing);
						$writing->save();
						$this->nb_new_records++;
					} else {
						$this->nb_ignored_records++;
						log_status(__('line %s of file %s already exists', array(implode(' - ', $lines), $this->file_name)));
					}
				}
			}
			log_status(__(('%s record(s) inserted for %s'), array(strval($this->nb_new_records), $this->file_name)));
		}
	}
}
