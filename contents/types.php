<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

if (isset($_POST['submit'])) {
	$types = $_POST;
	unset($types['submit']);
	
	if(!empty($types['name_new'])) {
		$type = new Type();
		$type->name = $types['name_new'];
		$type->save();
	}
	unset($types['name_new']);
	
	foreach ($types as $id => $name) {
		$type = new Type();
		$type->load($id);
		if ($type->name != $name and !empty($name)) {
			$type->name = $name;
			$type->save();
		} elseif (empty($name) and $type->is_deletable()) {
			$type->delete();
		}
	}
}


$menu = new Menu_Area();
$menu->prepare_navigation(__FILE__);
echo $menu->show();

$heading = new Heading_Area();
echo $heading->show();

$types = new Types();
$types->select();
echo $types->show_form();