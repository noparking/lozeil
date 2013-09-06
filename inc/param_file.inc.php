<?php
/*
	lozeil
	$Author:  $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

class Param_File extends Config_File {
	function __construct($path) {
		parent::__construct($path, "param");
	}

	function update($values) {
		$return = true;
		return $return && parent::update($values);
	}
}