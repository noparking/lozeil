<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

if (isset($_POST['writing'])) {
	$writing = new Writing();
	$writing->load(array('id' => (int)$_POST['writing']['id']));
	$cleaned = $writing->clean($_POST['writing']);
	$writing->fill($cleaned);
	$bayesianelements = new Bayesian_Elements();
	$bayesianelements->stuff_with($writing);
	$bayesianelements->increment();
	$writing->save();
	$id = $writing->id;
}

$id = isset($id) ? $id : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

$writing = new Writing();
$writing->load(array('id' => $id));
$working = $writing->edit();

$area = new Working_Area($working);
echo $area->show();
