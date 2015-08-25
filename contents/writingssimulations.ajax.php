<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

$extra = "";
switch ($_REQUEST['action']) {
	
	case 'refresh_simulations_timeline':
		$writings = new Writings_Simulations();
		echo $writings->display_timeline_at($_SESSION['filter']['start']);
		
		exit(0);
		break;
	
	case 'reload_insert_form':
		$writing = new Writings_Simulation();
		echo $writing->display();

		exit(0);
		break;
	
	case "form_edit":
		$writing = new Writings_Simulation();
		$writing->load(array('id' => (int)$_REQUEST['table_simulations_modify_id']));
		echo $writing->form_in_table();

		exit(0);
		break;
	
	case "form_duplicate" :
		$writing = new Writings_Simulation();
		$writing->load(array('id' => (int)$_POST['table_simulations_form_duplicate_id']));
		echo $writing->form_duplicate();
		exit(0);
		break;
	
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
		if (isset($_POST['table_simulations_delete_id'])) {
			$writingssimulation = new Writings_Simulation($_POST['table_simulations_delete_id']);
			$writingssimulation->delete();
		}
		break;

	case 'duplicate':
		if (isset($_POST['simulation_id']) and isset($_POST['table_simulations_duplicate_amount'])) {
			$writingssimulation = new Writings_Simulation();
			$writingssimulation->load(array('id' => (int)$_POST['simulation_id']));
			$writingssimulation->duplicate($_POST['table_simulations_duplicate_amount']);
		}
		break;
	
	default :
		break;
}

$simulations = new Writings_Simulations();
$simulations->select();

echo json_encode(array('status' => show_status(), 'table' => $simulations->display(), 'extra' => $extra));
exit(0);
