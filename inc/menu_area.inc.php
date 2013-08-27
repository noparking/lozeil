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

	function __construct($header = "") {
		$this->header = $header;
		$this->handle = "<span id=\"menu_more\">".utf8_ucfirst(__("more"))."</span><span id=\"menu_less\">".utf8_ucfirst(__('less'))."</span>";
		$this->actions = array(
			"lines" => array(
					__("consulter le tableau de trésorerie") => "lines.php"
				),
			"sales" => array(
					__("faire le suivi des factures d'achat") => "lines.php"
				),
			"buying" => array(
					__("faire le suivi des factures de ventes") => "lines.php"
				),
			"import" => array(
					__("importer le journal de banque") => "lines.php"
				),
			"export" => array(
					__("effectuer un export") => "lines.php"
				),
		);
		
		$this->img_src = $GLOBALS['config']['layout_mediaserver'].$this->img_src;
	}

	function show() {
		$grid = array();
		$content = "<div class=\"menu\"><div class=\"header\">";
		if (!empty($this->header)) {
			$content .= "<div class=\"summary\">".$this->header."</div>";
		}
		$content .= "<div class=\"logo\">";
		if ($this->img_src) {
			$content .= "<img class=\"hand\"".(!$this->img_width ? "" : " width=\"".$this->img_width."\"").(!$this->img_height ? "" : " height=\"".$this->img_height."\"")." src=\"".$this->img_src."\">";
		}
		$content .= "</div></div>";
		
		$content .= "<div class=\"actions\">";
		foreach ($this->actions as $key => $link) {
			$nom = array_keys($link);
			$link = array_values($link);
			if ($key == "import") {
				$data = new Writings_Data_File();
				$grid['leaves'][$nom[0]]['value'] = $data->form_import($nom[0]);
			} else {
				$grid['leaves'][$nom[0]]['value'] = "<a href=\"".link_content("content=".$link[0])."\">".utf8_ucfirst($nom[0])."</a>";
			}
		}

		$list = new Html_List($grid);
		$content .= $list->show();
		$content .= "</div>";

		if (!empty($this->handle)) {
			$content .= "<div class=\"handle\"><div class=\"more hide\">".$this->handle."</div></div>";
		}
		
		$content .= "</div>";
		return $content;
	}		
}