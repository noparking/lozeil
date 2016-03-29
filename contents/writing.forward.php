<?php
/* Lozeil -- Copyright (C) No Parking 2016 - 2016 */

if (isset($_POST['writing'])) {
	$writing = new Writing();
	$writing->load(array('id' => (int)$_REQUEST['writing']['id']));
	$writing->forward($_REQUEST['writing']['period']);
}

$id = isset($id) ? $id : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

$writing = new Writing();
$writing->load(array('id' => $id));
$working = $writing->forwarder();

$area = new Working_Area($working);
echo $area->show();
