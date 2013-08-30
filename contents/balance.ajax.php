<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

$writings = new Writings();
$writings->filter_with(array('stop' => time()));
$writings->select();
echo $writings->show_balance_on_current_date();
exit(0);