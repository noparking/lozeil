<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Balances extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"accountingcodes",
			"accountingcodes_affectation",
			"activities",
			"balances",
			"reportings"
		);
	}

	function test_get_where() {
		$_SESSION['filter']['start'] = 1375308000;
		$_SESSION['filter']['stop'] = 1385308000;

		list($start, $stop) = determine_month($_SESSION['filter']['start']);

		$balances = new Balances();
		$balances->filter_with(array('start' => $start, 'stop' => $stop));
		$get_where = $balances->get_where();
		$this->assertPattern("/balances.day >= 1375308000/", $get_where[0]);
		$this->assertPattern("/balances.day <= 1377986399/", $get_where[1]);

		$balances2 = new Balances();
		$get_where2 = $balances2->get_where();
		$this->assertTrue(!isset($get_where2[0]));
		$this->assertFalse(isset($get_where2[1]));
	}

	function test_filter_with() {
		$balances = new Balances();
		$balances->filter_with(array('start' => mktime(0, 0, 0, 3, 9, 2013), 'stop' => mktime(0, 0, 0, 3, 10, 2013)));
		
		$this->assertEqual($balances->filters['start'], 1362783600);
		$this->assertEqual($balances->filters['stop'], 1362870000);
	}

	function test_get_accoutingcode_affectable() {
		$code_a1 = new Accounting_Code();
		$code_a1->name = "Code Comptable";
		$code_a1->number = 1;
		$code_a1->save();

		$code_a2 = new Accounting_Code();
		$code_a2->name = "Code Comptable 2";
		$code_a2->number = 1;
		$code_a2->save();

		$affectation_a1 = new Accounting_Code_Affectation();
		$affectation_a1->accountingcodes_id = $code_a1->id;
		$affectation_a1->reportings_id = 1;
		$affectation_a1->save();

		$affectation_a2 = new Accounting_Code_Affectation();
		$affectation_a2->accountingcodes_id = $code_a2->id;
		$affectation_a2->reportings_id = 0;
		$affectation_a2->save();

		$balance = new Balance();
		$balance->amount = 100;
		$balance->accountingcodes_id = $code_a1->id;
		$balance->day = time();
		$balance->save();

		$balance = new Balance();
		$balance->amount = 200;
		$balance->accountingcodes_id = $code_a2->id;
		$balance->day = time();
		$balance->save();

		$balances = new Balances();
		$data = $balances->get_accoutingcode_affectable();

		$this->assertEqual(count($data), 1);
		$this->assertFalse(array_key_exists($code_a1->id, $data));
		$this->assertTrue(array_key_exists($code_a2->id, $data));
		
		$this->truncateTables("accountingcodes", "accountingcodes_affectation", "balances");
	}

	function test_display() {
		$activity = new Activity();
		$activity->name = "Activité";
		$activity->save();

		$reporting1 = new Reporting();
		$reporting1->name = "Libellé 1";
		$reporting1->activities_id = $activity->id;
		$reporting1->save();

		$reporting2 = new Reporting();
		$reporting2->name = "Libellé 2";
		$reporting2->activities_id = $activity->id;
		$reporting2->save();

		$code = new Accounting_Code();
		$code->name = "Code Comptable";
		$code->save();

		$affectation1 = new Accounting_Code_Affectation();
		$affectation1->accountingcodes_id = $code->id;
		$affectation1->reportings_id = $reporting1->id;
		$affectation1->save();

		$line1 = new Balance();
		$line1->accountingcodes_id = $code->id;
		$line1->name = "première ligne";
		$line1->amount = 350.500;
		$line1->number = "60410000";
		$line1->day = time();
		$line1->save();

		$amounts = array(10);
		$line1->split($amounts, "amount");

		$line2 = new Balance();
		$line2->accountingcodes_id = $code->id;
		$line2->name = "deuxième ligne";
		$line2->amount = -350.500;
		$line2->day = time() - 100;
		$line2->save();

		$from = time() - 10;
		$to = time() + 10;

		$balances = new Balances();
		$balances->filter_with(array('start' => $from, 'stop' => $to));
		$balances->select();

		$this->assertEqual(count($balances), 2);
		
		$view = $balances->display();

		$this->assertPattern("/première ligne/", $view);
		$this->assertNoPattern("/350.50/", $view);
		$this->assertPattern("/340.50/", $view);
		$this->assertPattern("/Libellé/", $view);
		$this->assertPattern("/Activité/", $view);
		$this->assertPattern("/Libellé 1/", $view);
		$this->assertPattern("/<div class=\"modify show_acronym\">/", $view);
		$this->assertPattern("/<div class=\"split show_acronym\">/", $view);
		$this->assertPattern("/<div class=\"delete show_acronym\">/", $view);

		$this->assertNoPattern("/deuxième ligne/", $view);
		$this->assertNoPattern("/-350.50/", $view);

		$this->assertPattern("/première ligne \(split 1\)/", $view);
		$this->assertPattern("/<div class=\"merge show_acronym\">/", $view);
		$this->assertPattern("/10.00/", $view);

		$this->truncateTables("accountingcodes", "accountingcodes_affectation", "balances");
	}
}