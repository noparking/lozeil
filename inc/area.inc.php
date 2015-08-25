<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

abstract class Area {
	function __toString() {
		return $this->show();
	}
	
	abstract function show();
}
