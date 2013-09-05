<?php
/*
	lozeil
	$Author: $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

function export_excel($title, $value) {
	$error_reporting = error_reporting(E_ALL ^ E_NOTICE);

	require_once dirname(__FILE__)."/../inc/php_writeexcel030/class.writeexcel_workbook.inc.php";
	require_once dirname(__FILE__)."/../inc/php_writeexcel030/class.writeexcel_worksheet.inc.php";
	require_once dirname(__FILE__)."/../inc/php_writeexcel030/functions.writeexcel_utility.inc.php";

	if (!ini_get('safe_mode')) {
		set_time_limit(100);
	}

	$fname = tempnam(dirname(__FILE__)."/../var/tmp", "merge2.xls");
	$workbook = new writeexcel_workbook($fname);
	$workbook->_tempdir = dirname(__FILE__)."/../var/tmp";
	$worksheet = &$workbook->addworksheet();

	$titre_format =& $workbook->addformat();
	$titre_format->set_bold();
	$titre_format->set_fg_color(22);

	$date_format =& $workbook->addformat();
	$date_format->set_num_format('dd/mm/yyyy');

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
					} elseif (preg_match("/day|start|stop|date|birth/", $value_head) or preg_match("/^validation$/", $value_head)) {
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
						$value_show = utf8_decode($value_show);
						$value_show = preg_replace("/^(=)/", "'=", $value_show);
						$worksheet->write(column_number_in_excel($j).$i, $value_show, $format);
					}
					$j++;
				}
			}
			$i++;
		}
	}

	$workbook->close();

	error_reporting($error_reporting);

	header("Content-disposition: filename=export.xls");
	header("Content-Type: application/x-msexcel");
	header("Pragma: public");
	header("Cache-Control: max-age=0");
	$fh=fopen($fname, "rb");
	fpassthru($fh);
	unlink($fname);
	exit();
}
