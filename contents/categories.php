<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

$menu = Plugins::factory("Menu_Area");
echo $menu->show();


if (isset($_POST['name_new'])) {
	$category = new Category();
	$data['name'] = $_POST['name_new'];
	if (isset($_POST['vat_new']))
		$data['vat'] = $_POST['vat_new'];
	$cleaned = $category->clean_str($data);
	$category->fill($cleaned);
	(isset($_POST['vat_category'])) ? $category->vat_category = 1 : $category->vat_category = 0;

	$category->save();
 } 

if (isset($_POST['category'])) {
	foreach($_POST['category'] as $id => $data) {
		if (isset($data['checked']) or isset($data['submit'])) {
			$category = new Category();
			$category->load(array('id'=>$id));
			if ($category->id > 0) { 
				switch ($_POST['action']) {
				case "delete":
					$category->delete();
					break;
				case "add":
					echo $category->form_add();
					break;
				case "modify":
					echo $category->show_form_modification();
					break;
				case "save":
					$cleaned = $category->clean_str($data);
					$category->fill($cleaned);
					(isset($data['vat_category'])) ? $category->vat_category = 1 : $category->vat_category = 0;

					$category->save();
					break;
				default:;
				}
			}
		}
	}
 }

$heading = new Heading_Area(utf8_ucfirst(__('manage the categories')));
echo $heading->show();

$categories = new Categories();
echo $categories->add_category();
$categories->select();

$working = $categories->display();
$area = new Working_Area($working);
echo $area->show();