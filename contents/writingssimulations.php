<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

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
				$writingssimulation->load(array('id' => $_POST['id']));
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
				$writingssimulation->load(array('id' => (int)$_POST['table_writingssimulation_duplicate_id']));
				$writingssimulation->duplicate($_POST['table_writingssimulation_duplicate_amount']);
			}
			break;
	         default :
			break;
	}
}

$menu = Plugins::factory("Menu_Area");
echo $menu->show();

$writings_simulation = new Writings_Simulations();
if (!isset($_SESSION['filter']['start'])) {
	list($start, $stop) = determine_month(time());
	$_SESSION['filter'] = array('start' => $start, 'stop' => $stop);
 }
$start = isset($_GET['start']) ? $_GET['start'] : $_SESSION['filter']['start'];
$timestamp_selected = determine_integer_from_post_get_session(null, "start");
$heading = new Heading_Area(utf8_ucfirst(__('make a simulation')), $writings_simulation->display_timeline_at($start));
echo $heading->show();

$simulations = new Writings_Simulations();
$simulations->select();
$working = $simulations->display();

$simulation = new Writings_Simulation();
$working .= $simulation->form($timestamp_selected);

$area = new Working_Area($working);
echo $area->show();
