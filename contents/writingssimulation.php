<?php
/*
	lozeil
	$Author: $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

$menu = new Menu_Area();
$menu->prepare_navigation(__FILE__);
echo $menu->show();

$heading = new Heading_Area(utf8_ucfirst(__('make a simulation')));
echo $heading->show();

$simulation = new Writings_Simulation();
echo $simulation->show_form();