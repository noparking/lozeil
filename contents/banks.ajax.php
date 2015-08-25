<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */
$data ="";
$status = "false";

if (isset($_REQUEST) and $_REQUEST['action']) {
	switch ($_REQUEST['action']) {
	case "form_modif":
		$bank = new Bank();
		$bank->load(array('id' => $_REQUEST['id']));
		$data = $bank->show_form_modification();
		$status = "true";
		break;
	case "form_add":
		$bank = new Bank();
		$data = $bank->form_add();
		$status = "true";
		break;
	default : ;
	}
 }

echo json_encode(array('status' => $status, 'data' => $data));