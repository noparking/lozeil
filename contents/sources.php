<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

if (isset($_POST['sources'])) {
	foreach($_POST['sources'] as $id => $data) {
		$source = new Source();
		$source->load(array('id' => $id));
		if ($source->id > 0) {
			switch ($_POST['action']) {
				case "delete":
					$source->delete();
					break;
			}
		}
	}
} 

$menu = Plugins::factory("Menu_Area");
echo $theme->menu($menu);

$heading = new Heading_Area(utf8_ucfirst(__('manage the sources')));
echo $theme->heading($heading);

$source = new Source();
$sources = new Sources();
$sources->select();
$area = new Working_Area($source->link_to_edit().$sources->display());
echo $area->show();
