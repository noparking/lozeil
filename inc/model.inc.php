<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

class Model {
	public $data;
	public $generate_simple;
	public $generate_multiple;

	function display_options() {
		$prepare = new Html_Input("submit", ucfirst(__("prepare actual model")), "submit");
		$prepare->properties['data-active'] = "prepare";

		$generate_simple = new Html_Input("submit", ucfirst(__("generate simple activity model")), "submit");
		$generate_simple->properties['data-active'] = "generate_simple";
		
		$generate_multiple = new Html_Input("submit", ucfirst(__("generate multiple activities model")), "submit");
		$generate_multiple->properties['data-active'] = "generate_multiple";
		
		$apply = new Html_Input("submit", ucfirst(__("apply a new model")), "submit");
		$apply->properties['data-active'] = "apply";

		return "<div id=\"options\">".
			$prepare->input().$generate_simple->input().$generate_multiple->input().$apply->input()."
		</div>";
	}

	function generate_data() {
		$activities = new Activities();
		$activities->select();
		$reportings = new Reportings();
		$reportings->select();
		$codes = new Accounting_Codes();
		$codes->filter_with(array('>id' => 978));
		$codes->select();
		$affectations = new Accounting_Codes_Affectation();
		$affectations->select();

		$data = array(
			'activities' => $activities->getIterator(),
			'reportings' => $reportings->getIterator(),
			'codes' => $codes->getIterator(),
			'affectations' => $affectations->getIterator()
		);

		// $this->data = json_encode($data, JSON_PRETTY_PRINT); // FOR PHP > 5.4 ONLY
		$this->data = json_encode($data);
		$this->generate_simple = file_get_contents(dirname(__FILE__)."/../json/lozeil_simple.json");
		$this->generate_multiple = file_get_contents(dirname(__FILE__)."/../json/lozeil_multiple.json");
	}

	function apply($data) {
		$activities = new Activities();
		$activities->select();
		$activities->delete();
		foreach ($data['activities'] as $activity_data) {
			$activity = new Activity();
			$activity->fill($activity_data);
			$activity->insert();
			$activity->update_id($activity_data['id']);
		}
		$reportings = new Reportings();
		$reportings->select();
		$reportings->delete();
		foreach ($data['reportings'] as $reporting_data) {
			$reporting = new Reporting();
			$reporting->fill($reporting_data);
			$reporting->insert();
			$reporting->update_id($reporting_data['id']);
		}

		$codes = new Accounting_Codes();
		$codes->filter_with(array('>id' => 978));
		$codes->select();
		$codes->delete();
		foreach ($data['codes'] as $code_data) {
			$code = new Accounting_Code();
			$code->fill($code_data);
			$code->insert();
			$code->update_id($code_data['id']);
		}

		$affectations = new Accounting_Codes_Affectation();
		$affectations->select();
		$affectations->delete();
		foreach ($data['affectations'] as $affectation_data) {
			$affectation = new Accounting_Code_Affectation();
			$affectation->fill($affectation_data);
			$affectation->insert();
			$affectation->update_id($affectation_data['id']);
		}

		$balances = new Balances();
		$balances->select();
		$balances->delete();

		$imported = new Balances_Imported();
		$imported->select();
		$imported->delete();
	}

	function is_json($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	function display() {
		return "<div id=\"model\">".
			$this->display_prepare().$this->display_generate_simple().$this->display_generate_multiple().$this->display_apply()."
		</div>";
	}

	function display_prepare() {
		$title = "<h3>".ucfirst(__("actual model"))."</h3>";
		$input_area = new Html_Textarea("prepare_text", $this->data);
		$input_area->properties['readonly'] = "readonly";
		$input_area->properties['style'] = "width:500px;height:200px;";
		$submit = new Html_Input("select", ucfirst(__("select all")), "submit");

		return "<div id=\"prepare\">".
			$title.$input_area->input()."<br>".$submit->input()."
		</div>";
	}

	function display_generate_simple() {
		$title = "<h3>".ucfirst(__("simple activity"))."</h3>";
		$input_area = new Html_Textarea("prepare_text", $this->generate_simple);
		$input_area->properties['readonly'] = "readonly";
		$input_area->properties['style'] = "width:500px;height:200px;";
		$submit = new Html_Input("select", ucfirst(__("select all")), "submit");

		return "<div id=\"generate_simple\">".
			$title.$input_area->input()."<br>".$submit->input()."
		</div>";
	}

	function display_generate_multiple() {
		$title = "<h3>".ucfirst(__("multiple activities"))."</h3>";
		$input_area = new Html_Textarea("prepare_text", $this->generate_multiple);
		$input_area->properties['readonly'] = "readonly";
		$input_area->properties['style'] = "width:500px;height:200px;";
		$submit = new Html_Input("select", ucfirst(__("select all")), "submit");

		return "<div id=\"generate_multiple\">".
			$title.$input_area->input()."<br>".$submit->input()."
		</div>";
	}

	function display_apply() {
		$title = "<h3>".ucfirst(__("apply"))."</h3>";
		$input_area = new Html_Textarea("apply_text");
		$input_area->properties['style'] = "width:500px;height:200px;";
		$input_area->properties['placeholder'] = ucfirst(__("paste a model here"));
		$submit = new Html_Input("apply_submit", ucfirst(__("apply the model")), "submit");

		return "<div id=\"apply\">
			<form method=\"post\" action=\"\">".
				$title.$input_area->input()."<br>".$submit->input()."
			</form>
		</div>";
	}
}