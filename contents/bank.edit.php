<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

if (isset($_POST['bank'])) {
	$bank = new Bank();
	$bank->load(array('id' => (int)$_POST['bank']['id']));
	$cleaned = $bank->clean($_POST['bank']);
	$bank->fill($cleaned);
	$bank->save();
	$id = $bank->id;
}

$id = isset($id) ? $id : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

$bank = new Bank();
$bank->load(array('id' => $id));
$working = $bank->edit();

$area = new Working_Area($working);
echo $area->show();
