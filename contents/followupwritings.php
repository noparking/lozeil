<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

$menu = new Menu_Area();
$menu->prepare_navigation(__FILE__);
echo $menu->show();

$heading = new Heading_Area(utf8_ucfirst(__('consult statistics')));
echo $heading->show();

$sparkline = new Sparkline();
if (isset($_POST['submit'])) {
	$sparkline->period = $_POST['period'];
	if ($_POST['period'] == 'day') {
		$sparkline->type = 'line';
	} else {
		$sparkline->type = $_POST['type'];
	}
		$sparkline->year = mktime(0, 0, 0, 1, 1, $_POST['year']);
}

echo $sparkline->display();
