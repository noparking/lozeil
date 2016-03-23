<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2016 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Import_Data extends TableTestCase {
	function __construct() {
		parent::__construct();
	}
	
	function test_is_xlsx() {
		$import = new Import_Data("temp.123", "temp.xlsx", "");
		$this->assertTrue($import->is_xlsx());
		$import = new Import_Data("temp.123", "temp.QIF", "");
		$this->assertFalse($import->is_xlsx());
	}

	function test_is_slk() {
		$import = new Import_Data("temp.123", "temp.slk", "");
		$this->assertTrue($import->is_slk());
		$import = new Import_Data("temp.123", "temp.QIF", "");
		$this->assertFalse($import->is_slk());
	}
	
	function test_is_qif() {
		$import = new Import_Data("temp.123", "temp.QIF", "");
		$this->assertTrue($import->is_qif());
		$import = new Import_Data("temp.123", "temp.csv", "");
		$this->assertFalse($import->is_qif());
	}
	
	function test_is_ofx() {
		$import = new Import_Data("temp.123", "temp.OFX", "");
		$this->assertTrue($import->is_ofx());
		$import = new Import_Data("temp.123", "temp.csv", "");
		$this->assertFalse($import->is_ofx());
	}
	
	function test_is_csv() {
		$import = new Import_Data("temp.123", "temp.CSV", "");
		$this->assertTrue($import->is_csv());
		$import = new Import_Data("temp.123", "temp.ofx", "");
		$this->assertFalse($import->is_csv());
	}
	
	function test_is_line_koala() {
		$gooddata = array("27/03/2014",1,"ipsum",-5487);
		$gooddata2 = array("01/11/2015",3564,"ipsum",7);
		$baddata = array("01:11:2015",3564,"ipsum",7);
		$baddata2 = array("01/11/2015","pasentier","ipsum",7);
		$baddata3 = array("01/11/2015",3564,"ipsum","pasfloat");
		$import = new Import_Data();
		$this->assertTrue($import->is_line_koala($gooddata));
		$this->assertTrue($import->is_line_koala($gooddata2));
		$this->assertFalse($import->is_line_koala($baddata));
		$this->assertFalse($import->is_line_koala($baddata2));
		$this->assertFalse($import->is_line_koala($baddata3));
	}
	
	function test_is_line_paybox() {
		$import = new Import_Data();
		$mydata = array (
				0 => '967003686',
				1 => '261556',
				2 => '5135830',
				3 => '001',
				4 => 'OPENTIME.FR',
				5 => '507355493',
				6 => '13/08/2013',
				7 => '966899879',
				8 => '1024997136',
				9 => '12/08/2013',
				10 => '0917',
				11 => '12/08/2013',
				12 => 'opentime.fr',
				13 => 'PPPS->AutoDebitAbonne',
				14 => 'Autorisation',
				15 => 'Paybox Direct Plus',
				16 => '652256', 
				17 => '26910',
				18 => '978',
				19 => '',
				20 => '',
				21 => 'FRA',
				22 => '',
				23 => 'CB-Visa',
				24 => '',
				25 => '',
				26 => '',
				27 => 'aaaaaa',
				28 => 'T�l�collect�',
				29 => '',
				30 => '', 
				31 => ''
			);
		$mydata2 = array (
				0 => '967003686',
				1 => '261556',
				2 => '5135830',
				3 => '001',
				4 => 'OPENTIME.FR',
				5 => '507355493',
				6 => '13.08.2013',
				7 => '966899879',
				8 => '1024997136',
				9 => '12/08/2013',
				10 => '0917',
				11 => '12/08/2013',
				12 => 'opentime.fr',
				13 => 'PPPS->AutoDebitAbonne',
				14 => 'Autorisation',
				15 => 'Paybox Direct Plus',
				16 => '652256', 
				17 => '26910',
				18 => '978',
				19 => '',
				20 => '',
				21 => 'FRA',
				22 => '',
				23 => 'CB-Visa',
				24 => '',
				25 => '',
				26 => '',
				27 => 'aaaaaa',
				28 => 'T�l�collect�',
				29 => '',
				30 => '', 
				31 => ''
			);
		$this->assertTrue($import->is_line_paybox($mydata));
		$this->assertFalse($import->is_line_paybox($mydata2));
	}
	
	function test_is_line_cic() {
		$import = new Import_Data();
		$mydata = array("01/07/2013", "01/07/2013", "", "152,20", "test de libellé", "123456");
		$mydata2 = array("01/07/2013", "01/07/2013", "-251", "152,20", "test de libellé", "123456");
		$mydata3 = array("01/07/2013", "01/07/2013", "", "", "test de libellé", "123456");
		$mydata4 = array("01/07/2013", "01/07/2013", "-25", "", "test de libellé", "123456");
		$this->assertTrue($import->is_line_cic($mydata));
		$this->assertFalse($import->is_line_cic($mydata2));
		$this->assertFalse($import->is_line_cic($mydata3));
		$this->assertTrue($import->is_line_cic($mydata4));
	}
	
	function test_is_line_coop() {
		$import = new Import_Data();
		$mydata = array("27/08/2013", "" , "", "12.52","DEBIT");
		$mydata2 = array("1275212000", "" , "", "12.52","DEBIT");
		$mydata3 = array("27/08/2013", "" , "", "12.52","");
		$this->assertTrue($import->is_line_coop($mydata));
		$this->assertFalse($import->is_line_coop($mydata2));
		$this->assertFalse($import->is_line_coop($mydata3));
	}
	
	function test_is_paybox() {
		$mydata = array (
			0 => array (
				0 => 'RemittancePaybox',
				1 => 'Bank',
				2 => 'Site',
				3 => 'Rank',
				4 => 'ShopName',
				5 => 'IdPaybox',
				6 => 'Date',
				7 => 'TransactionId',
				8 => 'IdAppel',
				9 => 'DateOfIssue',
				10 => 'HourOfIssue',
				11 => 'DateOfExpiry',
				12 => 'Reference',
				13 => 'Origin',
				14 => 'Type',
				15 => 'Canal',
				16 => 'NumberOfAuthorization',
				17 => 'Amount',
				18 => 'Currency',
				19 => 'Entity',
				20 => 'Operator',
				21 => 'Country',
				22 => 'CountryIP',
				23 => 'Payment',
				24 => 'ThreeDSecureStatus',
				25 => 'ThreeDSecureInscription',
				26 => 'ThreeDSecureWarranted',
				27 => 'RefArchive',
				28 => 'Status',
				29 => 'PAN',
				30 => 'IP',
				31 => 'ErrorCode'
			),
			1 => array (
				0 => '967003686',
				1 => '261556',
				2 => '5135830',
				3 => '001',
				4 => 'OPENTIME.FR',
				5 => '507355493',
				6 => '13/08/2013',
				7 => '966899879',
				8 => '1024997136',
				9 => '12/08/2013',
				10 => '0917',
				11 => '12/08/2013',
				12 => 'opentime.fr',
				13 => 'PPPS->AutoDebitAbonne',
				14 => 'Autorisation',
				15 => 'Paybox Direct Plus',
				16 => '652256', 
				17 => '26910',
				18 => '978',
				19 => '',
				20 => '',
				21 => 'FRA',
				22 => '',
				23 => 'CB-Visa',
				24 => '',
				25 => '',
				26 => '',
				27 => 'aaaaaa',
				28 => 'T�l�collect�',
				29 => '',
				30 => '', 
				31 => ''
			)
		);
		$data = new Import_Data();
		$row = 0;
		foreach ($mydata as $line) {
			foreach ($line as $key => $value) {
				$data->csv_data[$row][$key] = trim($value);
			}
			$row++;
		}
		$this->assertTrue($data->is_paybox($data->csv_data));
		$data->csv_data[0][6] = "Autre champ";
		$this->assertFalse($data->is_paybox($data->csv_data));
	}
	
	function test_is_cic() {
		$mydata = array(
			array("Date d'opération","Date de valeur","Débit","Crédit","Libellé","Solde"),
			array("02/07/2013", "01/07/2013", "", "152,20", "test de libellé", "1252,20"),
			array("05/07/2013", "04/07/2013", "-120,50", "", "test de libellé 2", "1300,20"),
			array("05/07/2013", "04/07/2013", "-120,50", "", "", "1300,20")
			);
		$data = new Import_Data();
		$row = 0;
		foreach ($mydata as $line) {
			foreach ($line as $key => $value) {
				$data->csv_data[$row][$key] = trim($value);
			}
			$row++;
		}
		$this->assertTrue($data->is_cic($data->csv_data));
		$data->csv_data[0][1] = "Autre champ";
		$this->assertFalse($data->is_cic($data->csv_data));
	}
	
	function test_is_coop() {
		$mydata = array(
			array("Date","Libellé","Libellé complémentaire","Montant","Sens","Numéro de chèque","Référence Interne de l'Opération",
				"Nom de l'émetteur","Identifiant de l'émetteur","Nom du destinataire","Identifiant du destinataire",
				"Nom du tiers débiteur","Identifiant du tiers débiteur","Nom du tiers créancier","Identifiant du tiers créancier",
				"Libellé de Client à Client - Motif","Référence de Client à Client","Référence de la Remise","Référence de la Transaction",
				"Référence Unique du Mandat","Séquence de Présentation"),
			array("02/07/2013", "libellé 1", " libellé complémentaire 1", "152,20", "DEBIT", "Numéro de chèque 1","Référence Interne de l'Opération 1",
				"Nom de l'émetteur 1","Identifiant de l'émetteur 1","Nom du destinataire 1","Identifiant du destinataire 1",
				"Nom du tiers débiteur 1","Identifiant du tiers débiteur 1","Nom du tiers créancier 1","Identifiant du tiers créancier 1",
				"Libellé de Client à Client - Motif 1","Référence de Client à Client 1","Référence de la Remise 1","Référence de la Transaction 1",
				"Référence Unique du Mandat 1","Séquence de Présentation 1"),
			array("03/07/2013", "", "", "152,20", "CREDIT")
			);
		$data = new Import_Data();
		$row = 0;
		foreach ($mydata as $line) {
			foreach ($line as $key => $value) {
				if ($key == 0) {
					$time = explode("/", $value);
					if (isset($time[1]) and $time[2]) {
						$value = mktime(0, 0, 0, $time[1], $time[0], $time[2]);
					}
				}
				if ($key == 3 and $value!= "Montant") {
					$value = to_float($value);
				}
				$data->csv_data[$row][$key] = trim($value);
			}
		  $row++;
		}
		$this->assertTrue($data->is_coop($data->csv_data));
		$data->csv_data[0][0] = "Autre champ";
		$this->assertFalse($data->is_coop($data->csv_data));
	}
}
