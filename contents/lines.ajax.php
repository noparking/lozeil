<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

if (isset($_REQUEST['method']) && $_REQUEST['method'] == 'json') {
	if (isset($_REQUEST['action'])) {
		$writings = new Writings();
		switch ($_REQUEST['action']) {
			case "merge":
				if ($_REQUEST['toMerge'] != 0 && !empty($_REQUEST['toMerge'])) {
					$writing_to_merge = new Writing();
					$writing_to_merge->load((int)$_REQUEST['toMerge']);
					if ($_REQUEST['destination'] != 0 && !empty($_REQUEST['destination'])) {
						$writing_destination = new Writing();
						$writing_destination->load((int)$_REQUEST['destination']);
						$writing_destination->merge($writing_to_merge);
					}
				}
				list($start, $stop) = determine_month($_SESSION['month']);
				$writings->filter_with(array('start' => $start, 'stop' => $stop));
				echo json_encode(array('table' => $writings->show_in_determined_order()));
				break;
			case "filter":
				if (!empty($_REQUEST['value'])) {
					$writings->filter_with(array('*' => $_REQUEST['value']));
				}
				list($start, $stop) = determine_month($_SESSION['month']);
				$writings->filter_with(array('start' => $start, 'stop' => $stop));
				echo $writings->show_in_determined_order();
				break;
			case "refresh_balance_data":
				$writings = new Writings();
				$writings->select();
				echo json_encode(array('timeline' => $writings->show_timeline(),'menu_balance' => $writings->show_balance_on_current_date()));
				break;
//			case "split":
//				if (isset($_REQUEST['amount']) && isset($_REQUEST['tosplit']) && $_REQUEST['tosplit'] != 0) {
//					$amount = str_replace(",", ".", $_REQUEST['amount']);
//					if (is_numeric($amount)) {
//						$writing_to_split = new Writing();
//						$writing_to_split->load((int)$_REQUEST['tosplit']);
//						$writing_to_split->split($amount);
//					}
//				}
//				break;
			default :
				break;
		}
		
		
	}
	exit(0);
}
