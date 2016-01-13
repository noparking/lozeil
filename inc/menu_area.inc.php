<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

class Menu_Area {
	public $header = "";
	public $actions = array();
	public $handle = "";
	public $img_width = 65.875;
	public $img_height = 17;
	public $img_src = "medias/images/logo.png";

	function __construct() {
		$this->img_src = $GLOBALS['config']['layout_mediaserver'].$this->img_src;
		
		$writings = new Writings();
		$writings->filter_with(array('stop' => time()));
		$writings->select_columns('amount_inc_vat');
		$writings->select();
		$this->header = $writings->display_balance_on_current_date();
		
		$this->handle = "<span id=\"menu_handle_hide\">".utf8_ucfirst(__("more"))."</span><span id=\"menu_handle_show\">".utf8_ucfirst(__('less'))."</span>";
	}
	
	function show() {
		$liste_menu = $this->grid_navigation();
		$plug = $this->grid_other_actions();
		foreach($plug as $p) {
			$liste_menu[] = $p;
		}
		
		$account = isset($GLOBALS['config']['account']) ? $GLOBALS['config']['account'] : "";
		$account = isset($GLOBALS['authenticated_user']) ? $GLOBALS['authenticated_user']->name : $account;

		$layout_logged_in_as = "<div id=\"layout_logged_in_as\"><p>".__('logged in as').": <strong>".$account."</strong> | ".Html_Tag::a(link_content("content=logout.php"),__('log out'))."</p></div>";
		$level0 = "<header><div id=\"menu\" class=\"default\"><div class=\"level_0 clearfix\"><ul>";
		$level1 = "<div class=\"level_1\" > <ul style=\"width: 1000px;\">";
		$page = isset($_GET['content']) && !empty($_GET['content'])?($_GET['content']):"users.php";
		foreach($liste_menu  as $cat) {
			$title = $cat['title'];
			$souscat = $cat['categorie'];
			
			if (in_array($page , $souscat) ) {
				$level0 .= "<li class=\"selected\"><a href=\"".link_content("content=".$this->firstinarray($souscat))." \" >".$title."</a></li>";	
				foreach($souscat as $nom => $lien)
				{
					if ($page == $lien )
						$level1 .= "<li class=\"selected\"><a href=\"".link_content("content=".$lien)."\" >".$nom."</a></li>";
					else
						$level1 .= "<li><a href=\"".link_content("content=".$lien)."\" >".$nom."</a></li>";
				}
				$level1 .= "</ul></div></div></header>";
			}
			else {
				$level0 .= "<li><a href=\"".link_content("content=".$this->firstinarray($souscat))." \" >".$title."</a></li>";
			}
		}

		$level0 .= "</ul><a href=\"index.php\"><img  class=\"logo_menu_image\" width=\"128\" height=\"30\" src=\"".$GLOBALS['config']['layout_mediaserver']."medias/images/logo-menu.png\" alt=\"Lozeil - logiciel web de gestion de trésorerie\"></img></a></div>";
		return $layout_logged_in_as.$level0.$level1;
	}
	
	function firstinarray($array) {
		foreach($array as $value) {
			return $value;
		}
	}
	
	function grid_other_actions() {
		$grid = array();				
		return $grid;
	}
	
	function show_grid_other_actions() {
		$list = new Html_List($this->grid_other_actions());
		return $list->show();
	}
	
	function grid_navigation() {
		(isset($GLOBALS['param']['ext_treasury']) && $GLOBALS['param']['ext_treasury'] == "1") ? $ext_treasury = true : $ext_treasury = false;
		(isset($GLOBALS['param']['ext_simulation']) && $GLOBALS['param']['ext_simulation'] == "1") ? $ext_simulation = true : $ext_simulation = false;
		(isset($GLOBALS['param']['ext_account_custom_result']) && $GLOBALS['param']['ext_account_custom_result'] == "1") ? $ext_account_custom_result = true : $ext_account_custom_result = false;

		$treasury = array(
			ucwords(__("detail")) => "writings.php",
			ucwords(__("import")) => "writingsimport.php",
			ucwords(__("export")) => "writingsexport.php",
			ucwords(__("stats")) => "followupwritings.php"
		);

		$simulation = array(
			ucwords(__("detail")) => "writingssimulations.php",
		);

		$account_custom_result = array(
			ucwords(__("import")) => "balancesimport.php",
			ucwords(__("balance")) => "balances.php",
			ucwords(__("income statement")) => "balancesdetail.php",
			ucwords(__("customisation")) => "balancescustom.php",
			ucwords(__("export")) => "balancesexport.php"
		);

		$configuration = array(
			ucwords(__("account")) => "account.php",
			ucwords(__("users")) => "users.php",
			ucwords(__("categories")) => "categories.php",
			ucwords(__("banks")) => "banks.php",
			ucwords(__("sources")) => "sources.php",
		);
		
		if(isset($_SESSION['accountant_view']) and $_SESSION['accountant_view'] == "1" ) {
			$configuration[ucwords(__("activities"))] = "activities.php";
			$configuration[ucwords(__("models"))] = "models.php";
			$configuration[ucwords(__("accounting plan"))] = "accountingplan.php";
		}

		$grid = array();
		if ($ext_treasury === true) {
			$grid[] = array('title' => ucfirst(__("treasury")), 'categorie' => $treasury);
		}
		if ($ext_simulation === true) {
			$grid[] = array('title' => ucfirst(__("simulation")), 'categorie' => $simulation);
		}
		if ($ext_account_custom_result === true) {
			$grid[] = array('title' => ucfirst(__("account custom result")), 'categorie' => $account_custom_result);
		}
		$grid[] = array('title' => ucfirst(__("configuration")), 'categorie' => $configuration);
		return $grid;
	}
	
	function form_calculate_vat() {
		$date = new Html_Input_Date("vat_date", determine_vat_date());
		$date->img_src = "medias/images/link_calendar_white.png";
		$submit = new Html_Input("submit_calculate_vat", __('ok'), "submit");
		$form = "<form method=\"post\" name=\"menu_actions_other\" action=\"".link_content("content=writings.php")."\" enctype=\"multipart/form-data\">".
					$date->item("")." ".$submit->input()
				."</form>";
		return $form;
	}
}
