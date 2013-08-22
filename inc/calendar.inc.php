<?php
/*
	opentime
	$Author: perrick $
	$URL: svn://svn.noparking.net/var/repos/opentime/inc/calendar.inc.php $
	$Revision: 5346 $

	Copyright (C) No Parking 2001 - 2012
*/

class Calendar {
	public $day;
	public $start;
	public $stop;
	public $view = "m";
	public $user_id = 0;
	public $users_id = array();
	public $project_id = 0;
	public $projects_id = array();
	
	protected $width = 300;
	protected $colors = array();

	function __construct($day, $view="m") {
		$this->day = $day;
		$this->view = $view;
		
		switch ($this->view) {
			case "d":
				$this->start = mktime(0, 0, 0, date("m", $this->day), date("d", $this->day), date("Y", $this->day));
				$this->stop = mktime(23, 59, 59, date("m", $this->day), date("d", $this->day), date("Y", $this->day));
				break;
			case "w":
				$this->start = mktime(0, 0, 0, date("m", $this->day), date("d", $this->day) - ((date("w", $this->day) + 6) % 7), date("Y", $this->day));
				$this->stop = mktime(23, 59, 59, date("m", $this->start), date("d", $this->start) + 6, date("Y", $this->start));
				break;
			case "m":
			default:
				$monthstart = mktime(0, 0, 0, date("m", $this->day), 1, date("Y", $this->day));
				$monthstop = mktime(0, 0, 0, date("m", $this->day) + 1, 0, date("Y", $this->day));
				$this->start = mktime(0, 0, 0, date("m", $this->day), 2 - date("w", $monthstart), date("Y", $this->day));
				$this->stop = mktime(23, 59, 59, date("m", $this->day) + 1, 0 + 7 - date("w", $monthstop), date("Y", $this->day));
				break;
		}
	}
	
	function color($user_id) {
		if (!isset($this->colors[$user_id])) {
			$option = new User_Option();
			$option->user_id = $user_id;
			$option->name = "color";
			$option->load();
			$this->colors[$user_id] = $option->value;
			
			if (empty($this->colors[$user_id])) {
				$this->colors[$user_id] = (int)rand(10, 99).(int)rand(10, 99).(int)rand(10, 99);
			}
		}
		
		return $this->colors[$user_id];
	}
	
	function legend() {
		$html = "";
		
		if (is_array($this->users_id) and count($this->users_id) > 1) {
			$users = new Users();
			$users->select(
				array(
					'id' => $this->users_id,
				)
			);
			$html = "<ul class=\"calendar-legend\">";
			foreach ($users->gateway as $user) {
				$user->load();
				$html .= "<li>".color_square_html($this->color($user->id))." ".$user->name."</li>";
			}
			$html .= "</ul>";
		}
		
		return $html;
	}

	function grid_header() {
		switch ($this->view) {
			case "d":
				return $this->grid_header_daily();
			case "w":
				return $this->grid_header_weekly();
			case "m":
			default:
				return $this->grid_header_monthly();
		}
	}

	function grid_header_daily() {
		return array(
			'navigation' => array(
				'cells' => array(
					array(
						'value' => $GLOBALS['array_week'][date("w", $this->day)]." ".date("d", $this->day)." ".$GLOBALS['array_month'][date("n", $this->day)]." ".date("Y", $this->day),
						'colspan' => count($this->users_id) + 2,
						'style' => "width : ".$this->width."pt;"
					),
				),
				'class' => "calendar-header-navigation",
			),
		);
	}

	function grid_header_weekly() {
		$grid = array(
			'header_navigation' => array(
				'cells' => array(
					array(
						'value' => $GLOBALS['txt_week']." ".(int)date("W", $this->day)." - ".$GLOBALS['array_month'][date("n", $this->day)]." ".date("Y", $this->day),
						'colspan' => 7,
					),
				),
				'class' => "calendar-header-navigation",
			),
		);
		
		$grid['header_day']['class'] = "calendar-header-days";
		for ($i = 0; $i < 7; $i++) {
			$day = mktime(0, 0, 0, date("m", $this->start), date("d", $this->start) + $i, date("Y", $this->start));
			$grid['header_day']['cells'][$i] = calendar_link_day($GLOBALS['array_week'][$i + 1]." ".date("d", $day), $day, array());
		}
		
		return $grid;
	}

