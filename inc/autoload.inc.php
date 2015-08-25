<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

require("cache.inc.php");

class Lozeil_Autoload_File_Filter extends filterIterator {
	const suffix = '.inc.php';

	function __construct($path) {
		parent::__construct(new recursiveIteratorIterator(new RecursiveDirectoryIterator($path)));
	}

	function accept() {
		return (is_readable($this->getPathName()) and substr($this->getPathName(), - strlen(self::suffix)) === self::suffix);
	}
}

class Lozeil_Autoload {
	private static $classes_directory = null;
	private static $classes_index = null;
	static function autoload($class) {
		if (!self::include_class_from_lozeil($class)) {
			if (!self::include_class_from_plugins($class)) {
				if (!self::include_class_from_applications($class)) {
					//trigger_error("Class missing: ".$class);
					return false;
				}
			}
		}
	}

	static function register($classes_directory, $classes_index) {
		self::$classes_directory = $classes_directory;
		self::$classes_index = $classes_index;
		spl_autoload_register(array(__CLASS__, 'autoload'));
	}

	private static function include_class_from_lozeil($class) {
		$file = (self::$classes_directory . '/' . strtolower($class)).Lozeil_Autoload_File_Filter::suffix;

		if (file_exists($file)) {
			require($file);
			return true;
		} else {
			return false;
		} 
		
	}
	private static function generate_classes_index(& $classes) {
		$classes = array();

		try {
			foreach (new Lozeil_Autoload_File_Filter(self::$classes_directory) as $inode) {
				$file = file_get_contents($inode->getPathName());

				if (preg_match_all('/^(?:abstract\s+|final\s+)?class\s+(\w+)\s*/m', $file, $matches, PREG_SET_ORDER) > 0) {
					foreach ($matches as $match) {
						$classes[$match[1]] = $inode->getPathName();
					}
				}
			}

			$classes_index_directory = dirname(self::$classes_index);

			if (is_dir($classes_index_directory) and is_writable($classes_index_directory)) {
				file_put_contents(self::$classes_index, serialize($classes), LOCK_EX);
			}
		} catch (exception $exception) {
			trigger_error($exception->getMessage(), E_USER_ERROR);
		}
	}
	private static function include_class_from_plugins($class) {
		$class = strtolower($class);

		$underscore = strpos($class, '_');

		if ($underscore === false) {
			return false;
		} else {
			$plugin_name = strtolower(substr($class, 0, $underscore));

			$plugin_directories = directories_for_plugins();

			if (!isset($plugin_directories[$plugin_name])) {
				return false;
			} else {
				$file = $plugin_directories[$plugin_name] . '/inc/' . $class . Lozeil_Autoload_File_Filter::suffix;

				if (file_exists($file)) {
					require($file);
					return true;
				}
				else {
					$file = $plugin_directories[$plugin_name] . '/inc/' . substr_replace($class, '', 0, strlen(strtolower(basename($plugin_name))."_")) . Lozeil_Autoload_File_Filter::suffix;

					if (!file_exists($file)) {
						return false;
					} else {
						require($file);
						return true;
					}
				}
			}
		}
	}
	
	private static function include_class_from_applications($class) {
		$class = strtolower($class);

		$underscore = strpos($class, '_');

		if ($underscore === false) {
			return false;
		} else {
			$plugin_name = strtolower(substr($class, 0, $underscore));

			$applications_directories = directories_for_applications();

			if (!isset($applications_directories[$plugin_name])) {
				return false;
			} else {
				$file = $applications_directories[$plugin_name] . '/inc/' . $class . Lozeil_Autoload_File_Filter::suffix;

				if (file_exists($file)) {
					require($file);
					return true;
				}
				else {
					$file = $applications_directories[$plugin_name] . '/inc/' . substr_replace($class, '', 0, strlen(strtolower(basename($plugin_name))."_")) . Lozeil_Autoload_File_Filter::suffix;

					if (!file_exists($file)) {
						return false;
					} else {
						require($file);
						return true;
					}
				}
			}
		}
	}
}
