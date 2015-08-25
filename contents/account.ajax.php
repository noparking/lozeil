<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

$status = "false";
$data = "";

if (isset($_REQUEST['action'])) {
	switch ($_REQUEST['action']) {
		case "form_modif" :
			$file_param = new Param_File(isset($GLOBALS['pathconfig']['cfg']) ? $GLOBALS['pathconfig']['cfg']."param.inc.php" : dirname(__FILE__)."/../cfg/param.inc.php");
			$values = $file_param->values();
			$value = $values['param'][$_REQUEST['id']];
			$data = $file_param->choice_form($_REQUEST['id'], $value);
			$status = "true";
			break;
	}
}

echo json_encode(array('status' => $status,'data' => $data));
