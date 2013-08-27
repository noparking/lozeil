<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Import extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"writings",
			"banks"
		);
	}
	
	function test_import_cic() {
		$name = tempnam('/tmp', 'csv');
		
		$mydata =array(
			array("Date d'opération","Date de valeur","Débit","Crédit","Libellé","Solde"),
			array("02/07/2013", "01/07/2013", "", "152,20", "test de libellé", "1252,20"),
			array("05/07/2013", "04/07/2013", "-120,50", "", "test de libellé 2", "1300,20"),
			array("05/07/2013", "04/07/2013", "-120,50", "", "", "1300,20")
			);
		
		$handle = fopen($name, 'w');
		
		foreach($mydata as $data) {
			fputcsv($handle, $data, ";");
		}
		
		$import = new Import();
		$file['tmp_name'] = $name;
		$_POST['bank_id'] = 1;
		$import->import_cic($file);
		
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 1,
				'amount_inc_vat' => "152.2000000",
				'bank_id' => 1,
				'comment' => "test de libellé",
				'delay' => mktime(0, 0, 0, 7, 1, 2013),
				'unique_key' => hash('md5', mktime(0, 0, 0, 7, 1, 2013)."test de libellé"."1"."152.2")
			)
		);
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 2,
				'amount_inc_vat' => "-120.5000000",
				'bank_id' => 1,
				'comment' => "test de libellé 2",
				'delay' => mktime(0, 0, 0, 7, 4, 2013),
				'unique_key' => hash('md5', mktime(0, 0, 0, 7, 4, 2013)."test de libellé 2"."1"."-120.5")
			)
		);
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 3,
				'amount_inc_vat' => "-120.5000000",
				'bank_id' => 1,
				'comment' => "",
				'delay' => mktime(0, 0, 0, 7, 4, 2013),
				'unique_key' => hash('md5', mktime(0, 0, 0, 7, 4, 2013)."1"."-120.5")
			)
		);
		$import->import_cic($file);
		
		fclose($handle);
		unlink($name);
		$writings = new Writings();
		$writings->select();
		$unique_keys = $writings->get_unique_key_in_array();
		$this->assertTrue(array_unique($unique_keys) == $unique_keys);
		$this->assertTrue(count($unique_keys) === 3);
		$this->truncateTable("writings");
	}
	
	
	function test_import_coop() {
		$name = tempnam('/tmp', 'csv');
		
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
		
		$handle = fopen($name, 'w');
		
		foreach($mydata as $data) {
			fputcsv($handle, $data, ";");
		}
		
		$import = new Import();
		$file['tmp_name'] = $name;
		$_POST['bank_id'] = 2;
		$import->import_coop($file);
		fclose($handle);
		unlink($name);
		
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 1,
				'amount_inc_vat' => "-152.200000",
				'bank_id' => 2,
				'comment' => "libellé 1",
				'delay' => mktime(0, 0, 0, 7, 2, 2013),
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
"
				)
		);
		
		$this->assertRecordExists(
			"writings",
			array(
				'id' => 2,
				'amount_inc_vat' => "152.200000",
				'bank_id' => 2,
				'delay' => mktime(0, 0, 0, 7, 3, 2013),
				)
		);
		
		$writings = new Writings();
		$writings->select();
		$unique_keys = $writings->get_unique_key_in_array();
		$this->assertTrue(array_unique($unique_keys) == $unique_keys);
		$this->assertTrue(count($unique_keys) === 2);
		$this->truncateTable("writings");
	}
	
	function test_form_import() {
		$bank = new Bank();
		$bank->name = "Bank 1";
		$bank->save();
		
		$bank2 = new Bank();
		$bank2->name = "Bank 2";
		$bank2->save();
		
		$import = new Import();
		$form_import = $import->form_import("label");
		$this->assertPattern("/class=\"import\"/", $form_import);
		$this->assertPattern("/label/", $form_import);
		$this->assertPattern("/import_writings/", $form_import);
		$this->assertPattern("/input_file/", $form_import);
		$this->assertPattern("/bank_id/", $form_import);
		$this->assertPattern("/import_submit/", $form_import);
		$this->assertPattern("/Bank 1/", $form_import);
		$this->assertPattern("/Bank 2/", $form_import);
		
		$this->truncateTable("banks");
	}
}
