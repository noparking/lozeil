<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

switch ($_REQUEST['action']) {
	case "edit":
		$writingssimulation = new Writings_Simulation();
		$writingssimulation->load((int)$_REQUEST['id']);
		echo $writingssimulation->form_in_table();
		exit(0);
		break;
	default :
		break;
}

exit(0);
