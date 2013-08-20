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

function compareSPLFileInfo($splFileInfo1, $splFileInfo2) {
    return strcmp($splFileInfo1->getFileName(), $splFileInfo2->getFileName());
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
