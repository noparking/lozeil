<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */
$data = "";
$status = "false";

if (isset($_REQUEST) and $_REQUEST['action']) {
	switch ($_REQUEST['action']) {
	case "form_modif":
		$category = new Category();
		$category->load(array('id' => $_REQUEST['id']));
		$data = $category->show_form_modification();
		$status = "true";
		break;
	case "form_add":
		$category = new Category();
		$data = $category->form_add();
		$status = "true";
		break;
	case "fill_vat":
		$category = new Category();
		$category->load(array('id' => $_REQUEST['value']));
		$data = $category->vat;
		$status = "true";
		break;
	default : ;
	}
 }
echo json_encode(array('status' => $status, 'data' => $data));
