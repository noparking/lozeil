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
	
	function test_get_join() {
		$writings = new Writings();
		$join = $writings->get_join();
		$this->assertPattern("/LEFT JOIN accounts/", $join[0]);
		$this->assertPattern("/ON accounts.id = writings.account_id/", $join[0]);
		$this->assertPattern("/LEFT JOIN sources/", $join[1]);
		$this->assertPattern("/ON sources.id = writings.source_id/", $join[1]);
		$this->assertPattern("/LEFT JOIN types/", $join[2]);
		$this->assertPattern("/ON types.id = writings.type_id/", $join[2]);
		$this->assertPattern("/ON sources.id = writings.source_id/", $join[1]);
		$this->assertPattern("/LEFT JOIN banks/", $join[3]);
		$this->assertPattern("/ON banks.id = writings.bank_id/", $join[3]);
	}
	
	function test_get_columns() {
		$writings = new Writings();
		$columns = $writings->get_columns();
		$this->assertPattern("/`writings`.*/", $columns[0]);
		$this->assertPattern("/accounts.name as account_name, sources.name as source_name, types.name as type_name, banks.name as bank_name/", $columns[1]);
	}
	
	function test_determine_order() {
		$writings = new Writings();
		$_REQUEST['sort_by'] = "delay";
		$_REQUEST['order_direction'] = 0;
		$writings->determine_order();
		$order = $writings->get_query();
		$this->assertPattern("/ORDER BY delay ASC/", $order);
		
		$_REQUEST['order_direction'] = 1;
		$_REQUEST['sort_by'] = "account_name";
		$writings->determine_order();
		$order2 = $writings->get_query();
		$this->assertPattern("/ORDER BY account_name DESC/", $order2);
	}
	
	function test_show() {
		$_SESSION['month_encours'] = mktime(0, 0, 0, 7, 1, 2013);
		
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
		$account2 = new Account();
		$account2->name = "Account 2";
		$account2->save();
		$bank2 = new Bank();
		$bank2->name = "Bank 2";
		$bank2->save();
		$source2 = new Source();
		$source2->name = "Source 2";
		$source2->save();
		$type2 = new Type();
		$type2->name = "Type 2";
		$type2->save();
		
		$writing = new Writing();
		$writing->account_id = 1;
		$writing->amount_excl_vat = 190.50;
		$writing->amount_inc_vat = 250;
		$writing->bank_id = 1;
		$writing->comment = "Ceci est un test";
		$writing->delay = mktime(10, 0, 0, 7, 29, 2013);
		$writing->information = "Complément d'infos";
		$writing->paid = 0;
		$writing->source_id = 1;
		$writing->type_id = 1;
		$writing->unique_key = "e50b79ffaccc6b50d018aad432711418";
		$writing->vat = 19.6;
		$writing->save();
		
		$writing2 = new Writing();
		$writing2->account_id = 2;
		$writing2->amount_excl_vat = 90.50;
		$writing2->amount_inc_vat = 100;
		$writing2->bank_id = 2;
		$writing2->comment = "Ceci est un autre élément du test";
		$writing2->delay = mktime(10, 0, 0, 7, 10, 2013);
		$writing2->information = "Autre complément d'infos";
		$writing2->paid = 1;
		$writing2->source_id = 2;
		$writing2->type_id = 2;
		$writing2->unique_key = "e50b79ffaccc6b50d018aad432711418";
		$writing2->vat = 5.5;
		$writing2->save();
		
		$writing3 = new Writing();
		$writing3->account_id = 1;
		$writing3->amount_excl_vat = 190.50;
		$writing3->amount_inc_vat = 250;
		$writing3->paid = 0;
		$writing3->type_id = 2;
		$writing3->vat = 5.5;
		$writing3->source_id = 2;
		$writing3->delay = strtotime('+1 months', mktime(10, 0, 0, 7, 29, 2013));
		$writing3->save();
		
		$writing4 = new Writing();
		$writing4->account_id = 1;
		$writing4->amount_excl_vat = 250;
		$writing4->amount_inc_vat = 279;
		$writing4->paid = 0;
		$writing4->type_id = 1;
		$writing4->vat = 5.5;
		$writing4->source_id = 2;
		$writing4->delay = strtotime('-1 months', mktime(10, 0, 0, 7, 29, 2013));
		$writing4->save();
		
		$writings = new Writings();
		$writings->set_order('delay', 'ASC');
		$writings->filter = "month";
		$writings->select();
		
		$table = $writings->show();
		$this->assertPattern("/<td>19.60<\/td>/", $table);
		$this->assertPattern("/<td>190.5<\/td>/", $table);
		$this->assertPattern("/Bank 1/", $table);
		$this->assertPattern("/Source 1/", $table);
		$this->assertPattern("/Type 1/", $table);
		$this->assertPattern("/Account 1/", $table);
		$this->assertPattern("/Ceci est un test/", $table);
		$this->assertPattern("/Autre complément d'infos/", $table);
		$this->assertNoPattern("/e50b79ffaccc6b50d018aad432711418/", $table);
		$this->assertPattern("/class=\"draggable\"/", $table);
		$this->assertNoPattern("/<td>250.00<\/td>/", $table);
		$this->assertNoPattern("/279/", $table);
		
		$this->truncateTable("writings");
		$this->truncateTable("sources");
		$this->truncateTable("types");
		$this->truncateTable("accounts");
		$this->truncateTable("banks");
	}
	
	function test_show_timeline() {
		$_SESSION['month_encours'] = 1375308000;
		$writings = new Writings();
		
		$this->assertPattern("/".strtotime('-2 months', 1375308000)."/", $writings->show_timeline());
		$this->assertPattern("/".strtotime('-1 months', 1375308000)."/", $writings->show_timeline());
		$this->assertPattern("/1375308000/", $writings->show_timeline());
		$this->assertPattern("/".strtotime('+1 months', 1375308000)."/", $writings->show_timeline());
		$this->assertPattern("/".strtotime('+2 months', 1375308000)."/", $writings->show_timeline());
		$this->assertPattern("/".strtotime('+3 months', 1375308000)."/", $writings->show_timeline());
		$this->assertPattern("/".strtotime('+4 months', 1375308000)."/", $writings->show_timeline());
		$this->assertPattern("/".strtotime('+5 months', 1375308000)."/", $writings->show_timeline());
		$this->assertPattern("/".strtotime('+6 months', 1375308000)."/", $writings->show_timeline());
		$this->assertPattern("/".strtotime('+7 months', 1375308000)."/", $writings->show_timeline());
		$this->assertPattern("/".strtotime('+8 months', 1375308000)."/", $writings->show_timeline());
		$this->assertPattern("/".strtotime('+9 months', 1375308000)."/", $writings->show_timeline());
		$this->assertPattern("/".strtotime('+10 months', 1375308000)."/", $writings->show_timeline());
		$this->assertPattern("/timeline_month_encours/", $writings->show_timeline());
		$this->assertPattern("/timeline_month_navigation/", $writings->show_timeline());
	}
	
	function test_get_where() {
		$_SESSION['month_encours'] = 1375308000;
		$writings = new Writings();
		$writings->filter = "month";
		$get_where = $writings->get_where();
		$this->assertPattern("/writings.delay >= 1375308000/", $get_where[0]);
		$this->assertPattern("/writings.delay < ".strtotime('+1 months', 1375308000)."/", $get_where[1]);
		$writings->filter = "";
		$get_where = $writings->get_where();
		$this->assertTrue($get_where[0] == 1);
		$this->assertFalse(isset($get_where[1]));
	}
	
	function test_balance_on_date() {
		$writing1 = new Writing();
		$writing1->amount_inc_vat = 150.56;
		$writing1->delay = mktime(10, 0, 0, 7, 20, 2013);
		$writing1->save();
		
		$writings = new Writings();
		$writings->select();
		$this->assertEqual($writings->balance_on_date(mktime(10, 0, 0, 7, 29, 2013)), 150.56);
		$this->assertEqual($writings->balance_on_date(mktime(10, 0, 0, 7, 19, 2013)), 0);
		
		$writing2 = new Writing();
		$writing2->amount_inc_vat = -2150.56;
		$writing2->delay = mktime(10, 0, 0, 7, 18, 2013);
		$writing2->save();
		
		$writings->select();
		$this->assertEqual($writings->balance_on_date(mktime(10, 0, 0, 7, 29, 2013)), -2000);
		$this->assertEqual($writings->balance_on_date(mktime(10, 0, 0, 7, 19, 2013)), -2150.56);
		$this->assertEqual($writings->balance_on_date(mktime(10, 0, 0, 7, 17, 2013)), 0);

		$this->truncateTable("writings");
	}
	
	function test_get_unique_key_in_array() {
		$writing = new Writing();
		$writing->unique_key = "unique 1";
		$writing->save();
		$writing2 = new Writing();
		$writing2->unique_key = "unique 2";
		$writing2->save();
		$writing3 = new Writing();
		$writing3->unique_key = "unique 3";
		$writing3->save();
		$writings = new Writings();
		$writings->select();
		$unique_keys = $writings->get_unique_key_in_array();
		$this->assertTrue(in_array("unique 1", $unique_keys));
		$this->assertTrue(in_array("unique 2", $unique_keys));
		$this->assertTrue(in_array("unique 3", $unique_keys));
	}
}
