<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

class Category extends Record {
	public $id = 0;
	public $name = "";
	public $vat = 0;
	public $vat_category = 0;
	public $timestamp = 0;
	
	function __construct($id = 0, db $db = null) {
		parent::__construct($db);
		$this->id = $id;
	}

	function db($db) {
		if ($db instanceof db) {
			$this->db = $db;
		}
	}
	
	function load(array $key = array(), $table = "categories", $columns = null) {
		return parent::load($key, $table, $columns);
	}
	
	function ask_before_delete() {
		if ((int)$this->id > 0) {
			$id = new Html_Input("category[id]", (int)$this->id, "hidden");
			$delete = new Html_Input("submit", __('delete'), "submit");
			
			$list = array(
				'submit' => array(
					'class' => "itemsform-submit",
					'value' => $delete->input(),
				),
			);

			$form = "<h3>".__("Delete category %s", array($this->name))."</h3>";
			$form .= "<form method=\"post\" action=\"\">";
			$form .= $id->input_hidden();
			$items = new Html_List(array('leaves' => $list, 'class' => "itemsform"));
			$form .= $items->show();
			$form .= "</form>";
			
			return $form;
		} else {
			return false;
		}
	}

	function edit() {
		$id = new Html_Input("category[id]", (int)$this->id, "hidden");
		$name = new Html_Input("category[name]", $this->name);
		$vat = new Html_Input("category[vat]", $this->vat);
		$vat_category = new Html_Checkbox("category[vat_category]", 1, $this->vat_category);
		
		$save = new Html_Input("submit", __('save'), "submit");
		
		$list = array(
			'name' => array(
				'class' => "itemsform-head itemsform-bold clearfix",
				'value' => $name->item(__("name")),
			),
			'vat' => array(
				'class' => "clearfix",
				'value' => $vat->item(__("default VAT")),
			),
			'vat_category' => array(
				'class' => "itemsform-head itemsform-bold clearfix",
				'value' => $vat_category->item(__("VAT category")),
			),
			'submit' => array(
				'class' => "itemsform-submit",
				'value' => $save->input(),
			),
		);
		
		if ((int)$this->id > 0) {
			$form = "<h3>".__("Edit category %s", array($this->name))."</h3>";
		} else {
			$form = "<h3>".__("Add new category")."</h3>";
		}
		$form .= "<form method=\"post\" action=\"\">";
		$form .= $id->input_hidden();
		$items = new Html_List(array('leaves' => $list, 'class' => "itemsform"));
		$form .= $items->show();
		$form .= "</form>";
		
		return $form;
	}
	
	function links_to_operations() {
		return $this->link_to_edit().$this->link_to_delete();
	}

	function link_to_edit() {
		if ((int)$this->id > 0) {
			return Html_Tag::a(link_content("content=category.edit.php&id=".$this->id), __("Edit category %s", array($this->name)), array('class' => "ajax"));
		} else {
			return Html_Tag::a(link_content("content=category.edit.php&id"), __("Add new category"), array('class' => "ajax"));
		}
	}

	function link_to_delete() {
		if ((int)$this->id > 0) {
			return Html_Tag::a(link_content("content=category.delete.php&id=".$this->id), __("Delete"), array('class' => "ajax"));
		} else {
			return "";
		}
	}

	function save() {
		if (is_numeric($this->id) and $this->id != 0) {
			$this->id = $this->update();
		} else {
			$this->id = $this->insert();
		}
		return $this->id;
	}
	
	function insert() {
		$result = $this->db->query_with_id("
			INSERT INTO ".$this->db->config['table_categories']."
			SET name = ".$this->db->quote($this->name).",
			vat = ".(float)$this->vat.",
			vat_category = ".(int)$this->vat_category.",
			timestamp = ".time()
		);
		$this->id = $result[2];
		$this->db->status($result[1], "i", __('category'));
		return $this->id;
	}
	
	function update() {
		$result = $this->db->query("
			UPDATE ".$this->db->config['table_categories']." 
			SET name = ".$this->db->quote($this->name).", 
			vat = ".(float)$this->vat.",
			vat_category = ".(float)$this->vat_category.",
			timestamp = ".time()."
			WHERE id = ".(int)$this->id
		);
		$this->db->status($result[1], "u", __('category'));
		return $this->id;
	}


	function delete() {
		if (is_numeric($this->id) and $this->id != 0) {
			$result = $this->db->query("DELETE FROM ".$this->db->config['table_categories'].
				" WHERE id = '".$this->id."'"
			);
			$this->db->status($result[1], "d", __('category'));
		}
		return $this->id;
	}
	
	function clean_str($variables) {
		$cleaned = array();

		if (isset($variables['name'])) {
			$cleaned['name'] = strip_tags($variables['name']);
			$cleaned['name'] = trim(preg_replace('/\s+/', ' ', $cleaned['name']));			
		}

		if (isset($variables['vat'])) {
			$cleaned['vat'] = strip_tags($variables['vat']);
			$cleaned['vat'] = trim(preg_replace('/\s+/', ' ', $cleaned['vat']));
		}

		return $cleaned;
	}

	function is_deletable() {
		$result = $this->db->value_exists("SELECT count(1) FROM ".$this->db->config['table_writings'].
			" WHERE categories_id = '".$this->id."'"
		);
		return !$result;
	}
	
	function is_in_use() {
		$result = $this->db->value_exists("SELECT count(1) FROM ".$this->db->config['table_writings'].
			" WHERE categories_id = '".$this->id."'"
		);
		return $result;
	}

	function clean($variables) {
		$cleaned = array();

		if (isset($variables['name'])) {
			$cleaned['name'] = strip_tags($variables['name']);
			$cleaned['name'] = trim(preg_replace('/\s+/', ' ', $cleaned['name']));			
		}
		if (isset($variables['vat'])) {
			$cleaned['vat'] = to_float($variables['vat']);
		}
		$cleaned['vat_category'] = isset($variables['vat_category']) ? (int)$variables['vat_category'] : 0;

		return $cleaned;
	}

	function is_recently_modified(){
		if ($this->timestamp > (time() - 10)) {
			return true;
		}
		return false;
	}
}
