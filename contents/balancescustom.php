<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

if (isset($_POST) and isset($_POST['begin_date']) and isset($_POST['show_submit'])) {
	list($_SESSION['filter']['start'], $_SESSION['filter']['stop']) = determine_fiscal_year(timestamp_from_year($_POST['begin_date']));
} else if (isset($_GET['start']) and isset($_GET['stop'])) {
	$_SESSION['filter'] = array('start' => $_GET['start'], 'stop' => $_GET['stop']);
} else if (!isset($_SESSION['filter']) or empty($_SESSION['filter'])) {
	list($_SESSION['filter']['start'], $_SESSION['filter']['stop']) = determine_fiscal_year(time());
}

if (isset($_POST['activities_change'])) {
	$_SESSION['currentactivity'] = $_POST['activities_change'];
} else if (!isset($_SESSION['curentactivity'])) {
	$activities = new Activities();
	$activities->select();
	if (count($activities) > 0) {
		$_SESSION['currentactivity'] = $activities->current()->id;
	}
} 

$menu = Plugins::factory("Menu_Area");
echo $menu->show();

$heading = new Heading_Area(utf8_ucfirst(__("income statement customisation")));
echo $heading->show();

if (isset($_SESSION['currentactivity'])) {
	$reportings = new Reportings();
	$reporting = new Reporting();
	
	$working = $reporting->form_activity($_SESSION['currentactivity'], $_SESSION['filter']['start']);
	$working .= $reportings->display_balancescustom($_SESSION['currentactivity'], $_SESSION['filter']);
	echo $working;
} else {
	status(__("activity"), __("must exist at least one"), -1);
	if (isset($_SESSION['accountant_view']) and $_SESSION['accountant_view'] == 1) {
		header("Location: index.php?content=activities.php");
		exit();
	}
}