	function grid_header_monthly() {
		$grid = array(
			'header_navigation' => array(
				'cells' => 	array(
					array(
						'value' => $GLOBALS['array_month'][date("n", $this->day)]." ".date("Y", $this->day),
						'colspan' => 7,
					),
				),
				'class' => "calendar-header-navigation",
			),
		);
		
		$grid['header_day']['class'] = "calendar-header-days";
		for ($i = 0; $i < 7; $i++) {
			$grid['header_day']['cells'][$i] = $GLOBALS['array_week'][$i + 1];
		}
		
		return $grid;
	}
	
	function grid_body() {
		switch ($this->view) {
			case "d":
				return $this->grid_body_daily();
			case "w":
				return $this->grid_body_weekly();
			case "m":
			default:
				return $this->grid_body_monthly();
		}
	}
	
	function grid_body_daily() {
		$height = get_calendar_day_height();
		$width = $this->width / max(1, count($this->users_id) - 3);
		
		$grid = array();
		$grid['pre'][] = force_nonempty();
		foreach ($this->users_id as $user_id) {
			$grid['pre']['user'.$user_id] = array(
					'value' => "",
					'style' => "width : ".$width."pt;",
			);
			if (is_array($this->users_id) and count($this->users_id) > 1) {
				$grid['pre']['user'.$user_id]['style'] .= " border-top: 8px solid #".$this->color($user_id).";";
			}
			
		}
		$grid['pre'][] = force_nonempty();

		$grid['hour'][] = show_calendar_halfhour("", $this->start);
		foreach ($this->users_id as $user_id) {
			 $grid['hour']['user'.$user_id] = array(
				'value' => "",
				'style' => "height : ".$height."pt; width : ".$width."pt;",
			);				
		}
		$grid['hour'][] = show_calendar_halfhour("", $this->start);
		
		$grid['post'][] = force_nonempty();
		foreach ($this->users_id as $user_id) {
			$grid['post']['user'.$user_id] = array(
					'value' => "",
					'style' => "width : ".$width."pt;",
			);
		}
		$grid['post'][] = force_nonempty();

		foreach ($this->users_id as $user_id) {
			$items = get_calendar_import($user_id, 0, $this->start, $this->stop);
			$calendar_event = prepare_calendar_day_import($items, $width);
			$grid['pre']['user'.$user_id]['value'] = $calendar_event[0];
			$grid['hour']['user'.$user_id]['value'] = $calendar_event[1];
			$grid['post']['user'.$user_id]['value'] = $calendar_event[2];
		}
		
		return $grid;
	}
	
	function grid_body_weekly() {
		$encours = $this->start;
		while ($encours < $this->stop) {
			$grid['morning']['cells'][date("w", $this->start)] = array(
				'value' => $GLOBALS['txt_morning'],
				'colspan' => 7,
			);

			$day = mktime($GLOBALS['param']['calendar-hour_start'], date("i", $encours), 0, date("m", $encours), date("d", $encours), date("Y", $encours));
			$grid['forenoon']['cells'][$encours] = array(
				'value' => "",
				'data-day' => $day,
				'data-href' => link_content("content=userevent.php&amp;day=".$day),
				'class' => "clickable",
			);
			$grid['forenoon']['class'] = "without-items";
			
			foreach ($this->users_id as $user_id) {
				$grid['forenoon-user'.$user_id]['cells'][$encours] = array(
					'value' => "",
					'class' => "calendarday-user clickable",
				);
			}
			
			$grid['noon']['cells'][date("w", $this->start)] = array(
				'value' => $GLOBALS['txt_afternoon'],
				'colspan' => 7,
			);

			$day = mktime($GLOBALS['param']['calendar-hour_middle'], date("i", $encours), 0, date("m", $encours), date("d", $encours), date("Y", $encours));
			$grid['afternoon']['cells'][$encours] = array(
				'value' => "",
				'data-day' => $day,
				'data-href' => link_content("content=userevent.php&amp;day=".$day),
				'class' => "clickable",
			);
			$grid['afternoon']['class'] = "without-items";
			
			foreach ($this->users_id as $user_id) {
				$grid['afternoon-user'.$user_id]['cells'][$encours] = array(
					'value' => "",
					'class' => "calendarday-user clickable",
				);
			}
			
			$encours = mktime(0, 0, 0, date("m", $encours), date("d", $encours) + 1, date("Y", $encours));
		}
		
		foreach ($this->users_id as $user_id) {
			$items = get_calendar_import($user_id, 0, $this->start, $this->stop);
			foreach ($items as $item) {
				$day_noon = mktime($GLOBALS['param']['calendar-hour_middle'], date("i", $item['day']), 0, date("m", $item['day']), date("d", $item['day']), date("Y", $item['day']));
				if (!isset($item['start']) or $item['start'] < $day_noon) {
					$grid['forenoon-user'.$user_id]['cells'][$item['day']]['value'] .= prepare_calendar_content($item);
					$grid['forenoon-user'.$user_id]['cells'][$item['day']]['class'] = "calendarday-user clickable";
					if (is_array($this->users_id) and count($this->users_id) > 1) {
						$grid['forenoon-user'.$user_id]['cells'][$item['day']]['style'] = "border-top: 8px solid #".$this->color($user_id).";";
					}
					$grid['forenoon']['class'] = "with-items";
				}
				if (!isset($item['stop']) or $item['stop'] >= $day_noon) {
					$grid['afternoon-user'.$user_id]['cells'][$item['day']]['value'] .= prepare_calendar_content($item);
					$grid['afternoon-user'.$user_id]['cells'][$item['day']]['class'] = "calendarday-user clickable";
					if (is_array($this->users_id) and count($this->users_id) > 1) {
						$grid['afternoon-user'.$user_id]['cells'][$item['day']]['style'] = "border-top: 8px solid #".$this->color($user_id).";";
					}
					$grid['afternoon']['class'] = "with-items";
				}
			}
		}

		return $grid;
	}
	
