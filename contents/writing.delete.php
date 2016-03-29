<?php
/* Lozeil -- Copyright (C) No Parking 2016 - 2016 */

if (isset($_POST['writing'])) {
	$writing = new Writing();
	if ($writing->load(array('id' => (int)$_POST['writing']['id']))) {
		$writing->delete();
	}
	$id = 0;
}

$id = isset($id) ? $id : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

$writing = new Writing();
$writing->load(array('id' => $id));
$working = $writing->ask_before_delete();

$area = new Working_Area($working);
echo $area->show();
