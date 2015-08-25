<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

if (isset($_POST['apply_submit']) and isset($_POST['apply_text']) and !empty($_POST['apply_text'])) {
	$model = new Model();

	if ($model->is_json($_POST['apply_text'])) {
		$data = json_decode($_POST['apply_text'], TRUE);
		$model->apply($data);		
	} else {
		status(__("models"), __("wrong JSON format"), -1);
	}
}

$menu = Plugins::factory("Menu_Area");
echo $menu->show();

$heading = new Heading_Area(utf8_ucfirst(__("manage the models")));
echo $heading->show();

$model = new Model();
$model->generate_data();

$working = $model->display_options().$model->display();
$area = new Working_Area($working);
echo $area->show();
