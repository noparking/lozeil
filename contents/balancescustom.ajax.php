<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

$status = "false";
$data = "";

if (isset($_REQUEST['action']) and isset($_REQUEST['reportingcode'])) {
  switch ($_REQUEST['action']) {
  	case "form_edit_reporting":
		$reporting = new Reporting();
		$reporting->load(array('id' => $_REQUEST['reportingcode']));
		if ($reporting->id > 0 ) {
			$data = $reporting->form_edit();
			$status = "true";
		}
		break;
	case "form_add_reporting":
		$reporting = new Reporting();
		$reporting->load(array('id' => $_REQUEST['reportingcode']));
		$data =  $reporting->form_add();
		$status = "true";
		break;
	case "form_edit_accountingcode":
		$affectation = new Accounting_Code_Affectation();
		$affectation->load(array('accountingcodes_id' => $_REQUEST['accountingcodes_id']));

		if ($affectation->id > 0) {
			$data = $affectation->form_edit_with_reporting($_REQUEST['reportingcode']);
			$status = "true";
		}
		break;
	case "form_edit_accountingcode_non_affected":
		$affectation = new Accounting_Code_Affectation();
		$affectation->load(array('accountingcodes_id' => $_REQUEST['accountingcodes_id']));

		if ($affectation->id > 0) {
			$data = $affectation->form_edit_not_affected();
			$status = "true";
		}
		break;
	case "edit_reporting":
		$reporting = new Reporting();
		$reporting->load(array('name' => $_REQUEST['name'], 'activities_id' => $_SESSION['currentactivity']));
		if ($reporting->id > 0 and $reporting->id != $_REQUEST['reportingcode']) {
			status(__("reporting"), __("name already exists"), -1);
		} else {
			$reporting = new Reporting();
			$reporting->load(array('id' => $_REQUEST['reportingcode']));
			$data = $reporting->clean($_REQUEST);
			$reporting->fill($data);
			if (!isset($data['base'])) {
				$reporting->base = 0;
			} else {
				$reporting->base = 1;
			}
			if ($reporting->id > 0) {
				$reporting->save();
			}

		}
		break;
	case "add_reporting":
		$reporting = new Reporting();
		$reporting->load(array('name' => $_REQUEST['name']));
		if (empty($_REQUEST['name'])) {
			status(__("reporting"), __("no name"), -1);						
		}
		else if ($reporting->id > 0) {
			status(__("reporting"), __("name already exists"), -1);
		} else {
			$reporting = new Reporting();
			$data = $reporting->clean($_REQUEST);
			$reporting->fill($data);
			$reporting->activities_id = $_SESSION['currentactivity'];
			$reporting->save();
		}
		break;
	case "delete" :
		$reporting = new Reporting();
		$reporting->load(array('id' => $_REQUEST['reportingcode']));
		$reporting->delete_in_cascade();
		$status = "true";
		break;
	case "edit_accountingcode":
		$reporting = new Reporting();
		$reporting->load(array('id' => $_REQUEST['reportingcode']));

		if ($reporting->id > 0) {
			$code = new Accounting_Code();
			$code->load(array('id' => $_REQUEST['accountingcodes_id']));
			$cleaned_data = $code->clean($_REQUEST);
			$code->fill($cleaned_data);
			$code->save();

			$affectation = new Accounting_Code_Affectation();
			$affectation->load(array('reportings_id' => $_REQUEST['affectation_reporting_id'], 'accountingcodes_id' => $_REQUEST['accountingcodes_id']));

			if ($reporting->id != $affectation->reportings_id) {
				$affectation->reportings_id = $reporting->id;
			}
			$affectation->save();
		}
		break;
	case "edit_accountingcode_non_affected":
		$reporting = new Reporting();
		$reporting->load(array('id' => $_REQUEST['reportingcode']));

		if ($reporting->id > 0) {
			$code = new Accounting_Code();
			$code->load(array('id' => $_REQUEST['accountingcodes_id']));
			
			$affectation = new Accounting_Code_Affectation();
			$affectation->load(array('reportings_id' => $reporting->id, 'accountingcodes_id' => $_REQUEST['accountingcodes_id']));
			
			if ($code->id > 0) {
				$affectation->accountingcodes_id = $_REQUEST['accountingcodes_id'];
				$affectation->reportings_id = $reporting->id;
				$affectation->save();
				$cleaned_data = $code->clean($_REQUEST);
				$code->fill($cleaned_data);
				$code->save(); 
			}
		}
		break;
	case "delete_accounting":
		if (isset($_REQUEST['accountingcode'])) {
			$affectation = new Accounting_Code_Affectation();
			$affectation->load(array('accountingcodes_id' => $_REQUEST['accountingcode'], 'reportings_id' => $_REQUEST['reportingcode'] ));
			if ($affectation->id > 0) {
				$affectation->desaffect();
				$status = "true";
			}
		}
		break;
	case "order":
		if (isset($_REQUEST['id_previous'])) {
			if ($_REQUEST['id_previous'] == 0) {
				$reporting = new Reporting();
				$reporting->load(array('id' => $_REQUEST['reportingcode']));
				if ($reporting->id > 0) {
					$reporting->toend();
				}
			} 
			else {
				$reporting = new Reporting();
				$reporting->load(array('id' => $_REQUEST['reportingcode']));
				if ($reporting->id > 0 ) {
					$reporting->changesort($_REQUEST['id_previous']);
				}
			}
		}
		break;
	case "dragdrop_affected":
		if (isset($_REQUEST['accountingcode']) and isset($_REQUEST['data']) and $_REQUEST['data'] > 0) {
			$affectation = new Accounting_Code_Affectation();
			$affectation_drag = new Accounting_Code_Affectation();
			$affectation->load(array('accountingcodes_id' => $_REQUEST['accountingcode'],'reportings_id' => $_REQUEST['reportingcode']));			
			$affectation_drag->load(array('accountingcodes_id' => $_REQUEST['accountingcode'],'reportings_id' => $_REQUEST['data']));
			$accountingc = new Accounting_Code();
			$accountingc->load(array('id' => $_REQUEST['accountingcode']));

			$affectation->reportings_id = $_REQUEST['reportingcode'];
			$affectation->accountingcodes_id = $_REQUEST['accountingcode'];

			if ($_REQUEST['data'] != $_REQUEST['reportingcode']) {
				$affectation->save();
				$affectation_drag->delete();
				$status = "true";	
			}
		}				
		break;
	case "dragdrop_non_affected":
		if (isset($_REQUEST['accountingcode']) and $_REQUEST['reportingcode'] > 0) {
			$affectation = new Accounting_Code_Affectation();
			$affectation->load(array('accountingcodes_id' => $_REQUEST['accountingcode'], 'reportings_id' => 0));
			$affectation->reportings_id = $_REQUEST['reportingcode'];
			$affectation->save();
			$status = "true";	
		}				
		break;
	case "remember_checkbox_status":
		foreach ($_REQUEST['reportingcode'] as $id => $val) {
			$_SESSION['checkbox_reporting'][$val] = $_REQUEST['check'][$id];
		}
		break;
	}
} else if (isset($_REQUEST['action']) and isset($_REQUEST['checkbox_reporting'])) {
	$_REQUEST['checkbox_reporting'] = explode(",", $_REQUEST['checkbox_reporting']);
	foreach ($_REQUEST['checkbox_reporting'] as $id) {
		$reporting = new Reporting();
		$reporting->load(array('id' => $id));
		if ($reporting->id > 0) {
			switch ($_REQUEST['action']) {
				case "desaffect":
					$reporting->desaffect();
					break;
				case "desaffect_in_cascade":
					$reporting->desaffect_in_cascade();
					break;
				case "delete":
					$reporting->delete_in_cascade();
					break;
			}
		}
	}
} else if (isset($_REQUEST['action']) and isset($_REQUEST['checkbox_accountingcode'])) {
	$_REQUEST['checkbox_accountingcode'] = explode(",", $_REQUEST['checkbox_accountingcode']);
	$codes = array();
	$reportings = array();
	foreach ($_REQUEST['checkbox_accountingcode'] as $couple) {
		$couple = explode("/", $couple);
		$codes[] += $couple[0];
		$reportings[] += $couple[1];
	}

	foreach ($codes as $reporting_id => $id) {
		$affectation = new Accounting_Code_Affectation();
		switch ($_REQUEST['action']) {
			case "include":
				if (isset($_REQUEST['include_into_reporting'])) {
					$reporting = new Reporting();
					$reporting->load(array('id' => $_REQUEST['include_into_reporting']));
					$affectation->load(array('reportings_id'=> $reportings[$reporting_id], 'accountingcodes_id' => $id));
					if ($reporting->id > 0) {
						$accountingcode = new Accounting_Code();
						$accountingcode->load(array('id' => $id));
						if ($id > 0) {
							$affectation->accountingcodes_id = $accountingcode->id;
							$affectation->reportings_id = $reporting->id;
							$affectation->save();
						}
					}
				}
				break;
			case "reaffect":
				$code = new Accounting_Code();
				$code->load(array('id'=> $id));
				if ($code->id > 0) {
					$code->reaffect_by_default();
				}
				break;
			case "delete":
				$affectation->load(array('reportings_id' => $reportings[$reporting_id], 'accountingcodes_id' => $id));
				if ($affectation->id > 0) {
					$affectation->desaffect();
				}
				break;
		}
	}
}

$reportings = new Reportings();
echo json_encode(array('status' => show_status(), 'table' => $reportings->show_table_balancescustom($_SESSION['currentactivity'], $_SESSION['filter']), 'data' => $data));
