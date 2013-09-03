<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

if (isset($_POST['submit'])) {
	$sources = $_POST;
	unset($sources['submit']);
	
	if(!empty($sources['name_new'])) {
		$source = new Source();
		$source->name = $sources['name_new'];
		$source->save();
	}
	unset($sources['name_new']);
	
	foreach ($sources as $id => $name) {
		$source = new Source();
		$source->load($id);
		if ($source->name != $name and !empty($name)) {
			$source->name = $name;
			$source->save();
		} elseif (empty($name)) {
			$source->delete();
		}
	}
}


$menu = new Menu_Area();
$menu->prepare_navigation(__FILE__);
echo $menu->show();

$heading = new Heading_Area();
echo $heading->show();

$sources = new Sources();
$sources->select();
echo $sources->show_form();