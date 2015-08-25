<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_File extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"files"
		);
	}
	
	function test_save_load() {
		$file = new File();
		$file->writings_id = 2;
		$file->hash = "123dsf21v31sdfxc21";
		$file->value = "fichier.pdf";
		$file->save();
		$file_loaded = new File();
		$file_loaded->load(array('id' => 1));
		$this->assertEqual($file_loaded->writings_id, $file->writings_id);
		$this->assertEqual($file_loaded->hash, $file->hash);
		$this->assertEqual($file_loaded->value, $file->value);
		$this->truncateTable("files");
	}
	
	function test_update() {
		$file = new File();
		$file->writings_id = 2;
		$file->hash = "123dsf21v31sdfxc21";
		$file->value = "fichier.pdf";
		$file->save();
		$file_loaded = new File();
		$file_loaded->id = 1;
		$file_loaded->writings_id = 3;
		$file_loaded->hash = "qs1dsfxcv64sfex2";
		$file_loaded->value = "fichier2.pdf";
		$file_loaded->update();
		$file_loaded2 = new File();
		$this->assertTrue($file_loaded2->load(array('id' => 1)));
		$this->assertNotEqual($file_loaded2->writings_id, $file->writings_id);
		$this->assertNotEqual($file_loaded2->hash, $file->hash);
		$this->assertNotEqual($file_loaded2->value, $file->value);
		$this->truncateTable("files");
	}
	
	function test_delete() {
		$file = new File();
		$file->writings_id = 2;
		$file->hash = "123dsf21v31sdfxc21";
		$file->value = "fichier.pdf";
		$file->save();
		$file_loaded = new File();
		$this->assertTrue($file_loaded->load(array('id' => 1 )));
		$file->delete();
		$this->assertFalse($file_loaded->load(array('id' => 1 )));
		$this->truncateTable("files");
	}
	
	function test_save_attachment() {
		$writing = new Writing();
		$writing->save();
		$name = tempnam('/tmp', 'pdf');
		$raw_file = array(
			'table_1' => array(
				'name' => 'pdf',
				'type' => 'application/pdf',
				'tmp_name' => $name,
				'error' => 0,
				'size' => 29850
			)
		);
		$file = new File();
		//$this->assertTrue($file->save_attachment($raw_file));
		
		$raw_file = array(
			'table_1' => array(
				'name' => 'fichier.pdf',
				'type' => 'application/pdf',
				'tmp_name' => $name,
				'error' => 1,
				'size' => 29850
			)
		);
		$this->assertFalse($file->save_attachment($raw_file));
		unlink($name);
		
		$name = tempnam('/tmp', 'pdf');
		$raw_file = array(
			'table_3' => array(
				'name' => 'fichier.pdf',
				'type' => 'application/pdf',
				'tmp_name' => $name,
				'error' => 0,
				'size' => 29850
			)
		);
		$this->assertFalse($file->save_attachment($raw_file));
		unlink($name);
		
		$this->truncateTable("writings");
		$this->truncateTable("files");
	}
}
