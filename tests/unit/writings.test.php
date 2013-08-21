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
	}
	
	function test_get_columns() {
		$writings = new Writings();
		$columns = $writings->get_columns();
		$this->assertPattern("/`writings`.*/", $columns[0]);
		$this->assertPattern("/accounts.name as account_name, sources.name as source_name, types.name as type_name/", $columns[1]);
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
		$writing = new Writing();
		$writing->account_id = 1;
		$writing->amount_excl_tax = 190.50;
		$writing->amount_inc_tax = 25;
		$writing->paid = 0;
		$writing->type_id = 1;
		$writing->vat = 19.6;
		$writing->source_id = 2;
		$writing->delay = mktime(10, 0, 0, 7, 31, 2013);
		$writing->save();
		
		$writing2 = new Writing();
		$writing2->account_id = 2;
		$writing2->amount_excl_tax = 10.50;
		$writing2->amount_inc_tax = 20;
		$writing2->paid = 1;
		$writing2->type_id = 2;
		$writing2->vat = 5.5;
		$writing2->source_id = 3;
		$writing2->delay = mktime(10, 30, 0, 7, 31, 2013);
		$writing2->save();
		
		$writing3 = new Writing();
		$writing3->account_id = 1;
		$writing3->amount_excl_tax = 190.50;
		$writing3->amount_inc_tax = 250;
		$writing3->paid = 0;
		$writing3->type_id = 1;
		$writing3->vat = 5.5;
		$writing3->source_id = 2;
		$writing3->delay = mktime(10, 45, 0, 7, 31, 2013);
		$writing3->save();
		
		$writings = new Writings();
		$writings->set_order('delay', 'ASC');
		$writings->select();
		
		$html = $writings->show();
		$this->assertPattern("/<button class=\"split\"/", $html);
		$this->assertPattern("/<th /", $html);
		$this->assertPattern("/<td>10.5<\/td>/", $html);
		$this->assertPattern("/<td>250<\/td>/", $html);
		$this->assertPattern("/<td>19.60<\/td>/", $html);
		$this->assertPattern("/class=\"grid_header\"/", $html);
		$this->assertPattern("/class=\"draggable\"/", $html);
		
		$this->truncateTable("writings");
	}
}