	function grid_body_monthly() {
		$grid = array();
		
		$encours = $this->start;
		while ($encours < $this->stop) {
			$event = new Event(0);
			$event->day = $encours;
			if (date("m", $encours) == date("m", $this->day)) {
				$class = "clickable";
				$class_header = "";
			} else {
				$class = "outside clickable";
				$class_header = "outside";
			}

			$week = "";
			if (date("N", $encours) == 1) {
				$week = "<span class=\"weeknumber\">".date("W", $encours)."</span>";
				$class .= " with-weeknumber";
			}
			$grid['week'.date("W", $encours)]['cells'][$encours] = array(
				'value' => $week.$event->link(date("d", $encours), array('class' => $class_header)),
				'data-day' => $encours,
				'data-href' => link_content("content=userevent.php&amp;day=".$encours),
				'class' => $class." without-event",
			);
			$grid['week'.date("W", $encours)]['class'] = "without-items";
			
			foreach ($this->users_id as $user_id) {
				$grid['week'.date("W", $encours)."-user".$user_id]['cells'][$encours] = array(
					'value' => "",
					'class' => "calendarday-user",
				);
			}
			
			foreach ($this->projects_id as $project_id) {
				$grid['week'.date("W", $encours)."-project".$project_id]['cells'][$encours] = array(
					'value' => "",
					'class' => "calendarday-project",
				);
			}

			$encours = mktime(0, 0, 0, date("m", $encours), date("d", $encours) + 1, date("Y", $encours));
		}

		foreach ($this->users_id as $user_id) {
			$items = get_calendar_import($user_id, 0, $this->start, $this->stop);
			foreach ($items as $item) {
				$key = 'week'.date("W", $item['day'])."-user".$user_id;
				if (!isset($grid[$key]['cells'][$item['day']]['value'])) {
					$grid[$key]['cells'][$item['day']]['value'] = "";
				}
				$grid[$key]['cells'][$item['day']]['value'] .= prepare_calendar_content($item);
				$grid[$key]['cells'][$item['day']]['class'] = "calendarday-user clickable";
				if (is_array($this->users_id) and count($this->users_id) > 1) {
					$grid[$key]['cells'][$item['day']]['style'] = "border-top: 8px solid #".$this->color($user_id).";";
				}
				$grid['week'.date("W", $item['day'])]['class'] = "with-items";
			}
		}
		
		foreach ($this->projects_id as $project_id) {
			$items = get_calendar_import(0, $project_id, $this->start, $this->stop);
			foreach ($items as $item) {
				$key = 'week'.date("W", $item['day'])."-project".$project_id;
				if (!isset($grid['week'.date("W", $item['day'])."-project".$project_id]['cells'][$item['day']]['value'])) {
					$grid[$key]['cells'][$item['day']]['value'] = "";
				}
				$grid[$key]['cells'][$item['day']]['value'] .= prepare_calendar_content($item);
				$grid[$key]['cells'][$item['day']]['class'] = "calendarday-project clickable";
				if (is_array($this->projects_id) and count($this->projects_id) > 1) {
					$grid[$key]['cells'][$item['day']]['style'] = "border-top: 8px solid #".$this->color($project_id).";";
				}
				$grid['week'.date("W", $item['day'])]['class'] = "with-items";
			}
		}

		return $grid;
	}
	
	function grid() {
		return $this->grid_header() + $this->grid_body();
	}
	
