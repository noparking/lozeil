<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Writings extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"writings",
			"categories",
			"sources",
			"banks"
		);
	}
	
	function test_get_join() {
		$writings = new Writings();
		$join = $writings->get_join();
		$this->assertPattern("/LEFT JOIN categories/", $join[0]);
		$this->assertPattern("/ON categories.id = writings.categories_id/", $join[0]);
		$this->assertPattern("/LEFT JOIN sources/", $join[1]);
		$this->assertPattern("/ON sources.id = writings.sources_id/", $join[1]);
		$this->assertPattern("/ON sources.id = writings.sources_id/", $join[1]);
		$this->assertPattern("/LEFT JOIN banks/", $join[2]);
		$this->assertPattern("/ON banks.id = writings.banks_id/", $join[2]);
	}
	
	function test_get_columns() {
		$writings = new Writings();
		$columns = $writings->get_columns();
		$this->assertPattern("/`writings`.*/", $columns[0]);
		$this->assertPattern("/categories.name as category_name, sources.name as source_name, banks.name as bank_name/", $columns[1]);
	}
	
	function test_show() {
		$_SESSION['timestamp'] = mktime(0, 0, 0, 7, 1, 2013);
		list($start, $stop) = determine_month($_SESSION['timestamp']);
		$category = new Category();
		$category->name = "Category 1";
		$category->save();
		$bank = new Bank();
		$bank->name = "Bank 1";
		$bank->save();
		$source = new Source();
		$source->name = "Source 1";
		$source->save();
		$category2 = new Category();
		$category2->name = "Category 2";
		$category2->save();
		$bank2 = new Bank();
		$bank2->name = "Bank 2";
		$bank2->save();
		$source2 = new Source();
		$source2->name = "Source 2";
		$source2->save();
		
		$writing = new Writing();
		$writing->categories_id = 1;
		$writing->amount_excl_vat = 190.50;
		$writing->amount_inc_vat = 250;
		$writing->banks_id = 1;
		$writing->comment = "Ceci est un test";
		$writing->day = mktime(10, 0, 0, 7, 29, 2013);
		$writing->information = "Complément d'infos";
		$writing->paid = 0;
		$writing->sources_id = 1;
		$writing->number = 1;
		$writing->unique_key = "e50b79ffaccc6b50d018aad432711418";
		$writing->vat = 19.6;
		$writing->save();
		
		$writing2 = new Writing();
		$writing2->categories_id = 2;
		$writing2->amount_excl_vat = 90.50;
		$writing2->amount_inc_vat = 100;
		$writing2->banks_id = 2;
		$writing2->comment = "Ceci est un autre élément du test";
		$writing2->day = mktime(10, 0, 0, 7, 10, 2013);
		$writing2->information = "Autre complément d'infos";
		$writing2->paid = 1;
		$writing2->sources_id = 2;
		$writing2->number = 2;
		$writing2->unique_key = "e50b79ffaccc6b50d018aad432711418";
		$writing2->vat = 5.5;
		$writing2->save();
		
		$writing3 = new Writing();
		$writing3->categories_id = 1;
		$writing3->amount_excl_vat = 190.50;
		$writing3->amount_inc_vat = 250;
		$writing3->paid = 0;
		$writing3->number = 2;
		$writing3->vat = 5.5;
		$writing3->sources_id = 2;
		$writing3->day = strtotime('+1 months', mktime(10, 0, 0, 7, 29, 2013));
		$writing3->save();
		
		$writing4 = new Writing();
		$writing4->categories_id = 1;
		$writing4->amount_excl_vat = 250;
		$writing4->amount_inc_vat = 279;
		$writing4->paid = 0;
		$writing4->number = 1;
		$writing4->vat = 5.5;
		$writing4->sources_id = 2;
		$writing4->day = strtotime('-1 months', mktime(10, 0, 0, 7, 29, 2013));
		$writing4->save();
		
		$writings = new Writings();
		$writings->set_order('day', 'ASC');
		$writings->filter_with(array('start' => $start, 'stop' => $stop));
		$writings->select();
		
		$table = $writings->show();
		$this->assertPattern("/<td>19.60<\/td>/", $table);
		$this->assertPattern("/<td>190.5<\/td>/", $table);
		$this->assertPattern("/Bank 1/", $table);
		$this->assertPattern("/Source 1/", $table);
		$this->assertPattern("/Category 1/", $table);
		$this->assertPattern("/Ceci est un test/", $table);
		$this->assertPattern("/Autre complément d'infos/", $table);
		$this->assertNoPattern("/e50b79ffaccc6b50d018aad432711418/", $table);
		$this->assertPattern("/class=\"draggable\"/", $table);
		$this->assertNoPattern("/<td>250.00<\/td>/", $table);
		$this->assertNoPattern("/279/", $table);
		
		$writings = new Writings();
		$writings->set_order('day', 'ASC');
		$writings->filter_with(array('*' => "élément"));
		$writings->select();
		
		$table = $writings->show();
		$this->assertPattern("/Ceci est un autre élément du test/", $table);
		$this->assertNoPattern("/Ceci est un test/", $table);
		
		$writings = new Writings();
		$writings->set_order('day', 'ASC');
		$writings->filter_with(array('*' => "Bank"));
		$writings->select();
		
		$table = $writings->show();
		$this->assertPattern("/Bank 1/", $table);
		$this->assertPattern("/Bank 2/", $table);
		
		$writings = new Writings();
		$writings->set_order('day', 'ASC');
		$writings->filter_with(array('*' => "Source 1"));
		$writings->select();
		
		$table = $writings->show();
		$this->assertPattern("/Source 1/", $table);
		$this->assertNoPattern("/Source 2/", $table);
		
		$this->truncateTable("writings");
		$this->truncateTable("sources");
		$this->truncateTable("categories");
		$this->truncateTable("banks");
	}
	
	function test_show_timeline_at() {
		$_SESSION['timestamp'] = 1375308000;
		$writings = new Writings();
		
		$this->assertPattern("/".strtotime('-2 months', 1375308000)."/", $writings->show_timeline_at($_SESSION['timestamp']));
		$this->assertPattern("/".strtotime('-1 months', 1375308000)."/", $writings->show_timeline_at($_SESSION['timestamp']));
		$this->assertPattern("/1375308000/", $writings->show_timeline_at($_SESSION['timestamp']));
		$this->assertPattern("/".strtotime('+1 months', 1375308000)."/", $writings->show_timeline_at($_SESSION['timestamp']));
		$this->assertPattern("/".strtotime('+2 months', 1375308000)."/", $writings->show_timeline_at($_SESSION['timestamp']));
		$this->assertPattern("/".strtotime('+3 months', 1375308000)."/", $writings->show_timeline_at($_SESSION['timestamp']));
		$this->assertPattern("/".strtotime('+4 months', 1375308000)."/", $writings->show_timeline_at($_SESSION['timestamp']));
		$this->assertPattern("/".strtotime('+5 months', 1375308000)."/", $writings->show_timeline_at($_SESSION['timestamp']));
		$this->assertPattern("/".strtotime('+6 months', 1375308000)."/", $writings->show_timeline_at($_SESSION['timestamp']));
		$this->assertPattern("/".strtotime('+7 months', 1375308000)."/", $writings->show_timeline_at($_SESSION['timestamp']));
		$this->assertPattern("/".strtotime('+8 months', 1375308000)."/", $writings->show_timeline_at($_SESSION['timestamp']));
		$this->assertPattern("/".strtotime('+9 months', 1375308000)."/", $writings->show_timeline_at($_SESSION['timestamp']));
		$this->assertPattern("/".strtotime('+10 months', 1375308000)."/", $writings->show_timeline_at($_SESSION['timestamp']));
		$this->assertPattern("/timeline_month_encours/", $writings->show_timeline_at($_SESSION['timestamp']));
		$this->assertPattern("/timeline_month_navigation/", $writings->show_timeline_at($_SESSION['timestamp']));
	}
	
	function test_get_where() {
		$_SESSION['timestamp'] = 1375308000;
		list($start, $stop) = determine_month($_SESSION['timestamp']);
		$writings = new Writings();
		$writings->filter_with(array('start' => $start, 'stop' => $stop));
		$get_where = $writings->get_where();
		$this->assertPattern("/writings.day >= 1375308000/", $get_where[0]);
		$this->assertPattern("/writings.day <= 1377986399/", $get_where[1]);
		$writings2 = new Writings();
		$get_where2 = $writings2->get_where();
		$this->assertTrue(!isset($get_where2[0]));
		$this->assertFalse(isset($get_where2[1]));
	}
	
	function test_show_balance_at() {
		$writing1 = new Writing();
		$writing1->amount_inc_vat = 150.56;
		$writing1->day = mktime(10, 0, 0, 7, 20, 2013);
		$writing1->save();
		
		$writings = new Writings();
		$writings->select();
		$this->assertEqual($writings->show_balance_at(mktime(10, 0, 0, 7, 29, 2013)), 150.56);
		$this->assertEqual($writings->show_balance_at(mktime(10, 0, 0, 7, 19, 2013)), 0);
		
		$writing2 = new Writing();
		$writing2->amount_inc_vat = -2150.56;
		$writing2->day = mktime(10, 0, 0, 7, 18, 2013);
		$writing2->save();
		
		$writings->select();
		$this->assertEqual($writings->show_balance_at(mktime(10, 0, 0, 7, 29, 2013)), -2000);
		$this->assertEqual($writings->show_balance_at(mktime(10, 0, 0, 7, 19, 2013)), -2150.56);
		$this->assertEqual($writings->show_balance_at(mktime(10, 0, 0, 7, 17, 2013)), 0);

		$this->truncateTable("writings");
	}
	
	function test_filter_with() {
		$writings = new Writings();
		$writings->filter_with(array('start' => mktime(0, 0, 0, 3, 9, 2013), 'stop' => mktime(0, 0, 0, 3, 10, 2013), '*' => 'fullsearch'));
		$this->assertEqual($writings->filters['start'], 1362783600);
		$this->assertEqual($writings->filters['stop'], 1362870000);
		$this->assertEqual($writings->filters['*'], "fullsearch");
	}
}
