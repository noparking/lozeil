<?php
/*
	lozeil
	$Author: perrick $
	$URL:  $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

function show_triple_select($block_id, $tree, $input_first, $input_second, $input_third, $input_last) {
	$html = '<script language="JavaScript" type="text/javascript">';
	$html .= 'function get_tree_'.$block_id.'() {';
	$html .= '  var tree = '.phpvariable_2_json($tree).';';
	$html .= '  return tree;';
	$html .= '}';
	$html .= '</script>';

	$visibility_2 = 'hidden';
	$display_2 = 'none';
	$visibility_3 = 'hidden';
	$display_3 = 'none';


	foreach ($tree as $key => $leaf) {
		if(isset($leaf['selected']) and $leaf['selected'] == "selected") {
			$branch[] = $key;
			$visibility_2 = 'visible';
			$display_2 = 'inline';
		}
		foreach ($leaf['children'] as $sub_key => $sub_leaf) {
			if (isset($sub_leaf['selected']) and $sub_leaf['selected'] == "selected") {
				$branch[] = $key;
				$branch[] = $sub_key;
				$visibility_2 = 'visible';
				$display_2 = 'inline';
				$visibility_3 = 'visible';
				$display_3 = 'inline';
			}
			if (isset($sub_leaf['children']) and is_array($sub_leaf['children'])) {
				foreach ($sub_leaf['children'] as $sub_sub_key => $sub_sub_leaf) {
					if (isset($sub_sub_leaf['selected']) and $sub_sub_leaf['selected'] == "selected") {
						$branch[] = $key;
						$branch[] = $sub_key;
						$branch[] = $sub_sub_key;
						$visibility_2 = 'visible';
						$display_2 = 'inline';
						$visibility_3 = 'visible';
						$display_3 = 'inline';
					}
				}
			}
		}
	}
	if (!isset($branch)) {
		$branch = array("","");
	}
	if (!isset($branch[1])) {
		$branch[1] = "";
	}
	if (!isset($tree[$branch[0]]['children'][$branch[1]]['children'])) {
		$visibility_3 = 'hidden';
		$display_3 = 'none';
	}

	$html .= '<span name="'.$block_id.'" id="'.$block_id.'" >';
    $html .= '<select name="'.$input_first.'" id="'.$input_first.'" onchange="javascript:show_triple_select(\''.$block_id.'\', this);">';
	if (!empty($branch[0])) {
		foreach ($tree as $key => $leaf) {
			if ($key == $branch[0]) {
    			$html .= '<option selected="selected" value="'.$key.'">'.$leaf['value'].'</option>';
    		} else {
    			$html .= '<option value="'.$key.'">'.$leaf['value'].'</option>';
    		}
		}
	} else {
		foreach ($tree as $key => $leaf) {
			$html .= '<option value="'.$key.'">'.$leaf['value'].'</option>';
		}
	}
    $html .= '</select> ';

	$html .= '<select name="'.$input_second.'" id="'.$input_second.'" style="visibility : '.$visibility_2.'; display : '.$display_2.';" onchange="javascript:show_triple_select(\''.$block_id.'\', this);">';
    if (!empty($branch[0])) {
    	foreach ($tree[$branch[0]]['children'] as $sub_key => $sub_leaf) {
    		if ($sub_key == $branch[1]) {
    			$html .= '<option selected="selected" value="'.$sub_key.'">'.$sub_leaf['value'].'</option>';
    		} else {
    			$html .= '<option value="'.$sub_key.'">'.$sub_leaf['value'].'</option>';
    		}
    	}
    }
	$html .= '</select> ';

	$html .= '<select name="'.$input_third.'" id="'.$input_third.'" style="visibility : '.$visibility_3.'; display : '.$display_3.';" onchange="javascript:show_triple_select(\''.$block_id.'\', this);">';
	if (!empty($branch[1])) {
		if (isset($tree[$branch[0]]['children'][$branch[1]]['children']) and is_array($tree[$branch[0]]['children'][$branch[1]]['children'])) {
	    	foreach ($tree[$branch[0]]['children'][$branch[1]]['children'] as $sub_sub_key => $sub_sub_leaf) {
	    		if ($sub_sub_key == $branch[2]) {
	    			$html .= '<option selected="selected" value="'.$sub_sub_key.'">'.$sub_sub_leaf['value'].'</option>';
	    		} else {
	    			$html .= '<option value="'.$sub_sub_key.'">'.$sub_sub_leaf['value'].'</option>';
	    		}
	    	}
		}
    }
	$html .= '</select>';

	$html .= '<input type="hidden" name="'.$input_last.'" id="'.$input_last.'" value="';
	if (!empty($branch[2])) {
		$html .= $branch[2];
	} else {
		$html .= $branch[1];
	}
	$html .=  '" />';
	$html .= '</span>';

	return $html;
}

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

function show_alert_box() {
	trigger_error("Function 'show_alert_box' is now deprecated. Please use 'Alert_Area::show'.", E_USER_WARNING);
}

function show_img_toggle($vars_id, $link="more", $properties="") {
	if (!is_array($vars_id)) {
		$vars_id = array($vars_id);
	}

	return "<img class=\"hand maskable\" data-target=\"".join(",", $vars_id)."\" src=\"".@$GLOBALS['config']['layout_mediaserver']."medias/images/link_".$link.".gif\" ".$properties."/>";
}

function show_mask_div($id, $string) {
	return "<div id=\"".$id."\" class=\"mask\">".$string."</div>";
}

function get_js_files() {
	trigger_error("Function 'get_js_files' is deprecated since 31/05/2011, please use 'Theme_Default::js_files'.", E_USER_WARNING);
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

function get_menu_css_files() {
	trigger_error("Function 'get_menu_css_files' is deprecated since 31/05/2011, please use 'Theme_Default::css_files'.", E_USER_WARNING);
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

function show_half_divs($left, $right) {
	$divs = "<div>\n";
	$divs .= "<div class=\"half_left\">\n";
	$divs .= $left;
	$divs .= "</div>\n";
	$divs .= "<div class=\"half_right\">\n";
	$divs .= $right;
	$divs .= "</div>\n";
	$divs .= "<div class=\"spacer\"></div>\n";
	$divs .= "</div>\n";

	return $divs;
}

function show_tip_action($tip) {
	$tip_action = "";

	if ($tip) {
		$tip_action = " onFocus=\"show_obj('".$tip."');\" onBlur=\"hide_obj('".$tip."');\"";
	}

	return $tip_action;
}

function show_tip_content($tip, $string) {
	$string = ($string)?$string:@$GLOBALS['txt_noname'];

	if ($tip) {
		$tip = "<div id=\"".$tip."\"".convert_attribute("class", "tip").">".$string."</div>";
	} else {
		$tip = $string;
	}

	return $tip;
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

function show_style($style_array="") {
	if (is_array($style_array)) {
		$style = prepare_style($style_array);
		$style_html = " style=\"".trim($style)."\"";
	}

	return $style_html;
}

function prepare_style($style_array="") {
	$style = "";
	if (is_array($style_array)) {
		foreach ($style_array as $style_key => $style_val) {
			if (strpos($style_key, "color")) {
				$style_val = "#".$style_val;
			}
			$style .= $style_key." : ".$style_val."; ";
		}
		$style = trim($style);
	}

	return $style;
}

function show_input_img($link_img) {
	$input_img = "";

	if (extension_loaded("gd")) {
		$link_file = substr($link_img, 0, strpos($link_img, "&"));
		if ($link_img and file_exists(dirname(__FILE__)."/../chart/".$link_file)) {
			$link_img = link_content("chart=".$link_img."&".time());
			$input_img = "<div class=\"images\"><img src=\"".$link_img."\" alt=\"\" /></div>\n";
		}
	}

	return $input_img;
}

function get_herit_css_files() {
	$herit_css_files = array();

	if (file_exists(dirname(__FILE__)."/../medias/css/styles_color.css")) {
		$herit_css_files[] = "css/styles_color.css";
	}
	if (file_exists(dirname(__FILE__)."/../medias/css/styles_width.css")) {
		$herit_css_files[] = "css/styles_width.css";
	}

	return $herit_css_files;
}

function show_page_navigation($total, $content, $init="") {
	$control = new Form_Page_Control($content, $total);
	return $control->show($init);
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

function show_print_sheet($content, $user_id, $text, $get_params = array()) {
	$get = "";

	foreach ($get_params as $name => $value) {
		$get .= "&amp;".$name."=".$value;
	}

	$print_sheet = "<a class=\"print\" href=\"".link_content("content=".$content."&amp;view=details&amp;user_id=".$user_id.$get)."\" onclick=\"openwindow(this.href,width='600',height='500'); return false;\">".$text."</a>";

	return $print_sheet;
}

function convert_limit($init="0") {
	$init = (int)$init;

	if (!@$GLOBALS['param']['nb_records']) {
		@$GLOBALS['param']['nb_records'] = 50;
	}

	$limit = " LIMIT ".$init.", ".@$GLOBALS['param']['nb_records'];

	return $limit;
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

function square_html($name) {
	$extracted_name = extract_name($name);
	$dir = $GLOBALS['config']['layout_mediaserver'];
	
	return "<img src=\"".$dir.$name."\" width=\"14\" height=\"14\" alt=\"".$extracted_name."\" />";
}

function show_archive($archive_data) {
	$html_archive = "";
	if (is_array($archive_data)) {
		foreach($archive_data as $clef_data => $valeur_data) {
			if (is_numeric($clef_data)) {
				if (isset($passage)) {
					$html_archive .= "<br />\n";
				} else {
					$passage = 0;
				}
				$html_archive .= $valeur_data."<br />\n";
				$passage++;
			} elseif ($valeur_data) {
				$html_archive .= "<strong>".$clef_data." :</strong> ".$valeur_data."<br />\n";
			}
		}
	}

	return $html_archive;
}

function show_edit_form($name, $txt_name, $content, $input="", $value_name) {
	return show_link_form($name, $txt_name, $GLOBALS['status_modify'], $content, "do_edit", $input, $value_name);
}

function show_new_form($name, $txt_name, $content, $input="") {
	if (!$content) {
		$content = @$GLOBALS['content'];
	}

	return show_link_form($name, $txt_name, @$GLOBALS['status_createnew'], $content, "new", $input);
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

function spread_input_color($name, $value) {
	trigger_error("Function 'spread_input_color' is deprecated since 26/08/2009, please use 'Html_Input_Color::input'.", E_USER_WARNING);
}

function show_cell_original($value, $class="", $colspan="", $bgcolor="") {
	if (is_array($value)) {
		$input = prepare_cell($value);
	} else {
		if ($bgcolor) {
			$bgcolor = convert_attribute("bgcolor", $bgcolor);
		} else {
			$bgcolor = "";
		}
		$class = convert_attribute("class", $class);
		$colspan = convert_attribute("colspan", $colspan);

		$input = "<td".$bgcolor.$class.$colspan.">".$value."</td>\n";
	}

	return $input;
}

function convert_attribute($name, $value) {
	$value = trim($value);
	if ($name and $value) {
		if ($name == "bgcolor") {
			$value = "#".color_validate_hex($value);
		}
		$attribute = " ".$name."=\"".$value."\"";
	} else {
		$attribute = "";
	}

	return $attribute;
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

function show_input_date($name, $value="0", $tip="", $id="", $extra="", $help="1", $clock="0") {
	$help_input = "";
	if ($help == 1) {
		$help_input = " (".@$GLOBALS['txt_DDMMYYYY'].")";
	}
	$clock_input = "";
	if ($clock) {
		$clock_input = "<td>";
		$clock_input .= "<input id=\"".$name."hour\" type=\"text\" name=\"".$name."hour\" value=\"".(($value) ? adodb_date("H",$value) : "")."\" size=\"3\" />:";
		$clock_input .= "<input id=\"".$name."minute\" type=\"text\" name=\"".$name."minute\" value=\"".(($value) ? adodb_date("i",$value) : "")."\" size=\"3\" />";
		$clock_input .= "&nbsp;<img class=\"hand\" onclick=\"ToggleClock('".$name."clock')\" src=\"".@$GLOBALS['config']['layout_mediaserver']."medias/images/link_clock.gif\" width=\"14\" height=\"14\" />";
		$clock_input .= show_input_clock($name, $value);
		$clock_input .= "</td>";
	}
	$tip_input = "";
	if ($tip) {
		$tip_input = " onFocus=\"show_obj('".$tip."');\" onBlur=\"hide_obj('".$tip."');\"";
	}
//	if (!$id or $clock) {
		$id = $name."day";
//	}
	$id_input = " id=\"".$id."\"";

	if (!is_array($extra)) {
		$extra = array($extra, "", "");
	}
	$input = "";
	if ($clock) {
		$input .= "<table><tr><td>";
	}
	$input .= "<input".$id_input." type=\"text\" name=\"".$name."day\" value=\"".(($value) ? adodb_date("d",$value) : "")."\" size=\"3\"".$tip_input.$extra[0]." />-";
	$input .= "<input id=\"".$name."month\" type=\"text\" name=\"".$name."month\" value=\"".(($value) ? adodb_date("m",$value) : "")."\" size=\"3\"".$tip_input.$extra[1]." />-";
	$input .= "<input id=\"".$name."year\" type=\"text\" name=\"".$name."year\" value=\"".(($value) ? adodb_date("Y",$value) : "")."\" size=\"6\"".$tip_input.$extra[2]." />";
	$input .= "&nbsp;<img class=\"hand\" onclick=\"ToggleCalendar('".$name."calendar')\" src=\"".@$GLOBALS['config']['layout_mediaserver']."medias/images/link_calendar.gif\" width=\"14\" height=\"14\" />";
	$input .= show_input_calendar($name, $value);
	if ($clock) {
		$input .= "</td>";
		$input .= $clock_input;
		$input .= "</tr></table>";
	}

	return $input;
}

function show_input_clock($name, $timestamp) {
	if (@$GLOBALS['param']['calendar_picker']) {
		if ($timestamp == 0) {
			$timestamp = time();
		}

		$input_clock = "<br /><table id=\"".$name."clock\" style=\"visibility: hidden; display: none;\">\n";
		$input_clock .= "<tbody>\n";
		$input_clock .= "<tr>\n";
		$input_clock .= "<td colspan=\"7\" valign=\"center\">\n";
		$input_clock .= "<select id=\"".$name."clockhour\" onchange=\"FillClock('".$name."clock', event)\"> \n";
		$input_clock .= "<option> -- </option>\n";
		for ($i=7; $i <= 22; $i++) {
			if ($i < 10) {
				$i = "0".$i;
			}
			$selected = "";
			if ($i == adodb_date("H", $timestamp) and adodb_date("G\hi", $timestamp) != "0h00") {
				$selected = " selected=\"selected\"";
			}
			$input_clock .= "<option".$selected.">".$i."h</option>\n";
		}
		$input_clock .= "</select> \n";
		$input_clock .= "<select id=\"".$name."clockminute\" onchange=\"FillClock('".$name."clock', event);\"> \n";
		$input_clock .= "<option> -- </option>\n";
		for ($i=0; $i < 60; $i += 5) {
			if ($i < 10) {
				$i = "0".$i;
			}
			$selected = "";
			if ($i == get_minute($timestamp, "5") and adodb_date("G\hi", $timestamp) != "0h00") {
				$selected = " selected=\"selected\"";
			}
			$input_clock .= "<option".$selected.">".$i."</option>\n";
		}
		$input_clock .= "</select> \n";
		$input_clock .= "</td>\n";
		$input_clock .= "</tr>\n";
		$input_clock .= "</tbody>\n";
		$input_clock .= "<tbody>\n";
		$input_clock .= "<tr>\n";
		$input_clock .= "<td align=\"right\" colspan=\"2\"><a href=\"javascript: HideClock('".$name."clock');\">".@$GLOBALS['txt_mask']."</a></td>\n";
		$input_clock .= "</tr>\n";
		$input_clock .= "</tbody>\n";
		$input_clock .= "</table>\n";

	} else {
		$input_clock = "";
	}

	return $input_clock;
}

function show_input_calendar($name, $timestamp) {
	if (@$GLOBALS['param']['calendar_picker']) {
		if ($timestamp == 0) {
			$timestamp = time();
		}

		$input_calendar = "<br /><table id=\"".$name."calendar\" style=\"visibility: hidden; display: none;\">\n";
		$input_calendar .= "<tbody>\n";
		$input_calendar .= "<tr>\n";
		$input_calendar .= "<td colspan=\"7\" valign=\"center\" nowrap=\"nowrap\">\n";
		$input_calendar .= "<select id=\"".$name."calendarmonth\" onchange=\"MakeCalendar('".$name."calendar')\"> \n";
		for ($i=1; $i <= 12; $i++) {
			if ($i == adodb_date("m", $timestamp)) {
				$selected = " selected=\"selected\"";
			} else {
				$selected = "";
			}
			$input_calendar .= "<option".$selected.">".@$GLOBALS['array_month'][$i]."</option>\n";
		}
		$input_calendar .= "</select> \n";
		$input_calendar .= "<select id=\"".$name."calendaryear\" onchange=\"MakeCalendar('".$name."calendar')\"> \n";

		$start_year = adodb_date("Y", strtotime("-5 years"));
		$stop_year = adodb_date("Y", strtotime("+5 years"));
		for ($i = $start_year; $i < $stop_year; $i++) {
			if ($i == adodb_date("Y", $timestamp)) {
				$selected = " selected=\"selected\"";
			} else {
				$selected = "";
			}
			$input_calendar .= "<option".$selected.">".$i."</option>\n";
		}
		$input_calendar .= "</select> \n";
		$input_calendar .= "</td>\n";
		$input_calendar .= "</tr>\n";
		$input_calendar .= "<tr>\n";
		for ($i=1; $i <= 7; $i++) {
			$input_calendar .= "<td>".@$GLOBALS['array_week'][$i][0]."</td>\n";
		}
		$input_calendar .= "</tr>\n";
		$input_calendar .= "</tbody>\n";
		$input_calendar .= "<tbody class=\"hand\" id=\"".$name."calendardayList\" onclick=\"FillDate('".$name."calendar', event)\" valign=\"center\">\n";
		for ($i=0; $i < 6; $i++) {
			$input_calendar .= "<tr>\n";
			for ($j=0; $j < 7; $j++) {
				$input_calendar .= "<td></td>\n";
			}
			$input_calendar .= "</tr>\n";
		}
		$input_calendar .= "</tbody>\n";
		$input_calendar .= "<tbody>\n";
		$input_calendar .= "<tr>\n";
		$input_calendar .= "<td align=\"right\" colspan=\"7\"><a href=\"javascript: HideCalendar('".$name."calendar');\">".@$GLOBALS['txt_mask']."</a></td>\n";
		$input_calendar .= "</tr>\n";
		$input_calendar .= "</tbody>\n";
		$input_calendar .= "</table>\n";

	} else {
		$input_calendar = "";
	}

	return $input_calendar;
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

function show_table_original($table_data, $row_em="last") {
	$tr_class = "";
	if (is_array($table_data)) {
		if ($row_em == "last") {
			$row_em = array(sizeof($table_data) - 1);
		} elseif ($row_em == "no") {
			$row_em = array();
		} elseif (!is_array($row_em)) {
			$row_em = array(min((int)$row_em, sizeof($table_data) - 1));
		}

		$html_table = "<table>\n";
		$size_table = sizeof($table_data);
		$current_row = 0;
		foreach($table_data as $table_clef => $table_row) {
			$tr_class = "";

			if ($current_row == 0 and sizeof($row_em) > 0) {
				$html_table .= "<thead>\n";
			}
			if (in_array($current_row, $row_em) and $current_row != 0) {
				$tr_class =  " class=\"em\"";
			}
			$html_table .= "<tr".$tr_class.">\n";
			foreach ($table_row as $td) {
				if (in_array($current_row, $row_em) and $current_row != 0 and is_comparable($td)) {
					if (determine_number($td) < 0) {
						$td_class = " class=\"red\"";
					} else {
						$td_class = " class=\"green\"";
					}
				}
				if (!isset($td_class)) {
					$td_class = "";
				}
				$html_table .= "<td".$td_class.">".$td."</td>\n";
				unset($td_class);
			}
			$html_table .= "</tr>\n";
			if ($current_row == 0 and sizeof($row_em) > 0) {
				$html_table .= "</thead>\n";
			}
			$current_row++;
		}
		$html_table .= "</table>\n";
	}

	return $html_table;
}

function prepare_cell($value, $current_row="1") {
	$value_class = "";
	$html_table = "";
	$value_colspan = "";
	$value_background = "";
	$value_style = "";
	$value_nowrap = "";
	if (is_array($value)) {
		if (isset($value[1]) and $value[1]) {
			$color_background = color_validate_hex($value[1]);
			$value_background = " bgcolor=\"#".$color_background."\"";
		}
		if (isset($value[2]) and $value[2]) {
			$value_colspan = " colspan=\"".(int)$value[2]."\"";
		}
		if (isset($value[3]) and $value[3]) {
			$value_class_old = $value_class;
			$value_class = " class=\"".$value[3]."\"";
		}
		if (isset($value[4]) and $value[4]) {
			$value_style = " style=\"".$value[4]."\"";
		}
		if (isset($value[5]) and $value[5] == "nowrap") {
			$value_nowrap = " nowrap=\"nowrap\"";
		}
		if ($current_row == 0) {
			if (isset($th_class) and $th_class == convert_attribute("class", "no")) {
				$html_table .= "<td".$value_colspan.$value_background.$value_style.$value_nowrap.">".$value[0]."</td>\n";
			} else {
				$html_table .= "<th".$value_colspan.$th_class.$value_background.$value_style.$value_nowrap.">".$value[0]."</th>\n";
			}
		} else {
			$html_table .= "<td".$value_colspan.$value_class.$value_background.$value_style.$value_nowrap.">".$value[0]."</td>\n";
		}
	} else {
		if ($current_row == 0) {
			if (isset($th_class) and $th_class == convert_attribute("class", "no")) {
				$html_table .= "<td>".$value."</td>\n";
			} else {
				if(!isset($th_class)) {
					$th_class = "";
				}
				$html_table .= "<th".$th_class.">".$value."</th>\n";
			}
		} else {
			$html_table .= "<td".$value_class.">".$value."</td>\n";
		}
	}

	return $html_table;
}

function percentage_format($amount) {
    if (empty($amount)) {
        $result = "";
    } else {
        $result = number_format($amount, 2, ".", " ")." %";
    }
    return $result;
}

function currency_format($amount, $locale = null) {
	return Format::currency_amount($amount, $locale);
}

function determine_class($number) {
	if (empty($number)) {
		$class = "";
	} elseif (determine_number($number) < 0) {
		$class = "red";
	} else {
		$class = "green";
	}

	return $class;
}

function show_table_background($table_data, $table_class="", $th_class="", $td_class="", $tr_class="") {
	if (is_array($table_data)) {
		if ($table_class) {
			$table_class = " class=\"".$table_class."\"";
			if (preg_match("/MSIE/", getenv("HTTP_USER_AGENT"))) {
				$table_class .= " cellspacing=\"1\"";
			}
		}
		$th_class = convert_attribute("class", $th_class);
		$td_class = convert_attribute("class", $td_class);
		$tr_class_encours  = "";
		if (is_string($tr_class)) {
			$tr_class_encours = convert_attribute("class", $tr_class);
		}

		$html_table = "<table".$table_class.">\n";
		$tr_encours = 0;
		$current_row = 0;
		foreach($table_data as $table_clef => $table_row) {
			if (is_array($tr_class) and (isset($tr_class[$tr_encours]) and $tr_class[$tr_encours])) {
				$tr_class_encours = convert_attribute("class", $tr_class[$tr_encours]);
			}
			$html_table .= "<tr".$tr_class_encours.">\n";
			$tr_encours++;
			foreach ($table_row as $td) {
				$td_colspan = "";
				$td_style = "";
				$td_nowrap = "";
				$td_extra = "";
				$td_background = "";

				if (is_array($td)) {
					if (isset($td[1]) and $td[1]) {
						$color_background = color_validate_hex($td[1]);
						$td_background = " bgcolor=\"#".$color_background."\"";
					}
					if (isset($td[2]) and $td[2]) {
						$td_colspan = " colspan=\"".(int)$td[2]."\"";
					}
					if (isset($td[3]) and $td[3]) {
						$td_class_old = $td_class;
						$td_class = " class=\"".$td[3]."\"";
					}
					if (isset($td[4]) and $td[4]) {
						$td_style = " style=\"".$td[4]."\"";
					}
					if ((isset($td[5])) and ($td[5] == "nowrap")) {
						$td_nowrap = " nowrap=\"nowrap\"";
					}
					if (isset($td[6]) and $td[6]) {
						$td_extra = $td[6];
					}
					if ($current_row == 0) {
						if ($th_class == convert_attribute("class", "no")) {
							$html_table .= "<td".$td_colspan.$td_background.$td_style.$td_nowrap.$td_extra.">".$td[0]."</td>\n";
						} else {
							$html_table .= "<th".$td_colspan.$th_class.$td_background.$td_style.$td_nowrap.$td_extra.">".$td[0]."</th>\n";
						}
					} else {
						if (!isset($td[0])) {
							$td[0] = "";
						}
						$html_table .= "<td".$td_colspan.$td_class.$td_background.$td_style.$td_nowrap.$td_extra.">".$td[0]."</td>\n";
					}
					if (isset($td[1]) and $td[1]) {
						unset($td_background);
					}
					if (isset($td[2]) and $td[2]) {
						unset($td_colspan);
					}
					if (isset($td[3]) and $td[3]) {
						$td_class = $td_class_old;
					}
					if (isset($td[4]) and $td[4]) {
						unset($td_style);
					}
					if (isset($td[5]) and $td[5]) {
						unset($td_nowrap);
					}
					if (isset($td[6]) and $td[6]) {
						unset($td_extra);
					}
				} else {
					if ($current_row == 0) {
						if ($th_class == convert_attribute("class", "no")) {
							$html_table .= "<td>".$td."</td>\n";
						} else {
							$html_table .= "<th".$th_class.">".$td."</th>\n";
						}
					} else {
						$html_table .= "<td".$td_class.">".$td."</td>\n";
					}
				}
			}
			$html_table .= "</tr>\n";
			$current_row++;
		}
		$html_table .= "</table>\n";
	}

	return $html_table;
}

function link_content($parameters="") {
	$link_content = "";

	if (isset($GLOBALS['config']['link_handling']) && $GLOBALS['config']['link_handling']) {
		$link_content .= $GLOBALS['config']['name'];
		if ($parameters) {
			$link_content .= "&".$parameters;
		}
	} elseif (isset($GLOBALS['location'])) {
		$link_content .= $GLOBALS['location'];
		if ($parameters) {
			$link_content .= "?".$parameters;
		}
	} else {
		$link_content = $_SERVER['SCRIPT_NAME']."?".$parameters;
	}

	return $link_content;
}

function link_logout() {
	$link_logout = link_content("content=logout.php");

	return $link_logout;
}

function link_hour_project($timestamp, $user_id, $customer_id, $project_id, $user_access, $user_customer, $weekend="") {
	if (!is_array($user_customer)) {
		$link_hour_project = array("", "");
	}

	if (is_numeric($customer_id)) {
		if ((in_array($customer_id, $user_customer) and preg_match("/a/", $user_access)) or preg_match("/aa/", $user_access)) {
			$link_hour_project = array("<a href=\"".link_content("content=projecttime.php&amp;day=".$timestamp."&amp;customer_id=".$customer_id."&amp;project_id=".$project_id."&amp;weekend=".$weekend)."\" class=\"navi_2\">", "</a>");
		} elseif (@$GLOBALS['param']['ext_userproject']) {
			$link_hour_project = array("<a href=\"".link_content("content=userprojects.php&amp;customer_id=".$customer_id."&amp;project_id=".$project_id)."\" class=\"navi_2\">", "</a>");
		} else {
			$link_hour_project = array("", "");
		}
	} else {
		$link_hour_project = array("", "");
	}

	return $link_hour_project;
}

function link_weekend($timestamp, $user_id, $weekend="") {
	if ($weekend != 1) {
		$weekend = "no";
	}
	$link_weekend = "[<a href=\"".link_content("content=usertime.php".
	"&amp;day=".$timestamp.
	"&amp;user_id=".$user_id.
	"&amp;weekend=".$weekend.
	"\" title=\"".@$GLOBALS['status_enableweekendacquisition'])."\">".@$GLOBALS['txt_weekend']."</a>]";

	return $link_weekend;
}

function link_show_all_tr($form, $pattern, $x, $day="") {
	$link_check_all = "<a class=\"shortcut\" href=\"javascript: show_all_tr(".$form.", '".$pattern."', 'true');\" title=\"".@$GLOBALS['txt_showall']."\">[".@$GLOBALS['txt_showall']."]</a>";
	$link_check_all .= " <a class=\"shortcut\" href=\"javascript: show_all_tr(".$form.", '".$pattern."', 'false');\" title=\"".@$GLOBALS['txt_maskall']."\">[".@$GLOBALS['txt_maskall']."]</a>\n";

	if ($day != "") {
		$link_check_all .= " <a class=\"shortcut\" href=\"javascript: show_all_tr_day(".$form.", '".$pattern."',".$day.");\" title=\"".@$GLOBALS['txt_showday']."\">[".@$GLOBALS['txt_showday']."]</a>\n";
	}

	return $link_check_all;
}

function link_check_all_both($form, $pattern) {
	$link_check_all = link_check_all($form, $pattern, "true");
	$link_check_all .= " ".link_check_all($form, $pattern, "false");
	return $link_check_all;
}

function link_check_all($form, $pattern, $bool="true") {
	$txt = $GLOBALS['txt_addall'];
	if ($bool != "true") {
		$bool = "false";
		$txt = $GLOBALS['txt_removeall'];
	}

	$link_check_all = "<a class=\"shortcut\" href=\"javascript: check_all(".$form.", '".$pattern."', '".$bool."');\" title=\"".$txt."\">[".$txt."]</a>";
	return $link_check_all;
}

function link_check_all_radio($form, $pattern, $link) {
	$link_check_all_radio = "<a class=\"shortcut\" href=\"javascript: check_all_radio(".$form.", '".$pattern."');\" title=\"".$link."\">[".$link."]</a>";
	return $link_check_all_radio;
}

function link_shift($timestamp, $user_id) {
	$link_shift = "<form method=\"post\" name=\"form_shift\" id=\"form_shift\" action=\"\">".
	"<input type=\"hidden\" name=\"action\" value=\"".@$GLOBALS['txt_shift']."\" />".
	"<input type=\"hidden\" name=\"day\" value=\"".$timestamp."\" />".
	"<input type=\"hidden\" name=\"user_id\" value=\"".$user_id."\" />".
	"[<a href=\"javascript: document.form_shift.submit();\" title=\"".@$GLOBALS['status_shiftprevioushours']."\">".@$GLOBALS['txt_shift']."</a>]".
	"</form>";
	return $link_shift;
}

function link_today($content, $timestamp, $user_id, $project_id) {
	switch($content) {
		case "usertimekeeper.php":
			$content = "content=usertimekeeper.php".
			"&amp;user_id=".$user_id;
			break;
		case "usertime.php":
		case "userhours.php":
		case "userhoursday.php":
		case "userhoursmenu.php":
			$content = "content=usertime.php".
			"&amp;user_id=".$user_id;
			break;

		case "projecttime.php":
		case "projecthoursday.php":
		case "projectplanningrequests.php":
		case "projectchargeplan.php" :
			$content = "content=".$content.
			"&amp;project_id=".$project_id;
			break;

		case "userplanningrequests.php":
		case "userchargeplan.php" :
		case "followupchargeplan.php":
		case "followuptimekeeper.php":
		case "followupcalendar.php":
		case "usercalendar.php":
			$content = "content=".$content.
			"&amp;user_id=".$user_id;
			break;

		default:
			$content = "content=usertime.php".
			"&amp;user_id=".$user_id;
			break;
	}

	$link_today = "[<a href=\"".link_content($content."&amp;day=".$timestamp)."\" title=\"".$GLOBALS['status_jumptotoday']."\">".$GLOBALS['txt_today']."</a>]";

	return $link_today;
}

function link_parent_project($project_id) {
	trigger_error("Function 'link_parent_project' is deprecated since 29/04/2013, please use 'Project::create_children'.", E_USER_WARNING);
}

function link_email($email, $string="") {
	return Html_Tag::mailto($email, $string);
}

function convert_link($link) {
	$converted_link = link_content("content=".$link);

	return $converted_link;
}
