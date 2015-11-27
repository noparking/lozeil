<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */
class Bot {
	
	public $directory_cfg = "";
	
	function __construct(db $db = null) {
		$this->db = $db;
		$this->directory_cfg = dirname(__FILE__)."/../cfg";
		$this->dbconfig = new Config_File(dirname(__FILE__)."/../cfg/config.inc.php", "dbconfig");
	}
	
	function setup() {
		echo utf8_ucfirst(__('database configuration'))."\n";
		
		$config_file = new Config_File($this->directory_cfg."/config.inc.php", "dbconfig" );
		$dist_config_file = new Config_File($this->directory_cfg."/config.inc.php.dist", "dbconfig" );
		
		$dbname = $config_file->change_config_value("name", $dist_config_file );
		$dbuser = $config_file->change_config_value("user", $dist_config_file );
		$dbpass = $this->input_hidden(__('password'));
		
		$need_overwrite = $config_file->overwrite( new Config_File($this->directory_cfg."/config.inc.php.dist"));
		if ($need_overwrite) {
			$this->dbconfig->update(array (
					'dbconfig' => array (
							'dbconfig' => array (
									"name" => $dbname,
									"user" => $dbuser,
									"pass" => $dbpass 
							) 
					) 
			) );
		}
		
		$param_file = new Param_File($this->directory_cfg."/param.inc.php" );
		$param_file->overwrite_file(new Param_File($this->directory_cfg."/param.inc.php.dist"));
		
		$load_config = new Config_File(dirname(__FILE__)."/../cfg/config.inc.php", "dbconfig" );
		$load_config->load_at_global_level();
		if ($this->db === null) {
			$this->db = new db();
		}
		$this->reinstall_database();
		
		echo utf8_ucfirst(__('create a default user'))."\n";
		while (empty($username)) {
			$username = $this->input(__('username'));
		}

		$password = $this->input_hidden(__('password'));
		$this->db->query("INSERT INTO ".$GLOBALS['dbconfig']['table_users']." (id, username, password) VALUES (1, '".$username."', password(".$this->db->quote($password)."));");
		$user = new User();
		$user->load(array('username' => $username));
		$user->savemodexpert(1);
		$this->import_accounting_plan();
	}
	
	function reinstall_database() {
		$this->uninstall_database();
		return $this->install_database();
	}
	
	function install_database() {
		if ($this->db === null) {
			$this->db = new db ();
		}
		$queries = array ();
		$tables = new Database_Tables();
		$tables->prepare();
		$tables->install();
		return true;
	}
	
	function uninstall_database() {
		$db = new db();
		$tables = array();
		foreach ($GLOBALS['dbconfig'] as $parameter => $table ) {
			if (substr($parameter, 0, 6 ) == 'table_') {
				$tables[] = $table;
			}
		}
		$db->query ( "DROP TABLE IF EXISTS ".join( ", ", $tables ) );
		return true;
	}
	
	function help() {
		$help = "Methods available within Lozeil:"."\n";
		$ReflectionClass = new ReflectionClass('Bot');
		foreach ( $ReflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method ) {
			if (! in_array($method->getName(), array (
					"help",
					"__construct" 
			) )) {
				$help .= "--".$method->getName ()."\n";
			}
		}
		return $help;
	}
	
	function update() {
		$this->log(__( "Start updating Lozeil" ));
		$this->update_svn();
		$this->update_lozeil();
		$this->log(__("Finish updating Lozeil"));
		return true;
	}
	
	function update_svn() {
		$this->log(__("Start updating SVN"));
		$result = exec("svn up");
		$this->log(__("Finish updating SVN"));
		return $result;
	}
	
	function update_lozeil() {
		$this->log(__("Start updating application"));
		$update = new Update();
		$current = $update->current() + 1;
		$last = $update->last();
		
		for($i = $current; $i <= $last; $i ++) {
			if (method_exists($update, "to_".$i )) {
				$update->{"to_".$i}();
				$update->config("version",$i );
			}
		}
		$this->log (__("Finish updating application"));
	}
	
