<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Writings_Export extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"bayesianelements",
			"writings",
			"accountingcodes",
			"activities",
			"reportings",
			"writingsimported"
		);
	}

	function tearDown() {
		$this->truncateTable("writings");
		$this->truncateTable("bayesianelements");
		$this->truncateTable("writings");
		$this->truncateTable("accountingcodes");
		$this->truncateTable("activities");
		$this->truncateTable("reportings");
		$this->truncateTable("writingsimported");
	}

	function test_get_form() {
		list($begin,$end)  = determine_fiscal_year(time());
		$form = Writings_Export::get_form("PATH");
		$this->assertPattern("/medias\/images\/link_calendar.png/",$form);
		$this->assertPattern("/value=\"".date("Y",$begin)."/",$form);
		$this->assertPattern("/value=\"".date("Y",$end)."/",$form);
		$this->assertPattern("/value=\"".date("m",$begin)."/",$form);
		$this->assertPattern("/value=\"".date("m",$end)."/",$form);
		$this->assertPattern("/value=\"".date("d",$begin)."/",$form);
		$this->assertPattern("/value=\"".date("d",$end)."/",$form);
	}

	function test_clean_and_set() {
		$export = new Writings_Export();
		$from = array("d"=> 20,"m"=> 4,"Y"=> 2012);
		$to  = array("d"=> 3,"m"=> 2,"Y"=> 2014);
		$post = array('date_picker_from'=> $from,'date_picker_to' => $to);
		$export->clean_and_set($post);
		$this->assertEqual(date("Y",$export->from),2012);
		$this->assertEqual(date("m",$export->from),4);
		$this->assertEqual(date("d",$export->from),20);
		$this->assertEqual(date("Y",$export->to),2014);
		$this->assertEqual(date("m",$export->to),2);
		$this->assertEqual(date("d",$export->to),3);

	}

	function test_reset_balance() {
		$w1 = new Writing();
		$w1->comment = 'Ceci est un test';
		$w1->save();
		$w2 = new Writing();
		$w2->comment = 'Ceci est un writing';
		$w2->save();
		$wi1 = new Writing_Imported();
		$wi1->hash = md5('Ceci est un test');
		$wi1->save();
		$wi2 = new Writing_Imported();
		$wi2->hash = md5('Ceci est un writing imported');
		$wi2->save();

		$writings = new Writings();
		$writings->select();
		$writings->delete();
		$writings_imported = new Writings_Imported();
		$writings_imported->delete();

		$writings->select();
		foreach ($writings as $writing) {
			$this->assertFalse($writing);
		}

		$writings_imported->select();
		foreach ($writings_imported as $wi) {
			$this->assertFalse($wi);
		}
	}
}
