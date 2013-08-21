<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Writing extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"writings",
			"accounts",
			"types",
			"sources"
		);
	}
	
	function test_save_load() {
		$writing = new Writing();
		$writing->account_id = 1;
		$writing->amount_excl_tax = 190.50;
		$writing->amount_inc_tax = 250;
		$writing->paid = 0;
		$writing->type_id = 1;
		$writing->vat = 19.6;
		$writing->source_id = 2;
		$writing->delay = mktime(10, 0, 0, 7, 29, 2013);
		$writing->save();
		$writing_loaded = new Writing();
		$writing_loaded->id = 1;
		$writing_loaded->load();
		$this->assertEqual($writing_loaded->id, $writing->id);
		$this->assertEqual($writing_loaded->account_id, $writing->account_id);
		$this->assertEqual($writing_loaded->amount_excl_tax, $writing->amount_excl_tax);
		$this->assertEqual($writing_loaded->amount_inc_tax, $writing->amount_inc_tax);
		$this->assertEqual($writing_loaded->paid, $writing->paid);
		$this->assertEqual($writing_loaded->type_id, $writing->type_id);
		$this->assertEqual($writing_loaded->vat, $writing->vat);
		$this->assertEqual($writing_loaded->source_id, $writing->source_id);
		$this->assertEqual($writing_loaded->delay, $writing->delay);
		$this->truncateTable("writing");
	}
	
	function test_update() {
		$writing = new Writing();
		$writing->account_id = 1;
		$writing->amount_excl_tax = 190.50;
		$writing->amount_inc_tax = 250;
		$writing->paid = 0;
		$writing->type_id = 1;
		$writing->vat = 19.6;
		$writing->source_id = 2;
		$writing->delay = mktime(10, 0, 0, 7, 29, 2013);
		$writing->save();
		$writing_loaded = new Writing();
		$writing_loaded->id = 1;
		$writing_loaded->load();
		$writing_loaded->account_id = 2;
		$writing_loaded->amount_excl_tax = 19.50;
		$writing_loaded->amount_inc_tax = 25;
		$writing_loaded->paid = 1;
		$writing_loaded->type_id = 2;
		$writing_loaded->vat = 5.5;
		$writing_loaded->source_id = 1;
		$writing_loaded->delay = mktime(10, 30, 0, 7, 29, 2013);
		$writing_loaded->save();
		$this->assertEqual($writing_loaded->id, 1);
		$this->assertEqual($writing_loaded->account_id, 2);
		$this->assertEqual($writing_loaded->amount_excl_tax, 19.5);
		$this->assertEqual($writing_loaded->amount_inc_tax, 25);
		$this->assertEqual($writing_loaded->paid, 1);
		$this->assertEqual($writing_loaded->type_id, 2);
		$this->assertEqual($writing_loaded->vat, 5.5);
		$this->assertEqual($writing_loaded->source_id, 1);
		$this->assertEqual($writing_loaded->delay, mktime(10, 30, 0, 7, 29, 2013));
		$this->truncateTable("writing");
	}
	
	function test_delete() {
		$writing = new Writing();
		$writing->account_id = 1;
		$writing->amount_excl_tax = 190.50;
		$writing->amount_inc_tax = 250;
		$writing->paid = 0;
		$writing->type_id = 1;
		$writing->vat = 19.6;
		$writing->source_id = 2;
		$writing->delay = mktime(10, 0, 0, 7, 29, 2013);
		$writing->save();
		$writing->delete();
		
		$writing_loaded = new Writing();
		$writing_loaded->id = 1;
		$this->assertFalse($writing_loaded->load());
		$this->truncateTable("writing");
	}
	
	function test_paid_to_text() {
		$writing = new Writing();
		$writing->paid = 0;
		$this->assertEqual($writing->paid_to_text(), __("Non"));
		$writing->paid = 1;
		$this->assertEqual($writing->paid_to_text(), __("Oui"));
		$this->truncateTable("writing");
	}
	
	function test_merge() {
		$writing = new Writing();
		$writing->id = 1;
		$writing->account_id = 1;
		$writing->amount_excl_tax = 190.50;
		$writing->amount_inc_tax = 250;
		$writing->paid = 0;
		$writing->type_id = 1;
		$writing->vat = 19.6;
		$writing->source_id = 2;
		$writing->delay = mktime(10, 0, 0, 7, 31, 2013);
		
		$writing_to_merge = new Writing();
		$writing_to_merge->id = 1;
		$writing_to_merge->account_id = 2;
		$writing_to_merge->amount_excl_tax = 100;
		$writing_to_merge->amount_inc_tax = 25;
		$writing_to_merge->paid = 1;
		$writing_to_merge->type_id = 5;
		$writing_to_merge->vat = 5.5;
		$writing_to_merge->source_id = 1;
		$writing_to_merge->delay = mktime(10, 30, 0, 7, 31, 2013);
		
		$writing->merge($writing_to_merge);
		
		$this->assertIdentical($writing_to_merge, $writing);
		
		$writing_to_merge_2 = new Writing();
		$writing_to_merge_2->account_id = NULL;
		$writing_to_merge_2->amount_excl_tax = NULL;
		$writing_to_merge_2->amount_inc_tax = NULL;
		$writing_to_merge_2->paid = NULL;
		$writing_to_merge_2->type_id = NULL;
		$writing_to_merge_2->vat = NULL;
		$writing_to_merge_2->source_id = NULL;
		$writing_to_merge_2->delay = NULL;
		
		$writing->merge($writing_to_merge_2);
		
		$this->assertIdentical($writing_to_merge, $writing);
		
		$writing_to_merge_2->account_id = 0;
		$writing_to_merge_2->amount_excl_tax = 0;
		$writing_to_merge_2->amount_inc_tax = 0;
		$writing_to_merge_2->paid = 0;
		$writing_to_merge_2->type_id = 0;
		$writing_to_merge_2->vat = 0;
		$writing_to_merge_2->source_id = 0;
		$writing_to_merge_2->delay = 0;
		
		$writing_to_merge_3 = new Writing();
		$writing_to_merge_3->id = 1;
		$writing_to_merge_3->account_id = 2;
		$writing_to_merge_3->amount_excl_tax = 0;
		$writing_to_merge_3->amount_inc_tax = 0;
		$writing_to_merge_3->paid = 0;
		$writing_to_merge_3->type_id = 5;
		$writing_to_merge_3->vat = 0;
		$writing_to_merge_3->source_id = 1;
		$writing_to_merge_3->delay = 0;
		
		$writing->merge($writing_to_merge_2);
		
		$this->assertIdentical($writing_to_merge_3, $writing);
		$this->truncateTable("writing");
	}
	
	function test_split() {
		$writing = new Writing();
		$writing->account_id = 1;
		$writing->amount_excl_tax = 167.22;
		$writing->amount_inc_tax = 200;
		$writing->paid = 0;
		$writing->type_id = 1;
		$writing->vat = 19.6;
		$writing->source_id = 2;
		$writing->delay = mktime(10, 0, 0, 7, 31, 2013);
		$writing->save();
		
		$writing->split(250);
		$writing_splited = new Writing();
		$writing_splited->load(2);
		$this->assertEqual($writing->amount_inc_tax, -50);
		$this->assertEqual($writing->amount_excl_tax, -41.806020);
		$this->assertEqual($writing_splited->account_id, 1);
		$this->assertEqual($writing_splited->amount_excl_tax, 209.030106);
		$this->assertEqual($writing_splited->amount_inc_tax, 250);
		$this->assertEqual($writing_splited->paid, 0);
		$this->assertEqual($writing_splited->type_id, 1);
		$this->assertEqual($writing_splited->vat, 19.6);
		$this->assertEqual($writing_splited->source_id, 2);
		$this->assertEqual($writing_splited->delay, mktime(10, 0, 0, 7, 31, 2013));
		
		$this->truncateTable("writing");
		
		$writing = new Writing();
		$writing->account_id = 1;
		$writing->amount_excl_tax = 188.13;
		$writing->amount_inc_tax = 225;
		$writing->paid = 0;
		$writing->type_id = 1;
		$writing->vat = 19.6;
		$writing->source_id = 2;
		$writing->delay = mktime(10, 0, 0, 7, 31, 2013);
		$writing->save();
		
		$writing->split(225);
		$writing_splited = new Writing();
		$writing_splited->load(2);
		$this->assertEqual($writing->amount_inc_tax, 0);
		$this->assertEqual($writing->amount_excl_tax, 0);
		$this->assertEqual($writing_splited->account_id, 1);
		$this->assertEqual($writing_splited->amount_excl_tax, 188.127090);
		$this->assertEqual($writing_splited->amount_inc_tax, 225);
		$this->assertEqual($writing_splited->paid, 0);
		$this->assertEqual($writing_splited->type_id, 1);
		$this->assertEqual($writing_splited->vat, 19.6);
		$this->assertEqual($writing_splited->source_id, 2);
		$this->assertEqual($writing_splited->delay, mktime(10, 0, 0, 7, 31, 2013));
		
		$this->truncateTable("writing");
	}
}
