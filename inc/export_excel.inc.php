<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Export_Excel {
	public $workbook;
	public $filename;
	
	function __construct() {
		error_reporting(E_ALL ^ E_NOTICE);
		
		require_once(dirname(__FILE__)."/../inc/php_writeexcel030/class.writeexcel_workbook.inc.php");
		require_once(dirname(__FILE__)."/../inc/php_writeexcel030/class.writeexcel_worksheet.inc.php");
		require_once(dirname(__FILE__)."/../inc/php_writeexcel030/functions.writeexcel_utility.inc.php");
	
		if (!ini_get("safe_mode")) {
			set_time_limit(100);
		}
		
		$this->filename = tempnam(dirname(__FILE__)."/../var/tmp", "merge2.xls");
		$this->workbook = new writeexcel_workbook($this->filename);
		$this->workbook->_tempdir = dirname(__FILE__)."/../var/tmp";
	}
	
	function passthru() {
		$this->workbook->close();
	
		header("Content-disposition: filename=export.xls");
		header("Content-Type: application/x-msexcel");
		header("Pragma: public");
		header("Cache-Control: max-age=0");
		$fh = fopen($this->filename, "rb");
		fpassthru($fh);
		unlink($this->filename);
		exit();
	}
}
