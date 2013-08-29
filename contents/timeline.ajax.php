<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

$writings = new Writings();
$writings->filter_with(array('stop' => strtotime("+1 year", $_SESSION['timestamp'])));
$writings->select();
echo $writings->show_timeline_at($_SESSION['timestamp']);
exit(0);