<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

if (isset($_FILES['file_balance']) and $_FILES['file_balance']['error'] == 0)  {
	if (preg_match("#.xlsx#",$_FILES['file_balance']['name']) == 1) {
		require dirname(__FILE__)."/../inc/office/excel/PHPExcel.php";
		
		$data = new Import_Balances($_FILES['file_balance']['tmp_name'], $_FILES['file_balance']['name'], $_FILES['file_balance']['type']);
		$data->import();
		$_SESSION['filter'] = $data->filters_after_import();
		status(__("import"), __(("%s balance records(s) inserted, %s ignored"), array(strval($data->nb_new_records), strval($data->nb_ignored_records))), 1);
		header('Location: '.link_content("content=balances.php&start=".$_SESSION['filter']['start']."&stop=".$_SESSION['filter']['stop']));
		exit();
	} else {
		status(__("balance"), __("format not supported"), -1);
	}
}

$menu = Plugins::factory("Menu_Area");
echo $menu->show();

$heading = new Heading_Area(utf8_ucfirst(__("import balance")));
echo $heading->show();

$balance = new Balance();
echo $balance->form_import_balance();
