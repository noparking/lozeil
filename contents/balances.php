<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

if (isset($_POST['date_picker_from'])) {
	list($_SESSION['filter']['start'], $_SESSION['filter']['stop']) = determine_fiscal_year(timestamp_from_year($_POST['date_picker_from']));
} else if (isset($_GET['start']) and isset($_GET['stop'])) {
	$_SESSION['filter'] = array('start' => $_GET['start'], 'stop' => $_GET['stop']);
} elseif (!isset($_SESSION['filter']) or empty($_SESSION['filter'])) {
	list($_SESSION['filter']['start'], $_SESSION['filter']['stop']) = determine_fiscal_year(time());
}

$menu = Plugins::factory("Menu_Area");
echo $menu->show();

$heading = new Heading_Area(utf8_ucfirst(__("income statement balance")));
echo $heading->show();

$balances = new Balances();
$balances->filter_with($_SESSION['filter']);
$balances->add_order("number");
$balances->select();

$working = $balances->display($_SESSION['filter']['start']); 
echo $working;
