<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

if (isset($_POST['banks'])) {
	foreach($_POST['banks'] as $id => $data) {
		$bank = new Bank();
			$bank->load(array('id' => $id));
			if ($bank->id > 0) {
				switch ($_POST['action']) {
					case "delete":
						$bank->delete();
						break;
				}
			}
	}
}  

$menu = Plugins::factory("Menu_Area");
echo $theme->menu($menu);

$heading = new Heading_Area(utf8_ucfirst(__('manage the banks')));
echo $theme->heading($heading);

$bank = new Bank();

$banks = new Banks();
$banks->select();

$area = new Working_Area($bank->link_to_edit().$banks->display());
echo $area->show();
