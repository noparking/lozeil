<?php
/* Lozeil -- Copyright (C) No Parking 2016 - 2016 */

if (isset($_POST['writing'])) {
	$writing = new Writing();
	$writing->load(array('id' => (int)$_REQUEST['writing']['id']));
	$writing->split($writing->clean_amounts_from_ajax($_REQUEST['writing']['amounts']));
}

$id = isset($id) ? $id : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

$writing = new Writing();
$writing->load(array('id' => $id));
$working = $writing->splitter();

$area = new Working_Area($working);
echo $area->show();
