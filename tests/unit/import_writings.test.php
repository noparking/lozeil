<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2015 */

require_once dirname(__FILE__)."/../inc/require.inc.php";
require_once dirname(__FILE__)."/../../inc/office/excel/PHPExcel.php";

class tests_Import_Writings extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"banks",
			"categories",
			"bayesianelements",
			"writings",
			"sources",
			"writingsimported"
		);
	}

	function tearDown() {
		$this->truncateTable("writings");
		$this->truncateTable("banks");
		$this->truncateTable("categories");
		$this->truncateTable("bayesianelements");
		$this->truncateTable("sources");
		$this->truncateTable("writingsimported");
	}

	function test_import_as_paybox() {
		require dirname(__FILE__)."/data/import.paybox.php";
		$name = tempnam('/tmp', 'csv');
		$handle = fopen($name, 'w+');
		
		foreach($mydata as $data) {
			fputcsv($handle, $data, ";");
		}
		
		$data = new Import_Writings($name);
		$data->sources_id = 1;
		$data->prepare_csv_data();
		$data->import_as_paybox();
		
		$this->assertRecordExists("writings", array(
				'id' => 1,
				'amount_inc_vat' => "269.1000000",
				'sources_id' => 1,
				'comment' => "opentime.fr PPPS->AutoDebitAbonne",
				'information' => "ShopName : OPENTIME.FR
TransactionId : 966899879
Canal : Paybox Direct Plus
Country : FRA
Payment : CB-Visa
Status : Telecollecte
IP : 109.190.127.105
",
			)
		);
		
		$this->assertRecordExists("writings", array(
				'id' => 2,
				'amount_inc_vat' => "240.000000",
				'sources_id' => 1,
				'comment' => "lozeil adrien.delannoy@noparking.net",
				'information' => "ShopName : LOZEIL
TransactionId : 966899879
Canal : Paybox Direct Plus
Country : FRA
Payment : CB-Mastercard
Status : Telecollecte
",
			)
		);

		$data = new Import_Writings($name);
		$data->sources_id = 1;
		$data->prepare_csv_data();
		$data->import_as_cic();
		
		fclose($handle);
		unlink($name);
		$writing = new Writing();
		$this->assertFalse($writing->load(array('id' => 5 )));
	}
	
	function test_import_as_cic() {
		require dirname(__FILE__)."/data/import.cic.php";
		$name = tempnam('/tmp', 'csv');
		$handle = fopen($name, 'w+');
		
		foreach($mydata as $data) {
			fputcsv($handle, $data, ";");
		}
		
		$data = new Import_Writings($name);
		$data->banks_id = 1;
		$data->prepare_csv_data();
		$data->import_as_cic();
		
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 1,
				'amount_inc_vat' => "152.2000000",
				'banks_id' => 1,
				'comment' => "test de libellé",
				'day' => mktime(0, 0, 0, 7, 1, 2013),
			)
		);
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 2,
				'amount_inc_vat' => "-120.5000000",
				'banks_id' => 1,
				'comment' => "test de libellé 2",
				'day' => mktime(0, 0, 0, 7, 4, 2013),
			)
		);
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 3,
				'amount_inc_vat' => "-120.5000000",
				'banks_id' => 1,
				'comment' => "",
				'day' => mktime(0, 0, 0, 7, 4, 2013),
			)
		);
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 4,
				'amount_inc_vat' => "-120.5000000",
				'banks_id' => 1,
				'comment' => "",
				'day' => mktime(0, 0, 0, 7, 4, 2013),
			)
		);

		$data = new Import_Writings($name);
		$data->banks_id = 1;
		$data->prepare_csv_data();
		$data->import_as_cic();
		
		fclose($handle);
		unlink($name);
		$writing = new Writing();
		$this->assertFalse($writing->load(array('id' => 5 )));
	}
	
	function test_import_as_coop() {
		require dirname(__FILE__)."/data/import.coop.php";
		$name = tempnam('/tmp', 'csv');
		$handle = fopen($name, 'w+');
		
		foreach($mydata as $data) {
			fputcsv($handle, $data, ";");
		}
		
		$data = new Import_Writings($name);
		$data->banks_id = 2;
		$data->prepare_csv_data();
		$data->import_as_coop();
		
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 1,
				'amount_inc_vat' => "-152.200000",
				'banks_id' => 2,
				'comment' => "libellé 1",
				'day' => mktime(0, 0, 0, 7, 2, 2013),
				'information' => "LibellÃ© complÃ©mentaire : libellÃ© complÃ©mentaire 1
NumÃ©ro de chÃ¨que : NumÃ©ro de chÃ¨que 1
RÃ©fÃ©rence Interne de l'OpÃ©ration : RÃ©fÃ©rence Interne de l'OpÃ©ration 1
Nom de l'Ã©metteur : Nom de l'Ã©metteur 1
Identifiant de l'Ã©metteur : Identifiant de l'Ã©metteur 1
Nom du destinataire : Nom du destinataire 1
Identifiant du destinataire : Identifiant du destinataire 1
Nom du tiers dÃ©biteur : Nom du tiers dÃ©biteur 1
Identifiant du tiers dÃ©biteur : Identifiant du tiers dÃ©biteur 1
Nom du tiers crÃ©ancier : Nom du tiers crÃ©ancier 1
Identifiant du tiers crÃ©ancier : Identifiant du tiers crÃ©ancier 1
LibellÃ© de Client Ã  Client - Motif : LibellÃ© de Client Ã  Client - Motif 1
RÃ©fÃ©rence de Client Ã  Client : RÃ©fÃ©rence de Client Ã  Client 1
RÃ©fÃ©rence de la Remise : RÃ©fÃ©rence de la Remise 1
RÃ©fÃ©rence de la Transaction : RÃ©fÃ©rence de la Transaction 1
RÃ©fÃ©rence Unique du Mandat : RÃ©fÃ©rence Unique du Mandat 1
SÃ©quence de PrÃ©sentation : SÃ©quence de PrÃ©sentation 1
",
				)
		);
		
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 2,
				'amount_inc_vat' => "152.200000",
				'banks_id' => 2,
				'day' => mktime(0, 0, 0, 7, 3, 2013),
			)
		);
		
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 3,
				'amount_inc_vat' => "152.200000",
				'banks_id' => 2,
				'day' => mktime(0, 0, 0, 7, 3, 2013),
			)
		);
		
		$data = new Import_Writings($name);
		$data->banks_id = 2;
		$data->prepare_csv_data();
		$data->import_as_coop();
		fclose($handle);
		unlink($name);
		$writing = new Writing();
		$this->assertFalse($writing->load(array('id' => 4 )));
	}
	
	function test_import_as_ofx() {
		require dirname(__FILE__)."/data/import.ofx.php";
		$name = tempnam('/tmp', 'ofx');
		$handle = fopen($name, 'w+');
		fwrite($handle, $content);
		fclose($handle);
		
		$data = new Import_Writings($name);
		$data->banks_id = 1;
		$data->import_as_ofx();
		
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 1,
				'amount_inc_vat' => "-35.000000",
				'amount_excl_vat' => "-35.000000",
				'vat' => "0",
				'banks_id' => 1,
				'day' => mktime(0, 0, 0, 10, 4, 2013),
				'comment' => "CARTE TEST",
				'information' => "MEMO TEST",
			)
		);
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 2,
				'amount_inc_vat' => "-10.500000",
				'banks_id' => 1,
				'day' => mktime(0, 0, 0, 12, 4, 2013),
				'comment' => "ABONNEMENT TEST",
				'information' => "",
			)
		);
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 3,
				'amount_inc_vat' => "7.500000",
				'banks_id' => 1,
				'day' => mktime(0, 0, 0, 10, 4, 2013),
				'comment' => "",
				'information' => "",
			)
		);
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 4,
				'amount_inc_vat' => "5.00000",
				'banks_id' => 1,
				'day' => mktime(0, 0, 0, 11, 4, 2013),
				'comment' => "",
				'information' => "",
			)
		);

		$writing = new Writing();
		$this->assertFalse($writing->load(array('id' => 5 )));
		
		$data = new Import_Writings($name);
		$data->banks_id = 1;
		$data->import_as_ofx();
		
		$this->assertTrue($writing->load(array('id' => 4 )));
		$this->assertFalse($writing->load(array('id' => 5 )));
	}
	
	function test_import_as_xlsx() {
		$this->backupTables("writings", "writingsimported");

		$file = dirname(__FILE__)."/data/import.xlsx";
		$importer = new Import_Writings($file,$file);
		$importer->import();
		$this->assertTrue(file_exists($file));
		$this->assertRecordExists("writings", array('banks_id' => 0));
		$this->assertRecordExists("writings", array('comment' => "CAPITAL", 'amount_inc_vat' => 155000));
		$this->assertRecordExists("writings", array('comment' => "LOGICIEL", 'amount_inc_vat' => "-59259.13"));
		$source = new Source();
		$source->load(array("name" => "koala"));
		$this->assertTrue($source->id > 0);
		$this->assertRecordExists("writings", array('sources_id' => $source->id));
	}

	function test_import_as_qif() {
		require dirname(__FILE__)."/data/import.qif.php";
		$name = tempnam('/tmp', 'qif');
		$handle = fopen($name, 'w+');
		fwrite($handle, $content);
		fclose($handle);
		
		$data = new Import_Writings($name);
		$data->banks_id = 1;
		$data->import_as_qif();
		
		
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 1,
				'amount_inc_vat' => "500.000000",
				'amount_excl_vat' => "500.000000",
				'vat' => "0",
				'banks_id' => 1,
				'day' => mktime(0, 0, 0, 10, 29, 2013),
				'comment' => "VIR NO PARKING REFERENCE NON TRANSMISE",
			)
		);
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 2,
				'amount_inc_vat' => "-10.600000",
				'banks_id' => 1,
				'day' => mktime(0, 0, 0, 11, 5, 2013),
				'comment' => "SNCF CARTE 21542154 PAIEMENT CB 0411 AMIENS",
			)
		);
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 3,
				'amount_inc_vat' => "-24.00000",
				'banks_id' => 1,
				'day' => mktime(0, 0, 0, 11, 6, 2013),
				'comment' => "FLIB TRAVEL INTE CARTE 21542154 PAIEMENT CB 0511 LU BASCHARAGE",
			)
		);
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 4,
				'amount_inc_vat' => "-20.00000",
				'banks_id' => 1,
				'day' => mktime(0, 0, 0, 11, 6, 2013),
				'comment' => "RETRAIT DAB 0411 BEAUVAIS AERO CRCA BRIE PICARD CARTE 21542154",
				'information' => "AUTRE CHAMPS INCONNU
ENCORE UN CHAMP INCONNU
",
			)
		);

		$writing = new Writing();
		$this->assertFalse($writing->load(array('id' => 5 )));
		
		$data = new Import_Writings($name);
		$data->banks_id = 1;
		$data->import_as_qif();
		
		$this->assertTrue($writing->load(array('id' => 4 )));
		$this->assertFalse($writing->load(array('id' => 5 )));
	}
}