	function show() {
		$username = get_user_name($this->user_id);
		$working = "<h2>".$username."</h2>";
		$working .= "<form action=\"\" method=\"post\">";
		$working .= show_table(array('lines' => $this->grid(), 'class' => "calendar calendar".$this->view." calendar-".count($this->users_id)."users"))."<br /><br />";
		$save = new Html_Input_Save("return");
		$working .= $save->input();
		$working .= "</form>";

		return $working;
	}
	
	function show_date() {
		switch ($this->view) {
			case "m":
				return date("m/Y", $this->day);
				break;
			case "w":
				return show_date_month_with_weeknumber($this->day);
				break;
			case "d":
				return Format::date($this->day);
				break;
		}
	}

	function show_timeline($content = "usercalendar.php") {
		$grid = array();
		
		$time_control = new Form_Time_Control($this->day, $this->view);
		$grid['leaves']['control']['value'] = $time_control->show();
		
		$grid['leaves']['date']['value'] = "<strong>".date("m/Y", $this->day)."</strong>";
		$grid['leaves']['date']['class'] = "timeline_date";

		$encours = mktime(0, 0, 0, date('m', $this->day), 1, date('Y', $this->day));
		$stop = mktime(0, 0, 0, date('m', $this->day) + 1, 0, date('Y', $this->day));
		
		$priorites = array(
			$GLOBALS['array_color']["event"] => 1,
			$GLOBALS['array_color']["request"] => 2,
			$GLOBALS['array_color']["absence"] => 3,
		);
		$colors = array();
		foreach (get_calendar_import($this->user_id, $this->project_id, $encours, $stop) as $event) {
			$day = mktime(0, 0, 0, date("m", $event['day']), date("d", $event['day']), date("Y", $event['day']));
			switch ($event['eventtype_name']) {
				case "request":
					$color = $GLOBALS['array_color']["request"];
					break;
				case "event":
					$color = $GLOBALS['array_color']["event"];
					break;
				case "absence":
					$color = $GLOBALS['array_color']["absence"];
					break;
			}
			
			if (!isset($colors[$day]) or ($priorites[$color] > $priorites[$colors[$day]])) {
				$colors[$day] = $color;
			}
		}
		
		while ($encours <= $stop) {
			$class = "weekday";
			if (in_array(date("w", $encours), array(0, 6))) {
				$class = "weekend";
			}
			
			$color = "FFFFFF";
			if (isset($colors[$encours])) {
				$color = $colors[$encours];
			}

			$grid['leaves'][$encours]['class'] = $class;
			$grid['leaves'][$encours]['value'] = "<a href=\"".link_content("content=".$content."&day=".$encours)."\">".date("d", $encours)."</a>";
			$grid['leaves'][$encours]['style'] = "background-color : #".$color;

			$encours = mktime(0, 0, 0, date("m", $encours), date("d", $encours) + 1, date("Y", $encours));
		}

		$timeline = "<span class=\"timeline\">";
		$list = new Html_List($grid);
		$timeline .= $list->show();
		$timeline .= "</span>";

		return $timeline;
	}

	function actions() {
		$actions = array();

		if ($GLOBALS['param']['ext_events']) {
			$actions['new'] = array();
			$actions['new']['class'] = "action_strong";
			$event = new Event(0);
			$actions['new']['value'] = $event->link($GLOBALS['txt_new']." ".$GLOBALS['txt_event']);
		}

		$actions['today'] = array();
		$actions['today']['class'] = "action_strong";
		$actions['today']['value'] = link_today($GLOBALS['content'], time(), $this->user_id, "");

		$actions['monthly'] = array();
		$actions['monthly']['class'] = "action_contrast";
		$actions['monthly']['value'] = show_link_form("calendarview", $GLOBALS['txt_monthly_'], "", $GLOBALS['content'], "view", array("calendarview_encours" => "m"), "m", "shortcut small", "");

		$actions['weekly'] = array();
		$actions['weekly']['class'] = "action_contrast";
		$actions['weekly']['value'] = show_link_form("calendarview", $GLOBALS['txt_weekly_'], "", $GLOBALS['content'], "view", array("calendarview_encours" => "w"), "w", "shortcut small", "");

		$actions['daily'] = array();
		$actions['daily']['class'] = "action_contrast";
		$actions['daily']['value'] = show_link_form("calendarview", $GLOBALS['txt_daily_'], "", $GLOBALS['content'], "view", array("calendarview_encours" => "d"), "d", "shortcut small", "");

		return $actions;
	}

