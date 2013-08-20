<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/


require dirname(__FILE__)."/../inc/require.inc.php";

if (isset($argv)) {
	$args = new arrayIterator(array_slice($argv, 1));
} else {
	$args = new arrayIterator(array_keys($_GET));
}

$bot = new Bot();

foreach ($args as $arg) {
	$arg = str_replace("-", "", $arg);
	if (method_exists($bot, $arg)) {
		$return = $bot->$arg();
		if ($return === true or $return === false) {
			return $return;
		} else {
			echo $return;	
		}
	} else {
		echo $bot->help();
	}
}

