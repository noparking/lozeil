<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Activity extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"activities",
			"reportings"
		);
	}
	
	function test_clean() {
		$activity = new Activity();
		$cleaned = $activity->clean(array('name' => '   478   <h1>'));
		$this->assertEqual($cleaned['name'], "478");
	}

	function test_save_load() {
		$activity = new Activity();
		$activity->name = "first activity";
		$activity->save();

		$activity_loaded = new Activity();
		$this->assertTrue($activity_loaded->load(array('id' => 1)));
		$this->assertEqual($activity_loaded->name, $activity->name);

		$this->truncateTables("activities");
	}
	
	function test_update() {
		$activity = new Activity();
		$activity->name = "first activity";
		$activity->save();

		$activity_loaded = new Activity();
		$activity_loaded->id = 1;
		$activity_loaded->name = "modify activity";
		$activity_loaded->update();

		$activity_loaded2 = new Activity();
		$this->assertTrue($activity_loaded2->load(array("id" => 1 )));
		$this->assertNotEqual($activity_loaded2->name, $activity->name);

		$this->truncateTables("activities");
	}
		
	function test_delete() {
		$activity = new Activity();
		$activity->name = "first activity";
		$activity->save();

		$this->assertTrue($activity->load(array("id" => 1)));
		$activity->delete();
		$this->assertFalse($activity->load(array("id" => 1)));

		$this->truncateTables("activities");
	}

	function test_delete_in_cascade() {
		$activity = new Activity();
		$activity->name = "Activité";
		$activity->save();

		$activity_not_deleted = new Activity();
		$activity_not_deleted->name = "Global";
		$activity_not_deleted->save();

		$reporting = new Reporting();
		$reporting->name = "Libellé";
		$reporting->activities_id = $activity->id;
		$reporting->save();

		$reporting_not_deleted = new Reporting();
		$reporting_not_deleted->name = "Libellé non supprimé";
		$reporting_not_deleted->activities_id = $activity_not_deleted->id;
		$reporting_not_deleted->save();;

		$activities = new Activities();
		$reportings = new Reportings();

		$activities->select();
		$reportings->select();

		$this->assertEqual(count($activities), 2);
		$this->assertEqual(count($reportings), 2);

		$activity->delete_in_cascade();

		$activities->select();
		$reportings->select();

		$this->assertEqual(count($activities), 1);
		$this->assertEqual(count($reportings), 1);
		$this->assertRecordNotExists("activities", array('name' => "Activité"));
		$this->assertRecordNotExists("reportings", array('name' => "Libellé"));
		$this->assertRecordExists("reportings", array('name' => "Libellé non supprimé"));
		$this->assertRecordExists("reportings", array('name' => "Libellé non supprimé"));

		$this->truncateTables("activities", "reportings");
	}


	function test_form_add() {
		$activity = new Activity();
		$form = $activity->form_add();
		$this->assertPattern("/name_new/", $form);
		$this->truncateTable("activities");
	}

	function test_show_form_modification () {
		$activity = new Activity();
		$activity->name = "Logiciel Opentime";
		$activity->save();
		$form = $activity->show_form_modification();
		$this->assertPattern("/Logiciel Opentime/",$form);
		$this->assertPattern("/action/",$form);

		$this->truncateTables("activities");
	}
}