	function alerts() {
		$title = $GLOBALS['tip_alert_calendar'];
		$elements = array();

		$calendar_items = new Calendar_Items();
		$calendar_items->select(array('assigned_id' => $this->user_id));

		if (count($calendar_items->gateway) > 0) {
			$export_ical = "<form method=\"post\" action=\"\" name=\"form_export_calendar\" id=\"form_export_calendar\">";
			$export_ical .= "<input type=\"hidden\" name=\"action\" value=\"export\" />";
			$export_ical .= "<input type=\"hidden\" name=\"calendar_user\" value=\"".$this->user_id."\" />";
			$export_ical .= "<input type=\"hidden\" name=\"method\" value=\"mime\" />";
			$export_ical .= "<a class=\"autosubmit\" href=\"#\">".$GLOBALS['status_exportical']."</a>";
			$export_ical .= "</form>\n";

			$elements['export_calendar'] = array();
			$elements['export_calendar']['value'] = $export_ical;
		}

		return array($title, $elements);
	}
}

function get_calendar_day_height() {
	return ($GLOBALS['param']['calendar-hour_stop'] - $GLOBALS['param']['calendar-hour_start'] + 1) * 30;
}

function show_calendarview_link($timestamp, $content="") {
	$calendarview_link = show_link_form("viewcalendar", "[ ".$GLOBALS['txt_monthly_']."&nbsp;", "", $content, "view", array("calendarview_encours" => "m"), "m", "shortcut small", "");
	$calendarview_link .= show_link_form("viewcalendar", "|&nbsp;".$GLOBALS['txt_weekly_']."&nbsp;", "", $content, "view", array("calendarview_encours" => "w"), "w", "shortcut small", "");
	$calendarview_link .= show_link_form("viewcalendar", "|&nbsp;".$GLOBALS['txt_daily_']." ]", "", $content, "view", array("calendarview_encours" => "d"), "d", "shortcut small", "");

	return $calendarview_link;
}

function prepare_calendar_content($var_content, $time_origin="0", $diff="", $extra = "") {
	$item = new Calendar_Item($var_content['id'], $var_content['eventtype_name']);
	$item->fill($var_content);

	return $item->content();
}

function prepare_inside_calendar_day_array($var, $width) {
	if (is_array($var)) {
		$var_clone = $var;
		$height_cumul = 0;
		$cat = 0;
		$cat_nb = array();
		$cat_stop = array();
		$cat_height_cumul = array();
		foreach ($var as $var_clef => $var_valeur) {
			$var_clone[$var_clef]['left'] = "0";

			$var_clone[$var_clef]['width'] = $width;

			$height = max(20, floor((($var_valeur['stop'] - $var_valeur['start']) / 3600) * 30));
			$var_clone[$var_clef]['height'] = $height;

			if ($var_clef > 0) {
				if(!isset($cat_stop[$cat])) {
					$cat_stop[$cat] = 0;
				}
				$cat_stop[$cat] = max($cat_stop[$cat], $var_clone[$var_clef - 1]['stop']);
				if ($var_valeur['start'] > $cat_stop[$cat]) {
					$cat++;
				}
			}
			$var_clone[$var_clef]['cat'] = (int)$cat;

			$top = floor((($var_valeur['start'] - mktime($GLOBALS['param']['calendar-hour_start'], 0, 0, date("m", $var_valeur['start']), date("d", $var_valeur['start']), date("Y", $var_valeur['start']))) / 3600) * 30);
			if ($var_clef > 0) {
				if(!isset($cat_stop[$cat])) {
					$cat_stop[$cat] = 0;
				}
				if ($var_clone[$var_clef]['start'] > $cat_stop[$cat]) {
					$height_cumul += $cat_height_cumul[$cat - 1];
				}
			}

			if(!isset($cat_height_cumul[$cat])) {
				$cat_height_cumul[$cat] = 0;
			}
			$var_clone[$var_clef]['top'] = $top - $height_cumul - $cat_height_cumul[$cat];
			$cat_height_cumul[$cat] += $height;
			if(!isset($cat_nb[$cat])) {
				$cat_nb[$cat] = 0;
			}
			$cat_nb[$cat]++;
		}

		$left_place=0;
		$var = $var_clone;
		foreach ($var as $var_clef => $var_valeur) {
			$var_clone[$var_clef]['width'] = floor($width / $cat_nb[$var_clone[$var_clef]['cat']]);
			if ($var_clef > 0) {
				if ($var_clone[$var_clef]['cat'] == $var_clone[$var_clef-1]['cat']) {
					$left_place++;
				} else {
					$left_place=0;
				}
			}
			$var_clone[$var_clef]['left'] = $left_place * $var_clone[$var_clef]['width'];
		}

		$var = $var_clone;
	}

	return $var;
}

