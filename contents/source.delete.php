<?php
/* Lozeil -- Copyright (C) No Parking 2016 - 2016 */

if (isset($_POST['source'])) {
	$source = new Source();
	if ($source->load(array('id' => (int)$_POST['source']['id']))) {
		$source->delete();
	}
	$id = 0;
}

$id = isset($id) ? $id : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

$source = new Source();
$source->load(array('id' => $id));
$working = $source->ask_before_delete();

$area = new Working_Area($working);
echo $area->show();
