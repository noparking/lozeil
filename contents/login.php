<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

if (isset($_SESSION)) {
	session_destroy();
}
$auth = new User_Authentication();

$working = "<center><div id=\"form_login\">".$auth->form()."</div></center>";
$area = new Working_Area($working);
echo $area->show();
