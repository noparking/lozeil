<?php
$status = "false";
$data = "";

if (isset($_REQUEST) and isset($_REQUEST['action']) and isset($_REQUEST['accountingcodes_id'])) {
	switch ($_REQUEST['action']) {
	case "form_edit":
		$accountcode = new Accounting_Code();
		$accountcode->load(array('number' => $_REQUEST['accountingcodes_id']));
		if ($accountcode->id > 0) {
			$data = $accountcode->form_edit();
			$status = "true";
		}
		break;
	case "form_add":
		$accountcode = new Accounting_Code();
		$accountcode->load(array('number' => $_REQUEST['accountingcodes_id']));
		if ($accountcode->id > 0) {
			$data = $accountcode->form_add();
			$status = "true";
		}
		break;
	case "delete":
		$accountcode = new Accounting_Code();
		$accountcode->load(array('number' => $_REQUEST['accountingcodes_id']));
		if ($accountcode->id > 0) {
			$accountcode->delete();
			$data = show_status();
			$status = "true";
		}
		break;
	case "edit":
		$accountcode = new Accounting_Code();
		$accountcode->load(array('number' => $_REQUEST['accountingcodes_id']));
		$data .= $_REQUEST['accountingcodes_id'];
		if ($accountcode->id > 0 ) {
			$data .= $_REQUEST['accountingcodes_name'];
			$cleaned = $accountcode->clean($_REQUEST);
			$accountcode->fill($cleaned);
			$accountcode->update();
			$status = "true";
		}
		break;
	case "add":
		$accountcode = new Accounting_Code();
		$accountcode->load(array('number' => $_REQUEST['accountingcodes_id']));
		if ($accountcode->id == 0) {
			$accountcode->number = $_REQUEST['accountingcodes_id'];
			$cleaned = $accountcode->clean($_REQUEST);
			$accountcode->fill($cleaned);
			$accountcode->save();
			$data = show_status();
			$status = "true";
		}
		else {
			status(__("number"), __("already exists"), 1);
			$data = show_status();
		}
		break;
	case "import_default":
		$accountcodes = new Accounting_Codes();
		$accountcodes->import_default();
		$status = "true";
		break;
	default:
		; 
	}
}

echo json_encode(array('status' => $status, 'data' => $data));