<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class Accounting_Code extends Record {
	public $id = 0;
	public $name = "";
	public $number = "";
		
	function __construct($id = 0, db $db = null) {
		parent::__construct($db);
		$this->id = $id;
	}

	function db($db) {
		if ($db instanceof db) {
			$this->db = $db;
		}
	}
	
	function load(array $key = array(), $table = "accountingcodes", $columns = null) {
		if (isset($key["number"])) {
			$key["number"] = $this->adjust_number($key["number"]);
		}
		return parent::load($key, $table, $columns);
	}
	
	function save() {
		$this->number = $this->adjust_number();
		if (is_numeric($this->id) and $this->id != 0) {
			$this->id = $this->update();

		} else {
			$this->id = $this->insert();
		}

		return $this->id;
	}
	
	function insert() {
		$result = $this->db->id("
			INSERT INTO ".$this->db->config['table_accountingcodes']."
			SET name = ".$this->db->quote($this->name)." ,
			number = ".$this->db->quote($this->number)
		);
		$this->id = $result[2];
		$reportings_id = $this->get_default_reporting();
		if ($reportings_id > 0 and $this->id > 0) {
			$affectation = new Accounting_Code_Affectation();
			$affectation->reportings_id = $reportings_id;
			$affectation->accountingcodes_id = $this->id;
			$affectation->save();
		}
		
		$this->db->status($result[1], "i", __("accounting code"));
		return $this->id;
	}
	
	function update() {
		$result = $this->db->query("UPDATE ".$this->db->config['table_accountingcodes'].
			" SET name = ".$this->db->quote($this->name)." ,
				number = ".$this->db->quote($this->number)."
				WHERE id = ".(int)$this->id
		);
		$this->db->status($result[1], "u", __("accounting code"));

		return $this->id;
	}

	function update_id($id) {
		$query = "UPDATE ".$this->db->config['table_accountingcodes']." 
			SET id = ".(int)$id."
			WHERE id = ".(int)$this->id;

		$this->db->query($query);
		return $id;
	}
	
	function delete() {
		$result = $this->db->query("DELETE FROM ".$this->db->config['table_accountingcodes'].
				" WHERE id = '".(int)$this->id."'"
		);
		$this->db->status($result[1], "d", __("accounting plan"));
		$result = $this->db->query("DELETE FROM ".$this->db->config['table_accountingcodes_affectation'].
				" WHERE accountingcodes_id = '".(int)$this->id."'"
		);
		$this->db->status($result[1], "d", __('accounting code'));
		return $this->id;
	}
	
	function clean($variables) {
		$cleaned = array();

		if (isset($variables['name'])) {
			$cleaned['name'] = strip_tags($variables['name']);
			$cleaned['name'] = trim(preg_replace('/\s+/', ' ', $cleaned['name']));			
		}

		if (isset($variables['accountingcodes_name'])) {
			$cleaned['name'] = strip_tags($variables['accountingcodes_name']);
			$cleaned['name'] = trim(preg_replace('/\s+/', ' ', $cleaned['name']));
		}

		return $cleaned;
	}

	function fullname() {
		return $this->number." - ".$this->name;
	}
	
	function adjust_number($number = null) {
		if ($number == null) {
			$number = $this->number;
		}
		 $number = substr($number, 0, 8);
		if (strlen($number) > 1) {
			while (strlen($number) < 8) {
				$number .= "0";
			}
		}
		return $number;
	}

	function reaffect_by_default() {
		$reportings_id = $this->get_default_reporting();
		if ($reportings_id > 0) {
			$reporting = new Reporting();
			$reporting->id = $reportings_id;
			$reporting->load(array('id' => $reportings_id));
			if ($reporting->id > 0) {
				$affectation = new Accounting_Code_Affectation();
				$affectation->load(array('accountingcodes_id' => $this->id));
				$affectation->reportings_id = $reporting->id;
				$affectation->accountingcodes_id = $this->id;
				$affectation->save();
			}
		}
	}

	function get_default_reporting() {
		$number = $this->adjust_number();
		$reporting = new Reporting();

		$reportings_id = 0;
		switch (true) {
			case intval($number) >= 70000000 and intval($number) <= 70999999:
				// A - Chiffre d'affaires
				$reporting->load(array('norm' => "A"));
				$reportings_id = $reporting->id;
				break;

			case intval($number) >= 60000000 and intval($number) <= 60999999:
				// B - Charges directes
				$reporting->load(array('norm' => "B"));
				$reportings_id = $reporting->id;
				break;

			case intval($number) >= 64000000 and intval($number) <= 64999999:
				// C - Coût productif
				$reporting->load(array('norm' => "C"));
				$reportings_id = $reporting->id;
				break;

			case intval($number) >= 71000000 and intval($number) <= 75999999:
			case intval($number) >= 79000000 and intval($number) <= 79999999:
				// G - Autres produits
				$reporting->load(array('norm' => "G"));
				$reportings_id = $reporting->id;
				break;

			case intval($number) >= 61000000 and intval($number) <= 62999999:
			case intval($number) >= 65000000 and intval($number) <= 65999999:
				// H - Frais généraux
				$reporting->load(array('norm' => "H"));
				$reportings_id = $reporting->id;
				break;

			case intval($number) >= 63000000 and intval($number) <= 63999999:
				// I - Impôts et taxes
				$reporting->load(array('norm' => "I"));
				$reportings_id = $reporting->id;
				break;

			case intval($number) >= 68000000 and intval($number) <= 68999999:
			case intval($number) >= 78000000 and intval($number) <= 78999999:
				// J - Dotations amortissement et provisions
				$reporting->load(array('norm' => "J"));
				$reportings_id = $reporting->id;
				break;

			case intval($number) >= 66000000 and intval($number) <= 66999999:
				// K - Charges financières
				$reporting->load(array('norm' => "K"));
				$reportings_id = $reporting->id;
				break;

			case intval($number) >= 76000000 and intval($number) <= 76999999:
				// L - Produits financiers
				$reporting->load(array('norm' => "L"));
				$reportings_id = $reporting->id;
				break;

			case intval($number) >= 67000000 and intval($number) <= 67999999:
				// M - Charges exceptionnelles
				$reporting->load(array('norm' => "M"));
				$reportings_id = $reporting->id;
				break;

			case intval($number) >= 77000000 and intval($number) <= 77999999:
				// N - Produits exceptionnels
				$reporting->load(array('norm' => "N"));
				$reportings_id = $reporting->id;
				break;

			case intval($number) >= 69000000 and intval($number) <= 69999999:
				// O - Impôt sociétés
				$reporting->load(array('norm' => "O"));
				$reportings_id = $reporting->id;
				break;

			default:;
		}
		
		return $reportings_id;
	}

	function form_add() {
		$name = new Html_Input("accountingcode_new_name", "");
		$number = new Html_Input("accountingcode_new_number", adapt_number($this->number));

		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'title' => array(
					'value' => "<h1>".__("add")."</h1>"
				),
				'name' => array(
					'value' => $name->item(ucfirst(__("name")))
				),
				'number' => array(
					'value' => $number->item(ucfirst(__("number")))
				),
				'modify' => array(
					'class' => "perso",
					'value' => "<input type=\"button\"  value=\"".__('add')."\"  onclick=\"add_accountingcode()\" />"
				)
			)
		);

		$list = new Html_List($grid);

		$form = "<div class=\"form_add_accountingcode\">
			<form method=\"post\" action=\"\" enctype=\"multipart/form-data\">".
				$list->show()."
			</form>
		</div>";

		return $form;
	}

	function form_edit() {
		$name = new Html_Input("accountingcode_name",$this->name);
		$id = new Html_Input("accountingcode_id",adapt_number($this->number),"hidden");

		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'title' => array(
					'value' => "<h1>".__("add new reporting")."</h1>"
				),
				'name' => array(
					'value' => $name->item(ucfirst(__("name")))
				),
				'modify' => array(
					'class' => "perso",
					'value' => "<input type=\"button\"  value=\"".__('modify')."\"  onclick=\"modify_accountingcode()\" />"
				)
			)
		);

		$list = new Html_List($grid);

		$form = "<div class=\"form_edit_accountingcode\">
			<form method=\"post\" action=\"\" enctype=\"multipart/form-data\">".
				$id->input_hidden().$list->show()."
			</form>
		</div>";

		return $form;
	}
}
