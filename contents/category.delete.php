<?php
/* Lozeil -- Copyright (C) No Parking 2016 - 2016 */

if (isset($_POST['category'])) {
	$category = new Category();
	if ($category->load(array('id' => (int)$_POST['category']['id']))) {
		$category->delete();
	}
	$id = 0;
}

$id = isset($id) ? $id : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

$category = new Category();
$category->load(array('id' => $id));
$working = $category->ask_before_delete();

$area = new Working_Area($working);
echo $area->show();
