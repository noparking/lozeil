<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Activities extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"activities",
			"reportings"
		);
	}

	function test_get_where() {
		$activities = new Activities();
		$activities->filter_with(array('global' => 1));
		$get_where = $activities->get_where();
		$this->assertPattern("/activities.global = 1/", $get_where[0]);

		$activities2 = new Activities();
		$get_where2 = $activities2->get_where();
		$this->assertTrue(!isset($get_where2[0]));
		$this->assertFalse(isset($get_where2[1]));
	}

	function test_filter_with() {
		$activities = new Activities();
		$activities->filter_with(array('global' => 0));
		$this->assertEqual($activities->filters['global'], 0);
	}

	function test_delete() {
		$activity = new Activity();
		$activity->name = "Activité";
		$activity->save();

		$activities = new Activities();
		$activities->select();

		$this->assertEqual(count($activities), 1);

		$activities->delete();
		$activities->select();

		$this->assertEqual(count($activities), 0);
		$this->truncateTable("activities");
	}

	function test_delete_in_cascade() {
		$activity = new Activity();
		$activity->name = "Activité";
		$activity->save();

		$reporting = new Reporting();
		$reporting->name = "Libellé";
		$reporting->activities_id = $activity->id;
		$reporting->save();

		$activities = new Activities();
		$activities->select();

		$reportings = new Reportings();
		$reportings->select();

		$this->assertEqual(count($activities), 1);
		$this->assertEqual(count($reportings), 1);

		$activities->delete_in_cascade();
		$activities->select();
		$reportings->select();

		$this->assertEqual(count($activities), 0);
		$this->assertEqual(count($reportings), 0);
		$this->truncateTable("activities", "reportings");
	}

	function test_global_exists() {
		$activity = new Activity();
		$activity->name = "Global";
		$activity->global = 1;
		$activity->save();

		$activities = new Activities();
		$activities->filter_with(array('global' => 1));
		$activities->select();

		$this->assertEqual(count($activities), 1);

		$activities->filter_with(array('global' => 0));
		$activities->select();

		$this->assertEqual(count($activities), 0);

		$this->truncateTables("activities", "reportings");
	}

	function test_display() {
		$activity = new Activity();
		$activity->name = "Activité";
		$activity->save();

		$activities = new Activities();
		$activities->select();
		$view = $activities->display();

		$this->assertPattern("/Activité/", $view);
		$this->truncateTable("activities");
	}
}