	function update_plugin() {
		$this->log(__( "Start updating plugin"));
		$update = new Accounts_Update();
		$current = $update->current()+1;
		$last = $update->last();
		
		for($i = $current; $i <= $last; $i ++) {
			if (method_exists($update, "to_".$i)) {
				$update->{"to_" . $i} ();
				$update->config("accounts_version", $i );
			}
		}
		$this->log( __("Finish updating application"));
	}
	
	function demo() {
		$this->reinstall_database();
		$this->log(__( "Start building up demo" ) );
		$this->db->query("INSERT INTO ".$GLOBALS['dbconfig']['table_users']." (id, username, password) VALUES (1, 'admin', password('admin'));" );
		$category = new Category();
		$category->name = 'Opentime.fr';
		$category->vat = 19.6;
		$category->save();
		$category = new Category();
		$category->name = 'Opentime.info';
		$category->vat = 19.6;
		$category->save();
		$category = new Category();
		$category->name = 'Aberlaas';
		$category->vat = 5.5;
		$category->save();
		$category = new Category();
		$category->name = 'Alticcio';
		$category->vat = 19.6;
		$category->save();
		$category = new Category();
		$category->name = 'Salaires';
		$category->vat = 0;
		$category->save();
		$category = new Category();
		$category->name = 'Lozeil';
		$category->vat = 19.6;
		$category->save();
		$category = new Category();
		$category->name = 'Telecom';
		$category->vat = 5.5;
		$category->save();
		
		// Investissement de départ
		$writing = new Writing();
		$category = new Category();
		$category->load( array (
				"id" => 1 
		) );
		$writing->day = mktime( 0, 0, 0, date('m', time()), 1, date('Y', time()) - 1 );
		$writing->number = rand(52, 52445621);
		$writing->vat = $category->vat;
		$writing->amount_inc_vat = - 5251.52;
		$writing->banks_id = 2;
		$writing->save();
		
		// Opentime.fr
		for($i = 0; $i < 24; $i ++) {
			for($j = 0; $j < 3; $j ++) {
				$writing = new Writing();
				$category = new Category();
				$category->load(array (
						"id" => 1 
				) );
				$writing->day = mktime( 0, 0, 0, date('m', time()) + $i, rand(15, 25), date('Y',time()) - 1 );
				$writing->categories_id = 1;
				$writing->number = rand(52,52445621);
				$writing->vat = $category->vat;
				$writing->amount_inc_vat = 925.52*(rand(92, 95)/100);
				$writing->banks_id = 2;
				$writing->save();
			}
		}
		
		// Opentime.info
		for($i = 0; $i < 24; $i = $i + 2) {
			for($j = 0; $j < 3; $j ++) {
				$writing = new Writing ();
				$category = new Category ();
				$category->load ( 2 );
				$writing->day = mktime( 0, 0, 0, date('m',time()) + $i, rand(15,25), date('Y',time()) - 1 );
				$writing->categories_id = 2;
				$writing->number = rand(52,52445621);
				$writing->vat = $category->vat;
				$writing->amount_inc_vat = 1256.25*(rand(92,95)/100);
				$writing->banks_id = 2;
				$writing->save();
			}
		}
		
		// Aberlaas
		for($i = 0; $i < 24; $i ++) {
			$writing = new Writing ();
			$category = new Category ();
			$category->load(3);
			$writing->vat = $category->vat;
			$writing->day = mktime(0, 0, 0, date('m', time() )+$i,rand(1,28),date('Y',time())-1);
			$writing->categories_id = 3;
			$writing->number = rand(52,52445621);
			$writing->amount_inc_vat = 112.23*(rand(92,95)/100);
			$writing->banks_id = 1;
			$writing->save();
			$writing = new Writing();
			$category = new Category();
			$category->load(3);
			$writing->day = mktime(0, 0, 0, date('m',time()) + $i,rand(1,28),date('Y',time())-1);
			$writing->categories_id = 3;
			$writing->number = rand(52, 52445621);
			$writing->vat = $category->vat;
			$writing->amount_inc_vat = - 112.23*(rand(92,95)/100);
			$writing->banks_id = 1;
			$writing->save();
		}
		
		// Alticcio
		for($i = 0; $i < 24; $i ++) {
			for($j = 0; $j < 2; $j ++) {
				$writing = new Writing();
				$category = new Category();
				$category->load(4);
				$writing->vat = $category->vat;
				$writing->number = rand(52,52445621);
				$writing->day = mktime(0, 0, 0,date('m',time() )+$i,rand(1, 28),date('Y',time())-1);
				$writing->categories_id = 4;
				$writing->amount_inc_vat = 505.62*(rand(92,95)/100);
				$writing->banks_id = 2;
				$writing->save();
			}
		}
		
		// Salaires
		for($i = 0; $i < 24; $i ++) {
			for($j = 0; $j < 4; $j ++) {
				$writing = new Writing();
				$category = new Category();
				$category->load(5);
				$writing->vat = $category->vat;
				$writing->day = mktime(0, 0, 0, date('m',time()) + $i, 25,date('Y',time())-1);
				$writing->categories_id = 5;
				$writing->number = rand(52,52445621);
				$writing->amount_inc_vat = -1205.25*(rand(92,95)/100);
				$writing->banks_id = 2;
				$writing->save();
			}
		}
		
		// Telecom
		for($i = 0; $i < 24; $i ++) {
			for($j = 0; $j < 2; $j ++) {
				$writing = new Writing();
				$category = new Category();
				$category->load(7);
				$writing->vat = $category->vat;
				$writing->day = mktime(0, 0, 0, date('m',time())+$i,25,date('Y',time())-1 );
				$writing->categories_id = 7;
				$writing->number = rand(52,52445621);
				$writing->amount_inc_vat = -9.95*(rand(92,95)/100);
				$writing->banks_id = 1;
				$writing->save();
			}
		}
		
		// Embauche
		for($i = 0; $i < 6; $i ++) {
			$writing = new Writing();
			$category = new Category();
			$category->load(5);
			$writing->vat = $category->vat;
			$writing->day = mktime( 0, 0, 0, date('m',time() ) + $i, 25,date('Y',time()));
			$writing->categories_id = 5;
			$writing->number = rand(52,52445621 );
			$writing->amount_inc_vat = -3052.21*(rand(92,95 )/100);
			$writing->banks_id = 2;
			$writing->save();
		}
		
		$this->log(__("Stop building up demo"));
	}
	
