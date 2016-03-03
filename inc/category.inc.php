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
	
	function clean($post) {
		$vat_category = 0;
		$cleaned = array();
		if (!empty($post['name_new'])) {
			$cleaned[0] = array (
				'name' => $post['name_new'],
				'vat' => str_replace(",", ".", $post['vat_new']),
				'vat_category' => 0
			);
			if (isset($post['vat_category'])) {
				$cleaned[0]['vat_category'] = 1;
				$vat_category++;
			}
		}
		
		if (isset($post['category'])) {
			foreach ($post['category'] as $id => $values) {
				$cleaned[$id] = array (
					'name' => $values['name'],
					'vat' => str_replace(",", ".", $values['vat']),
					'vat_category' => 0
				);
				if (isset($values['vat_category'])) {
					$cleaned[$id]['vat_category'] = 1;
					$vat_category++;
				}
			}
		}
		if ($vat_category > 1) {
			return false;
		} else {
			return $cleaned;
		}
	}

	function form_add() {
		$input = new Html_Input("name_new", "", "text");
		$input_vat = new Html_Input("vat_new", "", "text");
		$checkbox_category_vat = new Html_Checkbox("vat_category", "", true);
		$form = "<div class=\"\"><center><h3>".__('add new category')."</h3><form name=\"\" id=\"form_modif_category\"  method=\"post\"  action=\"".link_content("content=categories.php")."\"><table>";
		$form .= "<tr><td>".ucfirst(__('name'))."</td><td>".$input->input()."</td></tr>";
		$form .= "<tr><td>".ucfirst(__('default VAT'))."</td><td>".$input_vat->input()."</td></tr>";
		$form .= "<tr><td>".ucfirst(__('VAT category'))."</td><td>".$checkbox_category_vat->input()."</td></tr>";
		$form .= "<tr><td><input type=\"submit\" value=\"".__('add')."\" /></td></tr>";
		$form .="</table></form>";
		return $form;

	}
	
	function show_form_modification()
	{
		$input_name = new Html_Input("category[".$this->id."][name]",$this->name);
		$input_default_vat = new Html_Input("category[".$this->id."][vat]", $this->vat);
		$input_vat_category = new Html_Checkbox("category[".$this->id."][vat_category]", 1, $this->vat_category);
		$input_submit = new Html_Input("category[".$this->id."][submit]",__('modify'),"submit");
		$action = new Html_Input("action","save","hidden");
		$form = "<div class=\"\"><center><h3>".__('modify a category')."</h3><form name=\"\" id=\"form_modif_category\"  method=\"post\"  action=\"".link_content("content=categories.php")."\"><table>";
		$form .= "<tr><td>".ucfirst(__('name'))." : </td><td>".$input_name->input()."</td></tr>";
		$form .= "<tr><td>".ucfirst(__('default VAT'))." : </td><td>".$input_default_vat->input()."</td></tr>";
		$form .= "<tr><td>".ucfirst(__('VAT category'))." : </td><td>".$input_vat_category->input()."</td></tr>";
		$form .= "<tr><td>".$action->input().$input_submit->input()."</td></tr>";
		$form .= "</table></form></center></div><br><br>";
		
		return $form;
	}
	
	
	function show_form_add() {
		$form = "<div class=\"duplicate show_acronym\">
					<span class=\"operation\"> <input class=\"add\" type=\"button\"  id=\"".$this->id."\"/> </span> <br />
					<span class=\"acronym\">".__('add')."</span>
				</div>";
		
		return $form;
	}
	
	function form_delete() {
			$input_hidden_id = new Html_Input("table_category_delete_id", $this->id);
			$input_hidden_action = new Html_Input("action", "delete");
			$submit = new Html_Input("category[".$this->id."][submit]", '',"submit");
			$submit->properties = array(
				'onclick' => "javascript:return confirm('".utf8_ucfirst(__('are you sure?'))."')"
			);
			
			$form = "<div class=\"delete show_acronym\">
						<form method=\"post\" name=\"table_category_form_delete\" action=\"\" enctype=\"multipart/form-data\">".
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
