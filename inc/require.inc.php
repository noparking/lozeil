<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

$current_directory = dirname(__FILE__);

if (file_exists($current_directory."/../cfg/config.inc.php")) {
	require $current_directory."/../cfg/config.inc.php";
}
if (file_exists($current_directory."/../cfg/param.inc.php")) {
	require $current_directory."/../cfg/param.inc.php";
}

if (isset($GLOBALS['pathconfig']['cfg']) and !empty($GLOBALS['pathconfig']['cfg'])) {
	if (file_exists($GLOBALS['pathconfig']['cfg']."config.inc.php")) {
		require($GLOBALS['pathconfig']['cfg']."config.inc.php");
	}
	if (file_exists($GLOBALS['pathconfig']['cfg']."param.inc.php")) {
		require($GLOBALS['pathconfig']['cfg']."param.inc.php");
	}
}

require ($current_directory."/../inc/autoload.inc.php");
Lozeil_Autoload::register($current_directory, $current_directory."/../var/tmp/autoload.index");

$external_directories = array_merge(directories_for_plugins(), directories_for_applications());
foreach ($external_directories as $name => $path) {
	if (file_exists($path."/cfg/config.inc.php")) {
		require $path."/cfg/config.inc.php";
	}

	if (file_exists($path."/cfg/param.inc.php")) {
		require $path."/cfg/param.inc.php";
	}

	if (file_exists($path."/cfg/acl.inc.php")) {
		require $path."/cfg/acl.inc.php";
	}
}

if (!isset($GLOBALS['param']['locale_lang'])) {
	$GLOBALS['param']['locale_lang'] = "fr_FR";
}
$hl = $GLOBALS['param']['locale_lang'];
if (!file_exists($current_directory."/../lang/".$hl.".lang.php")) {
	$lang = substr($GLOBALS['param']['locale_lang'], 0, 2);
	$hl = $lang."_".strtoupper($lang);
}
$required_files[] = $current_directory."/../lang/".$hl.".lang.php";

foreach ($external_directories as $name => $path) {
	if (file_exists($path."/lang/".$GLOBALS['param']['locale_lang'].".lang.php")) {
		$required_files[] = $path."/lang/".$GLOBALS['param']['locale_lang'].".lang.php";
	} elseif (file_exists($path."/lang/".$hl.".lang.php")) {
		$required_files[] = $path."/lang/".$hl.".lang.php";
	}
}

$required_files[] = $current_directory."/adodb-time.inc.php";
$required_files[] = $current_directory."/misc.inc.php";
$required_files[] = $current_directory."/email.inc.php";
$required_files[] = $current_directory."/excel.inc.php";
$required_files[] = $current_directory."/export_excel.inc.php";
$required_files[] = $current_directory."/plugin.inc.php";

$required_files = array_unique($required_files);

foreach ($required_files as $required_file) {
	require $required_file;
}

if (function_exists("date_default_timezone_set")) {
	date_default_timezone_set("Europe/Paris");
}

if (strpos($_SERVER['SCRIPT_FILENAME'], "setup.php") === false  and strpos($_SERVER['SCRIPT_FILENAME'], "bot.php") === false) {
	$db = new db($dbconfig);
	$db->query("SET NAMES 'utf8'");
}
