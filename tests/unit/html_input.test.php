<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Html_Input extends UnitTestCase {
	function test_item() {
		$champ = new Html_Input("champ");
		$this->assertPattern("/field_empty/", $champ->item("Label", "Display"));
		
		$champ = new Html_Input("champ", "valeur");
		$this->assertNoPattern("/field_empty/", $champ->item("Label", "Display"));
	}

	function test_alert() {
		$champ = new Html_Input("champ");
		$this->assertPattern("/alert/", $champ->alert(array("alerte n°1")));
		$this->assertNoPattern("/alert/", $champ->alert(array()));
		$this->assertNoPattern("/alert/", $champ->alert("alerte n°1"));
	}
	
	function test_input__avec_properties_checked() {
		$champ = new Html_Input("champ");
		$champ->properties['checked'] = "checked";
		$this->assertPattern("/ checked=\"checked\"/", $champ->input());
	}

	function test_input__avec_properties_disabled() {
		$champ = new Html_Input("champ");
		$champ->properties['disabled'] = "disabled";
		$this->assertPattern("/ disabled=\"disabled\"/", $champ->input());
	}

	function test_input__sans_id() {
		$champ = new Html_Input("champ");
		$this->assertEqual($champ->name, "champ");
		$this->assertEqual($champ->name, $champ->id);
	}

	function test_input__avec_id() {
		$champ = new Html_Input("champ");
		$this->assertEqual($champ->name, "champ");
		$this->assertEqual($champ->name, $champ->id);
		$id = uniqid();
		$champ->id = $id;
		$this->assertEqual($champ->name, "champ");
		$this->assertEqual($champ->id, $id);
	}
}
