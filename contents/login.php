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
$html = $auth->form();

if (isset($_SESSION['global_status'][0])) {
	$html.= $_SESSION['global_status'][0]['value'];
}

echo "<div id=\"form_login\">".$html."</div>";