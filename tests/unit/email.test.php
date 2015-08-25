<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_email extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables();
	}	

	function test_prepare_email__avec_corps_modifie() {
		$content['Body'] = "un corps de TEXTE";
		$replace['TEXTE'] = "texte";
		$prepared_email = prepare_email($content, $replace);
		$this->assertEqual($prepared_email['Body'], "un corps de texte");
	}

	function test_prepare_email__avec_un_autre_nom_d_envoi() {
		$content['FromName'] = "Perrick";
		$content['To'] = "perrick@noparking.net";
		$content['ToName'] = "Perrick";
		$content['Subject'] = "un sujet";
		$content['Body'] = "un corps de texte";
		$prepared_email['From'] = $GLOBALS['param']['email_from'];
		$prepared_email['FromName'] = "Perrick";
		$prepared_email['To'] = "perrick@noparking.net";
		$prepared_email['ToName'] = "Perrick";
		$prepared_email['Subject'] = "un sujet";
		$prepared_email['Body'] = "un corps de texte";
		$this->assertEqual(prepare_email($content), $prepared_email);
	}

	function test_prepare_email__avec_une_autre_adresse_d_envoi() {
		$content['From'] = "from@noparking.net";
		$content['To'] = "perrick@noparking.net";
		$content['ToName'] = "Perrick";
		$content['Subject'] = "un sujet";
		$content['Body'] = "un corps de texte";
		$prepared_email['From'] = "from@noparking.net";
		$prepared_email['FromName'] = $GLOBALS['config']['name'];
		$prepared_email['To'] = "perrick@noparking.net";
		$prepared_email['ToName'] = "Perrick";
		$prepared_email['Subject'] = "un sujet";
		$prepared_email['Body'] = "un corps de texte";
		$this->assertEqual(prepare_email($content), $prepared_email);
	}

	function test_prepare_email__avec_une_adresse_d_envoi_erronee() {
		$content['From'] = "from-noparking.net";
		$content['To'] = "perrick@noparking.net";
		$content['ToName'] = "Perrick";
		$content['Subject'] = "un sujet";
		$content['Body'] = "un corps de texte";
		$prepared_email['From'] = $GLOBALS['param']['email_from'];
		$prepared_email['FromName'] = $GLOBALS['config']['name'];
		$prepared_email['To'] = "perrick@noparking.net";
		$prepared_email['ToName'] = "Perrick";
		$prepared_email['Subject'] = "un sujet";
		$prepared_email['Body'] = "un corps de texte";
		$this->assertEqual(prepare_email($content), $prepared_email);
	}

	function test_prepare_email() {
		$content['To'] = "perrick@noparking.net";
		$content['ToName'] = "Perrick";
		$content['Subject'] = "un sujet";
		$content['Body'] = "un corps de texte";
		$prepared_email['From'] = $GLOBALS['param']['email_from'];
		$prepared_email['FromName'] = $GLOBALS['config']['name'];
		$prepared_email['To'] = "perrick@noparking.net";
		$prepared_email['ToName'] = "Perrick";
		$prepared_email['Subject'] = "un sujet";
		$prepared_email['Body'] = "un corps de texte";
		$this->assertEqual(prepare_email($content), $prepared_email);
	}
}
