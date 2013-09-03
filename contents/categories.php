<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

if (isset($_POST['submit'])) {
	$categories = $_POST;
	unset($categories['submit']);
	
	if(!empty($categories['name_new'])) {
		$category = new Category();
		$category->name = $categories['name_new'];
		$category->save();
	}
	unset($categories['name_new']);
	
	foreach ($categories as $id => $name) {
		$category = new Category();
		$category->load($id);
		if ($category->name != $name and !empty($name)) {
			$category->name = $name;
			$category->save();
		} elseif (empty($name) and $category->is_deletable()) {
			$category->delete();
		}
	}
}

$menu = new Menu_Area();
$menu->prepare_navigation(__FILE__);
echo $menu->show();

$heading = new Heading_Area();
echo $heading->show();

$categories = new Categories();
$categories->select();
echo $categories->show_form();