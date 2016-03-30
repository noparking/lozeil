<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

if (isset($_POST['user'])) {
	$user = new User();
	$user->load(array('id' => (int)$_POST['user']['id']));
	$cleaned = $user->clean_in_cascade($_POST['user']);
	$user->fill_in_cascade($cleaned);
	$user->save_in_cascade();
	$id = $user->id;
}

$id = isset($id) ? $id : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

$user = new User();
$user->load_in_cascade(array('id' => $id));
$working = $user->edit();

$area = new Working_Area($working);
echo $area->show();
