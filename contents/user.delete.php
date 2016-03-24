<?php
/* Lozeil -- Copyright (C) No Parking 2016 - 2016 */

if (isset($_POST['user'])) {
	$user = new User();
	if ($user->load(array('id' => (int)$_POST['user']['id']))) {
		$user->delete();
	}
	$id = 0;
}

$id = isset($id) ? $id : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

$user = new User();
$user->load(array('id' => $id));
$working = $user->ask_before_delete();

$area = new Working_Area($working);
echo $area->show();
