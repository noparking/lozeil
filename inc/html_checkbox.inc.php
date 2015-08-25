<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Html_Checkbox {
	public $name;
	public $id;
	public $value;
	public $selected;
	public $properties = array();

	function __construct($name, $value, $selected = false) {
		$this->name = $name;
		$this->value = $value;
		$this->selected = $selected;

		$this->id = $name;
	}

	function tip($string) {
		$tip = "";

		if (isset($this->properties['tip']) and $this->properties['tip'] and $this->id) {
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

	function paragraph($label, $complement = "", $label_side = "left") {
		if ($label_side == "left") {
			return "<p>".$this->label($label).$this->input().$complement."</p>";
		} else {
			return "<p>".$this->input().$this->label($label).$complement."</p>";
		}
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
	
	function input() {
		$extra = "";
		if ($this->selected) {
			$extra .= " checked=\"checked\"";
		}
		if (isset($this->properties['tip'])) {
			$this->properties['onFocus'] = "show_obj('tip_".$this->id."');";
			$this->properties['onBlur'] = "hide_obj('tip_".$this->id."');";
		}
		foreach ($this->properties as $property => $value) {
			if ($property != "tip") {
				$extra .= " ".$property."=\"".$value."\"";
			}
		}

		$html = "<input id=\"".$this->id."\" name=\"".$this->name."\"".
		" value=\"".$this->value."\"".
		" type=\"checkbox\"".
		$extra.
		" />";

		return $html;
	}
	
	function input_readonly() {
		$extra = "";
		if ($this->selected) {
			$extra .= " checked=\"checked\"";
		}
		if (isset($this->properties['tip'])) {
			$this->properties['onFocus'] = "show_obj('tip_".$this->id."');";
			$this->properties['onBlur'] = "hide_obj('tip_".$this->id."');";
		}
		foreach ($this->properties as $property => $value) {
			if ($property != "tip") {
				$extra .= " ".$property."=\"".$value."\"";
			}
		}

		$html = "<input id=\"".$this->id."\" name=\"".$this->name."\"".
		" value=\"".$this->value."\"".
		" type=\"checkbox\"".
		" disabled=\"disabled\"".
		$extra.
		" />";

		return $html;
	}

	function item($label, $display = "", $complement = "") {
		$html = $this->label($label);
		if (empty($display) or !empty($this->selected)) {
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