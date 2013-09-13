<?php
/*
	opentime
	$Author: bodart $
	$URL: svn://svn.noparking.net/var/repos/opentime/inc/html_input_date.inc.php $
	$Revision: 5938 $

	Copyright (C) No Parking 2011 - 2012
*/

class Html_Input_Date extends Html_Input {
	public $name = "";
	public $value = 0;
	public $img_width = 20;
	public $img_height = 24;
	public $img_src = "medias/images/link_calendar.png";
	
	function __construct($name, $value = 0) {
		$this->name = $name;
		$this->value = $value;
		$this->img_src = $GLOBALS['config']['layout_mediaserver'].$this->img_src;
		$this->id = $this->name."[d]";
	}
	
	function name($suffix = "[d]") {
		return $this->name.$suffix;
	}
	
	function input_hidden() {
		$day = new Html_Input($this->name, $this->value);
		return $day->input_hidden();
	}
	
	function item($label, $complement = "") {
		return $this->label($label).$this->input().$complement;
	}
	
	function label($string) {
		$label = "";

		if ($this->id) {
			$label = "<label for=\"".$this->id."\">".Format::name($string)."</label>";
		}

		return $label;
	}
	
	function input() {
		$string = "<nobr>";
		
		$d = new Html_Input($this->name."[d]", (($this->value) ? adodb_date("d", $this->value) : ""));
		$d->properties = $this->properties + array(
			'class' => "input-date",
			'size' => 3,
		);
		$m = new Html_Input($this->name."[m]", (($this->value) ? adodb_date("m", $this->value) : ""));
		$m->properties = $this->properties + array(
			'class' => "input-date",
			'size' => 3,
		);
		$Y = new Html_Input($this->name."[Y]", (($this->value) ? adodb_date("Y", $this->value) : ""));
		$Y->properties = $this->properties + array(
			'class' => "input-date",
			'size' => 6,
		);

		$hours = new Html_Input($this->name."[hours]", (($this->value) ? adodb_date("hours", $this->value) : ""));
		$hours->properties = $this->properties + array(
			'class' => "input-date",
			'size' => 3,
		);
		
		
		$string .= $d->input()." ".$m->input()." ".$Y->input();

		if ($this->img_src) {
			switch (true) {
				case !isset($this->properties['disabled']):
				case $this->properties['disabled'] != "disabled":
					$string .= "<img class=\"hand\"".(!$this->img_width ? "" : " width=\"".$this->img_width."\"").(!$this->img_height ? "" : " height=\"".$this->img_height."\"")." src=\"".$this->img_src."\" onclick=\"ToggleCalendar('".$this->name("calendar")."')\" />";
					$string .= $this->show_calendar();
					break;
				default:
					break;					
			}
		}

		$string .= "</nobr>";

		return $string;
	}

	private function show_calendar() {
		$value = ($this->value > 0) ? $this->value : time();

			$input_calendar = "";
			$input_calendar .= "<div class=\"input-date-calendar\" id=\"".$this->name("calendar")."\" style=\"display: none; position: absolute; margin-top: ".($this->img_height ? $this->img_height : "20")."px; margin-left: -150px; background: #fff; border: 1px solid #aaa; z-index: 999;\">\n";
			$input_calendar .= "<!--[if lte IE 6.5]><iframe style='display:block; position: absolute; top: 0; left: 0; z-index: -1; width: 0; height: 0;'></iframe><![endif]-->\n";
			$input_calendar .= "<table>\n";
			$input_calendar .= "<tbody>\n";
			$input_calendar .= "<tr>\n";
			$input_calendar .= "<td colspan=\"7\" valign=\"center\" nowrap=\"nowrap\">\n";
			$input_calendar .= "<select id=\"".$this->name("calendarmonth")."\" onchange=\"MakeCalendar('".$this->name("calendar")."')\"> \n";
			for ($i=1; $i <= 12; $i++) {
				if ($i == adodb_date("m", $value)) {
					$selected = " selected=\"selected\"";
				} else {
					$selected = "";
				}
				$input_calendar .= "<option".$selected.">".$GLOBALS['array_month'][$i]."</option>\n";
			}
			$input_calendar .= "</select> \n";
			$input_calendar .= "<select id=\"".$this->name("calendaryear")."\" onchange=\"MakeCalendar('".$this->name("calendar")."')\"> \n";
			$start_year = adodb_date("Y", strtotime("-5 years"));
			$stop_year = adodb_date("Y", strtotime("+5 years"));
			for ($i = $start_year; $i < $stop_year; $i++) {
				if ($i == adodb_date("Y", $value)) {
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
			$input_calendar .= "<tbody class=\"hand\" id=\"".$this->name("calendardayList")."\" onclick=\"FillInputDate('".$this->name("calendar")."', event)\" valign=\"center\">\n";
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
			$input_calendar .= "<td align=\"right\" colspan=\"7\"><a href=\"javascript: HideCalendar('".$this->name("calendar")."');\">".  utf8_ucfirst(__('mask'))."</a></td>\n";
			$input_calendar .= "</tr>\n";
			$input_calendar .= "</tbody>\n";
			$input_calendar .= "</table>\n";
			$input_calendar .= "</div>\n";
	
		return $input_calendar;
	}
}
