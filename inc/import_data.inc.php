<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2015 */

class Import_Data {
	public $file_name = "";
	public $tmp_name = "";
	public $type = "";
	public $banks_id = 0;
	public $sources_id = 0;
	public $start = null;
	public $stop;
	public $t_start;
	public $t_stop;
	public $span = 1;
	public $csv_data = array();
	
	function __construct($tmp_name = "", $file_name = "", $type = "") {
		$this->tmp_name = $tmp_name;
		$this->file_name = $file_name;
		$this->type = $type;
		$this->csv_data = array();
	}
	
	function determine_start_stop($timestamp) {
		$this->start = !isset($this->start) ? $timestamp : min($this->start, $timestamp);
		$this->stop = !isset($this->stop) ? $timestamp : max($this->stop, $timestamp);
	}
	
	function import() {
		if ($this->is_csv()) {
			$this->prepare_csv_data();
			if ($this->banks_id > 0) {
				if ($this->is_cic($this->csv_data)) {
					$this->import_as_cic();
				} elseif ($this->is_coop($this->csv_data)) {
					$this->import_as_coop();
				} else {
					log_status(__(('file %s is not in supported format'),  $this->file_name));
				}
			} elseif ($this->sources_id > 0) {
				if ($this->is_paybox($this->csv_data)) {
					$this->import_as_paybox();
				} else {
					log_status(__(('file %s is not in supported format'),  $this->file_name));
				}
			}
		} elseif ($this->is_ofx()) {
			$this->import_as_ofx();
		} elseif ($this->is_qif()) {
			$this->import_as_qif();
		} elseif ($this->is_slk()){
			$this->prepare_slk_data();
			$this->import_as_slk();
		} elseif ($this->is_xlsx()){
			if ($this->prepare_xlsx_data() == true) {
				$this->import_as_xlsx();
			} else {
				log_status(__(('file %s is not in supported format'),  $this->file_name));
				return false;
			}
		}
	}
	
	function prepare_xlsx_data() {
		$inputFile = $this->tmp_name;
		$dir = dirname(__FILE__)."/../var/tmp/".time();
		mkdir($dir);
		$excel = PHPExcel_IOFactory::load($this->tmp_name);
		$worksheet = $excel->getSheet(0);

		$max_cell = $worksheet->getHighestRowAndColumn();
		$end_row = $worksheet->getHighestRow();
		$map = $worksheet->rangeToArray('A1:' . $max_cell['column'] . $max_cell['row']);
		$map = array_map('array_filter', $map);
		$map = array_filter($map);

		unset($map[0]);
		unset($map[1]);
		unset($map[2]);
		unset($map[4]);

		$this->csv_data = array();
		$date_begin = time();
		$date_end = time();

		if (!preg_match_all("/\d*\/\d*\/\d*/", $map[3][0], $matchs)) {
			return false;
		}
		$date_begin = $matchs[0][0];
		$date_end = $matchs[0][1];
		if (empty($date_begin) or empty($date_end) or (empty($date_begin) and empty($date_end))) {
			return false;
		}

		$a = strptime($date_begin, "%d/%m/%Y");
		$b = strptime($date_end, "%d/%m/%Y");
		$this->t_start = mktime(0, 0, 0, $a['tm_mon'] + 1, $a['tm_mday'], $a['tm_year'] + 1900);
		$this->t_stop = mktime(23, 59, 59, $b['tm_mon'] + 1, $b['tm_mday'], $b['tm_year'] + 1900);

		unset($map[3]);

		foreach ($map as $cell) {
			$number = (int)$cell[0];
			$libelle = (string)$cell[1];
			if ($number == 0 or empty($libelle) or (empty($cell[2]) and empty($cell[3]))) {
				return false;
			}
			if (isset($cell[2])) {
				$cell[2] = str_replace(" ", "", $cell[2]);
				$this->csv_data[] = array($date_end, $number, $libelle, "-".(float)$cell[2]);
			}
			if (isset($cell[3])) {
				$cell[3] = str_replace(" ", "", $cell[3]);
				$this->csv_data[] = array($date_end, $number, $libelle, (float)$cell[3]);
			}
		}

		unset($map);
		rmdir($dir);
		return true;
	}

