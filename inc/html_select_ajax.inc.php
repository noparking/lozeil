<?php
/*
	opentime
	$Author$
	$URL$
	$Revision$

	Copyright (C) No Parking 2010 - 2011
*/

class Html_Select_Ajax {
	public $id;
	public $url = "";
	public $elements = array();
	public $properties = array();

	function __construct($id, $url, $elements = array()) {
		$this->id = $id;
		$this->url = $url;
		$this->elements = $elements;
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
			$label = "<label for=\"".$this->id."\">".Format::name($string)."</label>";
		}

		return $label;
	}
	
	function input() {
		$search = new Html_Input(md5($this->id), "");
		$search->properties = array(
			'class' => "select-ajax",
			'autocomplete' => "off",
			'data-url' => $this->url,
			'data-name' => $this->id."[]",
			'data-format' => isset($this->properties['format']) ? $this->properties['format'] : "name",
		);

		if (isset($this->properties['size'])) {
			$search->size = $this->size;
		}
		
		$html = "<div class=\"select-ajax-content\">";
		$html .= $search->input();
		$html .= "<div class=\"select-ajax-dynamic\" id=\"".md5($this->id)."-dynamic\"></div>";
		$html .= "<div class=\"select-ajax-static\" id=\"".md5($this->id)."-static\">";
		foreach ($this->elements as $id => $value) {
			$element = new Html_Checkbox($this->id."[]", $id, true);
			$html .= "<div>".$element->input().$value."</div>";
		}
		$html .= "</div>";
		$html .= "</div>";
		
		return $html;
	}

	function paragraph($label, $complement = "") {
		return "<p>".$this->label($label).$this->selectbox().$complement."</p>";
	}

	function item($label, $display = "", $complement = "") {
		$html = $this->label($label);
		if (empty($display) or !empty($this->elements)) {
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
