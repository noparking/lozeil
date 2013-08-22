<?php
/*
	opentime
	$Author: perrick $
	$URL: svn://svn.noparking.net/var/repos/opentime/inc/html_date.inc.php $
	$Revision: 5185 $

	Copyright (C) No Parking 2001 - 2011
*/

class Html_Date {
	const day = "day";
	const month = "month";
	const year = "year";
	const calendar = "calendar";
	
	public $name = "";
	public $month = null;
	public $day = null;
	public $year = null;
	public $img_width = 14;
	public $img_height = 14;
	public $img_src = "medias/images/link_calendar.gif";
	public $format = 'd-m-y';
	public $tip = false;
	public $properties = array();
	
	function __construct($name, $month = null, $day = null, $year = null) {
		$this->name = $name;
		
		$this->day = new Html_Input($this->name(self::day), $day);
		$this->day->size = 3;
		$this->day->maxlength = 2;
		
		$this->month = new Html_Input($this->name(self::month), $month);
		$this->month->size = 3;
		$this->month->maxlength = 2;
		
		$this->year = new Html_Input($this->name(self::year), $year);
		$this->year->size = 6;
		$this->year->maxlength = 4;
		
		$this->img_src = $GLOBALS['config']['layout_mediaserver'].$this->img_src;
	}
	
	function name($suffix) {
		if (preg_match("/\]$/", $this->name)) {
			return substr($this->name, 0, -1).$suffix."]";
		} else {
			return $this->name.$suffix;
		}
	}
	
	function input_hidden() {
		return $this->day->input_hidden().$this->month->input_hidden().$this->year->input_hidden();
	}
	
	function input() {
		$string = "<nobr>";
		if ($this->tip) {
			$this->month->tip = true;
			$this->day->tip = true;
			$this->year->tip = true;
		}
		
		if (is_array($this->properties) and count($this->properties) > 0) {
			foreach ($this->properties as $property => $value) {
				$this->day->{$property} = $value;
				$this->month->{$property} = $value;
				$this->year->{$property} = $value;
			}
		}
		
		if (preg_match('|([dmy])(.)([dmy])(.)([dmy])|', $this->format, $matches)) {
			foreach (array_slice($matches, 1) as $match) {
				switch ($match) {
					case 'd':
						$string .= $this->day->input();
						break;
						
					case 'm':
						$string .= $this->month->input();
						break;
						
					case 'y':
						$string .= $this->year->input();
						break;
						
					default:
						$string .= $match;
				}
			}
		}
		
		if ($this->img_src) {
			$string .= "<img class=\"hand\"".(!$this->img_width ? "" : " width=\"".$this->img_width."\"").(!$this->img_height ? "" : " height=\"".$this->img_height."\"")." src=\"".$this->img_src."\" onclick=\"ToggleCalendar('".$this->name(self::calendar)."')\"/>";
			$string .= $this->show_calendar();
		}

		$string .= "</nobr>";

		return $string;
	}
	
	function timestamp($timestamp) {
		if ($timestamp != 0) {
			$this->month->value = adodb_date('m', $timestamp);
			$this->day->value = adodb_date('d', $timestamp);
			$this->year->value = adodb_date('Y', $timestamp);
		}
		
		return $this;
	}
	
	function label($text) {
		return $this->day->label($text);
	}
	
	function tip($text) {
		$tip = "";

		if (isset($this->tip) and $this->tip and $this->name) {
			$tip .= "<div id=\"tip_".$this->month->id."\"".convert_attribute("class", "tip").">".format_name($text)."</div>";
			$tip .= "<div id=\"tip_".$this->day->id."\"".convert_attribute("class", "tip").">".format_name($text)."</div>";
			$tip .= "<div id=\"tip_".$this->year->id."\"".convert_attribute("class", "tip").">".format_name($text)."</div>";
		}

		return $tip;
	}
	
	function item($label, $display = "", $complement = "") {
		$html = $this->label($label);
		if (!empty($display)) {
			$html .= "<span class=\"txt_field_empty\">".$display."</span>";
			$html .= "<div class=\"hidden_field field_empty\">".$this->input()."</div>"; 
		} else {
			$html .= "<div class=\"hidden_field\">".$this->input()."</div>";
		}
		if (!empty($complement)) {
			$html .= "<div class=\"field_complement\">".$complement."</div>";
		}
				
		return $html;
	}
	
	private function show_calendar() {
			$month = !$this->month->value ? date('m') : $this->month->value;
			$day = !$this->day->value ? date('d') : $this->day->value;
			$year = !$this->year->value ? date('Y') : $this->year->value;
			
			$timestamp = mktime(0, 0, 0, $month, $day, $year);

			$input_calendar = "";
			$input_calendar .= "<div class=\"input-date-calendar\" id=\"".$this->name(self::calendar)."\" style=\"display: none; position: absolute; margin-top: ".($this->img_height ? $this->img_height : "20")."px; margin-left: -150px; background: #fff; border: 1px solid #aaa;\">\n";
			$input_calendar .= "<!--[if lte IE 6.5]><iframe style='display:block; position: absolute; top: 0; left: 0; z-index: -1; width: 0; height: 0;'></iframe><![endif]-->\n";
			$input_calendar .= "<table>\n";
			$input_calendar .= "<tbody>\n";
			$input_calendar .= "<tr>\n";
			$input_calendar .= "<td colspan=\"7\" valign=\"center\" nowrap=\"nowrap\">\n";
			$input_calendar .= "<select id=\"".$this->name(self::calendar)."month\" onchange=\"MakeCalendar('".$this->name(self::calendar)."')\"> \n";
			for ($i=1; $i <= 12; $i++) {
				if ($i == adodb_date("m", $timestamp)) {
					$selected = " selected=\"selected\"";
				} else {
					$selected = "";
				}
				$input_calendar .= "<option".$selected.">".$GLOBALS['array_month'][$i]."</option>\n";
			}
			$input_calendar .= "</select> \n";
			$input_calendar .= "<select id=\"".$this->name(self::calendar)."year\" onchange=\"MakeCalendar('".$this->name(self::calendar)."')\"> \n";
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
				$input_calendar .= "<td>".$GLOBALS['array_week'][$i][0]."</td>\n";
			}
			$input_calendar .= "</tr>\n";
			$input_calendar .= "</tbody>\n";
			$input_calendar .= "<tbody class=\"hand\" id=\"".$this->name(self::calendar)."dayList\" onclick=\"FillDate('".$this->name(self::calendar)."', event)\" valign=\"center\">\n";
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
			$input_calendar .= "<td align=\"right\" colspan=\"7\"><a href=\"javascript: HideCalendar('".$this->name(self::calendar)."');\">".$GLOBALS['txt_mask']."</a></td>\n";
			$input_calendar .= "</tr>\n";
			$input_calendar .= "</tbody>\n";
			$input_calendar .= "</table>\n";
			$input_calendar .= "</div>\n";
	
		return $input_calendar;
	}
}
