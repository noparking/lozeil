<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Html_Cubismchart {
	public $name = "";
	public $data = array();
	public $width = 1095;
	public $height = 55;
	public $start = 0;
	public $title = '';
	
	function __construct($name = "") {
		$this->name = $name;
	}
	
	function prepare_data() {
		$data = "<ul class=\"cubism_data\">
			<li class=\"cubism_data_title\">".$this->title."</li>
			<li class=\"cubism_data_positive_average\">".$this->average_of_positive_values()."</li>
			<li class=\"cubism_data_negative_average\">".$this->average_of_negative_values()."</li>";
		
		foreach ($this->data as $value) {
			$data .="<li class=\"cubism_data_row\">".$value."</li>";
		}
		$data .= "</ul>";
		
		list($start,$stop) = determine_fiscal_year($this->start);
		$date1 = new DateTime(date("Y-m-d", (int)$this->start));
		$date2 = new DateTime(date("Y-m-d", (int)$start));
		$diff = $date1->diff($date2);
		$data .= "<ul class=\"cubism_option\">
			<li id=\"cubism_width\">".$this->width."</li>
			<li id=\"cubism_height\">".$this->height."</li>
			<li id=\"cubism_start_year\">".date('Y',$start)."</li>
			<li id=\"cubism_start_month\">".date('m',$start)."</li>
			<li id=\"cubism_isleap_year\">true</li>
			<li id=\"cubism_current_month\">".($diff->m + (intval($diff->d/28)) + 1)."</li>";
		
	
		for ($i = 0; $i < 12; $i++) {
			$date = strtotime("+".$i." months",$start);
			list($date,$stop) = determine_month($date);
			$data .= "<li class=\"cubism_link\">".link_content("content=writings.php&amp;start=".$date."&amp;stop=".$stop)."</li>";
		}
			$data .= "</ul>";
			
		return $data;
	}
	
	function prepare_navigation($filter = "", $scale = "") {
		list($previous_year_start, $previous_year_stop) = determine_fiscal_year(strtotime("-1 year ".date("m/d/Y", (int)$this->start)));
		list($next_year_start, $next_year_stop) = determine_fiscal_year(strtotime("+1 year ".date("m/d/Y", (int)$this->start)));
		return "<span id=\"cubismtimeline_back\">".Html_Tag::a(link_content("content=".$this->name.".php&amp;start=".$previous_year_start."&amp;stop=".$previous_year_stop."&amp;filter=".$filter."&amp;scale=".$scale),"<<")."</span>
			<span id=\"cubismtimeline_next\">".Html_Tag::a(link_content("content=".$this->name.".php&amp;start=".$next_year_start."&amp;stop=".$next_year_stop."&amp;filter=".$filter."&amp;scale=".$scale),">>")."</span>";
	}
	
	function show() {
		return "<div id=\"cubismtimeline\" style=\"width : ".(++$this->width)."px\"></div>".$this->prepare_data().$this->prepare_navigation();
	}
	
	function display() {
		return "<div id=\"cubismtimeline\" style=\"width : ".(++$this->width)."px\"></div>".$this->prepare_data();
	}
	
	function average_of_positive_values() {
		$sum = 0;
		$nb = 0;
		foreach ($this->data as $value) {
			if ($value > 0) {
				$sum = $sum + $value;
				$nb++;
			}
		}
		if ($nb == 0) {
			return 0;
		}
		return $sum/$nb;
	}
	
	function average_of_negative_values() {
		$sum = 0;
		$nb = 0;
		foreach ($this->data as $value) {
			if ($value < 0) {
				$sum = $sum + $value;
				$nb++;
			}
		}
		if ($nb == 0) {
			return 0;
		}
		return $sum/$nb;
	}
}
