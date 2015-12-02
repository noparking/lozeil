<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";
require_once dirname(__FILE__)."/../../inc/office/excel/PHPExcel.php";

class tests_Import_Balances extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"balances",
			"balancesimported",
			"balancesperiod",
			"bayesianelements"
		);
	}

	function skip() {
		$this->skipIf(!is_writable(dirname(__FILE__)."/../../var/tmp"), "Nécessite les droits d'écriture");
	}

	function tearDown() {
		$this->truncateTable("balances");
		$this->truncateTable("balancesimported");
		$this->truncateTable("balancesperiod");
	}

	function test_import_as_xlsx() {
		$this->backupTables("balances", "balancesimported", "balancesperiod");

		$file = dirname(__FILE__)."/data/import.xlsx";
		$importer = new Import_Balances($file,$file);
		$importer->import();

		$this->assertTrue(file_exists($file));
		$this->assertRecordExists("balancesperiod", array(
						'id' => 1
				)
		);
		$this->assertRecordExists("balances", array(
						'name' => "LOGICIEL",
						'amount' => -59259.13
				)
		);
		$this->assertRecordExists("balances", array(
						'name' => "CAPITAL",
						'amount' => 155000
				)
		);
		$this->assertRecordExists("balances", array(
						'name' => "70000",
						'amount' => 2000
				)
		);
		$this->teardown();
	}

	function test_import_as_paybox() {
		require dirname(__FILE__)."/data/import.paybox.php";
		$name = tempnam('/tmp', 'csv');
		$handle = fopen($name, 'w+');
		
		foreach($mydata as $data) {
			fputcsv($handle, $data, ";");
		}
		
		$data = new Import_Balances($name);
		$data->sources_id = 1;
		$data->prepare_csv_data();
		$data->import_as_paybox();
		
		$this->assertRecordExists("balances", array(
				'id' => 1,
				'amount' => "509.1000000",
				'name' => "lozeil adrien.delannoy@noparking.net",
			)
		);

		$data = new Import_Balances($name);
		$data->sources_id = 1;
		$data->prepare_csv_data();
		$data->import_as_cic();
		
		fclose($handle);
		unlink($name);
		$balance = new Balance();
		$this->assertFalse($balance->load(array('id' => 5 )));

		$this->tearDown();
	}
	
	function test_import_as_cic() {
		require dirname(__FILE__)."/data/import.cic.php";
		$name = tempnam('/tmp', 'csv');
		$handle = fopen($name, 'w+');
		
		foreach($mydata as $data) {
			fputcsv($handle, $data, ";");
		}
		
		$data = new Import_Balances($name);
		$data->banks_id = 1;
		$data->prepare_csv_data();
		$data->import_as_cic();
		
		$this->assertRecordExists("balances", array(
			'id' => 1,
			'amount' => "152.2000000",
			'name' => "test de libellé",
			'day' => mktime(0, 0, 0, 7, 1, 2013)
			)
		);
		$this->assertRecordExists("balances", array(
			'id' => 2,
			'amount' => "-361.5000000",
			'name' => "",
			'day' => mktime(0, 0, 0, 7, 4, 2013)
			)
		);

		$data = new Import_Balances($name);
		$data->banks_id = 1;
		$data->prepare_csv_data();
		$data->import_as_cic();
		
		fclose($handle);
		unlink($name);
		$balance = new Balance();
		$this->assertFalse($balance->load(array('id' => 5 )));

		$this->tearDown();
	}
	
	function test_import_as_coop() {
		require dirname(__FILE__)."/data/import.coop.php";
		$name = tempnam('/tmp', 'csv');
		$handle = fopen($name, 'w+');
		
		foreach($mydata as $data) {
			fputcsv($handle, $data, ";");
		}
		
		$data = new Import_Balances($name);
		$data->banks_id = 2;
		$data->prepare_csv_data();
		$data->import_as_coop();
		
		$this->assertRecordExists("balances", array(
			'id' => 1,
			'amount' => "-152.200000",
			'name' => "libellé 1",
			'day' => mktime(0, 0, 0, 7, 2, 2013),
			)
		);
		
		$this->assertRecordExists("balances", array(
			'id' => 2,
			'amount' => "304.400000",
			'day' => mktime(0, 0, 0, 7, 3, 2013),
			)
		);
		
		$data = new Import_Balances($name);
		$data->banks_id = 2;
		$data->prepare_csv_data();
		$data->import_as_coop();
		fclose($handle);
		unlink($name);
		$balance = new Balance();
		$this->assertFalse($balance->load(array('id' => 4 )));

		$this->tearDown();
	}
	
	function test_import_as_ofx() {
		require dirname(__FILE__)."/data/import.ofx.php";
		$name = tempnam('/tmp', 'ofx');
		$handle = fopen($name, 'w+');
		fwrite($handle, $content);
		fclose($handle);
		
		$data = new Import_Balances($name);
		$data->banks_id = 1;
		$data->import_as_ofx();
		
		$this->assertRecordExists("balances", array(
			'id' => 1,
			'amount' => "-27.500000",
			'day' => mktime(0, 0, 0, 10, 4, 2013),
			)
		);
		$this->assertRecordExists("balances", array(
			'id' => 2,
			'amount' => "-10.500000",
			'day' => mktime(0, 0, 0, 12, 4, 2013),
			'name' => "ABONNEMENT TEST",
			)
		);
		$this->assertRecordExists("balances", array(
			'id' => 3,
			'amount' => "5.000000",
			'day' => mktime(0, 0, 0, 11, 4, 2013)
			)
		);

		$balance = new Balance();
		$this->assertFalse($balance->load(array('id' => 5)));
		
		$data = new Import_Balances($name);
		$data->banks_id = 1;
		$data->import_as_ofx();
		
		$this->assertFalse($balance->load(array('id' => 4)));
		$this->assertFalse($balance->load(array('id' => 5)));
	
		$this->tearDown();
	}

	function test_import_as_qif() {
		require dirname(__FILE__)."/data/import.qif.php";
		$name = tempnam('/tmp', 'qif');
		$handle = fopen($name, 'w+');
		fwrite($handle, $content);
		fclose($handle);
		
		$data = new Import_Balances($name);
		$data->banks_id = 1;
		$data->import_as_qif();
		
		
		$this->assertRecordExists("balances", array(
			'id' => 1,
			'amount' => "500.000000",
			'day' => mktime(0, 0, 0, 10, 29, 2013),
			'name' => "VIR NO PARKING REFERENCE NON TRANSMISE",
			)
		);
		$this->assertRecordExists("balances", array(
			'id' => 2,
			'amount' => "-10.600000",
			'day' => mktime(0, 0, 0, 11, 5, 2013),
			'name' => "SNCF CARTE 21542154 PAIEMENT CB 0411 AMIENS",
			)
		);
		$this->assertRecordExists("balances", array(
			'id' => 3,
			'amount' => "-44.00000",
			'day' => mktime(0, 0, 0, 11, 6, 2013),
			'name' => "RETRAIT DAB 0411 BEAUVAIS AERO CRCA BRIE PICARD CARTE 21542154",
			)
		);

		$balance = new Balance();
		$this->assertFalse($balance->load(array('id' => 5 )));
		
		$data = new Import_Balances($name);
		$data->banks_id = 1;
		$data->import_as_qif();
		
		$this->assertFalse($balance->load(array('id' => 4 )));
		$this->assertFalse($balance->load(array('id' => 5 )));
	
		$this->tearDown();
	}
}
