<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

$writings = new Writings();
list($start, $stop) = determine_month($_SESSION['timestamp']);

switch ($_REQUEST['action']) {
	case "merge":
		$writing_from = new Writing();
		$writing_from->load((int)$_REQUEST['writing_from']);
		$writing_into = new Writing();
		$writing_into->load((int)$_REQUEST['writing_into']);
		$writing_into->merge_from($writing_from);
		break;
	case "filter":
		$_SESSION['filter_value_*'] = $_REQUEST['value'];
		break;
	case "sort":
		if ($_SESSION['order_col_name'] == $_REQUEST['order_col_name']) {
			$_SESSION['order_direction'] = $_SESSION['order_direction'] == "ASC" ? "DESC" : "ASC";
		} else {
			$_SESSION['order_col_name'] = $_REQUEST['order_col_name'];
		}
		break;
	default :
		break;
}
if (isset($_SESSION['filter_value_*']) and !empty($_SESSION['filter_value_*'])) {
	$writings->filter_with(array('*' => $_SESSION['filter_value_*']));
}
$writings->set_order($_SESSION['order_col_name'], $_SESSION['order_direction']);
$writings->filter_with(array('start' => $start, 'stop' => $stop));
$writings->select();
echo $writings->show();
exit(0);

