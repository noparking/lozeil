<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

class Source extends Record {
	public $id = 0;
	public $name = "";
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
	
	function load(array $key = array(), $table = "sources", $columns = null) {
		return parent::load($key, $table, $columns);
	}
	
	function ask_before_delete() {
		if ((int)$this->id > 0) {
			$id = new Html_Input("source[id]", (int)$this->id, "hidden");
			$delete = new Html_Input("submit", __('delete'), "submit");
			
			$list = array(
				'submit' => array(
					'class' => "itemsform-submit",
					'value' => $delete->input(),
				),
			);

			$form = "<h3>".__("Delete source %s", array($this->name))."</h3>";
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
		$id = new Html_Input("source[id]", (int)$this->id, "hidden");
		$name = new Html_Input("source[name]", $this->name);
		$save = new Html_Input("submit", __('save'), "submit");
		
		$list = array(
			'name' => array(
				'class' => "itemsform-head itemsform-bold clearfix",
				'value' => $name->item(__("name")),
			),
			'submit' => array(
				'class' => "itemsform-submit",
				'value' => $save->input(),
			),
		);
		
		if ((int)$this->id > 0) {
			$form = "<h3>".__("Edit source %s", array($this->name))."</h3>";
		} else {
			$form = "<h3>".__("Add new source")."</h3>";
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
			return Html_Tag::a(link_content("content=source.edit.php&id=".$this->id), __("Edit source %s", array($this->name)), array('class' => "ajax edit"));
		} else {
			return Html_Tag::a(link_content("content=source.edit.php&id"), __("Add new source"), array('class' => "ajax edit"));
		}
	}

	function link_to_delete() {
		if ((int)$this->id > 0) {
			return Html_Tag::a(link_content("content=source.delete.php&id=".$this->id), __("Delete"), array('class' => "ajax delete"));
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
			INSERT INTO ".$this->db->config['table_sources']."
			SET name = ".$this->db->quote($this->name).",
			timestamp = ".time()
		);
		$this->id = $result[2];
		$this->db->status($result[1], "i", __("source"));
		return $this->id;
	}
	
	function update() {
		$result = $this->db->query("
			UPDATE ".$this->db->config['table_sources']."
			SET name = ".$this->db->quote($this->name).",
			timestamp = ".time()."
			WHERE id = ".(int)$this->id
		);
		$this->db->status($result[1], "u", __("source"));
		return $this->id;
	}

	function delete() {
		$result = $this->db->query("
			DELETE FROM ".$this->db->config['table_sources']."
			WHERE id = ".(int)$this->id
		);
		$this->db->status($result[1], "d", __("source"));
		return $this->id;
	}
	
	function clean($variables) {
		$cleaned = array();

		if (isset($variables['name'])) {
			$cleaned['name'] = strip_tags($variables['name']);
			$cleaned['name'] = trim(preg_replace('/\s+/', ' ', $cleaned['name']));			
		}

		return $cleaned;
	}

	function is_deletable() {
		$result = $this->db->value_exists("
			SELECT count(1) FROM ".$this->db->config['table_writings']."
			WHERE sources_id = ".(int)$this->id
		);
		return !$result;
	}
	
	function is_recently_modified(){
		if ($this->timestamp > (time() - 10)) {
			return true;
		}
		return false;
	}
}
