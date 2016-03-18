<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

if (!isset($_GET['start'])) {
	$_GET['start'] = time();
}

$menu = Plugins::factory("Menu_Area");
echo $menu->show();

$heading = new Heading_Area(utf8_ucfirst(__('consult statistics')));
echo $heading->show();

$followupwritings = new Writings_Followup();

if (isset($_GET['filter'])) {
	$followupwritings->filter = $_GET['filter'];
}

if (isset($_GET['scale'])) {
	$followupwritings->scale = $_GET['scale'];
}

if (isset($_POST) and !empty($_POST) and isset($_POST['scale_timeseries_select'])) {
	$followupwritings->scale = $_POST['scale_timeseries_select'];
	$followupwritings->filter = $_POST['filter_timeseries_select'];
}

$working = $followupwritings->show($_GET['start']);

$area = new Working_Area($working);
echo $area->show();