function prepare_inside_calendar_day($var, $width) {
	$inside_calendar = "";
	$var = prepare_inside_calendar_day_array($var, $width);
	foreach ($var as $var_key => $var_content) {
		$inside_calendar .= prepare_calendar_content($var_content);
	}
	if ($inside_calendar) {
		$calendar_height = get_calendar_day_height();
		$inside_calendar = "<div style=\"height : ".$calendar_height."pt;\">".$inside_calendar."</div>";
	}

	return $inside_calendar;
}

function prepare_calendar_week_import($var) {
	$import_calendar = array();

	if (is_array($var)) {
		foreach ($var as $var_key => $var_content) {
			if (!isset($var_content['start']) or date("H", $var_content['start']) < $GLOBALS['param']['calendar-hour_middle']) {
				if(!isset($import_calendar[$var_content['day']][0])) {
					$import_calendar[$var_content['day']][0] = "";
				}
				$import_calendar[$var_content['day']][0] .= prepare_calendar_content($var_content);
				if (isset($var_content['start']) and date("H", $var_content['stop']) > $GLOBALS['param']['calendar-hour_middle']) {
					if(!isset($import_calendar[$var_content['day']][1])) {
						$import_calendar[$var_content['day']][1] = "";
					}
					$import_calendar[$var_content['day']][1] .= prepare_calendar_content($var_content, "", "afternoon");
				}
			} else {
				if(!isset($import_calendar[$var_content['day']][1])) {
					$import_calendar[$var_content['day']][1] = "";
				}
				$import_calendar[$var_content['day']][1] .= prepare_calendar_content($var_content);
			}
		}
	}

	return $import_calendar;
}

function prepare_calendar_day_import($var, $width="300") {
	$import_calendar = array();
	$import_calendar_0 = "";
	$import_calendar_2 = "";
	if (is_array($var)) {
		foreach ($var as $var_key => $var_content) {
			if (!isset($var_content['start']) or date("H", $var_content['start']) < $GLOBALS['param']['calendar-hour_start']) {
				$import_calendar_0 .= prepare_calendar_content($var_content);
			} elseif (date("H", $var_content['start']) >= $GLOBALS['param']['calendar-hour_stop']) {
				$import_calendar_2 .= prepare_calendar_content($var_content);
			} else {
				$import_calendar[] = $var_content;
			}
		}
		if (is_array($import_calendar)) {
			$import_calendar_1 = prepare_inside_calendar_day($import_calendar, $width);
		}
	}

	return array(force_nonempty($import_calendar_0), force_nonempty($import_calendar_1), force_nonempty($import_calendar_2));
}

function prepare_calendar_import($var, $extra = "") {
	$import_calendar = array();
	if (is_array($var)) {
		foreach ($var as $var_key => $var_content) {
			if (!isset($import_calendar[$var_content['day']])) {
				$import_calendar[$var_content['day']] = "";
			}
			$import_calendar[$var_content['day']] .= prepare_calendar_content($var_content, 0, "", $extra);
		}
	}

	return $import_calendar;
}

function show_calendar_halfhour($align="left", $timestamp) {
	$calendar_halfhour = "";
	$style = "";
	if ($align == "right") {
		$style = " style=\"text-align : right;\"";
	}
	for ($i=$GLOBALS['param']['calendar-hour_start']; $i <= $GLOBALS['param']['calendar-hour_stop']; $i++) {
		$timeencours = mktime($i, 0, 0, date("m", $timestamp), date("d", $timestamp), date("Y", $timestamp));
		$timeencours_halfhour = mktime($i, 30, 0, date("m", $timestamp), date("d", $timestamp), date("Y", $timestamp));

		if ($i < 10) {
			$i = "0".$i;
		}
		$calendar_halfhour .= "<div".$style." class=\"calendar-halfhour\">".calendar_link_day($i."h", $timeencours, array())."</div>\n";
		$calendar_halfhour .= "<div".$style." class=\"calendar-halfhour\">".calendar_link_day($i."h30", $timeencours_halfhour, array())."</div>\n";
	}

	return $calendar_halfhour;
}

function compare_day($a, $b) {
	return strnatcasecmp($a['daysort'], $b['daysort']);
}

