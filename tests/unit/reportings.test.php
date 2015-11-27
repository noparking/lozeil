<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2015 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Reportings extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"activities",
			"accountingcodes",
			"accountingcodes_affectation",
			"balances",
			"balancesperiod",
			"reportings"
		);
	}

	function tearDown() {
		$this->truncateTable("reportings");
		$this->truncateTable("activities");
		$this->truncateTable("accountingcodes");
		$this->truncateTable("accountingcodes_affectation");
		$this->truncateTable("balances");
	}

	function test_get_where() {
		$reportings = new Reportings();
		$reportings->filter_with(array('activities_id' => 1, 'reportings_id' => 42));
		$get_where = $reportings->get_where();
		$this->assertPattern("/reportings.activities_id = 1/", $get_where[0]);
		$this->assertPattern("/reportings.reportings_id = 42/", $get_where[1]);

		$reportings2 = new Reportings();
		$get_where2 = $reportings2->get_where();
		$this->assertTrue(!isset($get_where2[0]));
		$this->assertFalse(isset($get_where2[1]));
	}

	function test_filter_with() {
		$reportings = new Reportings();
		$reportings->filter_with(array('activities_id' => 1, 'reportings_id' => 42));
		$this->assertEqual($reportings->filters['activities_id'], 1);
		$this->assertEqual($reportings->filters['reportings_id'], 42);
	}
	
	function test_get_grid() {
		$capital = new Reporting();
		$capital->name = "capital";
		$capital->save();

		$capitalbrut = new Reporting();
		$capitalbrut->name = "capital brut";
		$capitalbrut->reportings_id = $capital->id;
		$capitalbrut->save();

		$this->assertTrue($capital->id > 0);
		$this->assertTrue($capitalbrut->id > 0);
		$reportings = new Reportings();
		$reportings->select();

		$data = $reportings->get_grid();

		$this->assertTrue(count($data) == 2);
		$this->assertEqual($data[$capital->sort]['name'], $capital->name );
		$this->assertEqual($data[$capitalbrut->sort]['name'],$capitalbrut->name);
		$this->assertTrue($data[$capital->sort]['level'] == 0 );
		$this->assertTrue($data[$capitalbrut->sort]['level'] ==  1);

		$this->truncateTables("accountingcodes", "accountingcodes_affectation", "reportings");
	}

	function test_display_reportings_detail() {
		$activity1 = new Activity();
		$activity1->name = "activité 1";
		$activity1->save();

		$activity2 = new Activity();
		$activity2->name = "activité 2";
		$activity2->save();

		$activity3 = new Activity();
		$activity3->name = "global";
		$activity3->global = 1;
		$activity3->save();

		$capital = new Reporting();
		$capital->name = "capital";
		$capital->activities_id = $activity1->id;
		$capital->save();

		$marge = new Reporting();
		$marge->name = "marge";
		$marge->activities_id = $activity2->id;
		$marge->base = "1";
		$marge->save();

		$result = new Reporting();
		$result->name = "result";
		$result->activities_id = $activity3->id;
		$result->base = "1";
		$result->save();

		$code1 = new Accounting_Code();
		$code1->name = "code un";
		$code1->number = "700001";
		$code1->save();

		$affectation1 = new Accounting_Code_Affectation();
		$affectation1->accountingcodes_id = $code1->id;
		$affectation1->reportings_id = $marge->id;
		$affectation1->save();

		$period1 = new Balance_Period();
		$period1->start = time();
		$period1->stop = strtotime("+5 months", $period1->start);
		$period1->save();

		$period2 = new Balance_Period();
		$period2->start = strtotime("-11 months", time());
		$period2->stop = time();
		$period2->save();

		$balance1 = new Balance();
		$balance1->accountingcodes_id = $code1->id;
		$balance1->period_id = $period2->id;
		$balance1->amount = 150;
		$balance1->day = time();
		$balance1->save();

		$code2 = new Accounting_Code();
		$code2->name = "code deux";
		$code2->number = "700002";
		$code2->save();

		$affectation2 = new Accounting_Code_Affectation();
		$affectation2->accountingcodes_id = $code2->id;
		$affectation2->reportings_id = $capital->id;
		$affectation2->save();

		$balance2 = new Balance();
		$balance2->accountingcodes_id = $code2->id;
		$balance2->period_id = $period1->id;
		$balance2->amount = -100;
		$balance2->day = time();
		$balance2->save();

		$balance3 = new Balance();
		$balance3->accountingcodes_id = $code2->id;
		$balance3->period_id = $period1->id;
		$balance3->amount = -350;
		$balance3->day = strtotime("-1 year", time());
		$balance3->save();

		$_SESSION['filter'] = array('start' => time(), 'stop' => strtotime("+1 year", time()));
		$_SESSION['filter']['period'] = "variable";

		$this->assertEqual(month_from_timestamp($period1->start, $period1->stop), 6);
		$this->assertEqual(month_from_timestamp($period2->start, $period2->stop), 12);
		$this->assertTrue($activity1->id > 0);
		$this->assertTrue($activity2->id > 0);
		$this->assertTrue($capital->id > 0);
		$this->assertTrue($marge->id > 0);
		$this->assertTrue($result->id > 0);
		$this->assertTrue($code1->id > 0);
		$this->assertTrue($affectation2->id > 0);
		$this->assertTrue($balance1->id > 0);
		$this->assertTrue($affectation1->id != $affectation2->id);
		$this->assertTrue($balance1->id != $balance2->id);

		$reportings = new Reportings();
		$view = $reportings->display_reportings_detail($_SESSION['filter']['period'], $_SESSION['filter']['start']);

		$this->assertPattern("/activité 1/",$view);
		$this->assertPattern("/activité 2/",$view);
		$this->assertPattern("/global/",$view);
		$this->assertPattern("/capital/", $view);
		$this->assertPattern("/marge/", $view);
		$this->assertPattern("/result/", $view);
		$this->assertPattern("/CODE UN/", $view);
		$this->assertPattern("/CODE DEUX/", $view);
		$this->assertPattern("/150.00".$GLOBALS['param']['currency']."/", $view);
		$this->assertPattern("/50.00".$GLOBALS['param']['currency']."/", $view);
		$this->assertPattern("#".__("span at import").": <strong>12".__("month")."</strong>#", $view);
		$this->assertPattern("#".__("span at import").": <strong>6".__("month")."</strong>#", $view);

		$this->truncateTables("activities", "accountingcodes", "accountingcodes_affectation", "balances", "balancesperiod", "reportings");
	}

	function test_show_view_body() {
		$activity1 = new Activity();
		$activity1->name = "activité 1";
		$activity1->save();

		$activity2 = new Activity();
		$activity2->name = "activité 2";
		$activity2->save();

		$activity3 = new Activity();
		$activity3->name = "global";
		$activity3->global = 1;
		$activity3->save();

		$capital = new Reporting();
		$capital->name = "capital";
		$capital->activities_id = $activity1->id;
		$capital->save();

		$marge = new Reporting();
		$marge->name = "marge";
		$marge->activities_id = $activity2->id;
		$marge->base = "1";
		$marge->save();

		$result = new Reporting();
		$result->name = "result";
		$result->activities_id = $activity3->id;
		$result->base = "1";
		$result->save();

		$code1 = new Accounting_Code();
		$code1->name = "code un";
		$code1->number = "700001";
		$code1->save();

		$affectation1 = new Accounting_Code_Affectation();
		$affectation1->accountingcodes_id = $code1->id;
		$affectation1->reportings_id = $marge->id;
		$affectation1->save();

		$period1 = new Balance_Period();
		$period1->start = mktime(0, 0, 0, 1, 1, 2014);
		$period1->stop = mktime(0, 0, 0, 6, 31, 2014);
		$period1->save();

		$period2 = new Balance_Period();
		$period2->start = mktime(0, 0, 0, 1, 1, 2015);
		$period2->stop = mktime(0, 0, 0, 12, 31, 2015);
		$period2->save();

		$balance1 = new Balance();
		$balance1->accountingcodes_id = $code1->id;
		$balance1->period_id = $period2->id;
		$balance1->amount = 150;
		$balance1->day = time();
		$balance1->save();

		$code2 = new Accounting_Code();
		$code2->name = "code deux";
		$code2->number = "700002";
		$code2->save();

		$affectation2 = new Accounting_Code_Affectation();
		$affectation2->accountingcodes_id = $code2->id;
		$affectation2->reportings_id = $capital->id;
		$affectation2->save();

		$balance2 = new Balance();
		$balance2->accountingcodes_id = $code2->id;
		$balance2->period_id = $period2->id;
		$balance2->amount = -100;
		$balance2->day = time();
		$balance2->save();

		$balance3 = new Balance();
		$balance3->accountingcodes_id = $code2->id;
		$balance3->period_id = $period1->id;
		$balance3->amount = -350;
		$balance3->day = strtotime("-1 year", time());
		$balance3->save();

		$_SESSION['filter'] = array('start' => mktime(0, 0, 0, 1, 1, 2015), 'stop' => mktime(0, 0, 0, 12, 31, 2015));
		$_SESSION['filter']['period'] = "variable";

		$view = [];
		$activities = new Activities();
		$activities->add_order("global");
		$activities->select();

		$_SESSION['global_ca'] = array('n' => 0, 'n-1' => 0, 'n-2' => 0);
		$_SESSION['global_result'] = array('n' => 0, 'n-1' => 0, 'n-2' => 0);

		$reportings = new Reportings();
		$view += $reportings->show_view_body($_SESSION['filter']['period'], $activity1, $_SESSION['filter']['start'], $_SESSION['filter']['stop']);

		foreach ($view as $uniqid => $data) {
			$view[] = $data;
			unset($view[$uniqid]);
		}

		$this->assertEqual($view[0]['cells'][3]['value'], "-100.00&euro;");
		$this->assertEqual($view[0]['cells'][4]['value'], "-100.00%");
		$this->assertEqual($view[0]['cells'][5]['value'], "-700.00&euro;");
		$this->assertEqual($view[0]['cells'][6]['value'], "-100.00%");
		$this->assertEqual($view[0]['cells'][9]['value'], "+600.00&euro;");
		$this->assertEqual($view[0]['cells'][10]['value'], "+85.71%");

		$view = $reportings->show_view_body($_SESSION['filter']['period'], $activity2, $_SESSION['filter']['start'], $_SESSION['filter']['stop']);

		foreach ($view as $uniqid => $data) {
			$view[] = $data;
			unset($view[$uniqid]);
		}

		$this->assertEqual($view[0]['cells'][3]['value'], "150.00&euro;");
		$this->assertEqual($view[0]['cells'][4]['value'], "100.00%");
		$this->assertEqual($view[0]['cells'][5]['value'], "0.00&euro;");
		$this->assertEqual($view[0]['cells'][6]['value'], "0.00%");
		$this->assertEqual($view[0]['cells'][9]['value'], "+150.00&euro;");
		$this->assertEqual($view[0]['cells'][10]['value'], "+100.00%");

		$view = $reportings->show_view_body($_SESSION['filter']['period'], $activity3, $_SESSION['filter']['start'], $_SESSION['filter']['stop']);

		foreach ($view as $uniqid => $data) {
			$view[] = $data;
			unset($view[$uniqid]);
		}

		$this->assertEqual($view[0]['cells'][3]['value'], "150.00&euro;");
		$this->assertEqual($view[0]['cells'][4]['value'], "100%");
		$this->assertEqual($view[0]['cells'][5]['value'], "0.00&euro;");
		$this->assertEqual($view[0]['cells'][6]['value'], "100%");
		$this->assertEqual($view[0]['cells'][9]['value'], "+150.00&euro;");
		$this->assertEqual($view[0]['cells'][10]['value'], "+100.00%");
	}

	function test_show_view_body__absolu() {
		$activity1 = new Activity();
		$activity1->name = "activité 1";
		$activity1->save();

		$activity2 = new Activity();
		$activity2->name = "activité 2";
		$activity2->save();

		$activity3 = new Activity();
		$activity3->name = "global";
		$activity3->global = 1;
		$activity3->save();

		$capital = new Reporting();
		$capital->name = "capital";
		$capital->activities_id = $activity1->id;
		$capital->save();

		$marge = new Reporting();
		$marge->name = "marge";
		$marge->activities_id = $activity2->id;
		$marge->base = "1";
		$marge->save();

		$result = new Reporting();
		$result->name = "result";
		$result->activities_id = $activity3->id;
		$result->base = "1";
		$result->save();

		$code1 = new Accounting_Code();
		$code1->name = "code un";
		$code1->number = "700001";
		$code1->save();

		$affectation1 = new Accounting_Code_Affectation();
		$affectation1->accountingcodes_id = $code1->id;
		$affectation1->reportings_id = $marge->id;
		$affectation1->save();

		$period1 = new Balance_Period();
		$period1->start = mktime(0, 0, 0, 1, 1, 2014);
		$period1->stop = mktime(0, 0, 0, 6, 31, 2014);
		$period1->save();

		$period2 = new Balance_Period();
		$period2->start = mktime(0, 0, 0, 1, 1, 2015);
		$period2->stop = mktime(0, 0, 0, 12, 31, 2015);
		$period2->save();

		$balance1 = new Balance();
		$balance1->accountingcodes_id = $code1->id;
		$balance1->period_id = $period2->id;
		$balance1->amount = 150;
		$balance1->day = time();
		$balance1->save();

		$code2 = new Accounting_Code();
		$code2->name = "code deux";
		$code2->number = "700002";
		$code2->save();

		$affectation2 = new Accounting_Code_Affectation();
		$affectation2->accountingcodes_id = $code2->id;
		$affectation2->reportings_id = $capital->id;
		$affectation2->save();

		$balance2 = new Balance();
		$balance2->accountingcodes_id = $code2->id;
		$balance2->period_id = $period2->id;
		$balance2->amount = -100;
		$balance2->day = time();
		$balance2->save();

		$balance3 = new Balance();
		$balance3->accountingcodes_id = $code2->id;
		$balance3->period_id = $period1->id;
		$balance3->amount = -350;
		$balance3->day = strtotime("-1 year", time());
		$balance3->save();

		$_SESSION['filter'] = array('start' => mktime(0, 0, 0, 1, 1, 2015), 'stop' => mktime(0, 0, 0, 12, 31, 2015));
		$_SESSION['filter']['period'] = "absolu";

		$view = [];
		$activities = new Activities();
		$activities->add_order("global");
		$activities->select();

		$_SESSION['global_ca'] = array('n' => 0, 'n-1' => 0, 'n-2' => 0);
		$_SESSION['global_result'] = array('n' => 0, 'n-1' => 0, 'n-2' => 0);

		$reportings = new Reportings();
		$view += $reportings->show_view_body($_SESSION['filter']['period'], $activity1, $_SESSION['filter']['start'], $_SESSION['filter']['stop']);

		foreach ($view as $uniqid => $data) {
			$view[] = $data;
			unset($view[$uniqid]);
		}

		$this->assertEqual($view[0]['cells'][3]['value'], "-100.00&euro;");
		$this->assertEqual($view[0]['cells'][4]['value'], "-100.00%");
		$this->assertEqual($view[0]['cells'][5]['value'], "-350.00&euro;");
		$this->assertEqual($view[0]['cells'][6]['value'], "-100.00%");
		$this->assertEqual($view[0]['cells'][9]['value'], "+250.00&euro;");
		$this->assertEqual($view[0]['cells'][10]['value'], "+71.43%");

		$view = $reportings->show_view_body($_SESSION['filter']['period'], $activity2, $_SESSION['filter']['start'], $_SESSION['filter']['stop']);

		foreach ($view as $uniqid => $data) {
			$view[] = $data;
			unset($view[$uniqid]);
		}

		$this->assertEqual($view[0]['cells'][3]['value'], "150.00&euro;");
		$this->assertEqual($view[0]['cells'][4]['value'], "100.00%");
		$this->assertEqual($view[0]['cells'][5]['value'], "0.00&euro;");
		$this->assertEqual($view[0]['cells'][6]['value'], "0.00%");
		$this->assertEqual($view[0]['cells'][9]['value'], "+150.00&euro;");
		$this->assertEqual($view[0]['cells'][10]['value'], "+100.00%");

		$view = $reportings->show_view_body($_SESSION['filter']['period'], $activity3, $_SESSION['filter']['start'], $_SESSION['filter']['stop']);

		foreach ($view as $uniqid => $data) {
			$view[] = $data;
			unset($view[$uniqid]);
		}

		$this->assertEqual($view[0]['cells'][3]['value'], "150.00&euro;");
		$this->assertEqual($view[0]['cells'][4]['value'], "100%");
		$this->assertEqual($view[0]['cells'][5]['value'], "0.00&euro;");
		$this->assertEqual($view[0]['cells'][6]['value'], "100%");
		$this->assertEqual($view[0]['cells'][9]['value'], "+150.00&euro;");
		$this->assertEqual($view[0]['cells'][10]['value'], "+100.00%");
	}
}
