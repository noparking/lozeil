<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

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
	if (is_array($title)) {
		foreach ($title as $title_key) {
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

function export_synthese_excel($date,$date_end,$values) {
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
	$titre_format->set_size(12);
	$titre_format->set_underline();

	$base_format =& $workbook->addformat();
	$base_format->set_border(1);
	$base_format->set_fg_color("grey");
	$base_format->set_color("white");
	$base_format->set_bold();
	$base_format->set_size(11);

	$level1_format =& $workbook->addformat();
	$level1_format->set_border(1);
	$level1_format->set_fg_color("grey");
	$level1_format->set_size(11);
	
	
	$level2_format =& $workbook->addformat();
	$level2_format->set_border(1);
	$level2_format->set_fg_color("silver");

	
	$level3_format =& $workbook->addformat();
	$level3_format->set_border(1);
	$level3_format->set_fg_color("white");
	
	$worksheet->write(column_number_in_excel(1)."1", utf8_decode("reporting"));
	$worksheet->write(column_number_in_excel(4)."1", utf8_decode(__('synthesis')." ".__('to')." ".date( "d/m/Y",$date)).__('at')." ".date( "d/m/Y",$date_end) , $titre_format);

	
	
	$j = 7;
	$col_label = column_number_in_excel(1);
	$col_real = column_number_in_excel(2);
	$col_real_ratio = column_number_in_excel(3);
	$col_n1 = column_number_in_excel(4);
	$col_n1_ratio = column_number_in_excel(5);
	$col_n2 = column_number_in_excel(6);
	$col_n2_ratio = column_number_in_excel(7);
	$col_ecart = column_number_in_excel(8);
	$col_ecart_ratio = column_number_in_excel(9);
	$worksheet->write($col_label.$j, utf8_decode(__('label')));
	$worksheet->write($col_real.$j, utf8_decode(__('made')));
	$worksheet->write($col_n1.$j, utf8_decode('N-1'));
	$worksheet->write($col_n2.$j, utf8_decode('N-2'));	
	$worksheet->write($col_ecart.$j, utf8_decode(__('difference')));

       
	$j +=2;
	foreach ($values as $name => $value){ 
		$worksheet->write($col_label.$j, utf8_decode($name), $titre_format);
		$j++;
		foreach(array_reverse($value,TRUE) as $name => $field) {
			switch ($field['level']) {
			case '0':
				$format =  $level1_format;
				break;
			case '1':
				$format = $level2_format;
				break;
			case '2':
				$format = $level3_format;
				break;
			default:
				$format = $level1_format;
				break;
			}
			
			if (intval($field['base']) == 1) {
				$format = $base_format;
			}
			$worksheet->write($col_label.$j,utf8_decode($field['name']), $format);
			$worksheet->write($col_real.$j, $field['real']['value'] ,$format);
			$worksheet->write($col_real_ratio.$j, $field['real']['ratio'] ,$format);
			$worksheet->write($col_n1.$j, $field['n-1']['value'] ,$format);
			$worksheet->write($col_n1_ratio.$j, $field['n-1']['ratio'] ,$format);
			$worksheet->write($col_n2.$j, $field['n-2']['value'] ,$format);
			$worksheet->write($col_n2_ratio.$j, $field['n-2']['ratio'] ,$format);
			$worksheet->write($col_ecart.$j, $field['ecart']['value'] ,$format);
			$worksheet->write($col_ecart_ratio.$j, $field['ecart']['ratio'] ,$format);
			$j += 2;
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
