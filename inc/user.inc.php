<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

class User extends Record  {
	public $id = 0;
	public $name = "";
	public $username = "";
	public $password = "";
	public $email = "";
	public $timestamp = 0;

	protected $db = null;
	protected $options = array();

	function __construct($user_id = 0, $db = null) {
		$this->id = (int)$user_id;

		if ($db === null) {
			$db = new db();
		}

		$this->db = $db;
	}
	
	function db($db) {
		if ($db instanceof db) {
			$this->db = $db;
		}
	}
	
	function options() {
		return $this->options;
	}
	
	function is_editing($name) {
		if (count($this->options) == 0) {
			$this->load_in_cascade(array('id' => $this->id));
		}
		if (isset($this->options[$name])) {
			if ($this->options[$name]->value == 1) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	function load(array $key = array(), $table = "users", $columns = null) {
		return parent::load($key, $table, $columns);
	}
	
	function load_in_cascade(array $key = array(), $table = "users", $columns = null) {
		$result = parent::load($key, $table, $columns);
		$options = new User_Options();
		$options->user_id = $this->id;
		$options->select();
		foreach ($options as $option) {
			$this->options[$option->name] = $option;
		}
		return $result;
	}
	
	function fill_in_cascade($hash) {
		return parent::fill($hash);
	}
	
	function link_to_edit() {
		if ((int)$this->id > 0) {
			return Html_Tag::a(link_content("content=user.edit.php&id=".$this->id), __("Edit user %s", array($this->name)), array('class' => "ajax edit"));
		} else {
			return Html_Tag::a(link_content("content=user.edit.php&id"), __("Add new user"), array('class' => "ajax edit"));
		}
	}

	function link_to_delete() {
		if ((int)$this->id > 0) {
			return Html_Tag::a(link_content("content=user.delete.php&id=".$this->id), __("Delete"), array('class' => "ajax delete"));
		} else {
			return "";
		}
	}

	function save() {
		if (is_numeric($this->id) and $this->id != 0) {
			$this->id = $this->update();

		} else {
			$this->id = $this->insert();
		}

		return $this->id;
	}
	
	function save_in_cascade() {
		$result = $this->save();
		$this->save_options();
		return $result;
	}
	
	function save_options() {
		foreach ($this->options as $name => $value) {
			$option = new User_Option();
			if ($option->load(array('user_id' => $this->id, 'name' => $name))) {
				if ($value === null) {
					$option->delete();
				} else {
					$option->value = $value;
					$option->save();
				}
			} else {
				$option->user_id = $this->id;
				$option->name = $name;
				$option->value = $value;
				$option->save();
			}
		}
	}
	
	function insert() {
		$query = "INSERT INTO ".$this->db->config['table_users']."
			SET name = ".$this->db->quote($this->name).",
			timestamp = ".time().",
			username = ".$this->db->quote($this->username).", ";
		if (isset($this->password) and !empty($this->password)) {
			$query .= " password = ".$GLOBALS['config']['mysql_password']."(".$this->db->quote($this->password)."), ";
		}
		$query .= " email = ".$this->db->quote($this->email);
				
		$result = $this->db->query_with_id($query);
		$this->id = $result[2];
		$this->db->status($result[1], "i", __('user'));

		return $this->id;
	}
	
	function update() {
		$query = "UPDATE ".$this->db->config['table_users'].
		" SET name = ".$this->db->quote($this->name).",
		username = ".$this->db->quote($this->username).", ";
		if (isset($this->password) and !empty($this->password)) {
			$query .= " password = ".$GLOBALS['config']['mysql_password']."(".$this->db->quote($this->password)."), ";
		}
		$query .=" email = ".$this->db->quote($this->email).",
		timestamp = ".time()."
		WHERE id = ".(int)$this->id;
		
		$result = $this->db->query($query);
		
		$this->db->status($result[1], "u", __('user'));

		return $this->id;
	}

	function delete() {
		$result = $this->db->query("
			DELETE FROM ".$this->db->config['table_users']."
			WHERE id = ".(int)$this->id
		);
		$this->db->status($result[1], "d", __('user'));
		return true;
	}

	function clean($variables) {
		$cleaned = array();

		if (isset($variables['name'])) {
			$cleaned['name'] = strip_tags($variables['name']);
			$cleaned['name'] = trim(preg_replace('/\s+/', ' ', $cleaned['name']));			
		}

		if (isset($variables['username'])) {
			$cleaned['username'] = strip_tags($variables['username']);
			$cleaned['username'] = trim(preg_replace('/\s+/', ' ', $cleaned['username']));
		}

		if (isset($variables['email'])) {
			$cleaned['email'] = strip_tags($variables['email']);
			$cleaned['email'] = trim(preg_replace('/\s+/', ' ', $cleaned['email']));
		}

		return $cleaned;
	}
	
	function clean_in_cascade($variables) {
		$cleaned = $this->clean($variables);
		if (isset($variables['options']) and is_array($variables['options'])) {
			foreach ($variables['options'] as $option => $option_value) {
				$cleaned['options'][$option] = $option_value;
			}
		}
		return $cleaned;
	}

	function password_request() {
		if ($this->email) {
			$time = time();
			$token = md5($time.$this->username.$this->password);
			$this->db->query("
				INSERT INTO ".$db->config['table_passwordrequests']."
				(user_id, timestamp, token, completed)
				VALUES (".(int)$this->id.", ".(int)$time.", ".$db->quote($token).", 0)"
			);
			$url = $GLOBALS['config']['root_url']."/index.php?content=passwordrequest.php&token=".$token;
			$emails = array(
				array(
					'To' => $this->email,
					'ToName' => $this->name,
					'From' => $GLOBALS['param']['email_from'],
					'FromName' => $GLOBALS['config']['name'],
					'Subject' => sprintf($GLOBALS['array_email']['password_request'][0], $this->username),
					'Body' => sprintf($GLOBALS['array_email']['password_request'][1], $this->username, $url),
				),
			);
			email_send($emails);
			return true;
		}
		return false;
	}
	
	function password_reset($token) {
		$db = new db();
		$result = $db->query("
			SELECT id, user_id, timestamp
			FROM ".$db->config['table_passwordrequests']."
			WHERE token = ".$db->quote($token)."
			AND completed = 0
			LIMIT 0, 1"
		);

		if ($result[1]) {
			$row = $db->fetch_array($result[0]);

			if ($row['timestamp'] > strtotime("-1 hour")) {
				$this->load(array('id' => $row['user_id']));
				$this->password = substr(md5(time().$token), 0, 8);
				$this->save();
				$db->query("
					UPDATE ".$db->config['table_passwordrequests']."
					SET completed = 1
					WHERE id = ".(int)$row['id']
				);

				$emails = array(
					array(
						'To' => $this->email,
						'ToName' => $this->name,
						'From' => $GLOBALS['param']['email_from'],
						'FromName' => $GLOBALS['config']['name'],
						'Subject' => sprintf($GLOBALS['array_email']['new_password'][0], $this->username),
						'Body' => sprintf($GLOBALS['array_email']['new_password'][1], $this->username, $this->password),
					),
				);

				email_send($emails);

				return true;
			}
		}

		return false;
	}
	
	function defaultpage($context = "") {
		if (empty($this->defaultpage)) {
			$this->defaultpage = "writings.php";
		}

		return $this->defaultpage;
	}
	
	function ask_before_delete() {
		if ((int)$this->id > 0) {
			$id = new Html_Input("user[id]", (int)$this->id, "hidden");
			$delete = new Html_Input("submit", __('delete'), "submit");
			
			$list = array(
				'submit' => array(
					'class' => "itemsform-submit",
					'value' => $delete->input(),
				),
			);

			$form = "<h3>".__("Delete user %s", array($this->name))."</h3>";
			$form .= "<form method=\"post\" action=\"\">";
			$form .= $id->input_hidden();
			$items = new Html_List(array('leaves' => $list, 'class' => "itemsform"));
			$form .= $items->show();
			$form .= "</form>";
			
			return $form;
		} else {
			return false;
		}
	}

	function edit() {
		$id = new Html_Input("user[id]", (int)$this->id, "hidden");
		$name = new Html_Input("user[name]", $this->name);
		$username = new Html_Input("user[username]", $this->username);
		$password = new Html_Input("user[password]", "", "password");
		$email = new Html_Input("user[email]", $this->email);
		$save = new Html_Input("submit", __('save'), "submit");
		
		$list = array(
			'name' => array(
				'class' => "itemsform-head itemsform-bold clearfix",
				'value' => $name->item(__("name")),
			),
			'username' => array(
				'class' => "itemsform-head itemsform-bold itemsform-head-bottom clearfix",
				'value' => $username->item(__("username")),
			),
			'password' => array(
				'class' => "clearfix",
				'value' => $password->item(__("password")),
			),
			'email' => array(
				'class' => "clearfix",
				'value' => $email->item(__("email")),
			),
		);
		
		$list['options_writings'] = array(
			'class' => "clearfix",
			'value' => __("Show columns within the writings view"),
		);
		$options = array(
			'accountingcodes_id' => __("accounting code"),
			'categories_id' => __("category"),
			'sources_id' => __("source"),
			'banks_id' => __("bank"),
			'number' => __("piece nb"),
			'vat' => __("VAT"),
		);
		foreach ($options as $option => $option_name) {
			$select = new Html_Select("user[options][".$option."]", array('--' => "--", '1' => __("yes"), '0' => __("no")), isset($this->options[$option]) ? $this->options[$option]->value : "");
			$list['options_'.$option] = array(
				'class' => "clearfix",
				'value' => $select->item($option_name),
			);
		}
		
		$list['submit'] = array(
			'class' => "itemsform-submit",
			'value' => $save->input(),
		);

		if ((int)$this->id > 0) {
			$form = "<h3>".__("Edit user %s", array($this->name))."</h3>";
		} else {
			$form = "<h3>".__("Add new user")."</h3>";
		}
		$form .= "<form method=\"post\" action=\"\">";
		$form .= $id->input_hidden();
		$items = new Html_List(array('leaves' => $list, 'class' => "itemsform"));
		$form .= $items->show();
		$form .= "</form>";
		
		return $form;
	}
	
	function links_to_operations() {
		return $this->link_to_edit().$this->link_to_delete();
	}

	function is_recently_modified(){
		if ($this->timestamp > (time() - 10)) {
			return true;
		}
		return false;
	}
}
