<?php
/* Lozeil -- Copyright (C) No Parking 2016 - 2016 */

class Theme {
	static function factory($name) {
		switch ($name) {
			case "ajax":
				$theme = new Theme_Ajax();
				break;
			default:
				$theme = new Theme_Default();
				break;
		}
		return $theme;
	}
}