	private function log($message) {
		Message::log($message);
		return $this;
	}
	
	private function input($message) {
		fwrite(STDOUT,"$message: ");
		$input = trim(fgets(STDIN));
		return $input;
	}
	
	private function input_hidden($message) {
		fwrite(STDOUT,"$message: ");
		$oldStyle = shell_exec('stty -g');
		shell_exec('stty -echo');
		$password = rtrim(fgets(STDIN),"\n");
		echo "\n";
		shell_exec('stty '.$oldStyle);
		return $password;
	}
	
	function import_accounting_plan() {
		require_once dirname(__FILE__)."/../lang/fr_FR.accountingcodes.php";
		foreach ($accountingcodes as $number => $name) {
			$code = new Accounting_Code();
			$code->number = $number;
			$code->name = $name;
			$code->save();
		}
	}
	
	function test_extensions() {
		$comment = "";
		
		$extensions = array (
				"bcmath",
				"calendar",
				"mcrypt" 
		);
		foreach ( $extensions as $extension ) {
			if (extension_loaded ( $extension )) {
				$comment .= $extension." - ".__ ( 'ok' )."\n";
			} else {
				$comment .= $extension." - ".__('to be modified' )."\n";
			}
		}
		
		$settings = array (
				"get_magic_quotes_gpc" => false 
		);
		foreach ( $settings as $setting => $value ) {
			if (call_user_func ( $setting ) == $value) {
				$comment .= $setting." - ".__('ok')."\n";
			} else {
				$comment .= $setting." - ".__('to be modified')."\n";
			}
		}
		
		echo $comment;
		
		return true;
	}
}
