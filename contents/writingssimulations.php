<?php
/*
	lozeil
	$Author: $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

$writings_simulation = new Writings_Simulations();
if (!empty($_POST)) {
	unset($_POST['submit']);
	$results = $writings_simulation->prepare_results_from_post($_POST);
}
$timestamp_selected = determine_integer_from_post_get_session(null, "timestamp");

$menu = new Menu_Area();
$menu->prepare_navigation(__FILE__);
echo $menu->show();

$heading = new Heading_Area(utf8_ucfirst(__('make a simulation')), $writings_simulation->display_timeline_at($timestamp_selected));
echo $heading->show();

$simulation = new Writings_Simulations();
$simulation->select();
echo $simulation->show_form();