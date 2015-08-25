<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

function is_directory_for_plugins_external() {
	if (isset($GLOBALS['config']['external_plugins']) && $GLOBALS['config']['external_plugins'] == false) {
		return false;
	} else {
		$directory = dirname(__FILE__)."/../..";
		return is_dir($directory."/lozeil") && is_dir($directory."/plugins");
	}
}

function relative_directory_for_plugins() {
	$directory = "";

	if (is_directory_for_plugins_external()) {
		$directory = "../";
	}

	return $directory;
}

function directory_for_applications() {
	return dirname(__FILE__)."/../../applications/";
}

function directories_for_applications() {
	$directories = array();
	$application_directory = directory_for_applications();
	clearstatcache();

	if (is_dir($application_directory)) {
		foreach (new directoryIterator($application_directory) as $inode) {
			if ($inode->isDir() and substr($inode->getFilename(), 0, 1) != ".") {
				$directories[$inode->getFilename()] = $inode->getPathname();
			}
		}
	}

	return $directories;
}

function directory_for_plugins() {
	$back = "/..";
	if (is_directory_for_plugins_external()) {
		$back .= "/..";
	}
	if (file_exists(dirname(__FILE__).$back."/plugins/")) {
		return realpath(dirname(__FILE__).$back."/plugins/") . "/";
	} else {
		return false;
	}
}

function directory_for_lozeil() {
	return realpath(dirname(__FILE__).'/../');
}

function directories_for_plugins() {
	static $plugin_directory = null;
	static $directories = null;
	static $time = null;

	if ($plugin_directory === null) {
		$plugin_directory = directory_for_plugins();
	}

	clearstatcache();

	if ($directories === null or ($time !== null and filemtime($plugin_directory) > $time)) {
		$directories = array();

		if (is_dir($plugin_directory)) {
			foreach (new directoryIterator($plugin_directory) as $inode) {
				if ($inode->isDir() and substr($inode->getFilename(), 0, 1) != ".") {
					$directories[$inode->getFilename()] = $inode->getPathname();
				}
			}
		}

		$time = filectime($plugin_directory);
	}

	return $directories;
}

function empty_cache_dir() {
	if (is_dir(dirname(__FILE__)."/../var")) {
		if ($handle = opendir(dirname(__FILE__)."/../var")) {
			while (false !== ($file = readdir($handle))) {
				if (preg_match("/\.cache\./", $file)) {
					unlink(dirname(__FILE__)."/../var/".$file);
				}
			}
			closedir($handle);
		}
	}
}

function cache_filename($required_files) {
	$string_files = "";
	foreach ($required_files as $required_file) {
		$string_files .= $required_file;
	}
	$string_cache = md5($string_files);
	$cache_file = dirname(__FILE__)."/../var/".$string_cache.".cache.php";

	return $cache_file;
}

function create_cache_file($required_files) {
	$cache_file = cache_filename($required_files);
	$cache_data = "<?php";

	foreach ($required_files as $required_file) {
		$cache_data .= preg_replace("/^<\?php/", "", file_get_contents($required_file));
	}

	$file = fopen($cache_file, "w");
 	fwrite($file, $cache_data);
	fclose($file);
}
