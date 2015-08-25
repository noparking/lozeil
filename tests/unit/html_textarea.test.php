<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Html_Textarea extends UnitTestCase {
	function test_item() {
		$champ = new Html_Textarea("champ");
		$this->assertPattern("/field_empty/", $champ->item("Label", "Display"));
		
		$champ = new Html_Textarea("champ", "valeur");
		$this->assertNoPattern("/field_empty/", $champ->item("Label", "Display"));
	}
	
	function test_input__avec_properties_checked() {
		$champ = new Html_Textarea("champ");
		$champ->properties['disabled'] = "disabled";
		$this->assertPattern("/ disabled=\"disabled\"/", $champ->input());
	}

	function test_input__sans_id() {
		$champ = new Html_Textarea("champ");
		$this->assertEqual($champ->name, "champ");
		$this->assertEqual($champ->name, $champ->id);
	}

	function test_input__avec_id() {
		$champ = new Html_Textarea("champ");
		$this->assertEqual($champ->name, "champ");
		$this->assertEqual($champ->name, $champ->id);
		$id = uniqid();
		$champ->id = $id;
		$this->assertEqual($champ->name, "champ");
		$this->assertEqual($champ->id, $id);
	}
	
	function test_input() {
		$champ = new Html_Textarea("champ");
		$this->assertPattern("/<textarea id=\"champ\" name=\"champ\">/", $champ->input());
		$this->assertPattern("/<\/textarea>/", $champ->input());
	}
}
