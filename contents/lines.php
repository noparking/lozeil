<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

$timestamp = mktime(0, 0, 0, date("m"), 1, date("Y"));
$_SESSION['timestamp'] = $timestamp;
if (!isset($_SESSION['order_col_name']) || !isset($_SESSION['order_direction'])) {
	$_SESSION['order_col_name'] = 'delay';
	$_SESSION['order_direction'] = 'ASC';
}

$timestamp_selected = determine_integer_from_post_get_session(null, "timestamp");
$selected_writing = determine_integer_from_post_get_session(null, "writings_id");
if (isset($timestamp_selected) and $timestamp_selected > 0) {
	$_SESSION['timestamp'] = $timestamp_selected;
}
list($start, $stop) = determine_month($_SESSION['timestamp']);

if (isset($_POST) and count($_POST) > 0) {
	switch ($_POST['action']) {
		case 'edit':
			if (isset($_POST['id']) and $_POST['id'] > 0) {
				$writing = new Writing($_POST['id']);
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
			if (isset($_POST['id'])) {
				$writing = new Writing($_POST['id']);
				$writing->delete();
			}
			break;
		case 'split':
			if (isset($_POST['split_amount'])) {
				$amount = str_replace(",", ".", $_POST['split_amount']);
				if (is_numeric($amount)) {
					$writing = new Writing();
					$writing->load((int)$_POST['id']);
					$writing->split($amount);
				}
			}
			break;
		case 'duplicate':
			if (isset($_POST['id']) and isset($_POST['duplicate_amount'])) {
				$writing = new Writing();
				$writing->load((int)$_POST['id']);
				$writing->duplicate((int)$_POST['duplicate_amount']);
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
$writings->filter_with(array('start' => $start, 'stop' => $stop));
$writings->select();

$heading = new Heading_Area(null, $writings->show_timeline_at($_SESSION['timestamp']), $writings->form_filter());
echo $heading->show();

echo $writings->show();

$writing = new Writing();
if ($selected_writing > 0) {
	$writing->load($selected_writing);
}
echo $writing->form();