	function prepare_slk_data() {
		if ($file_opened = fopen( $this->tmp_name , 'r') ) {
			$i = 0;
			$nb_data = 0;
			while(($data = fgets($file_opened, 1000)) !== FALSE) {
				if ($i == 7) {
					preg_match_all("#[0-9]{2}/[0-9]{2}/[0-9]{4}#", $data,$match);
					$time = str_replace("\"","",$match[0][0]);
					$time2 = str_replace("\"","",$match[0][1]);
				}
				if ($i >11) {
					if (preg_match("#C;X1#",$data)) {
						$content = preg_split("#;K#", $data);    
						$this->csv_data[$nb_data][0] = str_replace("\"","",$content[1]);    				
					} else if  (preg_match("#C;X2#",$data)) {
						$content = preg_split("#;K#", $data);    
						$this->csv_data[$nb_data][1] = str_replace("\"","",$content[1]);
					} else if(preg_match("#C;X3#",$data)) {
						$content = preg_split("#;K#", $data);    
						$this->csv_data[$nb_data][2] =  floatval(str_replace("\"","",$content[1]));   	
						$nb_data++;			
					} else if(preg_match("#C;X4#",$data)) {
						$content = preg_split("#;K#", $data);    
						$this->csv_data[$nb_data][2] =  floatval("-".str_replace("\"","",$content[1]));
						$nb_data++;	
					}
				}
				$i++;
			}
			fclose($file_opened);
		} else {
			log_status(__('can not open file')." : ".$this->file_name);
		}
		self::convert_date($this->csv_data, $time, $time2);
	}
		
	function prepare_csv_data() {
		if ($file_opened = fopen( $this->tmp_name , 'r') ) {
			$row = 0;

            while(($data = fgetcsv($file_opened, 1000, ';')) !== FALSE) {
				foreach ($data as $key => $value) {
					$this->csv_data[$row][$key] = trim($value);				}
	              $row++;
            }
			fclose($file_opened);
		} else {
			log_status(__('can not open file')." : ".$this->file_name);
		}
	}

	function is_paybox($data) {
		switch (true) {
			case $data[0][6] != "Date":
			case $data[0][7] != "TransactionId":
			case $data[0][12] != "Reference":
			case $data[0][13] != "Origin":
			case $data[0][15] != "Canal":
			case $data[0][17] != "Amount":
			case $data[0][21] != "Country":
			case $data[0][23] != "Payment":
			case $data[0][28] != "Status":
				return false;
			default :
				return true;
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
	
	function is_ca($data) {
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

	function is_line_koala($line) {
		switch (true) {
			case !preg_match("/\d*\/\d*\/\d*/", $line[0]) :
			case !is_int($line[1]) :
			case !is_numeric($line[3]) :
				return false;
			default :
				return true;
		}
	}

	
	function is_line_paybox($line) {
		$time = explode("/", $line[6]);
		
		switch (true) {
			case (!isset($time[1]) OR !isset($time[2])) :
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
	
	function is_csv() {
		if (strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION)) == "csv") {
			return true;
		} else {
			return false;
		}
	}
	
	function is_ofx() {
		if (strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION)) == "ofx") {
			return true;
		} else {
			return false;
		}
	}
	
	function is_qif() {
		if (strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION)) == "qif") {
			return true;
		} else {
			return false;
		}
	}
	function is_slk() {
		if (strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION)) == "slk") {
			return true;
		} else {
			return false;
		}
	}
	function is_xlsx() {
		if (strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION)) == "xlsx") {
			return true;
		} else {
			return false;
		}
	}
	
	function filters_after_import() {
		if (isset($this->start)) {
			$this->start = mktime(0,0,0,date('m',$this->start),1,date('Y',$this->start));
			$this->stop = strtotime("+1 year",$this->start);
			return array (
				'start' => $this->start,
				'stop' => $this->stop,
				'sources_id' => $this->sources_id,
				'banks_id' => $this->banks_id,
			);
		} else {
			return array();
		}
	}

	function convert_date($data,$debut,$fin) {
		$date_debut = new DateTime(date("Y-m-d", strtotime(str_replace('/', '-',$debut))));
		$date_fin = new DateTime(date("Y-m-d", strtotime(str_replace('/', '-',$fin))));

		$interval_month = new DateInterval('P1M');
		$interval_month = DateInterval::createFromDateString('1 months');

		
		for($i = 0; $i < count($data) ;$i++)
		{
			$value = $data[$i];
			$month = preg_split("#\-#",$value[0]);
			
			while(self::convert_String_to_month_number($month[1]) != intval($date_fin->format('m'))) date_sub($date_fin,$interval_month);
			$value[0] = mktime(0,0,0,self::convert_String_to_month_number($month[1]),$month[0],$date_fin->format('y'));
			$this->csv_data[$i] = $value;
		}
	}

	function convert_String_to_month_number($str) {
		preg_match("#\w{3}#", str_replace("�", "e", $str),$match);
		switch($match[0]) {
			case 'Jan':
				return 1;
				break;
			case 'Fev':
				return 2;
				break;
			case 'Mar':
				return 3;
				break;
			case 'Avr':
				return 4;
				break;
			case 'Mai':
				return 5;
				break;
			case 'Jui':
				return 6;
				break;
			case 'Jul':
				return 7;
				break;
			case 'Aou':
				return 8;
				break;
			case 'Sep':
				return 9;
				break;
			case 'Oct':
				return 10;
				break;
			case 'Nov':
				return 11;
				break;
			case 'Dec':
				return 12;
				break;
			default:
				return 1;
				break;
		}
	}
}
