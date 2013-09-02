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
		$content = "<div class=\"menu\"><div class=\"menu_header\">";
		if (!empty($this->header)) {
			$content .= "<div id=\"menu_header_balance\">".$this->header."</div>";
		}
		if (!empty($this->img_src)) {
			$content .= "<div id=\"menu_header_logo\"><img ".(!$this->img_width ? "" : " width=\"".$this->img_width."\"").(!$this->img_height ? "" : " height=\"".$this->img_height."\"")." src=\"".$this->img_src."\"></div>";
		}
		$content .= "</div>";
		
		$content .= "<div class=\"menu_actions\">";
		if (!empty($this->actions)) {
			$content .= $this->actions;
		}
		$content .= "</div>";

		if (!empty($this->handle)) {
			$content .= "<div class=\"menu_handle hide\">".$this->handle."</div>";
		}
		
		$content .= "</div>";
		return $content;
	}
	
	function prepare_navigation($content = "") {
		if (preg_match("/writings.php/", $content)) {
			$writings = new Writings();
			$writings->filter_with(array('stop' => time()));
			$writings->select_columns('amount_inc_vat');
			$writings->select();
			$this->header = $writings->show_balance_on_current_date();
			
			$data = new Writings_Data_File();
			$grid = array(
				'leaves' => array (
					0 => array(
						'value' => Html_tag::a(link_content("content=writings.php"), utf8_ucfirst(__("consult balance sheet")))
					),
					1 => array(
						'value' => $data->form_import()
					)
				)
			);	
			
			$list = new Html_List($grid);
			$this->actions = $list->show();
			
			$this->handle = "<span id=\"menu_handle_hide\">".utf8_ucfirst(__("more"))."</span><span id=\"menu_handle_show\">".utf8_ucfirst(__('less'))."</span>";
		}
	}
}
