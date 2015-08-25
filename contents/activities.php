<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

$menu = Plugins::factory("Menu_Area");
echo $menu->show();

$heading = new Heading_Area(utf8_ucfirst(__('manage the activities')));
echo $heading->show();

if (isset($_POST['activities'])) {
	foreach ($_POST['activities'] as $id => $data) {
		$activity = new Activity();
		$activity->load(array("id" => $id));
		if($activity->id > 0) {
			switch($_POST['action']) {
			case "modify":
				echo $activity->show_form_modification();
				break;
			case "delete":
				$activity->delete_in_cascade();
				break;
			case "save":
				if ($activity->global == 0 and isset($_POST['global'])) {
					$activity->delete_in_cascade();
					$activity = new Activity();
					$activity->global = 1;
					$activity->save();

					$activities = new Activities();
					$activities->select();
					if (count($activities) == 1) {
						$activity->create_single_default_plan();
					} else {
						$activity->create_multiple_default_plan("global");
					}
				} else if ($activity->global == 1 and !isset($_POST['global'])) {
					$activity->delete_in_cascade();
					$activity = new Activity();
					$activity->save();
					$activity->create_default_plan();
				}
				$cleaned = $activity->clean($data);
				$activity->fill($cleaned);
				$activity->save();
				break;
			}
		}
	}
} else if (isset($_POST['name_new']) and !empty($_POST['name_new'])) {
	$activity = new Activity();
	$activity->load(array("name" => $_POST['name_new']));
	if ($activity->id > 0) {
		status(__("name"), __("already exists"), -1);
	} else {
		$activities = new Activities();
		$activities->select();
		
		$activity = new Activity();
		$data['name'] = $_POST['name_new'];
		$cleaned = $activity->clean($data);
		$activity->fill($cleaned);
		$activity->save();
		if (isset($_POST['global_new']) and $activities->global_exists() == false) {
			$activity->global = 1;
			$activity->save();
			$activity->create_multiple_default_plan("global");
		} else {
			$activity->create_default_plan();
		}
		$_SESSION['currentactivity'] = $activity->id;
	}
}

$activities = new Activities();
echo $activities->add_activity();

$activities->select();
$working = $activities->display();
$area = new Working_Area($working);
echo $area->show();
