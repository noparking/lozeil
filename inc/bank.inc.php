<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

class Bank extends Record {
	public $id = 0;
	public $name = "";
	public $selected = 0;
	public $iban = "" ;
	public $accountingcodes_id = 0;
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
	
	function load(array $key = array(), $table = "banks", $columns = null) {
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
			INSERT INTO ".$this->db->config['table_banks']."
			SET name = ".$this->db->quote($this->name).",
			selected = ".$this->selected.",
			iban = ".$this->db->quote($this->iban).",
			accountingcodes_id = ".(int)$this->accountingcodes_id.",
			timestamp = ".time()
		);
		$this->id = $result[2];
		$this->db->status($result[1], "i", __("bank"));
		return $this->id;
	}
	
	function update() {
		$result = $this->db->query("
			UPDATE ".$this->db->config['table_banks']."
			SET name = ".$this->db->quote($this->name).",
			selected = ".$this->selected.",
			iban = ".$this->db->quote($this->iban).",
			accountingcodes_id = ".(int)$this->accountingcodes_id.",
			timestamp = ".time()."
			WHERE id = ".(int)$this->id
		);
		$this->db->status($result[1], "u", __("bank"));
		return $this->id;
	}

	function delete() {
		$result = $this->db->query("
			DELETE FROM ".$this->db->config['table_banks']."
			WHERE id = ".(int)$this->id
		);
		$this->db->status($result[1], "d", __("bank"));
		return $this->id;
	}
	
	function clean($variables) {
		$cleaned = array();

		$cleaned['name'] = "";
		if (isset($variables['name'])) {
			$cleaned['name'] = strip_tags($variables['name']);
			$cleaned['name'] = trim(preg_replace('/\s+/', ' ', $cleaned['name']));			
		}

		$cleaned['iban'] = "";
		if (isset($variables['iban'])) {
			$cleaned['iban'] = strip_tags($variables['iban']);
			$cleaned['iban'] = trim(preg_replace('/\s+/', ' ', $cleaned['iban']));
		}

		$cleaned['accountingcodes_id'] = 0;
		if (isset($variables['accountingcodes_id'])) {
			$cleaned['accountingcodes_id'] = (int)$variables['accountingcodes_id'];
		}
		
		return $cleaned;
	}

	function is_deletable() {
		$result = $this->db->value_exists("SELECT count(1) FROM ".$this->db->config['table_writings'].
			" WHERE banks_id = '".$this->id."'"
		);
		return !$result;
	}

	function is_recently_modified(){
		if ($this->timestamp > (time() - 10)) {
			return true;
		}
		return false;
	}

	function form_add() {
		$input = new Html_Input("name_new");
		$input_iban = new Html_Input("iban_new");
		$input_accountingcodes_id = new Html_Input_Ajax("accountingcodes_id_new", link_content("content=writings.ajax.php"));
		$checkbox = new Html_Checkbox("selected new", "", true);
		$form = "<div class=\"\"><center><h3>".__('add new bank')."</h3><form name=\"\" id=\"form_modif_source\"  method=\"post\"  action=\"".link_content("content=banks.php")."\"><table>";
		$form .= "<tr><td>".ucfirst(__('name'))."</td><td>".$input->input()."</td></tr>";
		$form .= "<tr><td>".ucfirst(__('iban'))."</td><td>".$input_iban->input()."</td></tr>";
		$form .= "<tr><td>".ucfirst(__('accounting code'))."</td><td>".$input_accountingcodes_id->input()."</td></tr>";
		$form .= "<tr><td>".ucfirst(__('use'))."</td><td>".$checkbox->input()."</td></tr>";
		$form .= "<tr><td><input type=\"submit\" value=\"".__('add')."\" /></td></tr>";
		$form .="</table></form>";
	
		return $form;
	}
	
	function show_form_modification() {
		$accountingcode = new Accounting_Code();
		$accountingcode_id = array();
		if ($accountingcode->load(array('id' => $this->accountingcodes_id))) {
			$accountingcode_id[] = $accountingcode->fullname();
		}
		
		$input_name = new Html_Input("banks[".$this->id."][name]",$this->name);
		$input_iban = new Html_Input("banks[".$this->id."][iban]",$this->iban);
		$input_accountingcodes_id = new Html_Input_Ajax("banks[".$this->id."][accountingcodes_id]", link_content("content=writings.ajax.php"), $accountingcode_id);
		$input_use = new Html_Checkbox("banks[".$this->id."][selected]", 1, $this->selected);
		$input_submit = new Html_Input("banks[".$this->id."][submit]",__('modify'),"submit");
		$action = new Html_Input("action","save","hidden");
		$form = "<div class=\"\"><center><h3>".__('modify a bank')."</h3><form name=\"\" id=\"form_modif_source\"  method=\"post\"  action=\"".link_content("content=banks.php")."\"><table>";
		$form .= "<tr><td>".ucfirst(__('name'))." : </td><td>".$input_name->input()."</td></tr>";
		$form .= "<tr><td>".ucfirst(__('iban'))." : </td><td>".$input_iban->input()."</td></tr>";
		$form .= "<tr><td>".ucfirst(__('accounting code'))."</td><td>".$input_accountingcodes_id->input()."</td></tr>";
		$form .= "<tr><td>".ucfirst(__('use'))." : </td><td>".$input_use->input()."</td></tr>";
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
			$input_hidden_id = new Html_Input("table_bank_delete_id", $this->id);
			$input_hidden_action = new Html_Input("action", "delete");
			$submit = new Html_Input("banks[".$this->id."][submit]", '',"submit");
			$submit->properties = array(
				'onclick' => "javascript:return confirm('".utf8_ucfirst(__('are you sure?'))."')"
			);
			
			$form = "<div class=\"delete show_acronym\">
						<form method=\"post\" name=\"table_bank_form_delete\" action=\"\" enctype=\"multipart/form-data\">".
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
}