function get_calendar_import($user_id, $project_id, $calendarstart, $calendarstop) {
	$user_calendar = array();

	if (($GLOBALS['param']['ext_time'] or $GLOBALS['param']['ext_holidays']) and $user_id) {
		$user_absences = get_simple_absences($user_id, $calendarstart, $calendarstop);
		$user_calendar = array_merge($user_calendar, $user_absences);
	}
	if ($GLOBALS['param']['ext_requests']) {
		$user_requests = get_simple_requests($user_id, $project_id, $calendarstart, $calendarstop);
		$user_calendar = array_merge($user_calendar, $user_requests);
	}
	if ($GLOBALS['param']['ext_events']) {
		$user_events = get_simple_events($user_id, $project_id, $calendarstart, $calendarstop);
		$user_calendar = array_merge($user_calendar, $user_events);
	}

	usort($user_calendar, "compare_day");

	return $user_calendar;
}

function prepare_calendar_name($name, $span="") {
	$span_content = "";
	if ($span) {
		$span_content = " - ".time_format($span);
	}

	return format_title($name).$span_content;
}

function prepare_calendar_link($name, $id, $day, $eventtype_name, $diff="") {
	$item = new Calendar_Item($id, $eventtype_name);
	$item->name = $name;
	$item->day = $day;

	return $item->link();
}

function prepare_calendar_navigation($timestamp, $calendarview="m", $content="", $width="300") {
	if ($calendarview == "d") {
		$calendar_table['navigation'][0] = array(
			'value' => $GLOBALS['array_week'][date("w", $timestamp)]." ".date("d", $timestamp)." ".$GLOBALS['array_month'][date("n", $timestamp)]." ".date("Y", $timestamp),
			'colspan' => 3,
			'style' => "width : ".$width."pt;"
		);
	} elseif ($calendarview == "w") {
		$calendar_table['navigation'][0] = array(
				'value' => $GLOBALS['txt_week']." ".(int)date("W", $timestamp)." - ".$GLOBALS['array_month'][date("n", $timestamp)]." ".date("Y", $timestamp),
				'colspan' => 7
			);
	} else {
		$calendar_table['navigation'][0] = array(
			'value' => $GLOBALS['array_month'][date("n", $timestamp)]." ".date("Y", $timestamp),
			'colspan' => 7
		);
	}

	return $calendar_table;
}

function js_init_blockTDlink() {
	return "<script type=\"text/javascript\">\nblockTDlink = false;\n</script>\n";
}

function calendar_link_day($string, $day, $attributes=array()) {
	$attribute = "";
	foreach ($attributes as $key => $value) {
		$attribute .= " ".$key."=\"".$value."\"";
	}

	$url = link_content("content=userevent.php&amp;day=".$day);

	return "<a href=\"".$url."\"".$attribute.">".format_name($string)."</a>";
}

