<?php
/*
	lozeil
	$Author: $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

if (isset($_POST['action']) and count($_POST) > 0) {
	switch ($_POST['action']) {
		
		case 'insert':
			$writingssimulation = new Writings_Simulation();
			if ($writingssimulation->is_form_valid($_POST)) {
				$writingssimulation->fill($_POST);
				$writingssimulation->save();
			}
			break;
			
		case 'edit':
			$writingssimulation = new Writings_Simulation();
			if (isset($_POST['id']) and $_POST['id'] > 0 and $writingssimulation->is_form_valid($_POST)) {
				$writingssimulation->load($_POST['id']);
				$writingssimulation->fill($_POST);
				$writingssimulation->save();
			}
			break;
			
		case 'delete':
			if (isset($_POST['table_writingssimulation_delete_id'])) {
				$writingssimulation = new Writings_Simulation($_POST['table_writingssimulation_delete_id']);
				$writingssimulation->delete();
			}
			break;
			
		case 'duplicate':
			if (isset($_POST['table_writingssimulation_duplicate_id']) and isset($_POST['table_writingssimulation_duplicate_amount'])) {
				$writingssimulation = new Writings_Simulation();
				$writingssimulation->load((int)$_POST['table_writingssimulation_duplicate_id']);
				$writingssimulation->duplicate($_POST['table_writingssimulation_duplicate_amount']);
			}
			break;
			
		default :
			break;
	}
}
$writings_simulation = new Writings_Simulations();
$timestamp_selected = determine_integer_from_post_get_session(null, "timestamp");

$menu = new Menu_Area();
$menu->prepare_navigation(__FILE__);
echo $menu->show();

$heading = new Heading_Area(utf8_ucfirst(__('make a simulation')), $writings_simulation->display_timeline_at($timestamp_selected));
echo $heading->show();

$simulations = new Writings_Simulations();
$simulations->select();
echo $simulations->display();

$simulation = new Writings_Simulation();
echo $simulation->form();