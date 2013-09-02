<?php
/*
	lozeil
	$Author: adrien $
	$URL:  $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

function clean_location($location) {
	return preg_replace("/\/(.*\/)*([a-zA-Z_]*\.php[0-9]?)(.*)/", "\\2", $location);
}

function __($string, $replacements = null) {
	if (isset($GLOBALS['__'][$string])) {
		$string = $GLOBALS['__'][$string];
	} else {
		trigger_error("Translation '".$string."' is missing.", E_USER_WARNING);
	}
	switch (true) {
		case $replacements === null:
			return $string;
		case is_array($replacements):
			return vsprintf($string, $replacements);
	}
}

function utf8_real_decode($string) {
	if (extension_loaded("mbstring")) {
		$real_decode = mb_convert_encoding($string, "ISO-8859-1", "UTF-8");
	} else {
		$real_decode = utf8_decode($string);
	}
	
	return $real_decode;
}

function utf8_ucwords($string) {
	if (extension_loaded("mbstring")) {
		$ucwords = mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
	} else {
		$ucwords = ucwords($string);
	}
	
	return $ucwords;

}

function utf8_ucfirst($string) {
	if (extension_loaded("mbstring")) {
		mb_internal_encoding("UTF-8");
		$ucfirst = mb_strtoupper(mb_substr($string, 0, 1)).mb_substr($string, 1);
	} else {
		$ucfirst = ucfirst($string);
	}
	
	return $ucfirst;
}

function utf8_strtolower($string) {
	if (extension_loaded("mbstring")) {
		mb_internal_encoding("UTF-8");
		$strtoupper = mb_strtolower($string);
	} else {
		$strtoupper = strtolower($string);
	}

	return $strtoupper;
}

function utf8_strtoupper($string) {
	if (extension_loaded("mbstring")) {
		mb_internal_encoding("UTF-8");
		$strtoupper = mb_strtoupper($string);
	} else {
		$strtoupper = strtoupper($string);
	}

	return $strtoupper;
}

function utf8_strlen($string) {
	if (extension_loaded('mbstring') === false) {
		return strlen($string);
	} else {
		mb_internal_encoding('UTF-8');
		return mb_strlen($string);
	}
}

function utf8_substr($string, $start, $length="") {
	if (extension_loaded("mbstring")) {
		mb_internal_encoding("UTF-8");
		if ($length !== "") {
			$substr = mb_substr($string, $start, $length);
		} else {
			$substr = mb_substr($string, $start);
		}
	} else {
		if ($length !== "") {
			$substr = substr($string, $start, $length);
		} else {
			$substr = substr($string, $start);
		}
	}

	return $substr;
}

function utf8_htmlentities($string) {
	return htmlentities($string, ENT_COMPAT, "UTF-8");
}

function utf8_urlencode($text) {
	return urlencode(utf8_decode($text));
}

function utf8_urldecode($text) {
	return urldecode(utf8_encode($text));
}

function determine_operation($vars) {
	if (is_array($vars)) {
		foreach ($vars as $operation) {
			if (!empty($operation)) {
				return $operation;
			}
		}
		return "";
	} else {
		return $vars;
	}
}

function get_error_log($start="", $stop="") {
	if ($stop == "") {
		$stop = time();
	}
	$file_error = dirname(__FILE__)."/../var/log/error.log.php";

	if (is_file($file_error) and is_readable($file_error)) {
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
	//$message = Plugins::transform_hook("error_status", $message);
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

			if (is_file($file_error) and is_writable($file_error)) {
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

function link_content($parameters="") {
	$link_content = "";
	if (isset($GLOBALS['config']['link_handling']) and $GLOBALS['config']['link_handling']) {
		$link_content .= $GLOBALS['config']['name'];
		if ($parameters) {
			$link_content .= "&".$parameters;
		}
	} elseif (isset($GLOBALS['location'])) {
		$link_content .= $GLOBALS['location'];
		if ($parameters) {
			$link_content .= "?".$parameters;
		}
	} else {
		$link_content = $_SERVER['SCRIPT_NAME']."?".$parameters;
	}

	return $link_content;
}

function determine_integer_from_post_get_session() {
	$keys = func_get_args();
	$integer = array_shift($keys);

	if ($integer > 0) {
		return $integer;
	}

	$variables = array("_POST", "_GET", "_SESSION");

	foreach ($variables as $variable) {
		foreach ($keys as $key) {
			if (isset($GLOBALS[$variable][$key]) and (!is_numeric($GLOBALS[$variable][$key]) or $GLOBALS[$variable][$key] > 0)) {
				return (int)$GLOBALS[$variable][$key];
			}
		}
	}

	return 0;
}

function is_url($url) {
	if (isset($GLOBALS['location'])) {
		return (preg_match("/^[#|http|".$GLOBALS['location']."]/", $url));
	} else {
		return (preg_match("/^[#|http]/", $url));
	}
}

function determine_month($timestamp) {
	$starttime = mktime(0, 0, 0, date("m", $timestamp), 1, date("Y",$timestamp));
	$stoptime = mktime(23, 59, 59, date("m", $timestamp) + 1 , 0, date("Y", $timestamp));

	return array($starttime, $stoptime);
}

function determine_first_day_of_month($timestamp) {
	$starttime = mktime(0, 0, 0, date("m", $timestamp), 1, date("Y",$timestamp));

	return $starttime;
}

function determine_first_day_of_next_month($timestamp) {
	$starttime = mktime(0, 0, 0, date("m", $timestamp) + 1, 1, date("Y",$timestamp));

	return $starttime;
}

function get_time($format,$act_time="") {
	if (!$act_time or $act_time == "") {
		$act_time = time();
	}
	if (!$format or $format == "") {
		return date("d.m.Y, H:i:s", $act_time);
	} else {
		return date($format, $act_time);
	}
}

function log_status($status) {
	Message::log($status);
	return true;
}