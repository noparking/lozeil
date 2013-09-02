<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

if (isset($_REQUEST['action'])) {
	$writings = new Writings();
	list($start, $stop) = determine_month($_SESSION['timestamp']);

	switch ($_REQUEST['action']) {
		case "merge":
			if ($_REQUEST['writing_from'] != 0 and !empty($_REQUEST['writing_from'])) {
				$writing_from = new Writing();
				$writing_from->load((int)$_REQUEST['writing_from']);
				if ($_REQUEST['writing_into'] != 0 and !empty($_REQUEST['writing_into'])) {
					$writing_into = new Writing();
					$writing_into->load((int)$_REQUEST['writing_into']);
					$writing_into->merge_from($writing_from);
				}
			}
			$writings->set_order($_SESSION['order_col_name'], $_SESSION['order_direction']);
			if (isset($_SESSION['filter_value_*']) and !empty($_SESSION['filter_value_*'])) {
				$writings_filter_value = $_SESSION['filter_value_*'];
				$writings->filter_with(array('*' => $writings_filter_value));
			}
			$writings->set_order($_SESSION['order_col_name'], $_SESSION['order_direction']);
			$writings->filter_with(array('start' => $start, 'stop' => $stop));
			$writings->select();
			echo $writings->show();
			break;

		case "filter":
			$_SESSION['filter_value_*'] = $_REQUEST['value'];
			if (!empty($_REQUEST['value'])) {
				$writings->filter_with(array('*' => $_REQUEST['value']));
			}
			$writings->set_order($_SESSION['order_col_name'], $_SESSION['order_direction']);
			$writings->filter_with(array('start' => $start, 'stop' => $stop));
			$writings->select();
			echo $writings->show();
			break;

		case "sort":
			if ($_SESSION['order_col_name'] == $_REQUEST['order_col_name']) {
				if ($_SESSION['order_direction'] == "ASC") {
					$_SESSION['order_direction'] = "DESC";
				} else {
					$_SESSION['order_direction'] = "ASC";
				}
			} else {
				$_SESSION['order_col_name'] = $_REQUEST['order_col_name'];
			}
			$writings->filter_with(array('start' => $start, 'stop' => $stop));
			if (isset($_SESSION['filter_value_*']) and !empty($_SESSION['filter_value_*'])) {
				$writings_filter_value = $_SESSION['filter_value_*'];
				$writings->filter_with(array('*' => $writings_filter_value));
			}
			$writings->set_order($_SESSION['order_col_name'], $_SESSION['order_direction']);
			$writings->select();
			echo $writings->show();
			break;

		default :
			break;
	}
}
exit(0);

