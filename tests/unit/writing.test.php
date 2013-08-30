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
			"accounts",
			"sources",
			"types",
			"writings",
			"banks"
		);
	}
	
	function test_save_load() {
		$writing = new Writing();
		$writing->account_id = 1;
		$writing->amount_excl_vat = 190.50;
		$writing->amount_inc_vat = 250;
		$writing->bank_id = 2;
		$writing->comment = "Ceci est un test";
		$writing->delay = mktime(10, 0, 0, 7, 29, 2013);
		$writing->information = "Complément d'infos";
		$writing->paid = 0;
		$writing->source_id = 2;
		$writing->type_id = 1;
		$writing->unique_key = "e50b79ffaccc6b50d018aad432711418";
		$writing->vat = 19.6;
		$writing->save();
		$writing_loaded = new Writing();
		$writing_loaded->id = 1;
		$writing_loaded->load();
		$this->assertEqual($writing_loaded->account_id, $writing->account_id);
		$this->assertEqual($writing_loaded->amount_excl_vat, $writing->amount_excl_vat);
		$this->assertEqual($writing_loaded->amount_inc_vat, $writing->amount_inc_vat);
		$this->assertEqual($writing_loaded->bank_id, $writing->bank_id);
		$this->assertEqual($writing_loaded->comment, $writing->comment);
		$this->assertEqual($writing_loaded->delay, $writing->delay);
		$this->assertEqual($writing_loaded->id, $writing->id);
		$this->assertEqual($writing_loaded->information, $writing->information);
		$this->assertEqual($writing_loaded->paid, $writing->paid);
		$this->assertEqual($writing_loaded->source_id, $writing->source_id);
		$this->assertEqual($writing_loaded->type_id, $writing->type_id);
		$this->assertEqual($writing_loaded->unique_key, $writing->unique_key);
		$this->assertEqual($writing_loaded->vat, $writing->vat);
		$this->truncateTable("writings");
	}
	
	function test_update() {
		$writing = new Writing();
		$writing->account_id = 1;
		$writing->amount_excl_vat = 190.50;
		$writing->amount_inc_vat = 250;
		$writing->bank_id = 2;
		$writing->comment = "Ceci est un test";
		$writing->delay = mktime(10, 0, 0, 7, 29, 2013);
		$writing->information = "Complément d'infos";
		$writing->paid = 0;
		$writing->source_id = 2;
		$writing->type_id = 1;
		$writing->unique_key = "e50b79ffaccc6b50d018aad432711418";
		$writing->vat = 19.6;
		$writing->save();
		$writing_loaded = new Writing();
		$writing_loaded->id = 1;
		$writing_loaded->load();
		$writing_loaded->account_id = 2;
		$writing_loaded->amount_excl_vat = 19.50;
		$writing_loaded->amount_inc_vat = 25;
		$writing_loaded->bank_id = 3;
		$writing_loaded->comment = "Ceci est un autre test";
		$writing_loaded->delay = mktime(10, 30, 0, 7, 29, 2013);
		$writing_loaded->information = "Autre complément d'infos";
		$writing_loaded->paid = 1;
		$writing_loaded->source_id = 1;
		$writing_loaded->type_id = 2;
		$writing_loaded->vat = 5.5;
		$writing_loaded->save();
		$this->assertEqual($writing_loaded->account_id, 2);
		$this->assertEqual($writing_loaded->amount_excl_vat, 19.5);
		$this->assertEqual($writing_loaded->amount_inc_vat, 25);
		$this->assertEqual($writing_loaded->bank_id, 3);
		$this->assertEqual($writing_loaded->comment, "Ceci est un autre test");
		$this->assertEqual($writing_loaded->delay, mktime(10, 30, 0, 7, 29, 2013));
		$this->assertEqual($writing_loaded->id, 1);
		$this->assertEqual($writing_loaded->information, "Autre complément d'infos");
		$this->assertEqual($writing_loaded->paid, 1);
		$this->assertEqual($writing_loaded->source_id, 1);
		$this->assertEqual($writing_loaded->type_id, 2);
		$this->assertEqual($writing_loaded->vat, 5.5);
		$this->truncateTable("writings");
	}
	
	function test_delete() {
		$writing = new Writing();
		$writing->account_id = 1;
		$writing->save();
		
		$this->assertTrue($writing->load());
		
		$writing->delete();
		
		$this->assertFalse($writing->load());
		$this->truncateTable("writings");
	}
	
	function test_merge_from() {
		$writing = new Writing();
		$writing->account_id = 1;
		$writing->amount_excl_vat = 190.50;
		$writing->amount_inc_vat = 250;
		$writing->bank_id = 2;
		$writing->comment = "Ceci est un test";
		$writing->delay = mktime(10, 0, 0, 7, 29, 2013);
		$writing->information = "Complément d'infos";
		$writing->paid = 0;
		$writing->source_id = 2;
		$writing->type_id = 1;
		$writing->vat = 19.6;
		
		$writing_to_merge = new Writing();
		$writing_to_merge->id = 1;
		$writing_to_merge->account_id = 2;
		$writing_to_merge->amount_excl_vat = 19.50;
		$writing_to_merge->amount_inc_vat = 25;
		$writing_to_merge->bank_id = 3;
		$writing_to_merge->comment = "Ceci est un autre test";
		$writing_to_merge->delay = mktime(10, 0, 0, 8, 26, 2013);
		$writing_to_merge->information = "Autre complément d'infos";
		$writing_to_merge->paid = 1;
		$writing_to_merge->source_id = 1;
		$writing_to_merge->type_id = 2;
		$writing_to_merge->vat = 5.5;
		$writing_to_merge->search_index = $writing_to_merge->search_index();
		
		$writing->merge_from($writing_to_merge);
		
		$this->assertIdentical($writing_to_merge, $writing);
		
		$writing_to_merge_2 = new Writing();
		$writing_to_merge_2->account_id = NULL;
		$writing_to_merge_2->amount_excl_vat = NULL;
		$writing_to_merge_2->amount_inc_vat = NULL;
		$writing_to_merge_2->bank_id = NULL;
		$writing_to_merge_2->comment = NULL;
		$writing_to_merge_2->delay = NULL;
		$writing_to_merge_2->information = NULL;
		$writing_to_merge_2->paid = NULL;
		$writing_to_merge_2->source_id = NULL;
		$writing_to_merge_2->type_id = NULL;
		$writing_to_merge_2->vat = NULL;
		
		$writing->merge_from($writing_to_merge_2);
		
		$this->assertIdentical($writing_to_merge, $writing);
		
		$writing_to_merge_2->account_id = 0;
		$writing_to_merge_2->amount_excl_vat = 0;
		$writing_to_merge_2->amount_inc_vat = 0;
		$writing_to_merge_2->bank_id = 0;
		$writing_to_merge_2->comment = "";
		$writing_to_merge_2->delay = 0;
		$writing_to_merge_2->information = "";
		$writing_to_merge_2->paid = 0;
		$writing_to_merge_2->source_id = 0;
		$writing_to_merge_2->type_id = 0;
		$writing_to_merge_2->vat = 0;
		
		$writing_to_merge_3 = new Writing();
		$writing_to_merge_3->id = 1;
		$writing_to_merge_3->account_id = 2;
		$writing_to_merge_3->amount_excl_vat = 0;
		$writing_to_merge_3->amount_inc_vat = 0;
		$writing_to_merge_3->bank_id = 3;
		$writing_to_merge_3->delay = 0;
		$writing_to_merge_3->paid = 0;
		$writing_to_merge_3->source_id = 1;
		$writing_to_merge_3->type_id = 2;
		$writing_to_merge_3->vat = 0;
		$writing_to_merge_3->search_index = $writing_to_merge_3->search_index();
		
		$writing->merge_from($writing_to_merge_2);
		
		$this->assertIdentical($writing_to_merge_3, $writing);
		$this->truncateTable("writings");
	}
	
	function test_split() {
		$writing = new Writing();
		$writing->account_id = 1;
		$writing->amount_excl_vat = 167.22;
		$writing->amount_inc_vat = 200;
		$writing->bank_id = 2;
		$writing->comment = "Ceci est un commentaire";
		$writing->delay = mktime(10, 0, 0, 7, 31, 2013);
		$writing->information = "Informations";
		$writing->paid = 0;
		$writing->source_id = 2;
		$writing->type_id = 1;
		$writing->vat = 19.6;
		$writing->save();
		
		$writing->split(250);
		$writing_splited = new Writing();
		$writing_splited->load(2);
		$this->assertEqual($writing->amount_inc_vat, -50);
		$this->assertEqual($writing->amount_excl_vat, -41.806020);
		$this->assertEqual($writing_splited->account_id, 1);
		$this->assertEqual($writing_splited->amount_excl_vat, 209.030100);
		$this->assertEqual($writing_splited->amount_inc_vat, 250);
		$this->assertEqual($writing_splited->bank_id, 2);
		$this->assertEqual($writing_splited->comment, "Ceci est un commentaire");
		$this->assertEqual($writing_splited->delay, mktime(10, 0, 0, 7, 31, 2013));
		$this->assertEqual($writing_splited->paid, 0);
		$this->assertEqual($writing_splited->source_id, 2);
		$this->assertEqual($writing_splited->type_id, 1);
		$this->assertEqual($writing_splited->vat, 19.6);
		
		$this->truncateTable("writings");
		
		$writing = new Writing();
		$writing->account_id = 1;
		$writing->amount_excl_vat = 188.13;
		$writing->amount_inc_vat = 225;
		$writing->paid = 0;
		$writing->type_id = 1;
		$writing->vat = 19.6;
		$writing->source_id = 2;
		$writing->delay = mktime(10, 0, 0, 7, 31, 2013);
		$writing->save();
		
		$writing->split(225);
		$writing_splited = new Writing();
		$writing_splited->load(2);
		$this->assertEqual($writing->amount_inc_vat, 0);
		$this->assertEqual($writing->amount_excl_vat, 0);
		$this->assertEqual($writing_splited->account_id, 1);
		$this->assertEqual($writing_splited->amount_excl_vat, 188.127090);
		$this->assertEqual($writing_splited->amount_inc_vat, 225);
		$this->assertEqual($writing_splited->paid, 0);
		$this->assertEqual($writing_splited->type_id, 1);
		$this->assertEqual($writing_splited->vat, 19.6);
		$this->assertEqual($writing_splited->source_id, 2);
		$this->assertEqual($writing_splited->delay, mktime(10, 0, 0, 7, 31, 2013));
		
		$this->truncateTable("writings");
	}
	
	function test_form() {
		$writing = new Writing();
		$form = $writing->form();
		$this->assertPattern("/".date('m')."/", $form);
		$this->assertPattern("/".date('Y')."/", $form);
		$this->assertPattern("/<option value=\"0\" selected=\"selected\">--<\/option>/", $form);
		$this->assertPattern("/value=\"insert\"/", $form);
		$this->assertNoPattern("/value=\"edit\"/", $form);
		$account = new Account();
		$account->name = "Account 1";
		$account->save();
		$bank = new Bank();
		$bank->name = "Bank 1";
		$bank->save();
		$source = new Source();
		$source->name = "Source 1";
		$source->save();
		$type = new Type();
		$type->name = "Type 1";
		$type->save();
		$writing = new Writing();
		$writing->account_id = 1;
		$writing->amount_excl_vat = 167.22;
		$writing->amount_inc_vat = 200;
		$writing->bank_id = 1;
		$writing->comment = "Ceci est un commentaire";
		$writing->delay = mktime(10, 0, 0, 7, 31, 2013);
		$writing->source_id = 1;
		$writing->type_id = 1;
		$writing->vat = 19.6;
		$writing->save();
		$writing->load(1);
		$form = $writing->form();
		$this->assertPattern("/".date('m', mktime(10, 0, 0, 7, 31, 2013))."/", $form);
		$this->assertPattern("/".date('Y', mktime(10, 0, 0, 7, 31, 2013))."/", $form);
		$this->assertPattern("/<option value=\"1\" selected=\"selected\">Type 1<\/option>/", $form);
		$this->assertPattern("/<option value=\"1\" selected=\"selected\">Source 1<\/option>/", $form);
		$this->assertPattern("/<option value=\"1\" selected=\"selected\">Bank 1<\/option>/", $form);
		$this->assertPattern("/<option value=\"1\" selected=\"selected\">Account 1<\/option>/", $form);
		$this->assertPattern("/value=\"167.220000\"/", $form);
		$this->assertPattern("/value=\"200.000000\"/", $form);
		$this->assertPattern("/value=\"19.60\"/", $form);
		$this->assertPattern("/Ceci est un commentaire/", $form);
		$this->assertNoPattern("/value=\"insert\"/", $form);
		$this->assertPattern("/value=\"edit\"/", $form);
		
		$this->truncateTable("writings");
		$this->truncateTable("sources");
		$this->truncateTable("types");
		$this->truncateTable("accounts");
		$this->truncateTable("banks");
	}
	
	function test_form_duplicate() {
		$writing = new Writing();
		$writing->id = 40;
		$form_duplicate = $writing->form_duplicate();
		$this->assertPattern("/class=\"table_writings_duplicate\"/", $form_duplicate);
		$this->assertPattern("/value=\"40\"/", $form_duplicate);
		$this->assertPattern("/table_writings_duplicate_submit/", $form_duplicate);
		$this->assertPattern("/table_writings_duplicate_amount/", $form_duplicate);
	}
	
	function test_form_delete() {
		$writing = new Writing();
		$writing->id = 40;
		$form_delete = $writing->form_delete();
		$this->assertPattern("/class=\"table_writings_delete\"/", $form_delete);
		$this->assertPattern("/value=\"40\"/", $form_delete);
		$this->assertPattern("/table_writings_delete_submit/", $form_delete);
		$this->assertPattern("/javascript:return confirm/", $form_delete);
	}
	
	function test_form_split() {
		$writing = new Writing();
		$writing->id = 40;
		$form_split = $writing->form_split();
		$this->assertPattern("/class=\"table_writings_split\"/", $form_split);
		$this->assertPattern("/value=\"40\"/", $form_split);
		$this->assertPattern("/table_writings_split_submit/", $form_split);
		$this->assertPattern("/table_writings_split_amount/", $form_split);
	}
	
	function test_fill() {
		$writing = new Writing();
		$hash = array();
		$hash['datepicker']['m'] = 8;
		$hash['datepicker']['d'] = 26;
		$hash['datepicker']['Y'] = 2013;
		$writing->fill($hash);
		$this->assertEqual($writing->delay, 1377468000);
	}
	
	function test_duplicate() {
		$writing = new Writing();
		$writing->delay = mktime(0, 0, 0, 8, 26, 2013);
		$writing->save();
		$writing->duplicate(5);
		$writings = new Writings();
		$writings->select();
		$this->assertTrue(count($writings) == 6);
		$writing->load(1);
		$this->assertEqual(mktime(0, 0, 0, 8, 26, 2013), $writing->delay);
		$writing->load(2);
		$this->assertEqual(mktime(0, 0, 0, 9, 26, 2013), $writing->delay);
		$writing->load(3);
		$this->assertEqual(mktime(0, 0, 0, 10, 26, 2013), $writing->delay);
		$writing->load(4);
		$this->assertEqual(mktime(0, 0, 0, 11, 26, 2013), $writing->delay);
		$writing->load(5);
		$this->assertEqual(mktime(0, 0, 0, 12, 26, 2013), $writing->delay);
		$writing->load(6);
		$this->assertEqual(mktime(0, 0, 0, 1, 26, 2014), $writing->delay);
		$this->truncateTable("writings");
	}
	
	function test_show_further_information() {
		$writing = new Writing();
		$writing->information = "Ceci est un complément d'information";
		$this->assertPattern("/class=\"table_writings_comment_further_information\"/", $writing->show_further_information());
		$this->assertPattern("/Ceci est un complément d'information/", $writing->show_further_information());
	}
}
