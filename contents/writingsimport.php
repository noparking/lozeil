<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

if ($_POST['bank_id'] > 0 && isset($_FILES) && $_FILES['input_file']['type'] == "text/csv" && $_FILES['input_file']['error'] == 0) {
	$data = new Writings_Data_File($_FILES['input_file']['tmp_name'], $_POST['bank_id']);
	$data->import();
}
header("Location: ".link_content("content=lines.php"));
exit;