<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/
if (isset($_POST['submit'])) {
	unset($_POST['submit']);
	$keys = array_keys($_POST);
	$banks = new Banks();
	$banks->select();
	foreach ($banks as $bank) {
		if (in_array($bank->id, $keys)) {
			$bank->selected = 1;
		} else {
			$bank->selected = 0;
		}
		$bank->save();
	}
}


$menu = new Menu_Area();
$menu->prepare_navigation(__FILE__);
echo $menu->show();

$heading = new Heading_Area(utf8_ucfirst(__('manage the banks')));
echo $heading->show();

$banks = new Banks();
$banks->select();
echo $banks->show_form();