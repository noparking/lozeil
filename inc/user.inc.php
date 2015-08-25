<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

class User extends Record  {
	public $id = 0;
	public $name = "";
	public $username = "";
	public $password = "";
	public $email = "";
	public $timestamp = 0;
	protected $db = null;

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
	
	function load(array $key = array(), $table = "users", $columns = null) {
		return parent::load($key, $table, $columns);
	}
	
	function save() {
		if (is_numeric($this->id) and $this->id != 0) {
			$this->id = $this->update();

		} else {
			$this->id = $this->insert();
		}

		return $this->id;
	}
	
	function insert() {
		$query = "INSERT INTO ".$this->db->config['table_users']."
			SET name = ".$this->db->quote($this->name).",
			timestamp = ".time().",
			username = ".$this->db->quote($this->username).", ";
			if (isset($this->password) and !empty($this->password)) {
				$query .= " password = ".$GLOBALS['config']['mysql_password']."(".$this->db->quote($this->password)."), ";
			}
			$query .=" email = ".$this->db->quote($this->email);
				
		$result = $this->db->id($query);
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
		$result = $this->db->query("DELETE FROM ".$this->db->config['table_users'].
			" WHERE id = '".$this->id."'"
		);
		$this->db->status($result[1], "d", __('user'));
		return $this->id;
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

	function password_request() {
		if ($this->email) {
			$db = new db();

			$time = time();
			$token = md5($time.$this->username.$this->password);
			$id = $db->id("
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
			$row = $db->fetchArray($result[0]);

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
			$this->defaultpage = "account.php";
		}

		return $this->defaultpage;
	}
	
	function ismodexpert()
	{
		$option = new User_Option();
		$option->load(array( "user_id" => $this->id , "name" => "viewexpert"));
		return empty($option->value)?false:($option->value == "1")?"1":false;
	}
	
	function savemodexpert($bool)
	{
		$option = new User_Option();
		$option->load(array("user_id"=>$this->id,"name"=>"viewexpert"));
		$option->user_id = $this->id;
		$option->name = "viewexpert";
		$option->value = $bool;
		$option->save();
	}
	
	function delmodexpert()
	{
		$option = new User_Option();
		$option->load(array("user_id" => $this->id,"name" => "viewexpert"));
		if ($option->id > 0) {
			$option->delete();
		}
	}

	static function show_form()	{
		$input_name = new Html_Input("new_user_name","","text");
		$input_username = new Html_Input("new_user_username","","text");
		$input_psw = new Html_Input("new_user_psw","","password");
		$input_mail = new Html_Input("new_user_mail","","text");
		$input_submit = new Html_Input("new_user_submit",__('add'),"submit");
		$checkbox_view = new Html_Checkbox("new_user_view","1");
		$form = "<center><div><h3>".__('add new user')."</h3><form method=\"post\"  action=\"".link_content("content=users.php")."\"><table>";
		$form .= "<tr><td>".ucfirst(__('name'))." : </td><td>".$input_name->input()."</td></tr>";
		$form .= "<tr><td>".ucfirst(__('username'))." : </td><td>".$input_username->input()."</td></tr>";
		$form .= "<tr><td>".ucfirst(__('password'))." : </td><td>".$input_psw->input()."</td></tr>";
		$form .= "<tr><td>".ucfirst(__('email'))." : </td><td>".$input_mail->input()."</td></tr>";
		if (isset($_SESSION['accountant_view']) and $_SESSION['accountant_view']) {
			$form .= "<tr><td>".ucfirst(__("expert mode"))."</td><td>".$checkbox_view->input()."</td></tr>";
		}
		$form .= "<tr><td>".$input_submit->input()."</td></tr>";
		$form .= "</table></form></div></center><br><br>";
		
		return $form;
	}
	
	
	function show_form_modification() {
		$input_name = new Html_Input("users[".$this->id."][name]",$this->name);
		$input_username = new Html_Input("users[".$this->id."][username]",$this->username);
		$input_psw = new Html_Input("users[".$this->id."][password]","","password");
		$input_mail = new Html_Input("users[".$this->id."][email]",$this->email);
		$input_submit = new Html_Input("users[".$this->id."][submit]",__('modify'),"submit");
		$action = new Html_Input("action","save","hidden");
		$checkbox_view = new Html_Checkbox("users[".$this->id."][view]","1",$this->ismodexpert());
		$form = "<div class=\"\"><center><h3>".__('modify a user')."</h3><form name=\"\" id=\"form_modif_user\"  method=\"post\"  action=\"".link_content("content=users.php")."\"><table>";
		$form .= "<tr><td>".ucfirst(__('name'))." : </td><td>".$input_name->input()."</td></tr>";
		$form .= "<tr><td>".ucfirst(__('username'))." : </td><td>".$input_username->input()."</td></tr>";
		$form .= "<tr><td>".ucfirst(__('password'))." : </td><td>".$input_psw->input()."</td></tr>";
		$form .= "<tr><td>".ucfirst(__('email'))." : </td><td>".$input_mail->input()."</td></tr>";
		if (isset($_SESSION['accountant_view']) and $_SESSION['accountant_view']) {
			$form .= "<tr><td>".ucfirst(__("expert mode"))."</td><td>".$checkbox_view->input()."</td></tr>";
		}
		$form .= "<tr><td>".$action->input().$input_submit->input()."</td></tr>";
		$form .= "</table></form></center></div><br><br>";
		
		return $form;
	}
	
	
	function show_form_add() {
		$form = "<div class=\"duplicate show_acronym\">
					<span class=\"operation\"> <input class=\"add\" type=\"button\"  id=\"".$this->id."\"/> </span> <br />
					<span class=\"acronym\">".__('add')."</span>
				</div>";
		
		return $form;
	}
	
	function form_delete() {
			$input_hidden_id = new Html_Input("table_users_delete_id", $this->id);
			$input_hidden_action = new Html_Input("action", "delete");
			$submit = new Html_Input("users[".$this->id."][submit]", '',"submit");
			$submit->properties = array(
				'onclick' => "javascript:return confirm('".utf8_ucfirst(__('are you sure?'))."')"
			);
			
			$form = "<div class=\"delete show_acronym\">
						<form method=\"post\" name=\"table_users_form_delete\" action=\"\" enctype=\"multipart/form-data\">".
							$input_hidden_action->input_hidden().$input_hidden_id->input_hidden().$submit->input()."
						</form>
						<span class=\"acronym\">".__('delete')."</span>
					</div>";
			
			return $form;
	}
	

	function show_form_modify() {
			$form = "<div class=\"modify show_acronym\">
					<span class=\"operation\"> <input class=\"modif\" type=\"button\"  id=\"".$this->id."\"/> </span> <br />
					<span class=\"acronym\">".__('modify')."</span>
					</div>";
			
			return $form;
	}
	
	function show_operations() {
		return $this->show_form_modify().$this->form_delete();
	}

	function is_recently_modified(){
		if ($this->timestamp > (time() - 10)) {
			return true;
		}
		return false;
	}
}