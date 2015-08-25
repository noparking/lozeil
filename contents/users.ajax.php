<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */
$status = "false";
$data = "";

if(isset($_REQUEST['action'])) {
	switch ($_REQUEST['action']) {
	case "form_modif" :
		$user = new User();
		$user->load(array("id" => $_REQUEST['id']));
		if (isset($_SESSION['accountant_view']) and $_SESSION['accountant_view'] == '1' or $user->id == $_SESSION['userid']) {
			$data = $user->show_form_modification();
			$status = "true";
		}
		break;

	case "form_add" :
		$user = new User();
		$data = $user->show_form();
		$status = "true";
		break;

	default : ;
	}
}

echo json_encode(array('status' => $status,'data' => $data ));