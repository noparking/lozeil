<?php
/* Lozeil -- Copyright (C) No Parking 2016 - 2016 */

if (isset($_POST['writing'])) {
	$writing = new Writing();
	$writing->load(array('id' => (int)$_REQUEST['writing']['id']));
	$writing->duplicate($_REQUEST['writing']['period']);
}

$id = isset($id) ? $id : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

$writing = new Writing();
$writing->load(array('id' => $id));
$working = $writing->duplicator();

$area = new Working_Area($working);
echo $area->show();
