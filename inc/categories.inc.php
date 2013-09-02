<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

class Categories extends Collector  {
	public $filters = null;
	
	function __construct($class = null, $table = null, $db = null) {
		$class = "Category";
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_categories'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}
	
	function names() {
		$names = array();
		$names[0] = "--";
		foreach ($this as $category) {
			$names[$category->id] = $category->name();
		}
		return $names;
	}
}
