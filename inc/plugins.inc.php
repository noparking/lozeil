<?php
/*
	opentime
	$Author: perrick $
	$URL: svn://svn.noparking.net/var/repos/opentime/inc/plugins.inc.php $
	$Revision: 5765 $

	Copyright (C) No Parking 2010 - 2013
*/

class Plugins {
	static function factory() {
		$args = func_get_args();
		$class = $args[0]; 
		foreach (directories_for_plugins() as $name => $path) {
			$hooks_file = $path."/inc/hooks.inc.php";

			if (file_exists($hooks_file)) {
				require_once($hooks_file);

				$class_hook = ucfirst($name)."_Hooks";

				if (class_exists($class_hook) and method_exists($class_hook, "factory")) {
					if ($class_from_plugin = call_user_func(array($class_hook, "factory"), $class)) {
						return new $class_from_plugin(isset($args[1]) ? $args[1] : null, isset($args[2]) ? $args[2] : null, isset($args[3]) ? $args[3] : null);
					}
				}
			}
		}
		return new $class(isset($args[1]) ? $args[1] : null, isset($args[2]) ? $args[2] : null, isset($args[3]) ? $args[3] : null);
	}

	static function call_hook($method, $args) {
		$hooks = array();

		foreach (directories_for_plugins() as $name => $path) {
			$hooks_file = $path."/inc/hooks.inc.php";

			if (file_exists($hooks_file)) {
				require_once($hooks_file);

				$class = ucfirst($name)."_Hooks";

				if (class_exists($class) and method_exists($class, $method)) {
					$hooks[$name] = call_user_func(array($class, $method), $args);
				}
			}
		}

		return $hooks;
	}

	static function transform_hook($method, $args, $extra = array()) {
		foreach (directories_for_plugins() as $name => $path) {
			$hooks_file = $path."/inc/hooks.inc.php";

			if (file_exists($hooks_file)) {
				require_once($hooks_file);

				$class = ucfirst($name)."_Hooks";

				if (class_exists($class) and method_exists($class, $method)) {
					$func_args = array($args);
					if ($extra) {
						$func_args[] = $extra;
					}
					$args = call_user_func_array(array($class, $method), $func_args);
				}
			}
		}

		return $args;
	}
}
