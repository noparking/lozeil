<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Html_Tag {
	static function a($url, $string = "", $properties = array()) {
		if (!$string) {
			$string = $url;
		}
		
		$attributes = "";
		foreach ($properties as $attribute => $value) {
			$attributes .= " ".$attribute."=\"".$value."\"";
		}

		if (is_url($url)) {
			$a = "<a href=\"".$url."\"".$attributes.">".$string."</a>";
		} else {
			$a = $string;
		}

		return $a;
	}
	
	static function label ($string = "" , $propertie = "" ) {
		
		return "<label id=\"".$propertie."\" name=\"".$propertie.">".$string."</label>";
	}
}
