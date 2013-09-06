<?php
/*
	opentime
	$Author: manon.polle $
	$URL: svn://svn.noparking.net/var/repos/opentime/inc/html_textarea.inc.php $
	$Revision: 5452 $

	Copyright (C) No Parking 2011 - 2011
*/

class Html_Textarea {
	public $name = '';
	public $id = '';
	public $value = '';
	public $properties = array();

	function __construct($name, $value="") {
		$this->name = $name;
		$this->value = $value;

		$this->id = $this->name;
	}
	
	function __set($name, $value) {
		$this->properties[$name] = $value;
	}
	
	function tip($string) {
		$tip = "";

		if (isset($this->properties['tip']) and $this->id) {
			$tip = "<div id=\"tip_".$this->id."\"".convert_attribute("class", "tip").">".Format::name($string)."</div>";
		}

		return $tip;

	}

	function label($string) {
		$label = "";

		if ($this->id) {
			$label = "<label for=\"".$this->id."\">".$string."</label>";
		}

		return $label;
	}

	function input() {
		$extra = "";
		if (isset($this->properties['tip'])) {
			$this->properties['onFocus'] = "show_obj('tip_".$this->id."');";
			$this->properties['onBlur'] = "hide_obj('tip_".$this->id."');";
		}
		foreach ($this->properties as $property => $value) {
			if (!in_array($property, array("tip", "inside"))) {
				$extra .= " ".$property."=\"".$value."\"";
			}
		}

		$html = "<textarea id=\"".$this->id."\" name=\"".$this->name."\"".$extra.">".$this->value."</textarea>";

		return $html;
	}

	function alert($errors = array()) {
		if (is_array($errors) and count($errors) > 0) {
			$html = "";
			foreach ($errors as $error) {
				$html .= "<li>".$error."</li>";
			}
			return "<ul class=\"alert\">".$html."</ul>";
		} else {
			return "";
		}
	}

	function paragraph($label) {
		return "<p>".$this->label($label).$this->input()."</p>";
	}

	function item($label, $display = "", $complement = "") {
		$inside = "";
		if (isset($this->properties['inside'])) {
			$inside = $this->properties['inside'];
		}
		$html = $this->label($label);
		if (empty($display) or !empty($this->value)) {
			$html .= "<div class=\"hidden_field\">".$this->input().$inside."</div>";
		} else {
			$html .= "<span class=\"txt_field_empty\">".$display."</span>";
			$html .= "<div class=\"hidden_field field_empty\">".$this->input().$inside."</div>"; 
		}
		if (!empty($complement)) {
			$html .= "<div class=\"field_complement\">".$complement."</div>";
		}
				
		return $html;
	}
}
