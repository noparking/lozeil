<?php
/*
	lozeil
	$Author: $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

class Export_Excel {
	public $workbook;
	public $filename;
	public $customer_id;
	public $project_id;
	public $user_id;
	public $start;
	public $stop;
	
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
	
	function cleanup_properties() {
		if (!is_array($this->project_id) and $this->project_id > 0) {
			$this->project_id = array($this->project_id);
		}
		return $this;
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

	function add_sheets($report) {
		switch ($report) {
			case "project_complete":
				return $this->add_sheets_project_complete();
			default:
				return false;
		}
	}

	function add_sheets_project_complete() {
		$this->add_sheet("hours");
		if ($GLOBALS['param']['ext_expenses']) {
			$this->add_sheet("expenses");
		}
		$this->add_sheet("salefigures");
		$this->add_sheet("purchases");
		
		return true;
	}
	
	function add_sheet($data) {
		switch($data) {
			case "hours":
				list($title, $value) = $this->hours();
				break;
			case "expenses":
				list($title, $value) = $this->expenses();
				break;
			case "salefigures":
				list($title, $value) = $this->salefigures();
				break;
			case "purchases":
				list($title, $value) = $this->purchases();
				break;
			default:
				return false;
		}
		
		if (isset($title) and isset($value)) {
			$worksheet = &$this->workbook->addworksheet();
	
			$titre_format = &$this->workbook->addformat();
			$titre_format->set_bold();
			$titre_format->set_fg_color(22);
		
			$date_format = &$this->workbook->addformat();
			$date_format->set_num_format("dd/mm/yyyy");
		
			$i = 0;
			$j = 0;
			if (is_array($title)) foreach ($title as $title_key) {
				$i++;
				if (is_array($title_key)) {
					foreach ($title_key as $id => $title_key_key) {
		    			$worksheet->write(column_number_in_excel($j).$i, utf8_decode($title_key[$j]), $titre_format);
		    			$j++;
					}
					$j = 0;
				} else {
					$i = 1;
					$worksheet->write(column_number_in_excel($j).$i, utf8_decode($title[$j]), $titre_format);
					$j++;
				}
			}
		
			$i++;
			if (is_array($value)) {
				foreach ($value as $value_key) {
					$j = 0;
					if (is_array($value_key)) {
						foreach ($value_key as $value_head => $value_show) {
							$format = "";
							if (preg_match("/_hours|span/", $value_head)) {
								$value_show = excel_span_format($value_show);
							} elseif (preg_match("/day|start|stop|date/", $value_head) or preg_match("/^validation$/", $value_head)) {
								if ($value_show) {
									$format = $date_format;
									$value_show = xl_date_list(date("Y", $value_show), date("m", $value_show), date("d", $value_show));
								} else {
									$value_show = "";
								}
							} elseif (preg_match("/weekdetails/", $value_head)) {
								$value_show = ($value_show)?excel_span_format(array_sum(unserialize($value_show))):"";
							}
							if (preg_match("/amount/", $value_head)) {
								$worksheet->write_number(column_number_in_excel($j).$i, $value_show);
							} else {
								$worksheet->write(column_number_in_excel($j).$i, utf8_decode($value_show), $format);
							}
							$j++;
						}
					}
					$i++;
				}
			}
		}
	}
	
	function hours() {
		$this->cleanup_properties();
		return prepare_excel_details_hour_link_with_projects_id($this->project_id, $this->start, $this->stop);
	}
	
	function expenses() {
		$this->cleanup_properties();
		return prepare_excel_expenses($this->user_id, $this->start, $this->stop, "", $this->project_id);
	}
	
	function salefigures() {
		$this->cleanup_properties();

		$salefigures = new Salefigures();
		$salefigures->customer_id = $this->customer_id;
		$salefigures->project_id = $this->project_id;
		$salefigures->start = $this->start;
		$salefigures->stop = $this->stop;
		$salefigures->select();

		$title = array(
			$GLOBALS['txt_day'],
			$GLOBALS['param']['level_0'],
			$GLOBALS['param']['level_1'],
			$GLOBALS['txt_number']." ".$GLOBALS['param']['sales'],
			$GLOBALS['txt_amount'],
			$GLOBALS['txt_paidamount'],
			$GLOBALS['txt_description'],
		);
		
		$values = array();
		foreach ($salefigures as $salefigure) {
			if (!isset($projects[$salefigure->project_id])) {
				$project = new Project($salefigure->project_id);
				$project->load();
				$projects[$salefigure->project_id] = $project;
				if (!isset($customers[$project->customer_id])) {
					$customer = new Customer($project->customer_id);
					$customer->load();
					$customers[$project->customer_id] = $customer;
				}
			}
			$values[] = array(
				'day' => $salefigure->day,
				'customer' => $customers[$projects[$salefigure->project_id]->customer_id]->name,
				'project' => $projects[$salefigure->project_id]->name,
				'number' => $salefigure->salefigure_number,
				'amount' => $salefigure->amount,
				'amount_paid' => $salefigure->amount_paid,
				'description' => $salefigure->description,
			);
		}
		 
		 return array($title, $values);
	}
	
	function purchases() {
		$this->cleanup_properties();

		$purchases = new Purchases();
		$purchases->customer_id = $this->customer_id;
		$purchases->project_id = $this->project_id;
		$purchases->start = $this->start;
		$purchases->stop = $this->stop;
		$purchases->select();

		$title = array(
			$GLOBALS['txt_day'],
			$GLOBALS['param']['level_0'],
			$GLOBALS['param']['level_1'],
			$GLOBALS['txt_number']." ".$GLOBALS['param']['purchases'],
			$GLOBALS['txt_amount'],
			$GLOBALS['txt_paidamount'],
			$GLOBALS['txt_description'],
		);
		
		$values = array();
		foreach ($purchases as $purchase) {
			if (!isset($projects[$purchase->project_id])) {
				$project = new Project($purchase->project_id);
				$project->load();
				$projects[$purchase->project_id] = $project;
				if (!isset($customers[$project->customer_id])) {
					$customer = new Customer($project->customer_id);
					$customer->load();
					$customers[$project->customer_id] = $customer;
				}
			}
			$values[] = array(
				'day' => $purchase->day,
				'customer' => $customers[$projects[$purchase->project_id]->customer_id]->name,
				'project' => $projects[$purchase->project_id]->name,
				'number' => $purchase->purchase_number,
				'amount' => $purchase->amount,
				'amount_paid' => $purchase->amount_paid,
				'description' => $purchase->description,
			);
		}
		 
		 return array($title, $values);
	}
}
