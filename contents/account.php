<?php
  /* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

$menu = Plugins::factory("Menu_Area");
echo $menu->show();

$heading = new Heading_Area(utf8_ucfirst(__("manage the account")));
echo $heading->show();

if (isset($_POST['account'])) {
	$keys = array_keys($_POST['account']);
	$key = $keys[0];
	$file_param = new Param_File(isset($GLOBALS['pathconfig']['cfg']) ? $GLOBALS['pathconfig']['cfg']."param.inc.php" : dirname(__FILE__)."/../cfg/param.inc.php");
	$cleaned = $file_param->clean($key, $_POST['account'][$key]['name']);
	if ($_POST['account'][$key]['name'] !== "none") {
		$file_param->write_value($key, $cleaned);
	}
} elseif (isset($_POST['reset'])) {
	$default_param = new Param_File(dirname(__FILE__)."/../cfg/param.".$GLOBALS['param']['locale_lang'].".default.php");
	$file_param = new Param_File(isset($GLOBALS['pathconfig']['cfg']) ? $GLOBALS['pathconfig']['cfg']."param.inc.php" : dirname(__FILE__)."/../cfg/param.inc.php");
	$values = $default_param->values();
	$value = $values['param'][$_POST['reset']];
	$file_param->write_value($_POST['reset'], $value);
} elseif (isset($_POST['submit']) and $_POST['submit'] == __("reset to default values")) {
	if (isset($_SESSION['accountant_view']) and $_SESSION['accountant_view'] == 1) {
		$default_param = new Param_File(dirname(__FILE__)."/../cfg/param.".$GLOBALS['param']['locale_lang'].".default.php");
		$file_param = new Param_File(isset($GLOBALS['pathconfig']['cfg']) ? $GLOBALS['pathconfig']['cfg']."param.inc.php" : dirname(__FILE__)."/../cfg/param.inc.php");
		$default_param->load_at_global_level();
		$default_values = $default_param->values(); 
		foreach ($default_values['param'] as $name => $value) {
			$file_param->write_value($name, $value);
		}
	} else {
		status(__("parameters"), __("modifications not allowed"), -1);
	}
}

$file_param = new Param_File(isset($GLOBALS['pathconfig']['cfg']) ? $GLOBALS['pathconfig']['cfg']."param.inc.php" : dirname(__FILE__)."/../cfg/param.inc.php");
$values = $file_param->values();
$area = new Working_Area($file_param->display($values['param']));
echo $area->show();
