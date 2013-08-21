<?php
/*
	lozeil
	$Author: perrick $
	$URL:  $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

function show_favicon_file() {
	$image = "favicon.ico";
	if (@$GLOBALS['param']['layout_type'] == "grey-level") {
		$image = "favicon.grey-level.ico";
	}

	return "<link rel=\"shortcut icon\" href=\"".@$GLOBALS['config']['layout_mediaserver']."medias/images/".$image."\" />\n";
}

function show_lines($lines_data=array()) {
	$content = "";

	foreach ($lines_data as $row) {
		$tr_attribute = "";
		if (!isset($row['cells'])) {
			$temp = $row;
			$row = array('cells' => $temp);
		}
		foreach ($row as $row_attribute => $row_attribute_value) {
			if (is_string($row_attribute) && $row_attribute != 'cells') {
				$tr_attribute .= " ".$row_attribute."=\"".$row_attribute_value."\"";
			}
		}
		$content .= "<tr".$tr_attribute.">";
		foreach ($row['cells'] as $cell) {
			$cell_value = show_cell($cell);
			if ($cell_value === false) {
				return false;
			}
			$content .= $cell_value;
		}
		$content .= "</tr>";
	}

	return $content;
}

function normalize_table($table_data) {
	if (is_array($table_data)) {
		if (isset($table_data['lines']) or isset($table_data['groups'])) {
			return $table_data;
		}
		if (isset($table_data[0]) and is_array($table_data[0])) {
			return array('lines' => $table_data);
		}
	}
	
	return false;
}

function show_table($table_data = array()) {
	$content = "";
	$table_attribute = "";
	$table_data = normalize_table($table_data);

	if ($table_data) {
		if (isset($table_data['groups'])) {
			foreach ($table_data['groups'] as $group) {
				$tbody_attribute = "";
				foreach ($group as $attribute => $attribute_value) {
					if (is_string($attribute) and $attribute != "lines") {
						$tbody_attribute .= " ".$attribute."=\"".$attribute_value."\"";
					}
				}
				$content_tbody = show_lines($group['lines']);

				$content .= "<tbody".$tbody_attribute.">".$content_tbody."</tbody>";
			}
		} else {
			$content = show_lines($table_data['lines']);
		}

		foreach ($table_data as $attribute => $attribute_value) {
			if (is_string($attribute) and !in_array($attribute, array("groups", "lines"))) {
				$table_attribute .= " ".$attribute."=\"".$attribute_value."\"";
			}
		}

		$content = "<table".$table_attribute.">".$content."</table>";
	}

	return $content;
}

function show_cell($cell_data = array()) {
	$td_attribute = "";
	$type = 'td';
	$value = "";

	if (!is_array($cell_data)) {
		$value = $cell_data;
		$cell_data = array();
	}

	foreach ($cell_data as $attribute => $attribute_value) {
		if (is_array($attribute_value)) {
			return false;
		}
		if ($attribute === 'value') {
			$value = $attribute_value;
		} elseif ($attribute === 'type') {
			if ($attribute_value == 'th') {
				$type = 'th';
			}
		} elseif (is_string($attribute)) {
			$td_attribute .= " ".$attribute."=\"".$attribute_value."\"";
		}
	}
	return "<".$type.$td_attribute.">".$value."</".$type.">";
}

function show_js_files($js_files) {
	$plugins_js_files = "";

    if (is_array($js_files)) {
		foreach ($js_files as $js_file) {
			$js_file = $GLOBALS['config']['layout_mediaserver'].$js_file."?v=".urlencode($GLOBALS['config']['version']);
			$plugins_js_files .= "<script src=\"".$js_file."\" language=\"JavaScript\" type=\"text/javascript\"></script>\n";
		}
	}

	return $plugins_js_files;
}

function show_css_files($css_files) {
	$css_link = "";

    if (is_array($css_files)) {
		$media_css_file = "";
		foreach ($css_files as $css_file) {
			if (preg_match("/(print)/", $css_file)) {
				$media_css_file = " media=\"print\"";
			}
			if (substr($css_file, 0, 7) != 'http://') {
				$css_file = $GLOBALS['config']['layout_mediaserver'].$css_file;
			}
			$css_file .= "?v=".urlencode($GLOBALS['config']['version']);
			$css_link .= "<link rel=\"stylesheet\" type=\"text/css\"".$media_css_file." href=\"".$css_file."\" />\n";
			$media_css_file = "";
		}
	}

	return $css_link;
}

function show_label($label, $string) {
	$string = ($string)?$string:@$GLOBALS['txt_noname'];

	if ($label) {
		$label = "<label for=\"".$label."\">".$string."</label>";
	} else {
		$label = $string;
	}

	return $label;
}

function show_status() {
	if (isset($_SESSION['global_status']) and !empty($_SESSION['global_status'])) {
		if ($GLOBALS['param']['layout_multiplestatus']) {
			$status_shown = "<ul class=\"content_status\">";
			foreach ($_SESSION['global_status'] as $status) {
				$status_shown .= $status['value'];
			}
			$status_shown .= "</ul>";
		} else {
			$last_priority = 0;
			foreach ($_SESSION['global_status'] as $status) {
				if ($status['priority'] >= $last_priority) {
					$status_shown = "<span class=\"small\">".$status['value']."</span>";
					$last_priority = $status['priority'];
				}
			}
		}
		unset($_SESSION['global_status']);
		return $status_shown;
	} else {
		return "";
	}
}

function show_button_submit_form($name, $txt_name, $action="new", $input="") {
	$form_name = "form_".$action."_".$name;

	$button_form = "<form method=\"post\" name=\"".$form_name."\" id=\"".$form_name."\" action=\"\">";
	$button_form .= "<input type=\"hidden\" name=\"action\" value=\"".$action."\">";
	if (is_array($input)) {
		foreach ($input as $input_clef => $input_value) {
			$button_form .= "<input type=\"hidden\" name=\"".$input_clef."\" value=\"".$input_value."\">";
		}
	}
	$button_form .= "<input type=\"submit\" name=\"submit\" value=\"".$txt_name."\" />";
	$button_form .= "</form>\n";

	return $button_form;
}

function show_hidden_form($name, $txt_name, $content, $action = "new", $inputs = array()) {
	$form_name = "form_".$action."_".$name."_".$txt_name;
	$hidden_form = "<form method=\"post\" action=\"".link_content("content=".$content)."\" id=\"".$form_name."\" name=\"".$form_name."\">";
	$hidden_form .= show_input_hidden("action", $action);
	$hidden_form .= show_input_hidden($name, $txt_name);
	if (is_array($inputs)) {
		foreach ($inputs as $key_name => $value) {
			$hidden_form .= show_input_hidden($key_name, $value);
		}
	}
	$hidden_form .= "</form>";
	return $hidden_form;
}

function show_link_form($name, $txt_name, $txt_link, $content, $action="new", $input="", $value_name=0, $class="", $bracket="", $diff="", $link_extra = "") {
	if (!$content) {
		$content = $GLOBALS['content'];
		$form_action = "";
	} else {
		$form_action  = link_content("content=".$content);
	}
	$txt_form = trim($txt_link." ".$txt_name);

	if (@$GLOBALS['param']['layout_type'] == "grey-level") {
		$bracket = false;
	}
	if ($bracket == "bracket") {
		$txt_form = "[".$txt_form."]";
	}

	$diff = trim($diff);
	if ($diff) {
		$diff = "_".str_replace(" ", "", $diff);
	}
	$class = convert_attribute("class", $class);
	$form_name = "form_".$action."_".$name."_".$value_name.$diff;
	$new_form = "<form method=\"post\" action=\"".$form_action."\" name=\"".$form_name."\" id=\"".$form_name."\">";
	$new_form .= "<input type=\"hidden\" name=\"action\" value=\"".$action."\" />";
	$new_form .= "<input type=\"hidden\" name=\"".$name."_encours\" value=\"".$value_name."\" />";
	if (is_array($input)) {
		foreach ($input as $input_clef => $input_value) {
			$new_form .= "<input type=\"hidden\" name=\"".$input_clef."\" value=\"".$input_value."\" />";
		}
	}
	$new_form .= "<a".$class." href=\"".link_content("content=".$content)."\" onclick=\"javascript: document.".$form_name.".submit(); return false;\"".$link_extra.">".$txt_form."</a>";
	$new_form .= "</form>\n";

	return $new_form;
}

function show_select($name, $value, $arraylist, $extra="", $empty = false) {
    $select = "<select id=\"".$name."\" name=\"".$name."\"".$extra.">";
	if ($empty) {
		$select .= "<option value=\"\">--</option>";
	}
	foreach($arraylist as $option_clef => $option_valeur) {
		$option_selected = "";
		if ($option_clef == $value) {
			$option_selected = " selected=\"selected\"";
		}
		$select .= "<option value=\"".$option_clef."\"".$option_selected.">".$option_valeur."</option>";
		unset($option_selected);
	}
	$select .= "</select>";

	return $select;
}

function show_input_hidden($name, $value, $id = "") {
	return show_input($name, $value, "", "hidden", "", "", $id);
}

function show_textarea($name, $value, $size="", $type="soft", $extra="", $class="") {
	if ($name) {
		$class = convert_attribute("class", $class);
		$type = convert_attribute("wrap", $type);
		if (is_array($size)) {
			$size = " cols=\"".(int)$size['cols']."\" rows=\"".(int)$size['rows']."\"";
		} else {
			$size = "";
		}
		$input = "<textarea".$type." id=\"".$name."\" name=\"".$name."\"".$size.$class.$extra.">".$value."</textarea>";
	} else {
		$input = $value;
	}

	return $input;
}

function show_input_submit($name, $value, $extra="", $class="", $id="") {
	return show_input($name, $value, "", "submit", $extra, $class, $id);
}

function show_input($name, $value, $size="", $type="text", $extra="", $class="", $id="") {
	if ($name) {
		$class = convert_attribute("class", $class);
		$type = convert_attribute("type", $type);
		if (is_numeric($size)) {
			$size = " size=\"".(int)$size."\"";
		} else {
			$size = "";
		}
		if ($id == "") {
			$id = $name;
		}
		$input = "<input".$type." id=\"".$id."\" name=\"".$name."\" value=\"".$value."\"".$size.$class.$extra." />";
	} else {
		$input = $value;
	}

	return $input;
}

function currency_format($amount, $locale = null) {
	return Format::currency_amount($amount, $locale);
}