function prepare_calendar($timestamp, $var_event="", $calendarview="m", $width="300") {
	extract(determine_calendar($timestamp, $calendarview));

	$class_calendarday = "";
	$calendar_event = array("","","");

	$calendarencours = $calendarstart;
	if ($calendarview != "d") {
		for ($i = 0; $i < 7; $i++) {
			if ($calendarview == "w") {
				$calendarmenu = mktime(0, 0, 0, date("m", $calendarstart), date("d", $calendarstart) + $i, date("Y", $calendarstart));
				$header_calendarday = calendar_link_day($GLOBALS['array_week'][$i + 1]." ".date("d", $calendarmenu), $calendarmenu, array());
				$calendar_table['jour'][$i] = $header_calendarday;
			} else {
				$calendar_table['jour'][$i] = $GLOBALS['array_week'][$i + 1];
			}
		}
	}
	if (is_array($var_event)) {
		if ($calendarview == "d") {
			$calendar_event = prepare_calendar_day_import($var_event);
		} elseif ($calendarview == "w") {
			$calendar_event = prepare_calendar_week_import($var_event);
		} else {
			$extra = " onMouseOver=\"javascript:blockTDlink = true;\" onMouseOut=\"javascript:blockTDlink = false;\"";
			$calendar_event = prepare_calendar_import($var_event, $extra);
		}
	}
	if ($calendarview == "d") {
		$calendar_height = get_calendar_day_height();
		$calendar_table['pre'][] = force_nonempty();
		$calendar_table['pre'][] = array('value' => $calendar_event[0], 'style' => "width : ".$width."pt;");
		$calendar_table['pre'][] = force_nonempty();
		$calendar_table['hour'][] = show_calendar_halfhour("", $calendarstart);
		$calendar_table['hour'][] = array('value' => $calendar_event[1], 'style' => "height : ".$calendar_height."pt; width : ".$width."pt;");
		$calendar_table['hour'][] = show_calendar_halfhour("right", $calendarstart);
		$calendar_table['post'][] = force_nonempty();
		$calendar_table['post'][] = array('value' => $calendar_event[2], 'style' => "width : ".$width."pt;");
		$calendar_table['post'][] = force_nonempty();
	} else {
		while ($calendarencours < $calendarstop) {
			$calendar_div = "";
			if (isset($calendar_event[$calendarencours])) {
				$calendar_div = $calendar_event[$calendarencours];
			}
			if ($calendarview == "w" or date("m", $calendarencours) == date("m", $timestamp)) {
				$class_calendarday = "clickable";
			} else {
				$class_calendarday = "outside clickable";
			}

			if ($calendarview == "w") {
				$calendar_table['morning'][date("w", $calendarstart)] = array('value' => $GLOBALS['txt_morning'], 'colspan' => 7);

				if (!isset($calendar_div[0])) {
					$calendar_div[0] = "";
				}

				$day = mktime($GLOBALS['param']['calendar-hour_start'], date("i", $calendarencours), 0, date("m", $calendarencours), date("d", $calendarencours), date("Y", $calendarencours));

				$date = date("w", $calendarencours);

				$calendar_table['forenoon'][$date]['value'] = $calendar_div[0];
				$calendar_table['forenoon'][$date]['data-day'] = $day;
				$calendar_table['forenoon'][$date]['data-href'] = link_content("content=userevent.php&amp;day=".$day);
				$calendar_table['forenoon'][$date]['class'] = $class_calendarday;
				
				$calendar_table['noon'][date("w", $calendarstart)] = array(
					'value' => $GLOBALS['txt_afternoon'],
					'colspan' => 7,
				);

				if (!isset($calendar_div[1])) {
					$calendar_div[1] = "";
				}

				$day = mktime($GLOBALS['param']['calendar-hour_middle'], date("i", $calendarencours), 0, date("m", $calendarencours), date("d", $calendarencours), date("Y", $calendarencours));

				$date = date("w", $calendarencours);

				$calendar_table['afternoon'][$date]['value'] = $calendar_div[1];
				$calendar_table['afternoon'][$date]['data-day'] = $day;
				$calendar_table['afternoon'][$date]['data-href'] = link_content("content=userevent.php&amp;day=".$day);
				$calendar_table['afternoon'][$date]['class'] = $class_calendarday;
				
			} else {
				$event = new Event(0);
				$event->day = $calendarencours;
				$class_header = "";
				if ($calendarview == "m" and date("m", $calendarencours) != date("m", $timestamp)) {
					$class_header = "outside";
				}
				$header_calendarday = $event->link(date("d", $calendarencours), array('class' => $class_header));

				$dateW = (int)date("W", $calendarencours);
				$datew = date("w", $calendarencours);
				$calendar_table[$dateW][$datew]['value'] = $header_calendarday.$calendar_div;
				$calendar_table[$dateW][$datew]['data-day'] = $calendarencours;
				$calendar_table[$dateW][$datew]['data-href'] = link_content("content=userevent.php&amp;day=".$calendarencours);
				$calendar_table[$dateW][$datew]['class'] = $class_calendarday;
			}
			$calendarencours = mktime(0, 0, 0, date("m", $calendarencours), date("d", $calendarencours) + 1, date("Y", $calendarencours));
		}
	}

	return $calendar_table;
}

function prepare_holidays($timestamp) {
	extract(determine_calendar($timestamp));

	$holidays_table = array();

	return $holidays_table;
}

function determine_calendar($timestamp, $calendarview="m") {
	if ($calendarview == "d") {
		$calendarstart = mktime(0, 0, 0, date("m", $timestamp), date("d", $timestamp), date("Y", $timestamp));
		$calendarstop = mktime(23, 59, 59, date("m", $timestamp), date("d", $timestamp), date("Y", $timestamp));
	} elseif ($calendarview == "w") {
		$calendarstart = mktime(0, 0, 0, date("m", $timestamp), date("d", $timestamp) - ((date("w", $timestamp) + 6) % 7), date("Y", $timestamp));
		$calendarstop = mktime(23, 59, 59, date("m", $calendarstart), date("d", $calendarstart) + 6, date("Y", $calendarstart));
	} else {
		$monthstart = mktime(0, 0, 0, date("m", $timestamp), 1, date("Y", $timestamp));
		$monthstop = mktime(0, 0, 0, date("m", $timestamp) + 1, 0, date("Y", $timestamp));
		$calendarstart = mktime(0, 0, 0, date("m", $timestamp), 2 - date("w", $monthstart), date("Y", $timestamp));
		$calendarstop = mktime(23, 59, 59, date("m", $timestamp) + 1, 0 + 7 - date("w", $monthstop), date("Y", $timestamp));
	}

	return array("calendarstart" => $calendarstart, "calendarstop" => $calendarstop);
}
