<?php
/* Lozeil -- Copyright (C) No Parking 2016 - 2016 */

if (isset($_REQUEST['action'])) {
	switch ($_REQUEST['action']) {
		case "preview_changes_split" :
			$writing = new Writing();
			$writing->load(array('id' => (int)$_REQUEST['id']));
			echo $writing->preview_split($_REQUEST['form']);
			break;
		case "preview_changes_duplicate" :
			$writing = new Writing();
			$writing->load(array('id' => (int)$_REQUEST['id']));
			echo $writing->preview_duplicate($_REQUEST['value']);
			exit(0);
			break;
		case "preview_changes_forward" :
			$writing = new Writing();
			$writing->load(array('id' => (int)$_REQUEST['id']));
			echo $writing->preview_forward($_REQUEST['value']);
			exit(0);
			break;
	}
}
