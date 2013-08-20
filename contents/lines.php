<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

$writings = new Writings();
$writings->set_order('delay', 'ASC');
$writings->select();

echo $writings->show();
