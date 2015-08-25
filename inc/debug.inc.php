<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class debug {
	public static function dump_as_string($var) {
		ob_start();
		var_dump($var);
		$dump = ob_get_contents();
		ob_end_clean();
		return $dump;
	}

	public static function trace($label="") {
		$trace = self::get_backtrace_as_string().$label."\n";

		if (php_sapi_name() != 'cli') {
			$trace = '<pre style="text-align:left;padding:3px;margin:3px;border:solid 1px #0f0;color:#000;background-color:#efe">'.$trace.'</pre>';
		}

		echo $trace;
	}

	public static function ftrace($label="") {
		$trace = self::get_backtrace_as_string().$label."\n";

		$filename = dirname(__FILE__)."/../var/log/message.log.php";

		file_put_contents($filename, $trace, FILE_APPEND);
	}

	public static function dump_as_date() {
		$args = func_get_args();

		$dump = '';

		foreach ($args as $arg) {
			$dump .= date("[ d/m/Y ]", $arg)." ".self::dump_as_string($arg);
		}

		$dump = self::get_backtrace_as_string() . $dump."\n";

		if (php_sapi_name() != 'cli') {
			$dump = '<pre style="text-align:left;padding:3px;margin:3px;border:solid 1px #000;color:#000;background-color:#eee">' . $dump . '</pre>';
		}

		echo $dump;
	}

	public static function fdump() {
		$args = func_get_args();

		$dump = '';

		foreach ($args as $arg) {
			if (is_int($arg) and $arg > 1000000000 and $arg < 2000000000) {
				$dump .= date("[d/m/Y]", $arg)." ".self::dump_as_string($arg);
			} else {
				$dump .= self::dump_as_string($arg);
			}
		}

		$filename = dirname(__FILE__)."/../var/log/message.log.php";

		file_put_contents($filename, $dump, FILE_APPEND);
	}

	public static function dump() {
		$args = func_get_args();

		$dump = '';

		foreach ($args as $arg) {
			if (is_int($arg) and $arg > 1000000000 and $arg < 2000000000) {
				$dump .= date("[d/m/Y]", $arg)." ".self::dump_as_string($arg);
			} else {
				$dump .= self::dump_as_string($arg);
			}
		}

		$dump = self::get_backtrace_as_string() . $dump."\n";

		if (php_sapi_name() != 'cli') {
			$dump = '<pre style="text-align:left;padding:3px;margin:3px;border:solid 1px #000;color:#000;background-color:#eee">' . $dump . '</pre>';
		}

		echo $dump;
	}

	public static function halt($label="HALT") {
		$die = self::get_backtrace_as_string().$label."\n";

		if (php_sapi_name() != 'cli') {
			$die = '<pre style="text-align:left;padding:3px;margin:3px;border:solid 1px #000;color:#000;background-color:#eee">' . $die . '</pre>';
		}

		die($die);
	}

	protected static function get_backtrace_as_string() {
		$string = '';
		$level = 0;

		foreach(array_reverse(array_slice(debug_backtrace(), 1)) as $backtrace) {
			if (!isset($backtrace['file'])) {
				$backtrace['file'] = 'unknown';
			}

			if (!isset($backtrace['line'])) {
				$backtrace['line'] = 'unknown';
			}

			$string .= str_repeat('  ', $level++).'=> ' . ($backtrace['file']) . ' on line ' . $backtrace['line'] . "\n";
		}

		return $string;
	}
}
