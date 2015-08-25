<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Working_Area extends Area {
	public $data;

	function __construct($data = "") {
		$this->data = $data;
	}

	function show() {
		return "<div class=\"content_working\">".$this->data."</div>";
	}
}