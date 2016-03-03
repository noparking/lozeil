<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

class Writing extends Record {
	public $id = 0;
	public $accountingcodes_id = 0;
	public $amount_excl_vat = 0;
	public $amount_inc_vat = 0;
	public $banks_id = 0;
	public $categories_id = 0;
	public $comment = "";
	public $day = 0;
	public $information = "";
	public $number = "";
	public $paid = 0;
	public $search_index = "";
	public $sources_id = 0;
	public $attachment = 0;
	public $timestamp = 0;
	public $vat = 0;
	
	function __construct($id = 0, db $db = null) {
		parent::__construct($db);
		$this->id = $id;
	}

	function db($db) {
		if ($db instanceof db) {
			$this->db = $db;
		}
	}
	
	function load(array $key = array(), $table = "writings", $columns = null) {
		return parent::load($key, $table, $columns);
	}
		
	
	
	function loadlastinserted($table = "writings") 
	{		
		
		
			$columns = $this->get_db_columns();
	
			$result = $this->db->query("
				SELECT ".join(", ", $columns)."
				FROM ".$this->db->config['table_'.$table]."
				WHERE `timestamp` in (select MAX(`timestamp`) from ".$this->db->config['table_'.$table]." ) LIMIT 0,1; "
			);
			$row = $this->db->fetch_array($result[0]);
			if ($row === false or $row === null)
			{

				return false;
			} 
			else 
			{
				foreach ($row as $column => $value) {
				$this->{$this->db_column_to_php_property($column)} = $value;
				}
			}

			return true;	
	}
	
	
	static function check_must_import()
	{
		$import = new Writing();
		$import->loadlastinserted($table = "writings") ;
		
		$nbmonth = (time() - $import->timestamp)/2592000;
		
		if( $nbmonth > 1)
		{
			return "<a href=\"".link_content("content=writingsimportbank.php")."\"  >".__('do not forget to import')."</a>";
		}
		else
		{
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

	function delete() {
		$result = $this->db->query("DELETE FROM ".$this->db->config['table_writings'].
			" WHERE id = '".$this->id."'"
		);
		$this->db->status($result[1], "d", __("line"));

		return $this->id;
	}
	
	function truncate() {
		$result = $this->db->query("DELETE FROM ".$this->db->config['table_writings']);
		$this->db->status($result[1], "d", __("line"));
		return $this->id;
	}

	function update() {
			$result = $this->db->query("UPDATE ".$this->db->config['table_writings'].
				" SET categories_id = ".(int)$this->categories_id.",
				banks_id = ".(int)$this->banks_id.",
				sources_id = ".(int)$this->sources_id.",
				amount_inc_vat = ".(float)$this->amount_inc_vat.",
				number  = ".$this->db->quote($this->number).",
				vat = ".(float)$this->vat.",
				amount_excl_vat = ".$this->calculate_amount_excl_vat().",
				comment = ".$this->db->quote($this->comment).",
				information = ".$this->db->quote($this->information).",
				paid = ".(int)$this->paid.",
				day = ".(int)$this->day.",	
				search_index = ".$this->db->quote($this->search_index()).",
				accountingcodes_id = ".(int)$this->accountingcodes_id.",
				attachment = ".(int)$this->attachment.",
				timestamp = ".time()."
				WHERE id = ".(int)$this->id
			);
		
		$this->db->status($result[1], "u", __("line"));

		return $this->id;
	}
	
	function insert() {
		$result = $this->db->query_with_id("
			INSERT INTO ".$this->db->config['table_writings']."
			SET categories_id = ".(int)$this->categories_id.",
			banks_id = ".(int)$this->banks_id.",
			sources_id = ".(int)$this->sources_id.",
			amount_inc_vat = ".(float)$this->amount_inc_vat.",
			number  = ".$this->db->quote($this->number).",
			vat = ".(float)$this->vat.",
			amount_excl_vat = ".$this->calculate_amount_excl_vat().",
			comment = ".$this->db->quote($this->comment).",
			information = ".$this->db->quote($this->information).",
			day = ".(int)$this->day.",
			search_index = ".$this->db->quote($this->search_index()).",
			accountingcodes_id = ".(int)$this->accountingcodes_id.",
			attachment = ".(int)$this->attachment.",
			paid = ".(int)$this->paid.",
			timestamp = ".time()
		);
		$this->id = $result[2];
		$this->db->status($result[1], "i", __("line"));
		return $this->id;
	}
	
	function clean($post) {
		$cleaned = array();
		
		if (isset($post['number'])) {
			$post['number'] = strip_tags($post['number']);
			$post['number'] = trim(preg_replace('/\s+/', ' ', $post['number']));
		}

		if (isset($post['comment'])) {
			$post['comment'] = strip_tags($post['comment']);
			$post['comment'] = trim(preg_replace('/\s+/', ' ', $post['comment']));
		}

		if (isset($post['information'])) {
			$post['information'] = strip_tags($post['information']);
			$post['information'] = trim(preg_replace('/\s+/', ' ', $post['information']));
		}

		if (isset($post['datepicker'])) {
			$cleaned['day'] = timestamp_from_datepicker($post['datepicker']);
		}
		
		if (isset($post['accountingcodes_id'])) {
			if ($post['accountingcodes_id'] != 0) {
				$cleaned['accountingcodes_id'] = (int)$post['accountingcodes_id'];
			}
		} else {
			$cleaned['accountingcodes_id'] = 0;
		}
		
		if (isset($post['paid'])) {
			$cleaned['paid'] = (int)$post['paid'];
		}
		
		if (isset($post['amount_inc_vat'])) {
			$cleaned['amount_inc_vat'] = str_replace(",", ".", $post['amount_inc_vat']);
		}
		
		$cleaned['categories_id'] = (int)$post['categories_id'];
		$cleaned['sources_id'] = (int)$post['sources_id'];
		$cleaned['comment'] = $post['comment'];
		if (isset($post['amount_inc_vat'])) {
			$cleaned['amount_excl_vat'] = str_replace(",", ".", $post['amount_excl_vat']);
		}
		$cleaned['vat'] = str_replace(",", ".", $post['vat']);
		$cleaned['number'] = $post['number'];
		
		return $cleaned;
	}
	
	function search_index() {
		$bank = new Bank();
		$bank->load(array('id' => $this->banks_id));
		$source = new Source();
		$source->load(array('id' => $this->sources_id));
		$category = new Category();
		$category->load(array('id' => $this->categories_id));
		
		return date("d/m/Y",$this->day)." ".$this->vat." ".$this->amount_excl_vat." ".$this->amount_inc_vat." ".$bank->name." ".$this->comment." ".$this->information." ".$this->number." ".$source->name." ".$category->name;
	}
	
	function calculate_amount_excl_vat() {
		if ($this->vat != -100) {
			return (float)round($this->amount_inc_vat/(($this->vat/100) + 1), 6);
		}
		return 0;
	}
	
	function merge_from(Writing $to_merge) {
		if ($this->banks_id == 0 or $to_merge->banks_id == 0) {
			if ($this->banks_id == 0) {
				$this->banks_id = $to_merge->banks_id > 0 ? (int)$to_merge->banks_id : $this->banks_id;
				$this->amount_inc_vat = $to_merge->amount_inc_vat;
				$this->day = $to_merge->day;
				$this->paid = $to_merge->paid;
			}
			$this->information = !empty($to_merge->information) ? $to_merge->information."\n".$this->comment."\n".$this->information : $this->information;
			$this->comment = !empty($to_merge->comment) ? $to_merge->comment : $this->comment;
			$this->categories_id = $to_merge->categories_id > 0 ? (int)$to_merge->categories_id : $this->categories_id;
			$this->sources_id = $to_merge->sources_id > 0 ? (int)$to_merge->sources_id : $this->sources_id;
			$this->vat = $to_merge->vat > 0 ? $to_merge->vat : $this->vat;
			$this->number = !empty($to_merge->number) ? $to_merge->number : $this->number;
			$this->accountingcodes_id = $to_merge->accountingcodes_id > 0 ? (int)$to_merge->accountingcodes_id : $this->accountingcodes_id;
			$to_merge->delete();
			$this->save();
		} else {
			$this->db->status(0, "u", __("line"));
			return false;
		}
	}
	
	function split($amount) {
		if (!is_array($amount)) {
			$amounts[] = $amount;
		} else {
			$amounts = $amount;
		}
		
		foreach ($amounts as $split_amount) {
			$split_amount = str_replace(",", ".", $split_amount);
			if (is_numeric($split_amount)) {
				$this->amount_inc_vat = ($this->amount_inc_vat - $split_amount);

				$writing = new Writing();
				$writing->load(array('id' => $this->id));
				$writing->amount_inc_vat = $split_amount;
				$writing->insert();
			}
		}
		
		$this->save();
		return $this->id;
	}
	
	function form() {
		return "<div id=\"insert_writings\"><span class=\"button\" id=\"insert_writings_show\">".utf8_ucfirst(__('show form'))."</span></div>";
	}
	
	function display() {
		$form = "
			<div class=\"insert_writings_form\">
			<form method=\"post\" name=\"insert_writings_form\" action=\"\" enctype=\"multipart/form-data\">";
		
		$input_hidden = new Html_Input("action", "insert");
		$form .= $input_hidden->input_hidden();
		
		$categories = new Categories();
		$categories->select();
		$sources = new Sources();
		$sources->select();
		$accountingcodes = new Accounting_Codes();
		$accountingcodes->select();
		
		$datepicker = new Html_Input_Date("datepicker", $_SESSION['filter']['start']);
		$category = new Html_Select("categories_id", $categories->names());
		$source = new Html_Select("sources_id", $sources->names());
		$accountingcode = new Html_Input_Ajax("accountingcodes_id", link_content("content=writings.ajax.php"), $accountingcodes->numbers());
		$number = new Html_Input("number");
		$amount_excl_vat = new Html_Input("amount_excl_vat");
		$vat = new Html_Input("vat");
		$amount_inc_vat = new Html_Input("amount_inc_vat");
		$comment = new Html_Textarea("comment");
		$paid = new Html_Radio("paid", array(__("no"),__("yes")));
		$submit = new Html_Input("submit", __('save'), "submit");
		
		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'date' => array(
					'value' => $datepicker->item(__('date')),
				),
				'category' => array(
					'value' => $category->item(__('category')),
				),
				'source' => array(
					'value' => $source->item(__('source')),
				),
				'accountingcode' => array(
					'value' => $accountingcode->item(__('accounting code')),
				),
				'number' => array(
					'value' => $number->item(__('piece nb')),
				),
				'amount_excl_vat' => array(
					'value' => $amount_excl_vat->item(__('amount excluding vat')),
				),
				'vat' => array(
					'value' => $vat->item(__('VAT')),
				),
				'amount_inc_vat' => array(
					'value' => $amount_inc_vat->item(__('amount including vat')),
				),
				'comment' => array(
					'value' => $comment->item(__('comment')),
				),
				'paid' => array(
					'value' => $paid->item(__('paid')),
				),
				'submit' => array(
					'value' => $submit->item(""),
				)
			)
		);
		if (!$_SESSION['accountant_view']) {
			unset($grid['leaves']['accountingcode']);
		}
		$list = new Html_List($grid);
		$form .= $list->show();
		
		$form .= "</form></div>";

		return $form;
	}
	
