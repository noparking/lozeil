<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2015 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Bayesian_Elements extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"accountingcodes",
			"banks",
			"bayesianelements",
			"categories",
			"sources",
			"writings"
		);
	}
	
	function test_get_distinct_table_id() {
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "amount_inc_vat";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 5;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 1;
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "amount_inc_vat";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 5;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 2;
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 4;
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "accountingcodes";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		
		$bayesianelements = new Bayesian_Elements();
		$ids = $bayesianelements->get_distinct_table_id("categories");
		$this->assertTrue($ids == array(3, 1, 2));
		$ids = $bayesianelements->get_distinct_table_id("accountingcodes");
		$this->assertTrue($ids == array(4));
		$this->truncateTable("bayesianelements");
	}
	
	function test_get_accounting_codes_in_use() {
		$accounting_code = new Accounting_Code();
		$accounting_code->save();
		$accounting_code = new Accounting_Code();
		$accounting_code->save();
		$accounting_code = new Accounting_Code();
		$accounting_code->save();
		$accounting_code = new Accounting_Code();
		$accounting_code->save();
		$accounting_code = new Accounting_Code();
		$accounting_code->save();
		$accounting_code = new Accounting_Code();
		$accounting_code->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "amount_inc_vat";
		$bayesianelement->table_name = "accountingcodes";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 5;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 1;
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "accountingcodes";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "amount_inc_vat";
		$bayesianelement->table_name = "accountingcodes";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 5;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 2;
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "accountingcodes";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 4;
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		
		$bayesianelements = new Bayesian_Elements();
		$accounting_codes_in_use = $bayesianelements->get_accounting_codes_in_use();
		$this->assertEqual(count($accounting_codes_in_use), 3);
		$this->truncateTable("bayesianelements");
	}
	
	function test_get_categories_in_use() {
		$category = new Category();
		$category->save();
		$category = new Category();
		$category->save();
		$category = new Category();
		$category->save();
		$category = new Category();
		$category->save();
		$category = new Category();
		$category->save();
		$category = new Category();
		$category->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "amount_inc_vat";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 5;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 1;
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "amount_inc_vat";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 5;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 2;
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 4;
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "accountingcodes";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		
		$bayesianelements = new Bayesian_Elements();
		$accounting_codes_in_use = $bayesianelements->get_categories_in_use();
		$this->assertEqual(count($accounting_codes_in_use), 3);
		$this->truncateTable("bayesianelements");
	}
	
	function test_stuff_with() {
		$writing = new Writing();
		$writing->amount_inc_vat = 50;
		$writing->categories_id = 5;
		$writing->accountingcodes_id = 205;
		$writing->comment = "payement de facture";
		$writing->save();
		$bayesianelements =new Bayesian_Elements();
		$bayesianelements->stuff_with($writing);
		$this->assertTrue(count($bayesianelements) == 6);
		$this->truncateTable("writings");
	}
	
	function test_increment() {
		$writing = new Writing();
		$writing->amount_inc_vat = 50;
		$writing->categories_id = 5;
		$writing->accountingcodes_id = 205;
		$writing->comment = "payement de facture";
		$writing->save();
		$bayesianelements = new Bayesian_Elements();
		$bayesianelements->stuff_with($writing);
		$bayesianelements->increment();
		
		$bayesianelements_loaded = new Bayesian_Elements();
		$bayesianelements_loaded->select();
		$this->assertTrue(count($bayesianelements_loaded) == 6);
		$this->assertRecordExists("bayesianelements", array(
			'element' => 'payement',
			'field' => 'comment',
			'table_name' => 'categories',
			'table_id' => 5,
			'occurrences' => 1
		));
		
		$bayesianelements->increment();
		$bayesianelements_loaded = new Bayesian_Elements();
		$bayesianelements_loaded->select();
		$this->assertTrue(count($bayesianelements_loaded) == 6);
		$this->assertRecordExists("bayesianelements", array(
			'element' => 'payement',
			'field' => 'comment',
			'table_name' => 'categories',
			'table_id' => 5,
			'occurrences' => 2
		));
		$this->truncateTable("bayesianelements");
		$this->truncateTable("writings");
	}
	
	function test_increment_decrement() {
		$writing_before = new Writing();
		$writing_before->amount_inc_vat = 50;
		$writing_before->categories_id = 0;
		$writing_before->comment = "payement";
		$writing_before->save();
		$bayesianelements = new Bayesian_Elements();
		$bayesianelements->stuff_with($writing_before);
		$bayesianelements->increment();
		$writing = new Writing();
		$writing->amount_inc_vat = 50;
		$writing->categories_id = 3;
		$writing->comment = "payement";
		$writing->save();
		
		$this->assertTrue(count($bayesianelements) == 0);
		$bayesianelements->increment_decrement($writing_before, $writing);
		$bayesianelements->select();
		$this->assertTrue(count($bayesianelements) == 2);
		$this->assertRecordExists("bayesianelements", array(
			'element' => 'payement',
			'field' => 'comment',
			'table_name' => 'categories',
			'table_id' => 3,
			'occurrences' => 1
		));
		$this->truncateTable("writings");
		$this->truncateTable("bayesianelements");
		
		$writing_before = new Writing();
		$writing_before->amount_inc_vat = 50;
		$writing_before->categories_id = 5;
		$writing_before->comment = "payement";
		$writing_before->save();
		$bayesianelements = new Bayesian_Elements();
		$bayesianelements->stuff_with($writing_before);
		$bayesianelements->increment();
		$writing = new Writing();
		$writing->amount_inc_vat = 50;
		$writing->categories_id = 3;
		$writing->comment = "payement";
		$writing->save();
		
		$this->assertTrue(count($bayesianelements) == 2);
		$this->assertRecordExists("bayesianelements", array(
			'element' => 'payement',
			'field' => 'comment',
			'table_name' => 'categories',
			'table_id' => 5,
			'occurrences' => 1
		));
		$bayesianelements->increment_decrement($writing_before, $writing);
		$bayesianelements->select();
		$this->assertTrue(count($bayesianelements) == 4);
		$this->assertRecordExists("bayesianelements", array(
			'element' => 'payement',
			'field' => 'comment',
			'table_name' => 'categories',
			'table_id' => 5,
			'occurrences' => 0
		));
		$this->assertRecordExists("bayesianelements", array(
			'element' => 'payement',
			'field' => 'comment',
			'table_name' => 'categories',
			'table_id' => 3,
			'occurrences' => 1
		));
		$this->truncateTable("writings");
		$this->truncateTable("bayesianelements");
	}
	
	function test_train() {
		$writing = new Writing();
		$writing->amount_inc_vat = 50;
		$writing->categories_id = 5;
		$writing->comment = "payement de facture";
		$writing->save();
		$writing = new Writing();
		$writing->amount_inc_vat = -20;
		$writing->categories_id = 3;
		$writing->comment = "virement interne no parking";
		$writing->save();
		$writing = new Writing();
		$writing->amount_inc_vat = -20;
		$writing->categories_id = 3;
		$writing->comment = "virement interne no parking";
		$writing->save();
		$writing = new Writing();
		$writing->amount_inc_vat = 52.35;
		$writing->categories_id = 2;
		$writing->comment = "coopa pour un employé";
		$writing->save();
		$writing = new Writing();
		$writing->amount_inc_vat = 100;
		$writing->comment = "enregistrement non valide";
		$writing->save();
		$bayesianelements = new Bayesian_Elements();
		$bayesianelements->train();
		$bayesianelements = new Bayesian_Elements();
		$categories = new Categories();
		$categories->select();
		$bayesianelements->prepare_id_estimation($GLOBALS['dbconfig']['table_categories'], $categories);
		$bayesianelements->select();
		$this->assertTrue(count($bayesianelements) == 11);
		$this->assertRecordExists(
		"bayesianelements",
			array(
				'id' => 1,
				'element' => "payement",
				'field' => "comment",
				'table_id' => 5,
				'occurrences' => 1
			)
		);
		$this->assertRecordExists(
		"bayesianelements",
			array(
				'id' => 3,
				'element' => "50.000000",
				'field' => "amount_inc_vat",
				'table_id' => 5,
				'occurrences' => 1
			)
		);
		$this->assertRecordExists(
		"bayesianelements",
			array(
				'id' => 4,
				'element' => "virement",
				'field' => "comment",
				'table_id' => 3,
				'occurrences' => 2
			)
		);
		$this->truncateTable("bayesianelements");
		$this->truncateTable("writings");
	}
	
	function test_element_probabilities() {
		$bayesianelements = new Bayesian_Elements();
		$bayesianelements->prepare_id_estimation($GLOBALS['dbconfig']['table_categories']);
		
		$this->assertEqual($bayesianelements->element_probabilities("virement", 3), 0);
		
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "comment";
		$bayesianelement->element = "virement";
		$bayesianelement->table_name = "categories";
		$bayesianelement->occurrences = 10;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "comment";
		$bayesianelement->element = "autre";
		$bayesianelement->table_name = "categories";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "amount_inc_vat";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 5;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 2;
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		
		$bayesianelements->prepare_id_estimation($GLOBALS['dbconfig']['table_categories']);
		
		$this->assertEqual($bayesianelements->element_probabilities("inexistant", 3), 0);
		
		$this->assertEqual($bayesianelements->element_probabilities("virement", 3), 10/17);
		
		$this->truncateTable("categories");
		$this->truncateTable("bayesianelements");
	}
	
	function test_fisher_element_probabilities() {
		$bayesianelements = new Bayesian_Elements();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "comment";
		$bayesianelement->element = "virement";
		$bayesianelement->table_name = "categories";
		$bayesianelement->occurrences = 10;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 2;
		$bayesianelement->field = "comment";
		$bayesianelement->element = "virement";
		$bayesianelement->table_name = "categories";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "amount_inc_vat";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 5;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 2;
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "autre";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 2;
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "test";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		$bayesianelements->prepare_id_estimation($GLOBALS['dbconfig']['table_categories']);
		
		$this->assertEqual($bayesianelements->fisher_element_probabilities("virement", 4), 0);
		
		$this->assertEqual($bayesianelements->fisher_element_probabilities("virement", 3), (10/15) / ((10/15) + (2/6)));
		
		$this->assertEqual($bayesianelements->fisher_element_probabilities("test", 2), (2/4) / (2/4));
		
		$this->truncateTable("categories");
		$this->truncateTable("bayesianelements");
	}
	
	
	function test_fisher_element_weighted_probabilities() {
		$bayesianelements = new Bayesian_Elements();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "comment";
		$bayesianelement->element = "virement";
		$bayesianelement->table_name = "categories";
		$bayesianelement->occurrences = 10;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "comment";
		$bayesianelement->element = "autre";
		$bayesianelement->table_name = "categories";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "amount_inc_vat";
		$bayesianelement->element = "autre";
		$bayesianelement->table_name = "categories";
		$bayesianelement->occurrences = 5;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 2;
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "virement";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		$bayesianelements->prepare_id_estimation($GLOBALS['dbconfig']['table_categories']);
		$this->assertEqual($bayesianelements->fisher_element_weighted_probabilities("virement", 3), (1 * 0.5 + 12 * ((10/17) / ((10/17) + (2/2)))) / (1 + 12));
		$this->assertEqual($bayesianelements->fisher_element_weighted_probabilities("virement", 2), (1 * 0.5 + 12 * ((2/2) / ((2/2) + (10/17)))) / (1 + 12));
		$this->assertEqual($bayesianelements->fisher_element_weighted_probabilities("virement", 4), (1 * 0.5 + 12 * (0)) / (1 + 12));
		$this->assertEqual($bayesianelements->fisher_element_weighted_probabilities("inexistant", 4), 0.5);
		$this->truncateTable("categories");
		$this->truncateTable("bayesianelements");
	}
	
	function test_fisher_data_probabilities() {
		$GLOBALS['param']['comment_weight'] = 1;
		$GLOBALS['param']['amount_inc_vat_weight'] = 0.3;
		
		$bayesianelements = new Bayesian_Elements();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "comment";
		$bayesianelement->element = "virement";
		$bayesianelement->table_name = "categories";
		$bayesianelement->occurrences = 10;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "comment";
		$bayesianelement->element = "autre";
		$bayesianelement->table_name = "categories";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "amount_inc_vat";
		$bayesianelement->element = "autre";
		$bayesianelement->table_name = "categories";
		$bayesianelement->occurrences = 5;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 2;
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "virement";
		$bayesianelement->occurrences = 2;
		$bayesianelement->save();
		$bayesianelements->prepare_id_estimation($GLOBALS['dbconfig']['table_categories']);
		$writing = new Writing();
		$writing->amount_inc_vat = 2;
		$writing->comment = "virement";
		$this->assertEqual($bayesianelements->fisher_data_probability($writing, 3),
				$bayesianelements->inverse_chi2(- 2 * log(((1 * 0.5) + (12 * ((10/17) / ((10/17) + (2/2))))) / (1 + 12) * (((3 * 0.5) + (0 * 0)) / (3 + 0))), 4)
			);
		$this->assertEqual($bayesianelements->fisher_data_probability($writing, 2),
				$bayesianelements->inverse_chi2(- 2 * log(((1 * 0.5) + (12 * ((2/2) / ((10/17) + (2/2))))) / (1 + 12) * (((3 * 0.5) + (0 * 0)) / (3 + 0))), 4)
			);
		$this->assertEqual($bayesianelements->fisher_data_probability($writing, 1),
				$bayesianelements->inverse_chi2(- 2 * log(((1 * 0.5) + (12 * 0)) / (1 + 12) * (((3 * 0.5) + (0 * 0)) / (3 + 0))), 4)
			);
		$this->truncateTable("bayesianelements");
	}
	
	function test_inverse_chi2() {
		$bayesianelements = new Bayesian_Elements();
		$m = 0.95 / 2;
		$sum = exp(-$m);
		$term = $sum;
		for ($i = 1; $i <= floor(10 / 2); $i++) {
			$term *= $m / $i;
			$sum += $term;
		}
		$value = min(array($sum, 1));
		$this->assertEqual($bayesianelements->inverse_chi2(0.95, 10), $value);
	}
	
	function test_fisher_element_id_estimated() {
		$GLOBALS['param']['comment_weight'] = 1;
		$GLOBALS['param']['amount_inc_vat_weight'] = 0.3;
		$GLOBALS['param']['fisher_threshold'] = 0.4;
		
		$category = new Category();
		$category->save();
		$category = new Category();
		$category->save();
		$category = new Category();
		$category->save();
		
		$bayesianelements = new Bayesian_Elements();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 1;
		$bayesianelement->field = "comment";
		$bayesianelement->element = "CARTE";
		$bayesianelement->table_name = "categories";
		$bayesianelement->occurrences = 14;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 2;
		$bayesianelement->field = "comment";
		$bayesianelement->element = "CARTE";
		$bayesianelement->table_name = "categories";
		$bayesianelement->occurrences = 62;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "amount_inc_vat";
		$bayesianelement->element = "CARTE";
		$bayesianelement->table_name = "categories";
		$bayesianelement->occurrences = 13;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 2;
		$bayesianelement->field = "comment";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "SNCF";
		$bayesianelement->occurrences = 28;
		$bayesianelement->save();
		$bayesianelements->prepare_id_estimation($GLOBALS['dbconfig']['table_categories']);
		
		$writing = new Writing();
		$writing->comment = "CARTE";
		$this->assertEqual($bayesianelements->fisher_element_id_estimated($writing), 0);
		$writing->comment = "CARTE SNCF";
		$this->assertEqual($bayesianelements->fisher_element_id_estimated($writing), 2);
		$writing->comment = "commentaire inconnu";
		$this->assertEqual($bayesianelements->fisher_element_id_estimated($writing), 0);
		
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 2;
		$bayesianelement->field = "amount_inc_vat";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "520.200000";
		$bayesianelement->occurrences = 8;
		$bayesianelement->save();
		
		$writing->amount_inc_vat = 520.200000;
		$writing->comment = "CARTE";
		$bayesianelements->prepare_id_estimation($GLOBALS['dbconfig']['table_categories']);
		$this->assertEqual($bayesianelements->fisher_element_id_estimated($writing), 2);
		
		
		$accounting_code = new Accounting_Code();
		$accounting_code->save();
		$accounting_code = new Accounting_Code();
		$accounting_code->save();
		$accounting_code = new Accounting_Code();
		$accounting_code->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 1;
		$bayesianelement->field = "comment";
		$bayesianelement->element = "CARTE";
		$bayesianelement->table_name = "accountingcodes";
		$bayesianelement->occurrences = 14;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 2;
		$bayesianelement->field = "comment";
		$bayesianelement->element = "CARTE";
		$bayesianelement->table_name = "accountingcodes";
		$bayesianelement->occurrences = 62;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 3;
		$bayesianelement->field = "amount_inc_vat";
		$bayesianelement->element = "CARTE";
		$bayesianelement->table_name = "accountingcodes";
		$bayesianelement->occurrences = 13;
		$bayesianelement->save();
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 2;
		$bayesianelement->field = "comment";
		$bayesianelement->element = "SNCF";
		$bayesianelement->table_name = "accountingcodes";
		$bayesianelement->occurrences = 28;
		$bayesianelement->save();
		$bayesianelements->prepare_id_estimation($GLOBALS['dbconfig']['table_accountingcodes']);
		
		$writing = new Writing();
		$writing->comment = "CARTE";
		$this->assertEqual($bayesianelements->fisher_element_id_estimated($writing), 0);
		$writing->comment = "CARTE SNCF";
		$this->assertEqual($bayesianelements->fisher_element_id_estimated($writing), 2);
		$writing->comment = "commentaire inconnu";
		$this->assertEqual($bayesianelements->fisher_element_id_estimated($writing), 0);
		
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->table_id = 2;
		$bayesianelement->field = "amount_inc_vat";
		$bayesianelement->table_name = "categories";
		$bayesianelement->element = "520.200000";
		$bayesianelement->occurrences = 8;
		$bayesianelement->save();
		
		$writing->amount_inc_vat = 520.200000;
		$writing->comment = "CARTE";
		$bayesianelements->prepare_id_estimation($GLOBALS['dbconfig']['table_accountingcodes']);
		$this->assertEqual($bayesianelements->fisher_element_id_estimated($writing), 2);
	}
}
