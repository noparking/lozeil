<?php
/*
	lozeil
	$Author: $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

class Message {
	static function file() {
		$date = new DateTime();
		$filedir = dirname(__FILE__)."/../var/log/".$date->format("Y")."/".$date->format("m")."/";
		$filename = $date->format("d")."_message.log";
		if (!file_exists($filedir)) {
			mkdir($filedir, 0755, true);
		}
		if (!file_exists($filedir.$filename)) {
			touch($filedir.$filename);
		}
		return $filedir.$filename;
	}

	static function log($message) {
		if (isset($GLOBALS['config']['message_log']) && $GLOBALS['config']['message_log']) {
			if (!isset($GLOBALS['content'])) {
				$GLOBALS['content'] = "login.php";
			}
			if (!isset($_SESSION['username'])) {
				$_SESSION['username'] = "";
			}
			$message = "[".date("d/m/Y H:i", time())."]\t".$message." (content : ".$GLOBALS['content'].") (user : ".$_SESSION['username'].")\n";
			$file = Message::file();

			if (is_file($file) and is_writable($file)) {
				return (bool)file_put_contents($file, $message, FILE_APPEND);
			} else {
				return false;
			}
		}

		return false;
	}

	function clear() {
		$file = Message::file();

		if (is_file($file) and is_writable($file)) {
			return false !== file_put_contents($file, "");
		} else {
			return false;
		}
	}

	function content() {
		return nl2br(file_get_contents(Message::file()));
	}
}
