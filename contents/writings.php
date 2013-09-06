<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

if (!isset($_SESSION['order_col_name']) or !isset($_SESSION['order_direction'])) {
	$_SESSION['order_col_name'] = 'day';
	$_SESSION['order_direction'] = 'ASC';
}

$timestamp_selected = determine_integer_from_post_get_session(null, "timestamp");
$selected_writing = determine_integer_from_post_get_session(null, "writings_id");

if ($timestamp_selected > 0) {
	$_SESSION['timestamp'] = $timestamp_selected;
} else {
	$_SESSION['timestamp'] = mktime(0, 0, 0, date("m"), 1, date("Y"));
}
list($start, $stop) = determine_month($_SESSION['timestamp']);

if (isset($_POST['action']) and count($_POST) > 0) {
	switch ($_POST['action']) {
		case 'edit':
			if (isset($_POST['id']) and $_POST['id'] > 0) {
				$writing = new Writing();
				$writing->load($_POST['id']);
				$writing->fill($_POST);
				$writing->save();
			}
			break;
			
		case 'insert':
			$writing = new Writing();
			$writing->fill($_POST);
			$writing->save();
			break;
		
		case 'delete':
			if (isset($_POST['table_writings_delete_id'])) {
				$writing = new Writing($_POST['table_writings_delete_id']);
				$writing->delete();
			}
			break;
			
		case 'split':
			if (isset($_POST['table_writings_split_amount'])) {
				$amount = str_replace(",", ".", $_POST['table_writings_split_amount']);
				if (is_numeric($amount)) {
					$writing = new Writing();
					$writing->load((int)$_POST['table_writings_split_id']);
					$writing->split($amount);
				}
			}
			break;
			
		case 'duplicate':
			if (isset($_POST['table_writings_duplicate_id']) and isset($_POST['table_writings_duplicate_amount'])) {
				$writing = new Writing();
				$writing->load((int)$_POST['table_writings_duplicate_id']);
				$writing->duplicate($_POST['table_writings_duplicate_amount']);
			}
			break;
			
		default:
			break;
	}
}

$menu = new Menu_Area();
$menu->prepare_navigation(__FILE__);
echo $menu->show();

$writings = new Writings();
$writings->set_order($_SESSION['order_col_name'], $_SESSION['order_direction']);
$writings_filter_value = "";
if (isset($_SESSION['filter_value_*']) and !empty($_SESSION['filter_value_*'])) {
	$writings_filter_value = $_SESSION['filter_value_*'];
	$writings->filter_with(array('*' => $writings_filter_value));
}
$writings->filter_with(array('start' => $start, 'stop' => $stop));
$writings->select();

$heading = new Heading_Area(utf8_ucfirst(__('consult balance sheet')), $writings->display_timeline_at($_SESSION['timestamp']), $writings->form_filter($writings_filter_value));
echo $heading->show();

echo $writings->display();

$writing = new Writing();
echo $writing->form();