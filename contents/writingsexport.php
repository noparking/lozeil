<?php
  /* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

if (isset($_POST) and  isset($_POST['date_picker_from'])) {
		
	$writings_export = new Writings_Export();
	$writings_export->clean_and_set($_POST);
	$writings_export->export();
	header("Location: ".link_content("content=account.php"));
	exit;
		
 }
 
 else {	
	 $menu = Plugins::factory("Menu_Area");
	 echo $menu->show();
	 $heading = new Heading_Area(utf8_ucfirst(__('export writings')));
	 echo $heading->show();
	 $writings_export = new Writings_Export();
	 echo $writings_export->get_form(link_content("content=account.php"));
 }