	function form_in_table() {
		if ($_SESSION['accountant_view']) {
			return $this->form_in_table_accountant();
		} else {
			return $this->form_in_table_normal();
		}
	}
	
	function form_in_table_accountant() {
		$categories = new Categories();
		$categories->select();
		$sources = new Sources();
		$sources->select();
		$banks = new Banks();
		$banks->select();
		
		$accountingcode = new Accounting_Code();
		$currentcode = array();
		if ($accountingcode->load(array('id' => $this->accountingcodes_id))) {
			$currentcode[] = $accountingcode->fullname();
		}
		
		$input_hidden = new Html_Input("action", "edit", "submit");
		$input_hidden_id = new Html_Input("writings_id", $this->id);
		$datepicker = new Html_Input_Date("datepicker", $this->day);
		$category = new Html_Select("categories_id", $categories->names(), $this->categories_id);
		$source = new Html_Select("sources_id", $sources->names(), $this->sources_id);
		$bank = new Html_Select("banks_id", $banks->names(), $this->banks_id);
		$accountingcode_input = new Html_Input_Ajax("accountingcodes_id", link_content("content=writings.ajax.php"), $currentcode);
		$number = new Html_Input("number", $this->number);
		$amount_excl_vat = new Html_Input("amount_excl_vat", $this->amount_excl_vat);
		$vat = new Html_Input("vat", $this->vat);
		$amount_inc_vat = new Html_Input("amount_inc_vat", $this->amount_inc_vat);
		$comment = new Html_Textarea("comment", $this->comment);
		$information = new Html_Textarea("information", $this->information);
		$information->properties['disabled'] = 'disabled';
		$paid = new Html_Radio("paid", array(__("no"),__("yes")), $this->paid);
		$submit = new Html_Input("submit", __('save'), "submit");
		
		$link = $this->attachment ? $this->link_to_file_attached() : "";
		
		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'date' => array(
					'value' => $datepicker->item(__('date')),
				),
				'category' => array(
					'value' => $category->item(__('category')),
				),
				'source' => array(
					'value' => $source->item(__('source')),
				),
				'bank' => array(
					'value' => $bank->item(__('bank')),
				),
				'accountingcode' => array(
					'value' => $accountingcode_input->item(__('accounting code')),
				),
				'number' => array(
					'value' => $number->item(__('piece nb')),
				),
				'amount_excl_vat' => array(
					'value' => $amount_excl_vat->item(__('amount excluding vat')),
				),
				'vat' => array(
					'value' => $vat->item(__('VAT')),
				),
				'amount_inc_vat' => array(
					'value' => $amount_inc_vat->item(__('amount including vat')),
				),
				'comment' => array(
					'value' => $comment->item(__('comment')),
				),
				'information' => array(
					'value' => $information->item(__('information')),
				),
				'paid' => array(
					'value' => $paid->item(__('paid')),
				),
				'submit' => array(
					'value' => $submit->item(""),
				)
			)
		);
		$list = new Html_List($grid);
		
		$form = "<div id=\"table_edit_writings\">
				<div class=\"table_edit_writings_form\">
					<form method=\"post\" name=\"table_edit_writings_form\" action=\"\" enctype=\"multipart/form-data\">".
					$input_hidden->input_hidden().$input_hidden_id->input_hidden().$list->show().
					"</form>".
					$link."
				</div>
			</div>";

		return $form;
	}
	
	function form_in_table_normal() {
		$categories = new Categories();
		$categories->select();
		$sources = new Sources();
		$sources->select();
				
		$input_hidden = new Html_Input("action", "edit", "submit");
		$input_hidden_id = new Html_Input("writings_id", $this->id);
		$datepicker = new Html_Input_Date("datepicker", $this->day);
		$category = new Html_Select("categories_id", $categories->names(), $this->categories_id);
		$source = new Html_Select("sources_id", $sources->names(), $this->sources_id);
		$number = new Html_Input("number", $this->number);
		$amount_excl_vat = new Html_Input("amount_excl_vat", $this->amount_excl_vat);
		$vat = new Html_Input("vat", $this->vat);
		$amount_inc_vat = new Html_Input("amount_inc_vat", $this->amount_inc_vat);
		$comment = new Html_Textarea("comment", $this->comment);
		$information = new Html_Textarea("information", $this->information);
		$information->properties['disabled'] = 'disabled';
		$paid = new Html_Radio("paid", array(__("no"),__("yes")), $this->paid);
		$submit = new Html_Input("submit", __('save'), "submit");
		
		$link = $this->attachment ? $this->link_to_file_attached() : "";
		
		if ($this->banks_id > 0) {
			$grid = array(
				'class' => "itemsform",
				'leaves' => array(
					'category' => array(
						'value' => $category->item(__('category')),
					),
					'source' => array(
						'value' => $source->item(__('source')),
					),
					'number' => array(
						'value' => $number->item(__('piece nb')),
					),
					'vat' => array(
						'value' => $vat->item(__('VAT')),
					),
					'comment' => array(
						'value' => $comment->item(__('comment')),
					),
					'information' => array(
						'value' => $information->item(__('information')),
					),
					'submit' => array(
						'value' => $submit->item(""),
					)
				)
			);
		} else {
			$grid = array(
				'class' => "itemsform",
				'leaves' => array(
					'date' => array(
						'value' => $datepicker->item(__('date')),
					),
					'category' => array(
						'value' => $category->item(__('category')),
					),
					'source' => array(
						'value' => $source->item(__('source')),
					),
					'number' => array(
						'value' => $number->item(__('piece nb')),
					),
					'amount_excl_vat' => array(
						'value' => $amount_excl_vat->item(__('amount excluding vat')),
					),
					'vat' => array(
						'value' => $vat->item(__('VAT')),
					),
					'amount_inc_vat' => array(
						'value' => $amount_inc_vat->item(__('amount including vat')),
					),
					'comment' => array(
						'value' => $comment->item(__('comment')),
					),
					'information' => array(
						'value' => $information->item(__('information')),
					),
					'paid' => array(
						'value' => $paid->item(""),
					),
					'submit' => array(
						'value' => $submit->item(""),
					),
				)
			);
		}
		$list = new Html_List($grid);
		
		$form = "<div id=\"table_edit_writings\">
				<div class=\"table_edit_writings_form\">
					<form method=\"post\" name=\"table_edit_writings_form\" action=\"\" enctype=\"multipart/form-data\">".
					$input_hidden->input_hidden().$input_hidden_id->input_hidden().$list->show().
					"</form>".
					$link."
				</div>
			</div>";

		return $form;
	}
	
	function show_form_duplicate() {
		$input_hidden_id = new Html_Input("table_writings_form_duplicate_id", $this->id);
		$input_hidden_action = new Html_Input("action", "form_duplicate");
		$submit = new Html_Input("table_writings_duplicate_submit", "", "submit");
		
		$form = "<div class=\"duplicate show_acronym\">
					<form method=\"post\" name=\"table_writings_form_duplicate\" action=\"\" enctype=\"multipart/form-data\">".
						$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input()."
					</form> 
                                        <span class=\"acronym\">".__('duplicate')."</span>
				</div>";
		
		return $form;
	}
	
	function form_duplicate() {
		$input_hidden_id = new Html_Input("writing_id", $this->id);
		$input_hidden_action = new Html_Input("action", "duplicate");
		$submit = new Html_Input("table_writings_duplicate_submit", utf8_ucfirst(__('save')), "submit");
		$select = new Html_Select("table_writings_duplicate_amount_select", $this->periods());
		$input_value = new Html_Input("table_writings_duplicate_amount", "");
		
		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'duplicate' => array(
					'value' => $select->item(utf8_ucfirst(__('duplicate over')), "", $input_value->input()),
				),
				'submit' => array(
					'value' => $submit->item(""),
				),
			)
		);
		$list = new Html_List($grid);
		$form = "<div class=\"form_duplicate\">
					<form method=\"post\" name=\"table_writings_duplicate\" action=\"\" enctype=\"multipart/form-data\">".
						$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$list->show()."
					</form>
				</div>";
		
		return $form."<div class=\"preview_changes\">".$this->preview_duplicate()."</div>";
	}
	
	function form_delete() {
		if ($this->banks_id == 0) {
			$input_hidden_id = new Html_Input("table_writings_delete_id", $this->id);
			$input_hidden_action = new Html_Input("action", "delete");
			$submit = new Html_Input("table_writings_delete_submit", "", "submit");
			$submit->properties = array(
				'onclick' => "javascript:return confirm('".utf8_ucfirst(__('are you sure?'))."')"
			);
			
			$form = "<div class=\"delete show_acronym\">
						<form method=\"post\" name=\"table_writings_delete\" action=\"\" enctype=\"multipart/form-data\">".
							$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input()."
						</form>
                                                <span class=\"acronym\">".__('delete')."</span>
					</div>";
			
			return $form;
		}
	}
	
	function show_form_split() {
		$input_hidden_id = new Html_Input("table_writings_form_split_id", $this->id);
		$input_hidden_action = new Html_Input("action", "form_split");
		$submit = new Html_Input("table_writings_form_split_submit", "", "submit");

		$form = "<div class=\"split show_acronym\">
					<form method=\"post\" name=\"table_writings_form_split\" action=\"\" enctype=\"multipart/form-data\">".
						$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input()."
					</form>
                                        <span class=\"acronym\">".__('split')."</span>
				</div>";
		
		return $form;
	}
	
	function form_split() {
		$input_hidden_id = new Html_Input("writing_id", $this->id);
		$input_hidden_action = new Html_Input("action", "split");
		$submit = new Html_Input("table_writings_split_submit", utf8_ucfirst(__('save')), "submit");
		$input_value = new Html_Input("table_writings_split_amount[new]", "");
		$input_value_clone = new Html_Input("table_writings_split_amount[new0]", "");
		$input_value_clone->properties = array('class' => 'li-clone');
		
		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'duplicate' => array(
					'value' => $input_value->item(utf8_ucfirst(__('split'))),
				),
				'duplicate_clone' => array(
					'value' => $input_value_clone->item(""),
				),
				'submit' => array(
					'value' => $submit->item(""),
				),
			)
		);

		$list = new Html_List($grid);
		
		$form = "<div class=\"form_split\">
					<form method=\"post\" name=\"table_writings_split\" action=\"\" enctype=\"multipart/form-data\">".
						$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$list->show()."
					</form>
				</div>";
		
		return $form."<div class=\"preview_changes\">".$this->preview_split()."</div>";
	}
	
	function show_form_forward() {
		if ($this->banks_id == 0) {
			$input_hidden_id = new Html_Input("table_writings_form_forward_id", $this->id);
			$input_hidden_action = new Html_Input("action", "form_forward");
			$submit = new Html_Input("table_writings_form_forward_submit", "", "submit");
			
			$form = "<div class=\"forward show_acronym\">
						<form method=\"post\" name=\"table_writings_form_forward\" action=\"\" enctype=\"multipart/form-data\">".
							$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input()."
						</form>
                                                <span class=\"acronym\">".__('forward')."</span>
					</div>";
		
			return $form;
		}
	}
	
	
	function form_forward() {
		$input_hidden_id = new Html_Input("writing_id", $this->id);
		$input_hidden_action = new Html_Input("action", "forward");
		$submit = new Html_Input("table_writings_forward_submit", utf8_ucfirst(__('save')), "submit");
		$select = new Html_Select("table_writings_forward_amount_select", $this->periods());
		$input_value = new Html_Input("table_writings_forward_amount", "");
		
		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'forward' => array(
					'value' => $select->item(utf8_ucfirst(__('forward')), "", $input_value->input()),
				),
				'submit' => array(
					'value' => $submit->item(""),
				),
			)
		);

		$list = new Html_List($grid);
		
		$form = "<div class=\"form_forward\">
					<form method=\"post\" name=\"table_writings_forward\" action=\"\" enctype=\"multipart/form-data\">".
						$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$list->show()."
					</form>
				</div>";

		return $form."<div class=\"preview_changes\">".$this->preview_forward()."</div>";
	}
	
	function show_form_modify() {
		$input_hidden_id = new Html_Input("table_writings_modify_id", $this->id);
		$input_hidden_action = new Html_Input("action", "form_edit");
		$submit = new Html_Input("table_writings_modify_submit", "", "submit");
		
		$form = "<div class=\"modify show_acronym\">
					<form method=\"post\" name=\"table_writings_modify\" action=\"\" enctype=\"multipart/form-data\">".
						$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input()."
					</form>
                                        <span class=\"acronym\">".__('modify')."</span>
				</div>";
			
		return $form;
	}
	
	function fill($hash) {
		$writing = parent::fill($hash);
		if($writing->banks_id > 0) {
			$writing->amount_excl_vat = $writing->calculate_amount_excl_vat();
		}
		
		return $writing;
	}
	
	function duplicate($amount) {
		if (is_numeric($amount) and $amount > 0) {
			for ($i=1; $i<=$amount; $i++) {
				$new_writing = $this;
				$new_writing->id = 0;
				$new_writing->day = strtotime('+1 months', $new_writing->day);
				$new_writing->banks_id = 0;
				$new_writing->number = "";
				$new_writing->save();
			}
		} else {
			$split = preg_split("/(q)|(y)|(a)|(t)|(m)/i", $amount, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			if (count($split) == 2 and is_numeric($split[0])) {
				if(preg_match("/(m)/i", $split[1])) {
					for ($i=1; $i<=$split[0]; $i++) {
						$new_writing = $this;
						$new_writing->id = 0;
						$new_writing->day = strtotime('+1 months', $new_writing->day);
						$new_writing->banks_id = 0;
						$new_writing->number = "";
						$new_writing->save();
					}
				} elseif(preg_match("/(q)|(t)/i", $split[1])) {
					for ($i=1; $i<=$split[0]; $i++) {
						$new_writing = $this;
						$new_writing->id = 0;
						$new_writing->day = strtotime('+3 months', $new_writing->day);
						$new_writing->banks_id = 0;
						$new_writing->number = "";
						$new_writing->save();
					}
				} elseif(preg_match("/(a)|(y)/i", $split[1])) {
					for ($i=1; $i<=$split[0]; $i++) {
						$new_writing = $this;
						$new_writing->id = 0;
						$new_writing->day = strtotime('+1 year', $new_writing->day);
						$new_writing->banks_id = 0;
						$new_writing->number = "";
						$new_writing->save();
					}
				}
			}
		}
	}
	
	function forward($amount) {
		if (is_numeric($amount) and $amount > 0) {
			$this->day = strtotime('+'.$amount.' months', $this->day);
			$this->save();
		} else {
			$split = preg_split("/(q)|(y)|(a)|(t)|(m)|(d)|(j)/i", $amount, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			if (count($split) == 2 and is_numeric($split[0])) {
				if(preg_match("/(m)/i", $split[1])) {
					$this->day = strtotime('+'.$split[0].' months', $this->day);
					$this->save();
				} elseif(preg_match("/(d)|(j)/i", $split[1])) {
					$this->day = strtotime('+'.($split[0]).' days', $this->day);
					$this->save();
				} elseif(preg_match("/(q)|(t)/i", $split[1])) {
					$this->day = strtotime('+'.($split[0] * 3).' months', $this->day);
					$this->save();
				} elseif(preg_match("/(a)|(y)/i", $split[1])) {
					$this->day = strtotime('+'.$split[0].' year', $this->day);
					$this->save();
				}
			}
		}
	}
	
	function show_further_information() {
		if (!empty($this->information)) {
			return "<div class=\"table_writings_comment_further_information\">".nl2br($this->information)."</div>";
		}
		return "";
	}
	
	function show_operations() {
		return $this->show_form_modify().$this->show_form_split().$this->show_form_duplicate().$this->show_form_forward().$this->form_delete();
	}
	
	function is_recently_modified(){
		if ($this->timestamp > (time() - 10)) {
			return true;
		}
		return false;
	}
	
	function get_data() {
		$datas = array();
		
		$datas['classification_target'] = array(
			$this->db->config['table_categories'] => $this->categories_id,
			$this->db->config['table_accountingcodes'] => $this->accountingcodes_id
		);
		
		preg_match_all('(\w{3,})u', $this->comment, $matches['comment']);
		
		$datas['classification_data'] = array(
			'comment' => $matches['comment'][0],
			'amount_inc_vat' => array($this->amount_inc_vat)
		);
		
		return $datas;
	}
	
	function different_from(Writing $writing) {
		switch (true) {
			case $this->accountingcodes_id != $writing->accountingcodes_id:
			case $this->categories_id != $writing->categories_id:
			case $this->amount_inc_vat != $writing->amount_inc_vat:
			case $this->comment != $writing->comment:
			case $this->sources_id != $writing->sources_id:
				return true;
			default :
				return false;
		}
	}
	
	function link_to_file_attached() {
		$files = new Files();
		$files->filter_with(array('writings_id' => $this->id));
		$files->select();
		
		$link = "<div class=\"manage_writing_attachment\">";
		foreach ($files as $file) {
			$input_hidden_id = new Html_Input("id", $file->id);
			$link .= "<form method=\"post\" name=\"open_writing_attachment\" action=\"\" enctype=\"multipart/form-data\">";
			$input_name = new Html_Input("open_writing_attachment", $file->value, "submit");
			$action = new Html_Input("action", "open_attachment");
			$link .= $input_hidden_id->input_hidden().$action->input_hidden().$input_name->input()."
					</form>
					<form method=\"post\" name=\"delete_writing_attachment\" action=\"\" enctype=\"multipart/form-data\">";
			$input_delete = new Html_Input("delete_writing_attachment", "X", "submit");
			$action = new Html_Input("action", "delete_attachment");
			$input_delete->properties = array(
				'onclick' => "javascript:return confirm('".utf8_ucfirst(__('are you sure?'))."')"
			);
			$link .= $input_hidden_id->input_hidden().$action->input_hidden().$input_delete->input();
			$link .= "</form><br />";
		}
		return $link."</div>";
	}
	
	function grid_preview_split($amounts) {
		$line = 0;
		$sum = 0;
		$rowspan = count($amounts) + 1;
		if ($rowspan <= 1) {
			$rowspan = 2;
			$amounts[0] = 0;
		}
		
		$grid = array(
			'header' => array(
				'cells' => array(
					array(
						'type' => "th",
						'value' => __('Current amount')
					),
					array(
						'type' => "th",
						'value' => __('New amounts')
					)
				)
			),
			'lines' => array(
				'cells' => array(
					array(
						'type' => "td",
						'rowspan' => $rowspan,
						'value' => round($this->amount_inc_vat, 2)." ".$GLOBALS['param']['currency']
					),
					array(
						'type' => "td",
						'value' => $amounts[0]." ".$GLOBALS['param']['currency']
					)
				)
			)
		);
		
		foreach ($amounts as $key => $amount) {
			if ($key != 0) {
				$grid["lines_".$line]["cells"][] = array(
					'type' => "td",
					'value' => $amount." ".$GLOBALS['param']['currency']
				);
				$line++;
			}
			$sum = $sum + $amount;
		}
		
		$grid["lines_last"]["cells"][] = array(
			'type' => "td",
			'value' => ($this->amount_inc_vat - $sum)." ".$GLOBALS['param']['currency']
		);
		
		return $grid;
	}
	
	function preview_split($request = "", $rowspan = 2) {
		$amounts = array();
		
		parse_str(urldecode($request), $parsed);
		if (!empty($parsed)) {
			$amounts = $this->clean_amounts_from_ajax($parsed['table_writings_split_amount']);
		}
		
		$html_table = new Html_table(array('lines' => $this->grid_preview_split($amounts)));
		
		return $html_table->show();
	}
	
	function preview_forward($value = "") {
		$day = "";
		if (is_numeric($value)) {
			$day = date("d/m/Y", strtotime('+'.$value.' months', $this->day));
		} else {
			$split = preg_split("/(q)|(y)|(a)|(t)|(m)|(d)|(j)/i", $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			if (count($split) == 2 and is_numeric($split[0])) {
				if (preg_match("/(m)/i", $split[1])) {
					$day = strtotime('+'.$split[0].' months', $this->day);
				} elseif(preg_match("/(d)|(j)/i", $split[1])) {
					$day = strtotime('+'.($split[0]).' days', $this->day);
				} elseif(preg_match("/(q)|(t)/i", $split[1])) {
					$day = strtotime('+'.($split[0] * 3).' months', $this->day);
				} elseif(preg_match("/(a)|(y)/i", $split[1])) {
					$day = strtotime('+'.$split[0].' year', $this->day);
				}
				$day = date("d/m/Y", $day);
			}
		}
		
		$grid = array(
			'header' => array(
				'cells' => array(
					array(
						'type' => "th",
						'value' => __('Current date')
					),
					array(
						'type' => "th",
						'value' => __('New date')
					)
				)
			),
			'lines' => array(
				'cells' => array(
					array(
						'type' => "td",
						'value' => date("d/m/Y", $this->day)
					),
					array(
						'type' => "td",
						'value' => $day
					)
				)
			),
		);
		$html_table = new Html_table(array('lines' => $grid));
		
		return $html_table->show();
	}
	
	function preview_duplicate($value = "") {
		$days = array();
		if (is_numeric($value) and $value > 0) {
			for ($i=1; $i<=$value; $i++) {
				$days[] = date("d/m/Y", strtotime('+'.$i.' months', $this->day));
			}
		} else {
			$split = preg_split("/(q)|(y)|(a)|(t)|(m)/i", $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			if (count($split) == 2 and is_numeric($split[0])) {
				if(preg_match("/(m)/i", $split[1])) {
					for ($i=1; $i<=$split[0]; $i++) {
						$days[] = date("d/m/Y", strtotime('+'.$i.' months', $this->day));
					}
				} elseif(preg_match("/(q)|(t)/i", $split[1])) {
					for ($i=1; $i<=$split[0]; $i++) {
						$days[] = date("d/m/Y", strtotime('+'.($i * 3).' months', $this->day));
					}
				} elseif(preg_match("/(a)|(y)/i", $split[1])) {
					for ($i=1; $i<=$split[0]; $i++) {
						$days[] = date("d/m/Y", strtotime('+'.$i.' year', $this->day));
					}
				}
			}
		}
		
		$grid = array(
			'header' => array(
				'cells' => array(
					array(
						'type' => "th",
						'value' => __('Current date')
					),
					array(
						'type' => "th",
						'value' => __('New date(s)')
					)
				)
			),
			'lines' => array(
				'cells' => array(
					array(
						'type' => "td",
						'id' => "preview_changes_align_top",
						'rowspan' => count($days) + 1,
						'value' => date("d/m/Y", $this->day)
					)
				)
			),
		);
		
		foreach ($days as $day) {
			$grid[] = array(
				'cells' => array(
					array(
						'type' => "td",
						'value' => $day
					)
				)
			);
		}
		
		if (empty($days)) {
			$grid['lines']['cells'][] = array(
				'type' => "td",
				'value' => ""
			);
		}
		
		$html_table = new Html_table(array('lines' => $grid));
		
		return $html_table->show();
	}
	function show_line() {
	  $bank = new bank();
	  $bank->load(array('id' => $this->banks_id));
	  return "<tr><td>".date('d/m/Y',$this->day)."</td><td>".$this->amount_inc_vat."</td><td>".$bank->name."</td><td>".$this->comment."</td></tr>";
	}
	
	function periods() {
		return array(
			"0" => "--",
			"1" => "1 ".__("month"),
			"2" => "2 ".__("months"),
			"3" => "3 ".__("months"),
			"4" => "4 ".__("months"),
			"5" => "5 ".__("months"),
			"6" => "6 ".__("months"),
			"7" => "7 ".__("months"),
			"8" => "8 ".__("months"),
			"9" => "9 ".__("months"),
			"10" => "10 ".__("months"),
			"11" => "11 ".__("months"),
			"12" => "12 ".__("months"),
			"1t" => "1 ".__("quarter"),
			"2t" => "2 ".__("quarters"),
			"3t" => "3 ".__("quarters"),
			"4t" => "4 ".__("quarters"),
			"1a" => "1 ".__("year"),
			"2a" => "2 ".__("years"),
		);
	}
	
	function clean_amounts_from_ajax(array $amounts) {
		$cleaned = array();
		foreach ($amounts as $amount) {
			if (!empty($amount)) {
				$cleaned[] = (float)str_replace(",", ".", $amount);
			}
		}
		return $cleaned;
	}

	function get_form() {
		$banks = new Banks();
		$banks->select();
		$sources = new Sources();
		$sources->select();

		$form = "<center><div class=\"form\"><form method=\"post\" name=\"menu_actions_import_form\" action=\"".link_content("content=writingsimport.php")."\" enctype=\"multipart/form-data\"><table>";
		$radio = new Html_Radio("select_form", array(__("banks"),__("sources")));
		$import_file = new Html_Input("menu_actions_import_file", "", "file");
		$bank_select = new Html_Select("menu_actions_import_bank", $banks->names_of_selected_banks());
		$sources_select = new Html_Select("menu_actions_import_source", $sources->names());
		$submit = new Html_Input("menu_actions_import_submit", "Ok", "submit");

		$form .= "<tr><td>".utf8_ucfirst(__('file'))." : </td><td>".$import_file->item("")."</td></tr>";
		$form .= "<tr><td>".utf8_ucfirst(__('import choice'))." : </td><td>".$radio->item("")."</td></tr>";
		$form .= "<tr id=\"bank_tr\"><td>".utf8_ucfirst(__('bank'))." : </td><td>".$bank_select->item("")."</td></tr>";
		$form .= "<tr id=\"source_tr\"><td>".utf8_ucfirst(__('source'))." : </td><td>".$sources_select->item("")."</td></tr>";
		$form .= "<tr><td>".$submit->input()."</td></tr>";
		$form .= "</table></form></div></center>";
		return $form;
	}

	function get_form_sources() {
		$sources = new Sources();
		$sources->select();
		$form = "<center><div class=\"form\" ><form method=\"post\" name=\"menu_actions_import_source_form\" action=\"".link_content("content=writingsimport.php")."\" enctype=\"multipart/form-data\"><table>";
		$import_file = new Html_Input("menu_actions_import_file", "", "file");
		$bank_select = new Html_Select("menu_actions_import_source", $sources->names());
		$submit = new Html_Input("menu_actions_import_submit", "Ok", "submit");
		$form .= "<tr><td>".utf8_ucfirst(__('file'))." : </td><td>".$import_file->item("")."</td></tr>";
		$form .= "<tr><td>".utf8_ucfirst(__('source'))." : </td><td>".$bank_select->item("")."</td></tr>";
		$form .= "<tr><td>".$submit->input()."</td></tr>";
		$form .= "</table></form></div></center>";
		return $form;
	}
}
