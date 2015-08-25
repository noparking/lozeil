<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Bayesian_Element extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"bayesianelements"
		);
	}
	
	function test_save_load() {
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->element = "filtre";
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "categories";
		$bayesianelement->table_id = 5;
		$bayesianelement->occurrences = 15;
		$bayesianelement->save();
		$bayesianelement_loaded = new Bayesian_Element();
		$bayesianelement_loaded->load(array('id' => 1));
		$this->assertEqual($bayesianelement_loaded->element, $bayesianelement->element);
		$this->assertEqual($bayesianelement_loaded->field, $bayesianelement->field);
		$this->assertEqual($bayesianelement_loaded->table_name, $bayesianelement->table_name);
		$this->assertEqual($bayesianelement_loaded->table_id, $bayesianelement->table_id);
		$this->assertEqual($bayesianelement_loaded->occurrences, $bayesianelement->occurrences);
		$this->truncateTable("bayesianelements");
	}
	
	function test_update() {
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->element = "filtre";
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "categories";
		$bayesianelement->table_id = 5;
		$bayesianelement->occurrences = 15;
		$bayesianelement->save();
		$bayesianelement_loaded = new Bayesian_Element();
		$bayesianelement_loaded->id = 1;
		$bayesianelement->element = "autre";
		$bayesianelement->field = "amount";
		$bayesianelement->table_name = "amount_inc_vat";
		$bayesianelement->table_id = 2;
		$bayesianelement->occurrences = 12;
		$bayesianelement_loaded->update();
		$bayesianelement_loaded2 = new Bayesian_Element();
		$this->assertTrue($bayesianelement_loaded2->load(array('id' => 1)));
		$this->assertNotEqual($bayesianelement_loaded2->element, $bayesianelement->element);
		$this->assertNotEqual($bayesianelement_loaded2->field, $bayesianelement->field);
		$this->assertNotEqual($bayesianelement_loaded2->table_name, $bayesianelement->table_name);
		$this->assertNotEqual($bayesianelement_loaded2->table_id, $bayesianelement->table_id);
		$this->assertNotEqual($bayesianelement_loaded2->occurrences, $bayesianelement->occurrences);
		$this->truncateTable("bayesianelements");
	}
	
	function test_delete() {
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->element = "premier";
		$bayesianelement->save();
		$bayesianelement_loaded = new Bayesian_Element();
		$this->assertTrue($bayesianelement_loaded->load(array('id' => 1)));
		$bayesianelement->delete();
		$this->assertFalse($bayesianelement_loaded->load(array('id' => 1)));
	}
	
	function test_increment() {
		$bayesian_element = new Bayesian_Element();
		$bayesian_element->element = "payement";
		$bayesian_element->field = "comment";
		$bayesian_element->table_name = "categories";
		$bayesian_element->table_id = 2;
		$bayesian_element->increment();
		$bayesian_elements = new Bayesian_Elements();
		$bayesian_elements->select();
		$this->assertTrue(count($bayesian_elements) == 1);
		$this->assertRecordExists("bayesianelements", array(
			'element' => 'payement',
			'field' => 'comment',
			'table_name' => 'categories',
			'table_id' => 2,
			'occurrences' => 1
		));
		$bayesian_element->increment();
		$bayesian_elements->select();
		$this->assertTrue(count($bayesian_elements) == 1);
		$this->assertRecordExists("bayesianelements", array(
			'element' => 'payement',
			'field' => 'comment',
			'table_name' => 'categories',
			'table_id' => 2,
			'occurrences' => 2
		));
		$this->truncateTable("bayesianelements");
	}
	
	function test_decrement() {
		$bayesian_element = new Bayesian_Element();
		$bayesian_element->element = "payement";
		$bayesian_element->field = "comment";
		$bayesian_element->table_name = "categories";
		$bayesian_element->table_id = 2;
		$bayesian_element->increment();
		$bayesian_elements = new Bayesian_Elements();
		$bayesian_elements->select();
		$this->assertTrue(count($bayesian_elements) == 1);
		$this->assertRecordExists("bayesianelements", array(
			'element' => 'payement',
			'field' => 'comment',
			'table_name' => 'categories',
			'table_id' => 2,
			'occurrences' => 1
		));
		$bayesian_element->decrement();
		$bayesian_elements->select();
		$this->assertTrue(count($bayesian_elements) == 1);
		$this->assertRecordExists("bayesianelements", array(
			'element' => 'payement',
			'field' => 'comment',
			'table_name' => 'categories',
			'table_id' => 2,
			'occurrences' => 0
		));
		$bayesian_element->decrement();
		$this->assertRecordExists("bayesianelements", array(
			'element' => 'payement',
			'field' => 'comment',
			'table_name' => 'categories',
			'table_id' => 2,
			'occurrences' => 0
		));
		$this->truncateTable("bayesianelements");
	}
}
