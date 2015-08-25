<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Model extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
				"activities",
				"reportings"
		);
	}

	function test_generate_data() {
		$activity = new Activity();
		$activity->name = "Activite Principale";
		$activity->save();

		$model = new Model();
		$model->generate_data();

		$this->assertPattern("/\"name\":\"Activite Principale\"/", $model->data);
		$this->assertPattern("/\"name\": \"Chiffres d'affaires\"/", $model->generate_simple);
		$this->assertPattern("/\"name\": \"Chiffres d'affaires\"/", $model->generate_multiple);
	}

	function test_apply() {
		$model = new Model();
		$model->generate_data();
		
		$data = json_decode($model->generate_simple, TRUE);
		$model->apply($data);

		$this->assertRecordExists("activities", array('name' => "Activité", 'global' => 1));
		$this->assertRecordExists("reportings", array('name' => "Marges brutes", 'reportings_id' => 0));
		$this->assertRecordExists("reportings", array('name' => "Chiffres d'affaires", 'norm' => "A", 'reportings_id' => 29));

		$this->truncateTables("activities", "reportings");
	}
}