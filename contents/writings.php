<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

$writings = new Writings();
if (isset($_POST)) {
	if (isset($_POST['vat_date']) and is_datepicker_valid($_POST['vat_date'])) {
		$writings->calculate_quarterly_vat(timestamp_from_datepicker($_POST['vat_date']));
		list($_SESSION['filter']['start'], $_SESSION['filter']['stop']) = determine_month(timestamp_from_datepicker($_POST['vat_date']));
		$vat_category = new Categories();
		$vat_category->filter_with(array("vat_category" => 1));
		$vat_category->select();
		if (isset($vat_category[0])) {
			$_SESSION['filter']['categories_id'] = $vat_category[0]->id;
		}
	}
}

if (isset($_REQUEST['action'])) {
	switch ($_REQUEST['action']) {
	case "open_attachment" :
		$file = new File();
		$file->load(array('id' => (int)$_REQUEST['id']));
		$file->open_attachment();
		exit();
		break;
	default:
		break;
	}
}

$menu = Plugins::factory("Menu_Area");
echo $menu->show();

$writings->set_order($_SESSION['order']['name'], $_SESSION['order']['direction'].", number DESC, amount_inc_vat DESC");

if (isset($_GET['start']) and isset($_GET['stop'])) {
	$_SESSION['filter'] = array('start' => $_GET['start'], 'stop' => $_GET['stop']);
} elseif (!isset($_SESSION['filter']) or empty($_SESSION['filter'])) {
	list($start, $stop) = determine_month(time());
	$_SESSION['filter'] = array('start' => $start, 'stop' => $stop);
}

$writings->filter_with($_SESSION['filter']);
$writings->select();

$heading = new Heading_Area(utf8_ucfirst(__('consult balance sheet')), $writings->display_timeline_at($_SESSION['filter']['start']), $writings->form_filter($_SESSION['filter']['start'], $_SESSION['filter']['stop']));
echo $heading->show();

$working =  $writings->display();
$working .= $writings->modify_options();

$writing = new Writing();
$working .= $writing->form();

$area = new Working_Area($working);
echo $area->show();
