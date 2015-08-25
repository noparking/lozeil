<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

$menu = Plugins::factory("Menu_Area");
echo $menu->show();

if (isset($_POST['name_new'])) {
	$source = new Source();
	$data['name'] = $_POST['name_new'];
	$cleaned = $source->clean($data);
	$source->fill($cleaned);
	$source->save();
}

if (isset($_POST['sources'])) {
	foreach($_POST['sources'] as $id => $data) {
		$source = new Source();
			$source->load(array('id' => $id));
			if ($source->id > 0) {
				switch ($_POST['action']) {
				case "add":
					echo $source->form_add();
					break;
				case "modify":
					echo $source->show_form_modification();
					break;
				case "delete":
					$source->delete();
					break;
				case "save":
					$cleaned = $source->clean($data);
					$source->fill($cleaned);
					$source->save();
					break;
				default:
				}
		}
	}
 } 

$heading = new Heading_Area(utf8_ucfirst(__('manage the sources')));
echo $heading->show();

$sources = new Sources();
echo $sources->add_source();
$sources->select();

$working = $sources->display();
$area = new Working_Area($working);
echo $area->show();
