<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

$menu = Plugins::factory("Menu_Area");
echo $menu->show();

if (isset($_POST['name_new'])) {
	$bank = new Bank();
	$data['name'] = $_POST['name_new'];
	if (isset($_POST['iban_new'])) {
		$data['iban'] = $_POST['iban_new'];
	}
	$data['accountingcodes_id'] = isset($_POST['accountingcodes_id_new']) ? $_POST['accountingcodes_id_new'] : 0;
	$cleaned = $bank->clean($data);
	$bank->fill($cleaned);
	$bank->selected = isset($_POST['selected_new']) ? 1 : 0;
	$bank->save();
} 

if (isset($_POST['banks'])) {
	foreach($_POST['banks'] as $id => $data) {
		$bank = new Bank();
			$bank->load(array('id' => $id));
			if ($bank->id > 0) {
				switch ($_POST['action']) {
				case "add":
					echo $bank->form_add();
					break;
				case "modify":
					echo $bank->show_form_modification();
					break;
				case "delete":
					$bank->delete();
					break;
				case "save":
					$cleaned = $bank->clean($data);
					$bank->fill($cleaned);
					(isset($data['selected'])) ? $bank->selected = 1 : $bank->selected = 0;
					$bank->save();
					break;
				default:
				}
			}
	}
}  

$heading = new Heading_Area(utf8_ucfirst(__('manage the banks')));
echo $heading->show();

$banks = new Banks();
echo $banks->add_bank();
$banks->select();

$working = $banks->display();
$area = new Working_Area($working);
echo $area->show();
