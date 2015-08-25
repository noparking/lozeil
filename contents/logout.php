<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

if (isset($_SESSION['previous_location'])) {
	session_destroy();
	header("Location: ".$_SESSION['previous_location']);
}
else {
	session_destroy();
	header ("Location: ".link_content("content=login.php"));
}
exit(0);