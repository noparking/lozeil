<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

if (isset($_POST['select_form'])) {
	if (isset($_FILES) and $_FILES['menu_actions_import_file']['error'] == 0) {
		require dirname(__FILE__)."/../inc/office/excel/PHPExcel.php";
		
		$data = new Import_Writings($_FILES['menu_actions_import_file']['tmp_name'], $_FILES['menu_actions_import_file']['name'], $_FILES['menu_actions_import_file']['type']);
		if (isset($_POST['menu_actions_import_bank']) and intval(($_POST['menu_actions_import_bank'])) > 0) {
			$data->banks_id = $_POST['menu_actions_import_bank'];
		} else if (isset($_POST['menu_actions_import_source']) and ($_POST['menu_actions_import_source']) > 0) {
			$data->sources_id = $_POST['menu_actions_import_source'];
		}
		$data->import();
		$_SESSION['filter'] = $data->filters_after_import();
		status(__("import"), __(("%s record(s) inserted, %s ignored"), array(strval($data->nb_new_records), strval($data->nb_ignored_records))), 1);
		if (isset($_SESSION['filter']['start'])) { 
			header("Location: ".link_content("content=writings.php&start=".$_SESSION['filter']['start']."&stop=".$_SESSION['filter']['stop']));
			exit();
		}
	} else {
		status(__("import"), __("error while uploading"), -1);
	}
}

$writing = new Writing();
$thereis = $writing->loadlastinserted();
if ($thereis) {
	$date = date('d/m/Y',$writing->timestamp);
} else {
	$date = __("never");
}

$menu = Plugins::factory("Menu_Area");
echo $menu->show();

$heading = new Heading_Area(utf8_ucfirst(__("import writings")."<h4>".__("last import")." : ".$date."</h4>"));
echo $heading->show();

echo $writing->get_form();
