<?php
  /* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

if (isset($_POST) and isset($_POST['date_picker_from']) and isset($_POST['show_submit'])) {
	list($_SESSION['filter']['start'], $_SESSION['filter']['stop']) = determine_fiscal_year(timestamp_from_year($_POST['date_picker_from']));
} else if (isset($_GET['start']) and isset($_GET['stop'])) {
	$_SESSION['filter'] = array('start' => $_GET['start'], 'stop' => $_GET['stop']);
} else if (!isset($_SESSION['filter']) or empty($_SESSION['filter'])) {
	list($_SESSION['filter']['start'], $_SESSION['filter']['stop']) = determine_fiscal_year(time());
}

if (isset($_POST['period_picker'])) {
	$_SESSION['filter']['period'] = $_POST['period_picker'];
} else {
	$_SESSION['filter']['period'] = "variable";
}

$menu = Plugins::factory("Menu_Area");
echo $menu->show();
   
$heading = new Heading_Area(utf8_ucfirst(__("income statement detail")));
echo $heading->show();

$reportings = new Reportings();
$reporting = new Reporting();

$working = $reporting->form_detail($_SESSION['filter']['period'], $_SESSION['filter']['start']);
$working .= $reportings->display_reportings_detail($_SESSION['filter']['period'], $_SESSION['filter']['start']); 
echo $working;