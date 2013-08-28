<?php
/*
	lozeil
	$Author:  $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

class Menu_Area {
	public $header = "";
	public $actions = array();
	public $handle = "";
	public $img_width = 65.875;
	public $img_height = 17;
	public $img_src = "medias/images/logo.png";

	function __construct() {
		$this->img_src = $GLOBALS['config']['layout_mediaserver'].$this->img_src;
	}

	function show() {
		$content = "<div class=\"menu\"><div class=\"header\">";
		if (!empty($this->header)) {
			$content .= "<div class=\"balance_summary\">".$this->header."</div>";
		}
		if (!empty($this->img_src)) {
			$content .= "<div class=\"logo\"><img ".(!$this->img_width ? "" : " width=\"".$this->img_width."\"").(!$this->img_height ? "" : " height=\"".$this->img_height."\"")." src=\"".$this->img_src."\"></div>";
		}
		$content .= "</div>";
		
		$content .= "<div class=\"actions\">";
		if (!empty($this->actions)) {
			$content .= $this->actions;
		}
		$content .= "</div>";

		if (!empty($this->handle)) {
			$content .= "<div class=\"handle\"><div class=\"more hide\">".$this->handle."</div></div>";
		}
		
		$content .= "</div>";
		return $content;
	}
	
	function prepare_navigation($content="") {
		if (preg_match("/(^|_)lines/", $content)) {
			$writings = new Writings();
			$writings->select();
			$this->header = $writings->show_balance_on_current_date();
			
			$data = new Writings_Data_File();
			$grid = array();
			
			$grid['leaves'][0]['value'] = Html_tag::a(link_content("content=lines.php"), utf8_ucfirst(__("consulter le tableau de trésorerie")));
			$grid['leaves'][1]['value'] = utf8_ucfirst(__("faire le suivi des factures d'achat (non implémenté)"));
			$grid['leaves'][2]['value'] = utf8_ucfirst(__("faire le suivi des factures de ventes (non implémenté)"));
			$grid['leaves'][3]['value'] = $data->form_import();
			$grid['leaves'][4]['value'] = utf8_ucfirst(__("effectuer un export (non implémenté)"));			
			
			$list = new Html_List($grid);
			$this->actions = $list->show();
			
			$this->handle = "<span id=\"menu_more\">".utf8_ucfirst(__("more"))."</span><span id=\"menu_less\">".utf8_ucfirst(__('less'))."</span>";
		}
	}
}