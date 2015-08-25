<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Application {
	function boot() {
		ob_start();
		
		if(!isset($_SESSION)) {
			session_start();
		}
		
		if(!isset($_SESSION['accountant_view'])) {
			$_SESSION['accountant_view'] = 0;
		}
		
		if (!isset($_SESSION['order']['name']) or !isset($_SESSION['order']['direction'])) {
			$_SESSION['order']['name'] = 'day';
			$_SESSION['order']['direction'] = 'ASC';
		}
		
		Plugins::call_hook("boot", array());
	}
	
	function mount() {
		Plugins::call_hook("mount", array());
	}
	
	function load() {
		Plugins::call_hook("load", array());
	}
	
	function shutdown() {
		Plugins::call_hook("shutdown", array());
	}
}
