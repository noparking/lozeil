<?php
/*
	lozeil
	$Author: $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

if (isset($_SESSION)) {
	session_destroy();
}
$auth = new User_Authentication();
echo $auth->form();
if (isset($status)) {
	echo $status['value'];
}