<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2016 */

class Activity extends Record {
	public $id = 0;
	public $name = 0;
	public $global = 0;
	public $timestamp = 0;
		
	function __construct($id = 0, db $db = null) {
		parent::__construct($db);
		$this->id = (int)$id;
	}
	
	function insert() {
		list($bool, ,$this->id) = $this->db->query_with_id("
			INSERT INTO ".$this->db->config['table_activities']." 
			SET name = ".$this->db->quote( $this->name ).",
			global = ".(int)$this->global.",
			timestamp = ".time()
		);
		return $bool;
	}
		
	function update() {
		if ($this->id <= 0) {
			return false;
		} else {
			$query = "UPDATE ".$this->db->config['table_activities']." 
						SET name = ".$this->db->quote( $this->name ).",
						global = ".(int)$this->global.",
						timestamp = ".time()." WHERE id = ".(int)$this->id;
			list( , $affected_rows) = $this->db->query($query);

			return $affected_rows == 1;
		}
	}

	function save() {
		return (is_numeric( $this->id ) and $this->id > 0)?$this->update():$this->insert();
	}
	
	function load(array $key = array(), $table = "activities", $columns = null) {
		return parent::load( $key, $table, $columns );
	}

	function update_id($id) {
		$query = "UPDATE ".$this->db->config['table_activities']." 
			SET id = ".(int)$id."
			WHERE id = ".(int)$this->id;

		$this->db->query($query);
		return $id;
	}

	function delete() {
		if ($this->id <= 0) {
			return false;
		} else {
			list( , $affected_rows) = $this->db->query("DELETE FROM ".$this->db->config['table_activities']." WHERE id = ".$this->id);
			if ($affected_rows <= 0) {
				return false;
			} else {
				$this->id = 0;
				return true;
			}
		}
	}
	
	function delete_in_cascade() {
		$reportings = new Reportings();
		$reportings->filter_with(array('activities_id' => $this->id));
		$reportings->select();
		foreach ($reportings as $reporting) {
			$reporting->delete_in_cascade();				
		}
		$this->delete();
	}

	function create_default_plan() {
		$activities = new Activities();
		$activities->select();

		if ($activities->count() == 1) {
			$this->create_multiple_default_plan("first");
		} else if ($activities->count() == 2 and $activities->global_exists() == true) {
			$this->create_multiple_default_plan("first");
		} else {
			$this->create_multiple_default_plan("other");			
		}
	}

	function create_single_default_plan() {
		$reportings = new Reportings();
		require dirname(__FILE__)."/../lang/".$GLOBALS['param']['locale_lang'].".reportings.php";

		$this->global = 1;
		$this->save();

		$plan = $reportings_plan['single_default_plan'];
		$reportings->save_reportings_default($plan, $this->id);
	}

	function create_multiple_default_plan($status) {
		$reportings = new Reportings();
		require dirname(__FILE__)."/../lang/".$GLOBALS['param']['locale_lang'].".reportings.php";

		if ($status == "global") {
			$plan = $reportings_plan['multiple_default_plan_global'];
		} else if ($status == "first") {
			$plan = $reportings_plan['multiple_default_plan_first'];
		} else {
			$plan = $reportings_plan['multiple_default_plan_other'];
		}
		$reportings->save_reportings_default($plan, $this->id);
	}

	function clean($variables) {
		$cleaned = array();

		if (isset($variables['name'])) {
			$cleaned['name'] = strip_tags($variables['name']);
			$cleaned['name'] = trim(preg_replace('/\s+/', ' ', $cleaned['name']));			
		}

		return $cleaned;
	}
	
	function is_recently_modified(){
		if ($this->timestamp > (time() - 10)) {
			return true;
		}
		return false;
	}

	function show_operations() {
		return $this->show_form_modify().$this->form_delete();
	}

	function form_add() {
		$activities = new Activities();
		$name = new Html_Input("name_new");
		$global = new Html_Checkbox("global_new", 0, false);
		$submit = new Html_Input("submit", __("add"), "submit");

		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'title' => array(
					'value' => "<h1>".__("add new activity")."</h1>"
				),
				'name' => array(
					'value' => $name->item(ucfirst(__("name")))
				),
				'global' => array(
					'value' => $activities->global_exists() == false ? $global->item(ucfirst(__("global"))) : ""
				),
				'submit' => array(
					'value' => $submit->input()
				)
			)
		);

		$list = new Html_List($grid);

		$form = "<div class=\"form_add_activity\">
			<form method=\"post\" action=\"\" id=\"form_add_activity\" enctype=\"multipart/form-data\">".
				$list->show()."
			</form>
		</div>";

		return $form;
	}
	
	function show_form_modification() {
		$activities = new Activities();
		$name = new Html_Input("activities[".$this->id."][name]",$this->name);
		$submit = new Html_Input("activities[".$this->id."][submit]", __('modify'), "submit");
		$global = new Html_Checkbox("global", 0, $this->global);
		$action = new Html_Input("action", "save", "hidden");
		
		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'title' => array(
					'value' => "<h1>".__("modify an activity")."</h1>"
				),
				'name' => array(
					'value' => $name->item(ucfirst(__("name")))
				),
				'global' => array(
					'value' => ($activities->global_exists() == false or $this->global == 1) ? $global->item(ucfirst(__("global"))) : ""
				),
				'submit' => array(
					'value' => $submit->input()
				)
			)
		);

		$list = new Html_List($grid);

		$form = "<div class=\"form_modif_activity\">
			<form method=\"post\" action=\"\" id=\"form_modif_activity\" enctype=\"multipart/form-data\">".
				$action->input_hidden().$list->show()."
			</form>
		</div>";

		return $form;
	}
	
	function form_delete() {
		$input_hidden_id = new Html_Input("table_activities_delete_id", $this->id);
		$input_hidden_action = new Html_Input("action", "delete");
		$submit = new Html_Input("activities[".$this->id."][submit]", '',"submit");
		$submit->properties = array(
			'onclick' => "javascript:return confirm('".utf8_ucfirst(__('are you sure?'))."')"
		);
		
		$form = "<div class=\"delete show_acronym\">
					<form method=\"post\" name=\"table_activities_form_delete\" action=\"\" enctype=\"multipart/form-data\">".
						$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input()."
					</form>
					<span class=\"acronym\">".__('delete')."</span>
				</div>";
		
		return $form;
	}
	
	function show_form_add() {
		$form = "<div class=\"duplicate show_acronym\">
					<span class=\"operation\"> <input class=\"add\" type=\"button\" id=\"".$this->id."\"/> </span> <br />
					<span class=\"acronym\">".__('add')."</span>
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
}
