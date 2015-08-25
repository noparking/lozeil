<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

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
	
	function save() {
		if (is_numeric($this->id) and $this->id != 0) {
			$this->id = $this->update();

		} else {
			$this->id = $this->insert();
		}

		return $this->id;
	}
	
	function insert() {
		$result = $this->db->id("
			INSERT INTO ".$this->db->config['table_sources']."
			SET name = ".$this->db->quote($this->name).",
			timestamp = ".time()
		);
		$this->id = $result[2];
		$this->db->status($result[1], "i", __('source'));

		return $this->id;
	}
	
	function update() {
		$result = $this->db->query("UPDATE ".$this->db->config['table_sources'].
			" SET name = ".$this->db->quote($this->name).",
			timestamp = ".time()."
			WHERE id = ".(int)$this->id
		);
		$this->db->status($result[1], "u", __('source'));

		return $this->id;
	}

	function delete() {
		$result = $this->db->query("DELETE FROM ".$this->db->config['table_sources'].
			" WHERE id = '".$this->id."'"
		);
		$this->db->status($result[1], "d", __('source'));

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
		$result = $this->db->value_exists("SELECT count(1) FROM ".$this->db->config['table_writings'].
			" WHERE sources_id = '".$this->id."'"
		);
		return !$result;
	}

	function form_add() {
		$input = new Html_Input("name_new");
		$input_vat = new Html_Input("vat_new");
		$checkbox_source_vat = new Html_Checkbox("vat_source", 1);
		$form = "<div class=\"\"><center><h3>".__('add new source')."</h3><form name=\"\" id=\"form_modif_source\"  method=\"post\"  action=\"".link_content("content=sources.php")."\"><table>";
		$form .= "<tr><td>".ucfirst(__('name'))."</td><td>".$input->input()."</td></tr>";
		$form .= "<tr><td><input type=\"submit\" value=\"".__('add')."\" /></td></tr>";
		$form .="</table></form>";
		return $form;

	}
	
	function show_form_modification()
	{
		$input_name = new Html_Input("sources[".$this->id."][name]",$this->name);
		$input_submit = new Html_Input("sources[".$this->id."][submit]",__('modify'),"submit");
		$action = new Html_Input("action","save","hidden");
		$form = "<div class=\"\"><center><h3>".__('modify a source')."</h3><form name=\"\" id=\"form_modif_source\"  method=\"post\"  action=\"".link_content("content=sources.php")."\"><table>";
		$form .= "<tr><td>".ucfirst(__('name'))." : </td><td>".$input_name->input()."</td></tr>";
		$form .= "<tr><td>".$action->input().$input_submit->input()."</td></tr>";
		$form .= "</table></form></center></div><br><br>";
		
		return $form;
	}
	
	
	function show_form_add() {
		$form = "<div class=\"duplicate show_acronym\">
						<span class=\"operation\"> <input class=\"add\" type=\"button\" id=\"".$this->id."\"/> </span> <br />
					<span class=\"acronym\">".__('add')."</span>
				</div>";
		
		return $form;
	}
	
	function form_delete() {
			$input_hidden_id = new Html_Input("table_source_delete_id", $this->id);
			$input_hidden_action = new Html_Input("action", "delete");
			$submit = new Html_Input("sources[".$this->id."][submit]", '',"submit");
			$submit->properties = array(
				'onclick' => "javascript:return confirm('".utf8_ucfirst(__('are you sure?'))."')"
			);
			
			$form = "<div class=\"delete show_acronym\">
						<form method=\"post\" name=\"table_source_form_delete\" action=\"\" enctype=\"multipart/form-data\">".
							$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input()."
						</form>
						<span class=\"acronym\">".__('delete')."</span>
					</div>";
			
			return $form;
	}
	

	function show_form_modify() {
			$form = "<div class=\"modify show_acronym\">
						<span class=\"operation\"> <input class=\"modif\" type=\"button\" id=\"".$this->id."\"/> </span> <br />
						<span class=\"acronym\">".__('modify')."</span>
					</div>";
			
		return $form;
	}
	
	function show_operations() {
		return $this->show_form_modify().$this->form_delete();
	}

	function is_recently_modified(){
		if ($this->timestamp > (time() - 10)) {
			return true;
		}
		return false;
	}
}
