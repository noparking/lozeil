<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

class Import_Balances extends Import_Data {
	public $nb_new_records = 0;
	public $nb_ignored_records = 0;
	public $unique_keys = array();

	function __construct($tmp_name = "", $file_name = "", $type = "") {
		parent::__construct($tmp_name, $file_name, $type);
	}

	function import_as_xlsx()  {
		$balances_imported = new Balances_Imported();
		$balances_imported->select();
		foreach ($balances_imported as $balance_imported) {
			$this->unique_keys[] = $balance_imported->hash;
		}

		$period = new Balance_Period();
		$period->start = $this->t_start;
		$period->stop = $this->t_stop;
		$period->save();

		foreach ($this->csv_data as $line) {
			if($this->is_line_koala($line)) {
				$time = explode("/", $line[0]);
				$day = mktime(0, 0, 0, $time[1], $time[0], $time[2]);
				if ($this->start == null or $this->start > $day ) {
					$this->start = $day;
				}

				$accountingcode = new Accounting_Code();
				$accountingcode->load(array('number' => $line[1]));
				if ($accountingcode->id == 0) {
					$accountingcode->number = $line[1];
					$accountingcode->name = $line[2];
					$accountingcode->save();
				}

				$balance = new Balance();
				$balance->load(array('accountingcodes_id' => $accountingcode->id, 'day' => $day));
				$balance->accountingcodes_id = $accountingcode->id;
				$balance->period_id = $period->id;
				$balance->number = $accountingcode->number;
				$balance->name = $line[2];
				$balance->amount += (float)($line[3]);
				$balance->day = $day;

				$hash = hash('md5', $balance->accountingcodes_id.$balance->day);
				if (!in_array($hash, $this->unique_keys)) {
					$balance->save();
					$balance_imported = new Balance_Imported();
					$balance_imported->load(array('balance_id' => $balance->id));
					$balance_imported->hash = $hash;
					$balance_imported->balance_id = $balance->id;
					$balance_imported->save();
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
		$bayesianelements_accounting_codes_id = new Bayesian_Elements();
		$bayesianelements_accounting_codes_id->prepare_id_estimation($GLOBALS['dbconfig']['table_accountingcodes']);

		$balances_imported = new Balances_Imported();
		$balances_imported->select();
		foreach ($balances_imported as $balance_imported) {
			$this->unique_keys[] = $balance_imported->hash;
		}

		$row_names = $this->csv_data[0];
		unset($this->csv_data[0]);
		
		$period = new Balance_Period();
		$period->span = $this->span;
		$period->save();

		foreach ($this->csv_data as $line) {
			if ($this->is_line_paybox($line)) {
				$time = explode("/", $line[6]);
				$day = mktime(0, 0, 0, $time[1], $time[0], $time[2]);
				if ($this->start == null or $this->start > $day ) {
					$this->start = $day;
				}

				$writing = new Writing();
				$writing->comment = $line[12]." ".$line[13];
				$writing->sources_id = $this->sources_id;
				$writing->amount_inc_vat = (float)(substr($line[17], 0, -2).".".substr($line[17], -2));
				$writing->paid = 1;
				$writing->day = $day;

				$code = new Accounting_Code();
				$code->load(array('id' => $bayesianelements_accounting_codes_id->fisher_element_id_estimated($writing)));

				unset($writing);

				$balance = new Balance();
				$balance->load(array('accountingcodes_id' => $code->id, 'day' => $day));
				$balance->number = $code->number;
				$balance->accountingcodes_id = $code->id;
				$balance->period_id = $period->id;
				$balance->name = $line[12]." ".$line[13];
				$balance->amount += (float)(substr($line[17], 0, -2).".".substr($line[17], -2));
				$balance->day = $day;

				$hash = hash('md5', $balance->accountingcodes_id.$balance->day);
				if (!in_array($hash, $this->unique_keys)) {
					$balance->save();
					$balance_imported = new Balance_Imported();
					$balance_imported->load(array('balance_id' => $balance->id));
					$balance_imported->hash = $hash;
					$balance_imported->balance_id = $balance->id;
					$balance_imported->save();
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
		$bayesianelements_accounting_codes_id = new Bayesian_Elements();
		$bayesianelements_accounting_codes_id->prepare_id_estimation($GLOBALS['dbconfig']['table_accountingcodes']);
		
		
		$balances_imported = new Balances_Imported();
		$balances_imported->select();
		foreach ($balances_imported as $balance_imported) {
			$this->unique_keys[] = $balance_imported->hash;
		}
		
		$row_names = $this->csv_data[0];
		unset($this->csv_data[0]);

		$period = new Balance_Period();
		$period->span = $this->span;
		$period->save();

		foreach ($this->csv_data as $line) {
			if ($this->is_line_cic($line)) {
				$time = explode("/", $line[1]);
				$day = mktime(0, 0, 0, $time[1], $time[0], $time[2]);
				if ($this->start == null or $this->start > $day ) {
					$this->start = $day;
				}

				$writing = new Writing();
				$writing->comment = $line[4];
				$writing->banks_id = $this->banks_id;
				$writing->amount_inc_vat = !empty($line[2]) ? (float)str_replace(",", ".", $line[2]) : (float)str_replace(",", ".", $line[3]);
				$writing->paid = 1;
				$writing->day = $day;

				$code = new Accounting_Code();
				$code->load(array('id' => $bayesianelements_accounting_codes_id->fisher_element_id_estimated($writing)));

				unset($writing);

				$balance = new Balance();
				$balance->load(array('accountingcodes_id' => $code->id, 'day' => $day));
				$balance->number = $code->number;
				$balance->name = $line[4];
				$balance->amount += !empty($line[2]) ? (float)str_replace(",", ".", $line[2]) : (float)str_replace(",", ".", $line[3]);
				$balance->accountingcodes_id = $code->id;
				$balance->period_id = $period->id;
				$balance->day = $day;

				$hash = hash('md5', $balance->accountingcodes_id.$balance->day);
				if (!in_array($hash, $this->unique_keys)) {
					$balance->save();
					$balance_imported = new Balance_Imported();
					$balance_imported->load(array('balance_id' => $balance->id));
					$balance_imported->hash = $hash;
					$balance_imported->balance_id = $balance->id;
					$balance_imported->save();
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
	
	function import_as_coop() {
		$bayesianelements_accounting_codes_id = new Bayesian_Elements();
		$bayesianelements_accounting_codes_id->prepare_id_estimation($GLOBALS['dbconfig']['table_accountingcodes']);
		
		$balances_imported = new Balances_Imported();
		$balances_imported->select();
		foreach ($balances_imported as $balance_imported) {
			$this->unique_keys[] = $balance_imported->hash;
		}
		
		$row_names = $this->csv_data[0];
		unset($this->csv_data[0]);

		$period = new Balance_Period();
		$period->span = $this->span;
		$period->save();
		
		foreach ($this->csv_data as $line) {
			if ($this->is_line_coop($line)) {
				$time = explode("/", $line[0]);
				$day = mktime(0, 0, 0, $time[1], $time[0], $time[2]);
				if ($this->start == null or $this->start > $day ) {
					$this->start = $day;
				}

				$writing = new Writing();
				$writing->comment = $line[1];
				$writing->banks_id = $this->banks_id;
				if ($line[4] == "DEBIT") {
					$line[3] = "-".$line[3];
				}
				$writing->amount_inc_vat = (float)str_replace(",", ".", $line[3]);
				$writing->paid = 1;
				$writing->day = $day;

				$code = new Accounting_Code();
				$code->load(array('id' => $bayesianelements_accounting_codes_id->fisher_element_id_estimated($writing)));

				unset($writing);

				$balance = new Balance();				
				$balance->load(array('accountingcodes_id' => $code->id, 'day' => $day));
				$balance->number = $code->number;
				$balance->name = $line[1];
				$balance->amount += (float)str_replace(",", ".", $line[3]);
				$balance->accountingcodes_id = $code->id;
				$balance->period_id = $period->id;
				$balance->day = $day;

				$hash = hash('md5', $balance->accountingcodes_id.$balance->day);
				if (!in_array($hash, $this->unique_keys)) {
					$balance->save();
					$balance_imported = new Balance_Imported();
					$balance_imported->load(array('balance_id' => $balance->id));
					$balance_imported->hash = $hash;
					$balance_imported->balance_id = $balance->id;
					$balance_imported->save();
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
		$bayesianelements_accounting_codes_id = new Bayesian_Elements();
		$bayesianelements_accounting_codes_id->prepare_id_estimation($GLOBALS['dbconfig']['table_accountingcodes']);
		
		$balances_imported = new Balances_Imported();
		$balances_imported->select();
		foreach ($balances_imported as $balance_imported) {
			$this->unique_keys_balance[] = $balance_imported->hash;
		}
		
		$blocks = preg_split("/<STMTTRN>/", file_get_contents($this->tmp_name));
		
		$period = new Balance_Period();
		$period->span = $this->span;
		$period->save();

		foreach($blocks as $block) {
			$block = strstr($block, "</STMTTRN>", true);
			
			$amount = 0;
			$day = 0;
			$name = "";
			
			if ($block) {
				$lines = explode("\n", $block);
				foreach($lines as $line) {
					if (strstr($line, "<TRNAMT>") !== false) {
						$amount = (float)str_replace("<TRNAMT>", "", $line);
					}
					if (strstr($line, "<DTPOSTED>") !== false) {
						$day = (int)strtotime(str_replace("<DTPOSTED>", "", $line));
					}
					if (strstr($line, "<NAME>") !== false) {
						$name = trim(preg_replace('/\t+/', '', str_replace("<NAME>", "", $line)));
					}
				}
				if ($this->start == null or $this->start > $day ) {
					$this->start = $day;
				}
				$writing = new Writing();
				$writing->amount_inc_vat = $amount;
				$writing->day = $day;
				$writing->comment = $name;
				$writing->banks_id = $this->banks_id;
				$writing->paid = 1;


				$code = new Accounting_Code();
				$code->load(array('id' => $bayesianelements_accounting_codes_id->fisher_element_id_estimated($writing)));

				unset($writing);

				$balance = new Balance();
				$balance->load(array('accountingcodes_id' => $code->id, 'day' => $day));
				$balance->number = $code->number;
				$balance->name = $name;
				$balance->amount += $amount;
				$balance->accountingcodes_id = $code->id;
				$balance->period_id = $period->id;
				$balance->day = $day;

				$hash = hash('md5', $balance->accountingcodes_id.$balance->day);
				if (!in_array($hash, $this->unique_keys)) {
					$balance->save();
					$balance_imported = new Balance_Imported();
					$balance_imported->load(array('balance_id' => $balance->id));
					$balance_imported->hash = $hash;
					$balance_imported->balance_id = $balance->id;
					$balance_imported->save();
					$this->nb_new_records++;
				} else {
					$this->nb_ignored_records++;
					log_status(__('line %s of file %s already exists', array(implode(' - ', $line), $this->file_name)));
				}
			}
		}
		log_status(__(('%s record(s) inserted for %s'), array(strval($this->nb_new_records), $this->file_name)));
	}
	
	function import_as_qif() {
		$bayesianelements_accounting_codes_id = new Bayesian_Elements();
		$bayesianelements_accounting_codes_id->prepare_id_estimation($GLOBALS['dbconfig']['table_accountingcodes']);
		
		$balances_imported = new Balances_Imported();
		$balances_imported->select();
		foreach ($balances_imported as $balance_imported) {
			$this->unique_keys_balance[] = $balance_imported->hash;
		}
		
		$blocks = explode("^", file_get_contents($this->tmp_name));
		
		$period = new Balance_Period();
		$period->span = $this->span;
		$period->save();

		foreach($blocks as $block) {
			$block = rtrim($block);
			if (!empty($block)) {
				$lines = explode("\n", $block);
				$amount = 0;
				$day = 0;
				$name = "";
				$goodline = false;
				foreach ($lines as $line) {
					if (!empty($line)) {
						$date_supposed = preg_split("/^D/", $line);
						$amount_supposed = preg_split("/^T/", $line);
						$comment_supposed = preg_split("/^P/", $line);
						$goodline |= (preg_match("/^N(.)*/", $line) > 0 ) ? true : false;
						if (isset($date_supposed[1])) {
							$date = explode("/", $date_supposed[1]);
							$date[2] = (strlen($date[2]) < 4) ? ($date[2] + 2000) : $date[2];
							$day = mktime(0, 0, 0, $date[1], $date[0], $date[2]);							
						} elseif (isset($amount_supposed[1])) {
							$amount = (float)$amount_supposed[1];
						} elseif (isset($comment_supposed[1])) {
							$name = $comment_supposed[1];
						} elseif (preg_match("/^N(.)*/", $line) <= 0 ) {
							$header = preg_split("/^!/", $line);
						}
					}
				}
				if($goodline) {
					if ($this->start == null or $this->start > $day ) {
						$this->start = $day;
					}
					
					$writing = new Writing();
					$writing->amount_inc_vat = $amount;
					$writing->day = $day;					
					$writing->comment = $name;
					$writing->banks_id = $this->banks_id;
					$writing->paid = 1;
					
					$code = new Accounting_Code();
					$code->load(array('id' => $bayesianelements_accounting_codes_id->fisher_element_id_estimated($writing)));

					unset($writing);

					$balance = new Balance();
					$balance->load(array('accountingcodes_id' => $code->id, 'day' => $day));
					$balance->number = $code->number;
					$balance->name = $name;
					$balance->amount += $amount;
					$balance->accountingcodes_id = $code->id;
					$balance->period_id = $period->id;
					$balance->day = $day;

					$hash = hash('md5', $balance->accountingcodes_id.$balance->day);
					if (!in_array($hash, $this->unique_keys)) {
						$balance->save();
						$balance_imported = new Balance_Imported();
						$balance_imported->load(array('balance_id' => $balance->id));
						$balance_imported->hash = $hash;
						$balance_imported->balance_id = $balance->id;
						$balance_imported->save();
						$this->nb_new_records++;
					} else {
						$this->nb_ignored_records++;
						log_status(__('line %s of file %s already exists', array(implode(' - ', $line), $this->file_name)));
					}
				}
			}
			log_status(__(('%s record(s) inserted for %s'), array(strval($this->nb_new_records), $this->file_name)));
		}
	}

	function import_as_slk() {
		$bayesianelements_categories_id = new Bayesian_Elements();
		$bayesianelements_categories_id->prepare_id_estimation($GLOBALS['dbconfig']['table_categories']);
		
		$bayesianelements_accounting_codes_id = new Bayesian_Elements();
		$bayesianelements_accounting_codes_id->prepare_id_estimation($GLOBALS['dbconfig']['table_accountingcodes']);
		
		$balances_imported = new Balances_Imported();
		$balances_imported->select();
		foreach ($balances_imported as $balance_imported) {
			$this->unique_keys[] = $balance_imported->hash;
		}
		
		$period = new Balance_Period();
		$period->span = $this->span;
		$period->save();

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

				$code = new Acccounting_Code();
				$code->load(array('id' => $bayesianelements_accounting_codes_id->fisher_element_id_estimated($writing)));

				$balance = new Balance();
				$balance->load(array('accountingcodes_id' => $code->id, 'day' => $day));
				$balance->number = $code->number;
				$balance->name = $writing->comment;
				$balance->amount += $writing->amount_inc_vat;
				$balance->accountingcodes_id = $code->id;
				$balance->period_id = $period->id;
				$balance->day = $writing->day;

				unset($writing);

				$hash = hash('md5', $balance->accountingcodes_id.$balance->day);
				if (!in_array($hash, $this->unique_keys)) {
					$balance->save();
					$balance_imported = new Balance_Imported();
					$balance_imported->load(array('balance_id' => $balance->id));
					$balance_imported->hash = $hash;
					$writing_imported->balance_id = $balance->id;
					$this->nb_new_records++;
				} else {
					$this->nb_ignored_records++;
					log_status(__('line %s of file %s already exists', array(implode(' - ', $line), $this->file_name)));
				}
			
		}
		log_status(__(('%s record(s) inserted for %s'), array(strval($this->nb_new_records), $this->file_name)));
	}
}
