<?php
/*
	lozeil
	$Author: $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

class Html_Select {
	public $id;
	public $options = array();
	public $selected;
	public $properties = array();

	function __construct($id, $options, $selected = "") {
		$this->id = $id;
		$this->options = $options;
		$this->selected = $selected;
	}

	function tip($string) {
		$tip = "";

		if (isset($this->properties['tip']) and $this->properties['tip'] and $this->id) {
			$tip = "<div id=\"tip_".$this->id."\"".convert_attribute("class", "tip").">".Format::name(utf8_htmlentities($string))."</div>";
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

	function selectbox() {
		$extra = "";
		foreach ($this->properties as $property_name => $property_value) {
			if ($property_name == "tip") {
				$extra .= " onFocus=\"show_obj('tip_".$this->id."');\" onBlur=\"hide_obj('tip_".$this->id."');\"";
			} elseif (!in_array($property_name, array("inside"))) {
				$extra .= " ".$property_name."=\"".$property_value."\"";
				
			}
		}
		$html = "<select id=\"".$this->id."\" name=\"".$this->id."\"".$extra.">\n";
		foreach ($this->options as $option_key => $option_value) {
			$selected = "";
			if ($option_key == $this->selected) {
				$selected = " selected=\"selected\"";
			}
			$html .= "<option value=\"".$option_key."\"".$selected.">".$option_value."</option>\n";
		}
		$html .= "</select>\n";

		return $html;
	}

	function pickable() {
		return "<img class=\"hand maskable\" data-target=\"picker_".$this->id."\" src=\"".$GLOBALS['config']['layout_mediaserver']."medias/images/link_more.gif\" />";
	}

	function picker($groups = array()) {
		$picker = "";
		foreach ($groups as $key => $group) {
			$picker .= $group['name'].":&nbsp;";
			if (isset($group['values']) and is_array($group['values'])) {
				foreach ($group['values'] as $name => $ids) {
					$picker .= "<a class=\"pickable\" data-values=\"[".implode(",", $ids)."]\" data-from=\"".$this->id."1\" data-target=\"".$this->id."\" href=\"#\">".$name."</a> &nbsp; ";
				}
			}
			$picker .= "<br />";
		}
		if (!empty($picker)) {
			$picker = "<br /><div id=\"picker_".$this->id."\" class=\"mask picker\">".$picker;
			$picker .= "<p class=\"right\"><a data-target=\"picker_".$this->id."\" class=\"maskable\">".@__('mask')."</a></p>\n";
			$picker .= "</div>";
		}

		return $picker;
	}

	function multiplecombobox() {
		$onfocus1 = "";
		$onfocus = "";
		if (isset($this->properties['tip']) and $this->properties['tip']) {
			$onfocus1 = " onFocus=\"show_obj('tip_".$this->id."');\" onBlur=\"hide_obj('tip_".$this->id."');\"";
			$onfocus = " onFocus=\"show_obj('tip_".$this->id."');\" onBlur=\"hide_obj('tip_".$this->id."');\"";
		}

		$option1 = "<select class=\"multiplecombobox multiplecombobox-from\" id=\"".$this->id."1\" name=\"".$this->id."1[]\" data-target=\"".$this->id."\" multiple=\"multiple\" size=\"7\"".$onfocus1.">";
		$option = "<select class=\"multiplecombobox multiplecombobox-to\" id=\"".$this->id."\" name=\"".$this->id."[]\" data-target=\"".$this->id."1\" multiple=\"multiple\" size=\"7\"".$onfocus.">";

		foreach ($this->options as $option_key => $option_value) {
			if (is_array($this->selected)) {
				if (in_array($option_key, $this->selected)) {
						$option .= "<option value=\"".$option_key."\">".$option_value."</option>";
				} else {
					$option1 .=  "<option value=\"".$option_key."\">".$option_value."</option>";
				}
			} else {
				$option1 .= "<option value=\"".$option_key."\">".$option_value."</option>";
			}
		}
		if (!preg_match("/<\/option>$/", $option)) {
			$option .= "<option value=\"--\">--</option>";
		}
		if (!preg_match("/<\/option>$/", $option1)) {
			$option1 .= "<option value=\"--\">--</option>";
		}

		$option1 .= "</select>";
		$option .= "</select>";

		$html = "<table><tr><td>";
		$html .= $option1;
		$html .= "</td>";
		if (isset($this->properties['align']) and $this->properties['align'] == "vertical") {
			$html .= "</tr></tr>";
		}
		$html .= "<td align=\"center\" valign=\"middle\">";
		$html .= "<input type=\"button\" class=\"multiplecombobox_button\" data-from=\"".$this->id."\" data-target=\"".$this->id."1\" value=\"&lt;&lt;\" />";
		$html .= "<input type=\"button\" class=\"multiplecombobox_button\" data-from=\"".$this->id."1\" data-target=\"".$this->id."\" value=\"&gt;&gt;\" /><br />";
		$html .= "<a class=\"shortcut multiplecombobox_all\" data-from=\"".$this->id."1\" data-target=\"".$this->id."\">".@$GLOBALS['txt_addall']."</a>";

		$html .= "</td>";
		if (isset($this->properties['align']) and $this->properties['align'] == "vertical") {
			$html .= "</tr></tr>";
		}
		$html .= "<td>";
		$html .= $option;
		$html .= "</td></tr></table>";

		return $html;
	}

	function paragraph($label, $complement = "") {
		return "<p>".$this->label($label).$this->selectbox().$complement."</p>";
	}
	
	function item($label, $display = "", $complement = "") {
		$inside = "";
		if (isset($this->properties['inside'])) {
			$inside = $this->properties['inside'];
		}
		$html = $this->label($label);
		if (empty($display) or !$this->is_selected_empty()) {
			$html .= "<div class=\"hidden_field\">".$this->selectbox().$inside."</div>";
		} else {
			$html .= "<span class=\"txt_field_empty\">".$display."</span>";
			$html .= "<div class=\"hidden_field field_empty\">".$this->selectbox().$inside."</div>"; 
		}
		if (!empty($complement)) {
			$html .= "<div class=\"field_complement\">".$complement."</div>";
		}
				
		return $html;
	}
	
	function item_multiplecombobox($label, $display = "", $complement = "") {
		$html = $this->label($label);
		if (empty($display) or !$this->is_selected_empty()) {
			$html .= "<div class=\"hidden_field\">".$this->multiplecombobox()."</div>";
		} else {
			$html .= "<span class=\"txt_field_empty\">".$display."</span>";
			$html .= "<div class=\"hidden_field field_empty\">".$this->multiplecombobox()."</div>"; 
		}
		if (!empty($complement)) {
			$html .= "<div class=\"field_complement\">".$complement."</div>";
		}
				
		return $html;
	}
	
	function is_selected_empty() {
		if (!is_array($this->selected) and is_array($this->options) and isset($this->options[$this->selected])) {
			return false;
		}
		
		if (empty($this->selected)) {
			return true;
		}
		
		if (is_array($this->selected) and count($this->selected) == 1) {
			if (in_array("--", $this->selected)) {
				return true;
			}
		}
		
		return false;
	}
}
