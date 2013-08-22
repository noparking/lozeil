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

if (isset($_POST) and count($_POST) > 0) {
	switch ($_POST['action']) {
		case 'getid':
			$writing = new Writing($_POST['id']);
			$writing->load();
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

if(!isset($writing)) {
	$writing = new Writing();
}

echo $writing->form();
