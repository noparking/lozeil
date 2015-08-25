<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Reporting extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"activities",
			"accountingcodes_affectation",
			"activities",
			"reportings"
		);
	}

	function tearDown() {
		$this->truncateTable("accountingcodes_affectation");
		$this->truncateTable("activities");
		$this->truncateTable("reportings");
	}

	function test_save_load() {
		$reporting = new Reporting();
		$reporting->name = "capital brut";
		$reporting->save();
		$this->assertTrue($reporting->id > 0);
		$reporting_loaded = new Reporting();
		$reporting_loaded->load(array('id'=> $reporting->id));
		$this->assertTrue($reporting_loaded->id > 0);
	}
	
	function test_clean() {
		$reporting = new Reporting();
		$cleaned_data = $reporting->clean(array('name' => "     <h1>    Capital Brut </h1>   "));

		$this->assertEqual($cleaned_data['name'], "Capital Brut");
	}

	function test_delete() {
		$reporting = new Reporting();
		$reporting->name = "capital brut";
		$reporting->save();

		$this->assertTrue($reporting->id > 0);
		$id = $reporting->id;
		$reporting->delete();

		$this->assertRecordNotExists("reportings", array('id' => $id));
	}

	function test_delete_in_cascade() {
		$reporting1 = new Reporting();
		$reporting1->name = "capital brut";
		$reporting1->save();
		
		$reporting2 = new Reporting();
		$reporting2->name = "marge brut";
		$reporting2->reportings_id = $reporting1->id;
		$reporting2->save();

		$affectation1 = new Accounting_Code_Affectation();
		$affectation1->reportings_id = $reporting1->id;
		$affectation1->save();
		
		$affectation2 = new Accounting_Code_Affectation();
		$affectation2->reportings_id = $reporting2->id;
		$affectation2->save();

		$affectations = new Accounting_Codes_Affectation();
		$affectations->select();
		$this->assertEqual(count($affectations), 2);
		
		$reportings = new Reportings();
		$reportings->select();
		$this->assertEqual(count($reportings), 2);
		
		$reporting1->delete_in_cascade();
		$affectations->select();

		$this->assertRecordExists("accountingcodes_affectation", array('reportings_id' => 0));
		$this->assertRecordExists("accountingcodes_affectation", array('reportings_id' => 0));
		$this->assertRecordNotExists("reportings", array('id' => $reporting1->id));
		$this->assertRecordNotExists("reportings", array('id' => $reporting2->id));
	}

	function test_desaffect() {
		$reporting = new Reporting();
		$reporting->name = "capital brut";
		$reporting->save();

		$affectation = new Accounting_Code_Affectation();
		$affectation->reportings_id = $reporting->id;
		$affectation->save();

		$affectations = new Accounting_Codes_Affectation();
		$affectations->select();
		$this->assertEqual(count($affectations), 1);
		
		$reportings = new Reportings();
		$reportings->select();
		$this->assertEqual(count($reportings), 1);
		
		$reporting->desaffect();
		$this->assertRecordNotExists("accountingcodes_affectation", array('reportings_id' => $reporting->id));
	}

	function test_desaffect_in_cascade() {
		$reporting1 = new Reporting();
		$reporting1->name = "capital brut";
		$reporting1->save();
		
		$reporting2 = new Reporting();
		$reporting2->name = "marge net";
		$reporting2->reportings_id = $reporting1->id;
		$reporting2->save();

		$affectation1 = new Accounting_Code_Affectation();
		$affectation1->reportings_id = $reporting1->id;
		$affectation1->save();
		
		$affectation2 = new Accounting_Code_Affectation();
		$affectation2->reportings_id = $reporting2->id;
		$affectation2->save();

		$affectations = new Accounting_Codes_affectation();
		$affectations->select();
		$this->assertEqual(count($affectations), 2);
		
		$reportings = new Reportings();
		$reportings->select();
		$this->assertEqual(count($reportings), 2);
		
		$reporting1->desaffect_in_cascade();
		$this->assertRecordNotExists("accountingcodes_affectation", array('reportings_id' => $reporting1->id));
		$this->assertRecordNotExists("accountingcodes_affectation", array('reportings_id' => $reporting2->id));
	}

	function test_contents() {
		$reporting = new Reporting();
		$reporting->name = "capital brut";
		$reporting->addContent(10);
		$reporting->save();
		$this->assertTrue($reporting->id > 0);
		$reporting_loaded = new Reporting();
		$reporting_loaded->load(array('id'=> $reporting->id));
		$this->assertTrue($reporting_loaded->id > 0);
		$this->assertEqual($reporting_loaded->contents, $reporting->contents);
	}

	function test_form_activity() {
		$activity = new Activity();
		$activity->name = "Activité principale";
		$activity->save();

		$reporting = new Reporting();
		$view = $reporting->form_activity($activity->id, 1);

		$this->assertPattern("/".ucfirst(__("activity"))."/", $view);
		$this->assertPattern("/Activité principale/", $view);
		$this->assertPattern("/".ucfirst(__("fiscal year begin"))."/", $view);
		$this->assertPattern("/1970/", $view);

		$this->truncateTables("activities");
	}

	function test_form_edit() {
		$activity1 = new Activity();
		$activity1->name = "Activité 1";
		$activity1->global = 0;
		$activity1->save();

		$reporting = new Reporting();
		$reporting->base = 1;
		$reporting->activities_id = $activity1->id;
		$reporting->save();
		$form = $reporting->form_edit();

		$this->assertPattern("/".ucfirst(__("name"))."/", $form);
		$this->assertPattern("/".ucfirst(__("included in"))."/", $form);
		$this->assertPattern("/".ucfirst(__("base"))."/", $form);

		$reporting = new Reporting();
		$reporting->base = 0;
		$form = $reporting->form_edit();

		$this->assertPattern("/".__("base")."/", $form);

		$activity2 = new Activity();
		$activity2->name = "Activité 2";
		$activity2->global = 1;
		$activity2->save();

		$reporting->activities_id = $activity2->id;
		$reporting->save();
		$form = $reporting->form_edit();
		$this->assertNoPattern("/".__("base")."/", $form);

		$this->truncateTables("activities", "reportings");
	}

	function test_form_add() {
		$activity1 = new Activity();
		$activity1->name = "Activité 1";
		$activity1->global = 0;
		$activity1->save();

		$reporting = new Reporting();
		$reporting->base = 1;
		$reporting->activities_id = $activity1->id;
		$reporting->save();
		$form = $reporting->form_edit();

		$this->assertPattern("/".ucfirst(__("name"))."/", $form);
		$this->assertPattern("/".ucfirst(__("included in"))."/", $form);
		$this->assertPattern("/".ucfirst(__("base"))."/", $form);

		$reporting = new Reporting();
		$reporting->base = 0;
		$form = $reporting->form_edit();
		$this->assertPattern("/".__("base")."/", $form);

		$activity2 = new Activity();
		$activity2->name = "Activité 2";
		$activity2->global = 1;
		$activity2->save();

		$reporting->activities_id = $activity2->id;
		$reporting->save();
		$form = $reporting->form_edit();
		$this->assertNoPattern("/".__("base")."/", $form);

		$this->truncateTables("activities", "reportings");
	}

	function test_form_include() {
		$activity = new Activity();
		$activity->name = "Activité Globale";
		$activity->save();

		$reporting1 = new Reporting();
		$reporting1->name = "Capital Brut";
		$reporting1->activities_id = $activity->id;
		$reporting1->save();

		$reporting2 = new Reporting();
		$reporting2->name = "Marge Brut";
		$reporting2->reportings_id = $reporting1->id;
		$reporting2->activities_id = $activity->id;
		$reporting2->save();

		$reporting = new Reporting();
		$form = $reporting->form_include();

		$this->assertPattern("/<option disabled> --------- Activité Globale --------- <\/option>/", $form[md5($activity->id)]);
		$this->assertPattern("/Capital Brut/", $form[$reporting1->id]);
		$this->assertPattern("/-- Marge Brut/", $form[$reporting2->id]);

		$this->truncateTables("activities", "reportings");
	}
}
