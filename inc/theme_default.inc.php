<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Theme_Default {
	function html_top() {
		return "<!DOCTYPE HTML>
			<html>";
	}
	
	function head() {
		return "<head>
			<title>".($GLOBALS['config']['title'] == '' ? '' : $GLOBALS['config']['title']." : ").$GLOBALS['config']['name']."</title>
			<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />".
			$this->css().
			$this->js_files()."
			<link href=\"".$GLOBALS['config']['layout_mediaserver']."medias/images/favicon.ico\" type=\"imgage/png\" rel=\"shortcut icon\" />
		</head>";
	}
	
	function body_top($location, $content = "") {
		return "<body class=\"".Format::body_class($location)." ".Format::body_class($content)."\">";
	}
	
	function css_files() {
		$css_files[] = "medias/css/menu.css";
		$css_files[] = "medias/css/styles.css";
		
		if (isset($GLOBALS['content']) and $GLOBALS['content'] == "writings.php") {
			$css_files[] = "medias/css/dropzone.css";
		}
		
		return $css_files;
	}

	function css() {
		$files = array_merge($this->css_files(), get_plugins_files("css"));
		return $this->show_css_files($files);
	}
	
	function show_css_files($css_files) {
		
		$html = "";
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
				$html .= "<link rel=\"stylesheet\" type=\"text/css\"".$media_css_file." href=\"".$css_file."\" />\n";
				$media_css_file = "";
			}
		}

		return $html;
	}
	
	function js_files() {
		$js_files[] = "medias/js/jquery.js";
		$js_files[] = "medias/js/spin.js";
		$js_files[] = "medias/js/draganddrop.jquery.js";
		$js_files[] = "medias/js/calendar.js";
		$js_files[] = "medias/js/colorbox.jquery.js";
		$js_files[] = "medias/js/common.js";
		$js_files[] = "medias/js/common.jquery.js";
		$js_files[] = "medias/js/d3.js";
		$js_files[] = "medias/js/cubism.js";
		$js_files[] = "medias/js/nagging-menu.js";
		if (isset($GLOBALS['content'])) {
		  if ($GLOBALS['content'] == "writings.php") {
			$js_files[] = "medias/js/dropzone.js";
			$js_files[] = "medias/js/writings.jquery.js";
			$js_files[] = "medias/js/timeline.jquery.js";
		  }
		  else if ($GLOBALS['content'] == "followupwritings.php") {
			$js_files[] = "medias/js/followupwritings.jquery.js";
		  }
		  else if ($GLOBALS['content'] == "writingssimulations.php") {
			$js_files[] = "medias/js/writingssimulations.jquery.js";
			$js_files[] = "medias/js/timeline.jquery.js";
		  }
		  else if ($GLOBALS['content'] == "accountingplan.php") {
			$js_files[] = "medias/js/accountingplan.jquery.js";
		  }
		  else if ($GLOBALS['content'] == "categories.php") {
			$js_files[] = "medias/js/categories.jquery.js";
		  }
		  else if ($GLOBALS['content'] == "sources.php") {
			$js_files[] = "medias/js/sources.jquery.js";
		  }
		  else if ($GLOBALS['content'] == "banks.php") {
			$js_files[] = "medias/js/banks.jquery.js";
		  }
		  else if ($GLOBALS['content'] == "users.php") {
			$js_files[] = "medias/js/users.jquery.js";
		  }
		  else if ($GLOBALS['content'] == "balancesdetail.php") {
			$js_files[] = "medias/js/balancesdetail.jquery.js";
		  }
		  else if ($GLOBALS['content'] == "balancescustom.php") {
		  	$js_files[] = "medias/js/dropzone.js";
			$js_files[] = "medias/js/balancescustom.jquery.js";
		  }
		  else if ($GLOBALS['content'] == "activities.php") {
			  $js_files[] = "medias/js/activities.jquery.js";
		  }
		  else if ($GLOBALS['content'] == "account.php") {
			  $js_files[] = "medias/js/accounts.jquery.js";
		  }
		  else if ($GLOBALS['content'] == "writingsimport.php") {
			  $js_files[] = "medias/js/writingsimport.jquery.js";
		  }
		  else if ($GLOBALS['content'] == "balances.php") {
			  $js_files[] = "medias/js/balances.jquery.js";
		  }
		  else if ($GLOBALS['content'] == "models.php") {
		  	  $js_files[] = "medias/js/models.jquery.js";
		  }
		}
		
		$html = "";

		if (is_array($js_files)) {
			foreach ($js_files as $js_file) {
				$js_file = $GLOBALS['config']['layout_mediaserver'].$js_file."?v=".urlencode($GLOBALS['config']['version']);
				$html .= "<script src=\"".$js_file."\" language=\"JavaScript\" type=\"text/javascript\"></script>\n";
			}
		}

		return $html;
	}
	
	function show_status() {
		$menu = "<div class=\"layout_status\">";
		$menu .= $this->status();
		$menu .= "</div>";
	
		return $menu;
	}
		
	function status() {
		return show_status();
	}
	
	function content_top() {
		return "<div class=\"content\">";
	}

	function content_bottom() {
		return "</div>
		<div class=\"content_copyright\">
		<div>
		&copy; No Parking 2013 - 2014 v.".$GLOBALS['config']['version']."
		</div>
		</div>
		<div class=\"loading\">
		</div>";
	}
	
	function body_bottom() {
		return "</body>";
	}
	
	function html_bottom() {
		return "</html>";
	}
}
