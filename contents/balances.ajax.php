<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

$status = "false";
$data = "";

if (isset($_REQUEST['action']) and isset($_REQUEST['checkbox_balance'])) {
	$codes = new Accounting_Codes();
	$codes->select();

	foreach ($_REQUEST['checkbox_balance'] as $id => $data) {
		if (isset($data['checked'])) {
			$balance = new Balance();
			$balance->load(array('id' => $id));

			$balance_imported = new Balance_Imported();
			$balance_imported->load(array('balance_id' => $balance->id));

			$code = new Accounting_Code();
			$code->load(array('id' => $balance->accountingcodes_id));

			$affectation = new Accounting_Code_Affectation();
			$affectation->load(array('accountingcodes_id' => $code->id));

			$balances = new Balances();
			$balances_imported = new Balances_Imported();

			switch ($_REQUEST['action']) {
			case "affected":
				if (isset($_REQUEST['include']) and $_REQUEST['include'] > 0) {
					$affectation->accountingcodes_id = $code->id;
					$affectation->reportings_id = $_REQUEST['include'];
					$affectation->save();
					$balance->save();
				}
				break;
			case "reaffect":
				$code->reaffect_by_default();
				$balance->save();
				break;
			case "split":
				if (isset($_REQUEST['ratio_input']) and $_REQUEST['ratio_input'] > 0 and $_REQUEST['ratio_input'] < 100) {
					$balance->split($_REQUEST['ratio_input'], "ratio");
				} else {
					status(__("balance"), __("split amount error"), -1);
				}
				break;
			case "delete only":
				$balance->delete();
				break;
			case "delete plus":
				$balance->delete();
				$balance_imported->delete();
				break;
			}
		}
	}
}

if (isset($_REQUEST['action']) and isset($_REQUEST['balance_id'])) {
	$balance = new Balance();
	$balance->load(array('id' => $_REQUEST['balance_id']));

	$balance_imported = new Balance_Imported();
	$balance_imported->load(array('balance_id' => $balance->id));

	switch ($_REQUEST['action']) {
	case "form_insert" :
		$data = $balance->form();
		break;
	case "form_split":
		$data = $balance->form_split();
		break;
	case "form_merge":
		$data = $balance->form_merge();
		break;
	case "delete":
		$balance->delete();
		$balance_imported->delete();
		break;
	case "insert":
		if (isset($_REQUEST['accountingcodes_id']) and isset($_REQUEST['balance_id']) and isset($_REQUEST['hidden_code'])) {
			if (!empty($_REQUEST['name'])) {
				$balance = new Balance();
				$balance->load(array('id' => $_REQUEST['balance_id']));
				$data = $balance->get_insert_data($_REQUEST);
				$balance->fill($data);
				$balance->save();
			} else {
				status("balance", __("name missing"), -1);
			}
		} else {
			status("balance", __("accounting code missing"), -1);
		}
		break;
	case "split":
		if (isset($_REQUEST['table_balances_split_amount'])) {
			$balance = new Balance();
			$balance->load(array('id' => (int)$_REQUEST['balance_id']));
			$amount = $balance->clean_amounts_from_ajax($_REQUEST['table_balances_split_amount']);
			if ($balance->verify_amounts($amount, $_REQUEST['input_split'])) {
				$balance->split($amount, $_REQUEST['input_split']);
			} else {
				status(__("balance"), __("split amount error"), -1);
			}
		}
		break;
	case "merge":
		if (isset($_REQUEST['table_balances_merge_accountingcodes_id'])) {
			$balance = new Balance();
			$balance->load(array('id' => (int)$_REQUEST['balance_id']));
			$balance->merge($_REQUEST['table_balances_merge_accountingcodes_id']);
			status(__("balance"), __("merged splits"), 1);
		}
		break;
	case "preview_changes":
		$balance = new Balance();
		$balance->load(array('id' => (int)$_REQUEST['balance_id']));
		if (preg_match("/table_balances_split_amount/", $_REQUEST['type']) or preg_match("/table_balances_split_submit/", $_REQUEST['type'])) {
			$data = $balance->preview_split($_REQUEST['form']);
		}
		break;
	}
}

if (isset($_REQUEST['action'])) {
	switch ($_REQUEST['action']) {
		case "search":
			$_SESSION['filter']['search'] = $_REQUEST['value'];
			break;
	}
}

$balances = new Balances();
$balances->filter_with($_SESSION['filter']);
$balances->add_order("number");
$balances->select();

echo json_encode(array('status' => show_status(), 'table' => $balances->display(), 'data' => $data));
