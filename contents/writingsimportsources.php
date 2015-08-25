<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

if (isset($_POST['menu_actions_import_file']) and  !isset($_POST['menu_actions_import_source'])) {
	if (isset($_FILES) and $_FILES['menu_actions_import_file']['error'] == 0) {
		if (isset($_POST['menu_actions_import_source']) and ($_POST['menu_actions_import_source']) > 0) {
			$data = new Import_Writings($_FILES['menu_actions_import_file']['tmp_name'], $_FILES['menu_actions_import_file']['name'], $_FILES['menu_actions_import_file']['type']);
			$data->sources_id = $_POST['menu_actions_import_source'];
			$data->import();
			$_SESSION['filter'] = $data->filters_after_import();
			status(__("import"), __(('%s record(s) inserted, %s ignored'), array(strval($data->nb_new_records), strval($data->nb_ignored_records))), 1);	
			if (isset($_SESSION['filter']['start'])) { 
				header("Location: ".link_content("content=writings.php&start=".$_SESSION['filter']['start']."&stop=".$_SESSION['filter']['stop']));
				exit();
			}
		}
	} else {
		status(__("import", __("error while uploading"), -1));
	}
}

$menu = Plugins::factory("Menu_Area");
echo $menu->show();

$heading = new Heading_Area(utf8_ucfirst(__("import writings from source")));
echo $heading->show();

$writing = new Writing();
echo $writing->get_form_sources();	
