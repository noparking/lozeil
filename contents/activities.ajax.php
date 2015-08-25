<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */
$data = "";
$status = "false";

if (isset($_REQUEST) and $_REQUEST['action']) {
	switch ($_REQUEST['action']) {
	case "form_modif":
		$activity = new Activity();
		$activity->load(array('id' => $_REQUEST['id']));
		$data = $activity->show_form_modification();
		$status = "true";
		break;
	case "form_add":
		$activity = new Activity();
		$data = $activity->form_add();
		$status = "true";
		break;
	default : ;
	}
 }

echo json_encode(array('status' => $status, 'data' => $data));
