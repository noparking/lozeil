<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

$writings = new Writings();
$extra = "";

if (!empty($_FILES)) {
	$file = new File();
	$file->save_attachment($_FILES);
	echo json_encode(array('status' => show_status()));
	exit(0);
}

if (isset($_REQUEST['action'])) {
	switch ($_REQUEST['action']) {

		case "preview_changes" :
			$writing = new Writing();
			$writing->load(array('id' => (int)$_REQUEST['id']));
			if (preg_match("/table_writings_split_amount/", $_REQUEST['type']) or preg_match("/table_writings_split_submit/", $_REQUEST['type'])) {
				echo $writing->preview_split($_REQUEST['form']);
			}
			if ($_REQUEST['type'] == "table_writings_forward_amount" or $_REQUEST['type'] == "table_writings_forward_amount_select") {
				echo $writing->preview_forward($_REQUEST['value']);
			}
			if ($_REQUEST['type'] == "table_writings_duplicate_amount" or $_REQUEST['type'] == "table_writings_duplicate_amount_select") {
				echo $writing->preview_duplicate($_REQUEST['value']);
			}
			exit(0);
			break;

		case "form_duplicate" :
			$writing = new Writing();
			$writing->load(array('id' => (int)$_POST['table_writings_form_duplicate_id']));
			echo $writing->form_duplicate();
			exit(0);
			break;


		case "form_forward" :
			$writing = new Writing();
			$writing->load(array('id' => (int)$_POST['table_writings_form_forward_id']));
			echo $writing->form_forward();
			exit(0);
			break;

		case "form_split" :
			$writing = new Writing();
			$writing->load(array('id' => (int)$_POST['table_writings_form_split_id']));
			echo $writing->form_split();
			exit(0);
			break;

		case "delete_attachment" :
			$file = new File();
			$file->load(array('id' => (int)$_REQUEST['id']));
			$file->delete_attachment();
			$writing = new Writing();
			$writing->load(array('id' => $file->writings_id));
			echo json_encode(array('status' => show_status(), 'link' => $writing->link_to_file_attached()));
			exit(0);
			break;

		case "search":
			$accounting_codes = new Accounting_Codes();
			$accounting_codes->fullname = isset($_REQUEST['name']) ? $_REQUEST['name'] : "";
			$accounting_codes->set_limit(10, 0);
			$output = "";
			if ($accounting_codes->fullname) {
				$accounting_codes->select();
				$output = json_encode($accounting_codes->fullnames());
			}
			echo $output;
			exit(0);
			break;	

		case "merge":
			$writing_from = new Writing();
			$writing_from->load(array('id' => (int)$_REQUEST['writing_from']));
			$writing_into = new Writing();
			$writing_into->load(array('id' => (int)$_REQUEST['writing_into']));
			$writing_into->merge_from($writing_from);
			break;

		case "edit":
			if (isset($_POST['writings_id']) and $_POST['writings_id'] > 0) {
				$writing = new Writing();
				$writing->load(array('id' => (int)$_POST['writings_id']));
				$writing_before = clone $writing;
				$cleaned = $writing->clean($_POST);
				$writing->fill($cleaned);
				$writing->save();

				$bayesianelements = new Bayesian_Elements();
				$bayesianelements->increment_decrement($writing_before, $writing);
			}
			break;

		case "form_edit":
			$writing = new Writing();
			$writing->load(array('id' => (int)$_REQUEST['table_writings_modify_id']));
			echo $writing->form_in_table();

			exit(0);
			break;

		case "filter":
			if (is_datepicker_valid($_POST['filter_day_start']) and is_datepicker_valid($_POST['filter_day_stop'])) {
				$cleaned = $writings->clean_filter_from_ajax($_POST);
				$_SESSION['filter'] = $cleaned;
			}
			$extra = $writings->form_filter($_SESSION['filter']['start'], $_SESSION['filter']['stop'], isset($_SESSION['filter']['search_index']) ? $_SESSION['filter']['search_index'] : "");
			break;

		case "sort":
			if ($_SESSION['order']['name'] == $_REQUEST['order_col_name']) {
				$_SESSION['order']['direction'] = $_SESSION['order']['direction'] == "ASC" ? "DESC" : "ASC";
			} else {
				$_SESSION['order']['name'] = $_REQUEST['order_col_name'];
			}
			break;

		case 'split':
			if (isset($_REQUEST['table_writings_split_amount'])) {
				$writing = new Writing();
				$amount = $writing->clean_amounts_from_ajax($_REQUEST['table_writings_split_amount']);
				$writing->load(array('id' => (int)$_REQUEST['writing_id']));
				$writing->split($amount);
			}
			break;

		case 'duplicate':
			if (isset($_POST['writing_id']) and isset($_POST['table_writings_duplicate_amount'])) {
				$writing = new Writing();
				$writing->load(array('id' => (int)$_POST['writing_id']));
				if (!empty($_POST['table_writings_duplicate_amount'])) {
					$writing->duplicate($_POST['table_writings_duplicate_amount']);
				} else {
					$writing->duplicate($_POST['table_writings_duplicate_amount_select']);
				}
			}
			break;

		case 'forward':
			if (isset($_POST['writing_id']) and isset($_POST['table_writings_forward_amount'])) {
				$writing = new Writing();
				$writing->load(array('id' => (int)$_POST['writing_id']));
				if (!empty($_POST['table_writings_forward_amount'])) {
					$writing->forward($_POST['table_writings_forward_amount']);
				} else {
					$writing->forward($_POST['table_writings_forward_amount_select']);
				}
			}
			break;

		case 'insert':
			$writing = new Writing();
			$cleaned = $writing->clean($_POST);
			$writing->fill($cleaned);
			$bayesianelements = new Bayesian_Elements();
			$bayesianelements->stuff_with($writing);
			$bayesianelements->increment();
			$writing->save();
			break;

		case 'reload_insert_form':
			$writing = new Writing();
			echo $writing->display();

			exit(0);
			break;

		case 'reload_select_writings':
			$writing = new Writings();
			echo $writing->modify_options();

			exit(0);
			break;

		case 'delete':
			if (isset($_POST['table_writings_delete_id'])) {
				$writing = new Writing($_POST['table_writings_delete_id']);
				$writing->delete();
			}
			break;

		case 'form_options' :
			if ($_POST['option'] == 'delete') {
				$writings->delete_from_ids(json_decode($_POST['ids']));
			} elseif ($_POST['option'] == 'estimate_accounting_code') {
				$writings->estimate_accounting_code_from_ids(json_decode($_POST['ids']));
			} elseif ($_POST['option'] == 'estimate_category') {
				$writings->estimate_category_from_ids(json_decode($_POST['ids']));
			} else {
				echo $writings->determine_show_form_modify($_POST['option']);
				exit(0);
			}
			break;

		case 'writings_modify' :
			$writings_to_modify = new Writings();
			$parameters = $writings_to_modify->clean_from_ajax($_POST);
			if (isset($parameters['id'])) {
				$writings_to_modify->id = $parameters['id'];
				$writings_to_modify->select();
				$writings_to_modify->apply($parameters['operation'], $parameters['value']);
			}
			break;

		default :
			break;

	}
}

$writings->filter_with($_SESSION['filter']);

$writings->add_order($_SESSION['order']['name']." ".$_SESSION['order']['direction']);
$writings->add_order("number DESC, amount_inc_vat DESC");
$writings->select();

echo json_encode(array('status' => show_status(), 'table' => $writings->show(), 'extra' => $extra));
exit(0);
