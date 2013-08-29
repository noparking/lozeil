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
				break;
			case "filter":
				if (!empty($_REQUEST['value'])) {
					$writings->filter['fullsearch'] = $_REQUEST['value'];
				}
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
		
		$writings->filter['month'] = 1;
		echo $writings->show_in_determined_order();
	}
	exit(0);
}
