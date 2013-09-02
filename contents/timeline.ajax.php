<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

$writings = new Writings();
echo $writings->show_timeline_at($_SESSION['timestamp']);
exit(0);