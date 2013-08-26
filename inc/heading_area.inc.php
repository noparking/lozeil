<?php
/*
	lozeil
	$Author:  $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

class Heading_Area {
	public $title;
	public $extra;

	function __construct($title = "", $extra = "") {
		$this->title = $title;
		$this->extra = $extra;
	}

	function show() {
		$content = "<div class=\"heading\">";
		if (!empty($this->title)) {
			$content .= "<h2>".$this->title."</h2>";
		}
		if (!empty($this->extra)) {
			$content .= "<div class=\"extra\">".$this->extra."</div>";
		}
		$content .= "</div>";
		return $content;
	}
}