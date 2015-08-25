<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

class Reporting extends Record {
	
	public $id = 0;
	public $norm = "";
	public $name = 0;
	public $sort = 0;
	public $reportings_id = 0;
	public $base = 0;
	public $contents = '';
	public $activities_id = 0;
	public $timestamp = 0;

	function __construct($id = 0, db $db = null) {
		parent::__construct ( $db );
		$this->id = ( int ) $id;
	}
	
	function save() {
		return (is_numeric($this->id) and $this->id > 0) ? $this->update() : $this->insert();
	}
	
	function load(array $key = array(), $table = "reportings", $columns = null) {
		return parent::load ( $key, $table, $columns );
	}

	function insert() {
		if ($this->id > 0) {
			$this->id = 0;
		}
		
		$result = $this->db->query ( "
			SELECT MAX(sort) as max
			 FROM " . $this->db->config ['table_reportings'] );
		
		$data = $this->db->fetchArray ( $result [0] );
		;
		$this->sort = $data ['max'] + 1;
		$query = "INSERT INTO ".$this->db->config['table_reportings']." 
		SET norm = ".$this->db->quote($this->norm).",
		name = ".$this->db->quote($this->name).",
		sort = ".$this->sort.",
		base = ".(int)$this->base.",
		reportings_id = ".$this->reportings_id.",
		contents = '".$this->contents."',
		activities_id = ".$this->activities_id.",
		timestamp = ".time();

		list( $bool, , $this->id ) = $this->db->id( $query );
		$this->db->status($bool, "i", __("reporting"));
		return $bool;
	}

	function update() {
		if ($this->id <= 0) {
			return false;
		} else {
			$query = "UPDATE " . $this->db->config ['table_reportings'].
			" SET norm = ".$this->db->quote($this->norm).",
			name = ".$this->db->quote ($this->name).",
			sort = ".$this->sort.",
			reportings_id = ".$this->reportings_id.",
			base = ".$this->base.",
			contents = '".$this->contents."',
			activities_id = '".$this->activities_id."',
			timestamp = '".time()."' 
			WHERE id = ".$this->id;

 			list( , $affected_rows ) = $this->db->query ( $query );
			$this->db->status($affected_rows, "u", __("reporting"));
			return $affected_rows == 1;
		}
	}
	
	function update_id($id) {
		$query = "UPDATE ".$this->db->config['table_reportings']." 
			SET id = ".(int)$id."
			WHERE id = ".(int)$this->id;

		$this->db->query($query);
		return $id;
	}

	function replace() {
		if ($this->id <= 0) {
			return false;
		} else {
			list(,$affected_rows) =
				$this->db->query( "REPLACE INTO ".$this->db->config['table_reportings']." SET id = ".( int )$this->id.","." name = " .$this->db->quote($this->name));

			return $affected_rows == 1;
		}
	}
	
	function delete() {
		if ($this->id <= 0) {
			return false;
		} else {
			list(,$affected_rows) = $this->db->query("DELETE FROM ".$this->db->config['table_reportings']." WHERE id = ".$this->id);
			$this->db->status($affected_rows, "d", __("reporting"));
			if ($affected_rows <= 0) {
				return false;
			} else {
				$this->id = 0;
				return true;
			}
		}
	}

	function changesort($id) {
		$reporting = new Reporting();
		$reporting->load( array('id' => $id));
		$this->sort = $reporting->sort;
		if ($reporting->id > 0) {
			$query = "UPDATE `reportings` SET `sort`= `sort` + 1 WHERE  `sort` >= " . $reporting->sort;
			$this->db->query($query);
			$this->update();
		}
		$this->save();
	}
	
	function toend() {
		$result = $this->db->query( "SELECT MAX(sort) as max FROM ".$this->db->config['table_reportings']);
		
		$data = $this->db->fetchArray($result[0]);
		$this->sort = $data['max'] + 1;
		$this->update();
	}
	
	function checkdependence($id) {
		$reporting = new Reporting();
		$reporting->load(array('id' => $id));
		if ($this->reportings_id == $reporting->id ) {
			return false;
		} 
		else if ($reporting->reportings_id == 0) {
			return true;
		}
		else {
			return $this->checkdependence($reporting->reportings_id);
		}
	}
	
	function addContent($value) {
		$array = $this->getContents();
		$array[] = $value;
		$this->setContents($array);
	}

	function setContents($array) {
		if(is_array($array) and !empty($array)) {
			foreach($array as $key => $id ) {
				
			}
			$this->contents = serialize($array);
		}
		else {
			$this->contents = '';
		}
	}

	function getContents() {
		$return = array();
		if (!empty($this->contents)) {
			foreach( unserialize($this->contents) as $rest => $id) {
				$return[intval($id)] = intval($id);	
			}
		}
		return $return;
	}

	function clean($data) {
		$cleaned_data = array();

		if (isset($data['name'])) {
			$cleaned_data['name'] = trim(strip_tags($data['name']));
		}
		if (isset($data['base'])) {
			$cleaned_data['base'] = $data['base'];
		}
		if (isset($_SESSION['currentactivity'])) {
			$cleaned_data['activities_id'] = $_SESSION['currentactivity'];
		}

		if (isset($data['reportings_id'])) {
			$master = new Reporting();
			$master->load(array('id' => $_REQUEST['reportings_id']));
			if ($data['action'] == "add_reporting" or ($master->id > 0 and $master->id != $this->id and $this->reportings_id != $master->id  and $this->id != $master->reportings_id)) {
				$cleaned_data['reportings_id'] = (int)$master->id;
				$cleaned_data['activities_id'] = (int)$master->activities_id;
			}
		} else {
			$cleaned_data['reportings_id'] = 0;
		}

		return $cleaned_data;
	}

	function delete_in_cascade() {
		$reportings = $this->get_reportings_childs();
		foreach ($reportings as $rpt) {
			$reporting = new Reporting();
			$reporting->load(array('id' => $rpt['id']));
			
			$reporting->desaffect();
			$reporting->delete();
		}
		$this->desaffect();
		$this->delete();
	}

	function desaffect() {
		$affectations = new Accounting_Codes_Affectation();
		$affectations->filter_with(array('reportings_id' => $this->id));
		$affectations->select();
		foreach ($affectations as $affectation) {
			$affectation->desaffect();
		}
	}

	function desaffect_in_cascade() {
		$this->desaffect();
		$reportings = $this->get_reportings_childs();
		foreach ($reportings as $rpt) {
			$reporting = new Reporting();
			$reporting->load(array('id' => $rpt['id']));
			$reporting->desaffect_in_cascade();
		}
	}
	
	function get_reportings_childs() {
		$query = "SELECT  `id` FROM `".$this->db->config['table_reportings']."` WHERE `reportings_id` = ".$this->id;
		$result = $this->db->query($query);
		$data = array();
		while(($d = $this->db->fetchArray($result[0]))) {
			$data[$d['id']] = array('id' => $d['id']);
		}
		return $data;
	}

	function is_recently_modified(){
		if ($this->timestamp > (time() - 10)) {
			return true;
		}
		return false;
	}

	function form_detail($period_id, $from = null) {
		if ($from == null) {
			$from = time();
		}
		list($from, $to) = determine_fiscal_year($from);
		$years = array();
		$begin_year = date("Y", $from);
		for ($i = intval($begin_year) - 7; $i <= intval($begin_year) + 7;$i++) {
			$years[$i]= $i;
		}

		$balances = new Balances();
		$grid = $balances->grid_period($from, $to);

		$date_picker_from = new Html_Select("date_picker_from", $years, date("Y", $from));
		$periods_picker = new Html_Select("period_picker", $grid, $period_id);
		$submit = new Html_Input("show_submit",__('show'), "submit");
		$month = $GLOBALS['param']['fiscal year begin'];
		if (strlen($month) == 1)  {
			$month = "0".$month;
		}
		
		$grid = array(
			'leaves' => array(
				'fiscal_year' => array(
					'style' => "padding: 5px;",
					'value' => ucfirst(__("fiscal year begin"))." 01/".$month."/".$date_picker_from->item("")
				),
				'period' => array(
					'style' => "padding: 5px;",
					'value' => ucfirst(__("variation type")).": ".$periods_picker->item("")
				),
				'submit' => array(
					'style' => "padding: 5px;",
					'value' => $submit->input()
				),
			)
		);

		$list = new Html_List($grid);
		$form = "<center><div class=\"form\"><form method=\"post\" action=\"\" enctype=\"multipart/form-data\">".$list->show()."</form></div></center>";

		return $form;
	}

	function form_activity($activity = 1, $from) {
		if ($from == null) {
			$from = time();
		}
		list($from, $to) = determine_fiscal_year($from);
		$years = array();
		$begin_year = date("Y", $from);
		for ($i = intval($begin_year) - 7; $i <= intval($begin_year) + 7;$i++) {
			$years[$i]= $i;
		}

		$activities = new Activities();
		$select = new Html_Select("activities_change", $activities->names(), $activity);
		$date_picker_from = new Html_Select("begin_date", $years, date("Y", $from));
		$submit = new Html_Input("show_submit",__('show'), "submit");
		$month = $GLOBALS['param']['fiscal year begin'];
		if (strlen($month) == 1)  {
			$month = "0".$month;
		}
		
		$grid = array(
			'leaves' => array(
				'activity' => array(
					'style' => "padding: 5px;",
					'value' => ucfirst(__("activity")).": ".$select->item("")
				),
				'fiscal_year' => array(
					'style' => "padding: 5px;",
					'value' => ucfirst(__("fiscal year begin"))." 01/".$month."/".$date_picker_from->item("")
				),
				'submit' => array(
					'style' => "padding: 5px;",
					'value' => $submit->input()
				),
				'form_add' => array(
					'style' => "padding: 5px;",
					'value' => $this->get_form_add_base()
				)
			)
		);

		$list = new Html_List($grid);
		$form = "<center><div class=\"form\"><form method=\"post\" action=\"\" enctype=\"multipart/form-data\">".$list->show()."</form></div></center>";

		return $form;
	}

	function form_include() {
		$include = array();
		$include[] = __('none');
		
		$reportings = new Reportings();
		$reportings->select();
		$old_activity_id = -1;
		foreach ($reportings->get_grid() as $reporting) {
			if ($old_activity_id != $reporting['activities_id']) {
					$activity = new Activity();
					$activity->load(array('id' => $reporting['activities_id']));
					$include[md5($activity->id)] = "<option disabled> --------- ".$activity->name." --------- </option>";
			}
			if ($reporting['level'] == 0) {
				$include[$reporting['id']] = $reporting['name'];
			} else if ($reporting['level'] == 1) {
				$include[$reporting['id']] = "-- ".$reporting['name'];
			} else {
				$include[$reporting['id']] = "---- ".$reporting['name'];
			}
	
			$old_activity_id = $reporting['activities_id'];
		}

		return $include;
	}

	function form_include_accountingcode() {
		$include = array();

		$reportings = new Reportings();
		$reportings->select();
		$old_activity_id = -1;
		foreach ($reportings->get_grid() as $reporting) {
			if ($old_activity_id != $reporting['activities_id']) {
					$activity = new Activity();
					$activity->load(array('id' => $reporting['activities_id']));
					$include[md5($activity->id)] = "<option disabled> --------- ".$activity->name." --------- </option>";
			}
			if ($reporting['level'] == 0) {
				$include[$reporting['id']] = $reporting['name'];
			} else if ($reporting['level'] == 1) {
				$include[$reporting['id']] = "-- ".$reporting['name'];
			} else {
				$include[$reporting['id']] = "---- ".$reporting['name'];
			}
	
			$old_activity_id = $reporting['activities_id'];
		}

		return $include;
	}

	function form_add() {
		$name_input = new Html_Input("name");
		$input_hidden_id = new Html_Input("reportingcode", $this->id, "hidden");
		$input_hidden_action = new Html_Input("action", "add_reporting", "submit");
		$includeinto = new Html_Select("reportings_id", $this->form_include(), $this->id);
		$submit = new Html_Input("submit", ucfirst(__("add")), "submit");

		$reporting = new Reporting();
		$reporting->load(array("base" => '1', 'activities_id' => $_SESSION['currentactivity']));

		$activity = new Activity();
		$activity->load(array('id' => $_SESSION['currentactivity']));

		$activities = new Activities();
		$activities->select();

		if ((count($activities) > 0 and $activity->global == 0) or (count($activities) == 1)) {
			if ($reporting->id == 0) {
				$base = new Html_Checkbox("base", 1);
			}
		}

		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'title' => array(
					'value' => "<h1>".__("add new reporting")."</h1>"
				),
				'name' => array(
					'value' => $name_input->item(ucfirst(__("name")))
				),
				'include' => array(
					'value' => $includeinto->item(ucfirst(__("included in")))
				),
				'base' => array(
					'value' => isset($base) ? $base->item(ucfirst(__("base"))) : ""
				),
				'submit' => array(
					'value' => $submit->input()
				)
			)
		);

