<?php
/* Lozeil -- Copyright (C) No Parking 2016 - 2016 */

if (isset($_POST['bank'])) {
	$bank = new Bank();
	if ($bank->load(array('id' => (int)$_POST['bank']['id']))) {
		$bank->delete();
	}
	$id = 0;
}

$id = isset($id) ? $id : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

$bank = new Bank();
$bank->load(array('id' => $id));
$working = $bank->ask_before_delete();

$area = new Working_Area($working);
echo $area->show();
