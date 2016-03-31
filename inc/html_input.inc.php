<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Html_Input {
	public $name = "";
	public $id = "";
	public $value = "";
	public $type = "";
	public $properties = array();

	function __construct($name, $value="", $type="text") {
		$this->name = $name;
		$this->value = $value;
		$this->type = $type;

		$this->id = $this->name;
	}

	function __set($name, $value) {
		$this->properties[$name] = $value;
	}

	function __get($name) {
		return isset($this->properties[$name]) ? $this->properties[$name] : null;
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

	function autocomplete($url) {
		$html = $this->input();

		$hidden_field = new Html_Input("", $url, "hidden");
		$hidden_field->id = $this->id."-autocomplete";
		$hidden_field->properties['class'] = "autocomplete";
		$hidden_field->properties['disabled'] = "disabled";
		$html .= $hidden_field->input();

		return $html;
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

		$html = "<input id=\"".$this->id."\" name=\"".$this->name."\"".
		" value=\"".$this->value."\"".
		" type=\"".$this->type."\"".
		$extra." />";
		
		if (isset($this->properties['inside'])) {
			$html .= $this->properties['inside'];
		}

		return $html;
	}

	function input_hidden() {
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

		$html = "<input id=\"".$this->id."\" name=\"".$this->name."\"".
		" value=\"".$this->value."\"".
		" type=\"hidden\"".
		$extra." />";

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

	function paragraph($label, $complement = "") {
		return "<p>".$this->label($label).$this->input().$complement."</p>";
	}
	
	function item_shown($label, $display = "", $complement = "") {
		return $this->label($label)."<span>".$this->value."</span>".$complement;
	}

	function item($label, $display = "", $complement = "") {
		$html = $this->label($label);
		if (empty($display) or !empty($this->value)) {
			$html .= "<div class=\"hidden_field\">".$this->input()."</div>";
		} else {
			$html .= "<span class=\"txt_field_empty\">".$display."</span>";
			$html .= "<div class=\"hidden_field field_empty\">".$this->input()."</div>"; 
		}
		if (!empty($complement)) {
			$html .= "<div class=\"field_complement\">".$complement."</div>";
		}
				
		return $html;
	}
}
