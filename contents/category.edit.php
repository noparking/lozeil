<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

if (isset($_POST['category'])) {
	$category = new Category();
	$category->load(array('id' => (int)$_POST['category']['id']));
	$cleaned = $category->clean($_POST['category']);
	$category->fill($cleaned);
	$category->save();
	$id = $category->id;
}

$id = isset($id) ? $id : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

$category = new Category();
$category->load(array('id' => $id));
$working = $category->edit();

$area = new Working_Area($working);
echo $area->show();
