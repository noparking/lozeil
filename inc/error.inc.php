<?php
/*
	lozeil
	$Author: perrick $
	$URL:  $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

function get_error_log($start="", $stop="") {
	if ($stop == "") {
		$stop = time();
	}
	$file_error = dirname(__FILE__)."/../var/log/error.log.php";

	if (is_file($file_error) && is_readable($file_error)) {
		$all_error = array();
		$content = $premier = file($file_error);
		for ($i=0; $i<sizeof($content); $i++) {
			$day   = (int)substr($content[$i], 1, 2);
			$month = (int)substr($content[$i], 4, 2);
			$year  = (int)substr($content[$i], 7, 4);
			$hour  = (int)substr($content[$i], 12, 2);
			$mn    = (int)substr($content[$i], 15, 2);
			$date  = mktime($hour, $mn, 0, $month, $day, $year);
			if ($date > $start and $date < $stop) {
				$all_error[] = array("date" => substr($content[$i], 1, 16), "error" => substr($content[$i], 19, strlen($content[$i])-13));
			}
		}
		return $all_error;
	} else {
		return false;
	}
}

function status($record, $value, $result) {
	if ($result > 0) {
		success_status($record." -> ".$value);
	} else {
		error_status($record." -> ".$value);
	}
}

function error_status($message, $priority = 0) {
	if (!isset($_SESSION['global_status'])) {
		$_SESSION['global_status'] = array();
	}
	$message = Plugins::transform_hook("error_status", $message);
	if ($GLOBALS['param']['layout_multiplestatus']) {
		$_SESSION['global_status'][] = array(
			'value' => "<li class=\"content_error_status\">".$message."</li>",
			'priority' => $priority,
		);
	} else {
		$_SESSION['global_status'][] = array(
			'value' => "<div class=\"content_error_status\"><span><ul><li>".$message."</li></ul></span></div>",
			'priority' => $priority,
		);
	}
	return $_SESSION['global_status'];
}

function success_status($message, $priority = 0) {
	if (!isset($_SESSION['global_status'])) {
		$_SESSION['global_status'] = array();
	}
	//$message = Plugins::transform_hook("success_status", $message);
	$_SESSION['global_status'][] = array(
			'value' => "<div class=\"content_success_status\"><span><ul><li>".$message."</li></ul></span></div>",
			'priority' => $priority
		);
	return $_SESSION['global_status'];
}

function error_handling($type, $msg, $file, $line, $args) {
	if (!isset($args['content'])) {
		$args['content'] = "";
	}

	if (!isset($args['query'])) {
		$args['query'] = "";
	}

	switch($type) {
		case E_NOTICE:
		case E_STRICT:
			break;

		default:
			$message = "[".date("d/m/Y H:i", time())."]\t".$msg." (error type ".$type .") (file ".$file.") (line : ".$line.") (content : ".$args['content'].") (query : ".$args['query'].")";

			$file_error = dirname(__FILE__)."/../var/log/error.log.php";

			if (!file_exists($file_error)) {
				touch($file_error);
			}

			if (is_file($file_error) && is_writable($file_error)) {
				error_log($message."\n", 3, $file_error);
			} else {
				error_log($message);
			}
			break;
	}

	if ($type == E_USER_ERROR) {
		die($msg);
	}
}
