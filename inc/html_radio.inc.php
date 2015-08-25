<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Html_Radio {
	public $id;
	public $name;
	public $options = array();
	public $selected;
	public $properties = array();

	function __construct($id, $options, $selected="") {
		$this->id = $id;
		$this->name = $id;
		$this->options = $options;
		$this->selected = $selected;
	}

	function tip($string) {
		$tip = "";

		if ($this->properties['tip'] and $this->id) {
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
		$html = "";
		$extra = "";

		if (isset($this->properties['tip']) and $this->properties['tip']) {
			$extra .= " onFocus=\"show_obj('tip_".$this->id."');\" onBlur=\"hide_obj('tip_".$this->id."');\"";
		}
		foreach ($this->options as $key => $value) {
			$checked = "";
			if ($this->selected == $key) {
				$checked = " checked=\"checked\"";
			}
			if (!isset($this->properties['front']) or $this->properties['front'] == "value") {
				$html .= $value." ";
			}
			if (isset($this->properties['disabled'])) {
				$extra .= "disabled=\"disabled\"";
			}
			$html .= "<input id=\"".$this->id."\" name=\"".$this->name."\"".
			" value=\"".$key."\"".
			" type=\"radio\"".
			$checked.
			$extra.
			" />";
			if (isset($this->properties['front']) and $this->properties['front'] != "value") {
				$html .= " ".$value;
			}
			if (isset($this->properties['separator'])) {
				$html .= $this->properties['separator'];
			}
		}
		if (isset($this->properties['separator'])) {
			$html = substr($html, 0, strlen($html) - strlen($this->properties['separator']));
		}
		return $html;
	}
	
	function item($label, $complement = "") {
		return $this->label($label).$this->input().$complement;
	}

	function paragraph($label, $complement = "") {
		return "<p>".$this->label($label).$this->input().$complement."</p>";
	}
}