<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

if (isset($_POST['source'])) {
	$source = new Source();
	$source->load(array('id' => (int)$_POST['source']['id']));
	$cleaned = $source->clean($_POST['source']);
	$source->fill($cleaned);
	$source->save();
	$id = $source->id;
}

$id = isset($id) ? $id : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

$source = new Source();
$source->load(array('id' => $id));
$working = $source->edit();

$area = new Working_Area($working);
echo $area->show();
