<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

if (isset($_POST['category'])) {
	foreach($_POST['category'] as $id => $data) {
		if (isset($data['checked'])) {
			$category = new Category();
			$category->load(array('id' => $id));
			if ($category->id > 0) { 
				switch ($_POST['action']) {
					case "delete":
						$category->delete();
						break;
				}
			}
		}
	}
}

$menu = Plugins::factory("Menu_Area");
echo $theme->menu($menu);
 
$heading = new Heading_Area(utf8_ucfirst(__('manage the categories')));
echo $theme->heading($heading);

$category = new Category();

$categories = new Categories();
$categories->select();

$area = new Working_Area($category->link_to_edit().$categories->display());
echo $area->show();
