<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Writings_Followup extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"writings",
			"categories"
		);
		$GLOBALS['param']['fiscal year begin'] = "01";
	}
	
	function test_show_timeseries_per_category_at() {
		$category = new Category();
		$category->name = "category 1";
		$category->save();
		$writing = new Writing();
		$writing->amount_inc_vat = 100;
		$writing->day = mktime(0, 0, 0, 10, 14, 2013);
		$writing->categories_id = 0;
		$writing->save();
		$writing = new Writing();
		$writing->amount_inc_vat = -10;
		$writing->day = mktime(0, 0, 0, 10, 14, 2013);
		$writing->categories_id = 0;
		$writing->save();
		$writing = new Writing();
		$writing->amount_inc_vat = 200;
		$writing->day = mktime(0, 0, 0, 10, 14, 2013);
		$writing->categories_id = 1;
		$writing->save();
		$writing = new Writing();
		$writing->amount_inc_vat = -50;
		$writing->day = mktime(0, 0, 0, 10, 14, 2014);
		$writing->categories_id = 0;
		$writing->save();
		$writingfollowup = new Writings_Followup();
		$timeseries = $writingfollowup->show_timeseries_per_category_at(mktime(0, 0, 0, 11, 15, 2013));
		$this->assertPattern("/category 1/", $timeseries);
		$this->assertPattern("/200/", $timeseries);
		$this->assertPattern("/aucune/", $timeseries);
		$this->assertPattern("/90/", $timeseries);
		$timeseries = $writingfollowup->show_timeseries_per_category_at(mktime(0, 0, 0, 11, 15, 2014));
		$this->assertPattern("/aucune/", $timeseries);
		$this->assertPattern("/-50/", $timeseries);
		$this->truncateTable("writings");
		$this->truncateTable("categories");
	}
	
	function test_show_timeseries_per_bank_at() {
		$bank = new Bank();
		$bank->name = "bank 1";
		$bank->save();
		$writing = new Writing();
		$writing->amount_inc_vat = 100;
		$writing->day = mktime(0, 0, 0, 10, 14, 2013);
		$writing->banks_id = 0;
		$writing->save();
		$writing = new Writing();
		$writing->amount_inc_vat = -10;
		$writing->day = mktime(0, 0, 0, 10, 14, 2013);
		$writing->banks_id = 0;
		$writing->save();
		$writing = new Writing();
		$writing->amount_inc_vat = 200;
		$writing->day = mktime(0, 0, 0, 10, 14, 2013);
		$writing->banks_id = 1;
		$writing->save();
		$writing = new Writing();
		$writing->amount_inc_vat = -50;
		$writing->day = mktime(0, 0, 0, 10, 14, 2014);
		$writing->banks_id = 0;
		$writing->save();
		$writingfollowup = new Writings_Followup();
		$timeseries = $writingfollowup->show_timeseries_per_bank_at(mktime(0, 0, 0, 11, 15, 2013));
		$this->assertPattern("/bank 1/", $timeseries);
		$this->assertPattern("/200/", $timeseries);
		$this->assertPattern("/aucune/", $timeseries);
		$this->assertPattern("/90/", $timeseries);
		$timeseries = $writingfollowup->show_timeseries_per_bank_at(mktime(0, 0, 0, 11, 15, 2014));
		$this->assertPattern("/aucune/", $timeseries);
		$this->assertPattern("/-50/", $timeseries);
		$this->truncateTable("writings");
		$this->truncateTable("banks");
	}
}