		$list = new Html_List($grid);
		$form = "<div class=\"form_edit_reporting\">
			<form method=\"post\" name=\"table_reportings_add\" action=\"\" enctype=\"multipart/form-data\">".
				$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$list->show()."
			</form>
		</div>";

		return $form;
	}

	function form_edit() {
		$name_input = new Html_Input("name", $this->name);
		$input_hidden_id = new Html_Input("reportingcode", $this->id,"hidden");
		$input_hidden_action = new Html_Input("action", "edit_reporting", "submit");
		$includeinto = new Html_Select("reportings_id", $this->form_include(), $this->reportings_id);
		$submit = new Html_Input("submit", __("modify"), "submit");

		$reporting = new Reporting();
		$reporting->load(array('base' => 1, 'activities_id' => $this->activities_id));

		$activity = new Activity();
		$activity->load(array('id' => $this->activities_id));

		$activities = new Activities();
		$activities->select();

		if ((count($activities) > 0 and $activity->global == 0) or (count($activities) == 1)) {
			if ($reporting->id == 0 or $this->base == 1) {
				$base = new Html_Checkbox("base", "", $this->base);
			}
		}

		$grid = array(
			'class' => "itemsform",
			'leaves' => array(
				'title' => array(
					'value' => "<h1>".__("modify a reporting")."</h1>"
				),
				'name' => array(
					'value' => $name_input->item(ucfirst(__("name")))
				),
				'include' => array(
					'value' => $includeinto->item(ucfirst(__("included in")))
				),
				'base' => array(
					'value' => isset($base) ? $base->item(ucfirst(__("base"))) : ""
				),
				'submit' => array(
					'value' => $submit->input()
				)
			)
		);

		$list = new Html_List($grid);
		$form = "<div class=\"form_edit_reporting\">
			<form method=\"post\" name=\"table_reportings_edit\" action=\"\" enctype=\"multipart/form-data\">".
				$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$list->show()."
			</form>
		</div>";
		
		return $form;
	}

	function get_view($checkbox) {
		return "<div class=\"view\">".
					$checkbox->item("")."<br>
					<span class=\"acronym\"></span>
				</div>";
	}

	function get_form_modify_reporting($id) {
		return "<div class=\"modify show_acronym\">
					<input type=\"button\" class=\"modif modify_reporting\" id=\"reporting_edit_submit\" onclick=\"form_edit('{$id}');\" /><br>
					<span class=\"acronym\">".__("modify")."</span>
				</div>";
	}

	function get_form_add_reporting($id) {
		return "<div class=\"duplicate show_acronym\">
					<input type=\"button\" class=\"add add_reporting\" id=\"reporting_add_submit\" onclick=\"form_add('{$id}');\" /><br>
					<span class=\"acronym\">".__("add")."</span>
				</div>";
	}

	function get_form_add_base() {
		return "<div class=\"duplicate\">
					<input type=\"button\" class=\"add show_acronym\" id=\"reporting_add_submit\" style=\"position: relative; top: 4px;\" onclick=\"form_add(0);\" />".ucfirst(__('add new reporting'))."
					<span class=\"acronym\">".__('add')."</span>
				</div>";
	}

	function get_form_delete_reporting($id) {
		return "<div class=\"delete show_acronym\">
					<input type=\"button\" class=\"del delete_reporting\" id=\"reporting_del_submit\" onclick=\"delete_reporting('{$id}');\" /><br>
					<span class=\"acronym\">".__("delete")."</span>
				</div>";
	}

	function get_form_modify_accountingcode($id_account, $id) {
		return "<div class=\"modify show_acronym\">
					<input type=\"button\" class=\"modif modify_accounting\" id=\"accounting_edit_submit\" onclick=\"form_edit_accountingcode('".$id_account."', '".$id."');\" /><br>
					<span class=\"acronym\">".__("modify")."</span>
				</div>";
	}

	function get_form_delete_accountingcode($id_account, $id) {
		return "<div class=\"delete show_acronym\">
					<input type=\"button\" class=\"del delete_accounting\" id=\"accounting_del_submit\" onclick=\"delete_accounting('".$id_account."', '".$id."');\" /><br>
					<span class=\"acronym\">".__("delete")."</span>
				</div>";
	}

	function get_form_modify_accountingcode_non_affected($id) {
		return "<div class=\"modify show_acronym\">
					<input type=\"button\" class=\"modif modify_accounting\" id=\"accounting_non_affected_edit_submit\" onclick=\"form_edit_accountingcode_non_affected('".$id."');\" /><br>
					<span class=\"acronym\">".__("modify")."</span>
				</div>";
	}
}
