<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

$current_month = mktime(0,0,0,date("m"),1,date("Y"));
$_SESSION['month_encours'] = $current_month;

$selected_month = determine_integer_from_post_get_session(null, "month");

if(isset($selected_month) and $selected_month > 0) {
	$_SESSION['month_encours'] = $selected_month;
}

$writing_form = "";

if (isset($_POST) and count($_POST) > 0) {
	switch ($_POST['action']) {
		case 'getid':
			$writing = new Writing($_POST['id']);
			$writing->load();
			$writing_form = $writing->form();
			break;
		case 'do_edit':
			$writing = new Writing($_POST['id']);
			$writing->fill($_POST);
			$writing->save();
			break;
		case 'insert':
			$writing = new Writing();
			$writing->fill($_POST);
			$writing->save();
			break;
		case 'delete':
			$writing = new Writing($_POST['id']);
			$writing->delete();
			break;
		case 'split':
			$amount = str_replace(",", ".", $_POST['split_amount']);
			if (is_numeric($amount)) {
				$writing_to_split = new Writing();
				$writing_to_split->load((int)$_POST['id']);
				$writing_to_split->split($amount);
			}
			break;
		case 'duplicate':
			$writing_to_duplicate = new Writing();
			$writing_to_duplicate->load((int)$_POST['id']);
			$writing_to_duplicate->duplicate((int)$_POST['duplicate_amount']);
			break;
		case 'getnew':
			$writing = new Writing();
			$writing_form = $writing->form();
			break;
		default:
			break;
	}
}

$writings = new Writings();

$extra = $writings->show_timeline();

$heading = new Heading_Area(null, $extra);
echo $heading->show();

$writings->set_order('delay', 'ASC');
$writings->select();

echo $writings->show();

$writing = new Writing();
echo $writing->get_form_new();

echo $writing_form;
