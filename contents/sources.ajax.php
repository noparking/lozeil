<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

$data = "";
$status= "false";

if (isset($_REQUEST) and $_REQUEST['action']) {
	switch ($_REQUEST['action']) {
	case "form_modif":
		$source = new Source();
		$source->load(array('id' => $_REQUEST['id']));
		$data = $source->show_form_modification();
		$status = "true";
		break;
	case "form_add":
		$source = new Source();
		$data = $source->form_add();
		$status = "true";
		break;
	default : ;
	}
 }

echo json_encode(array('status' => $status, 'data' => $data));