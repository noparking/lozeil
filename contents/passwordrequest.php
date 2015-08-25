<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

$status = "";
if (isset($_POST['login_email']) and !empty($_POST['login_email'])) {
	$users = new Users();
	
	$users->filter_with(array('username' => $_POST['login_email']));
	$users->select();
	if (count($users) == 1) {
		$user = $users[0];
		$status = $user->password_request();
		Message::log("A new password has been requested for ".$user->username);
	} else {
		$users->filters = array();
		$users->filter_with(array('email' => $_POST['login_email']));
		$users->select();
		if (count($users) >= 1) {
			foreach ($users as $user) {
				$status = $user->password_request();
				Message::log("A new password has been requested for ".$user->username);
			}
		} else {
			status($_POST['login_email'], __('not exisisting'), -1);
		}
	}
} elseif (isset($_GET['token'])) {
	$token = $_GET['token'];
	$user = new User();
	if ($user->password_reset($token)) {
		Message::log("A new password has been generated for ".$user->username);
	} else {
		header ("Location: ".$GLOBALS['config']['root_url']."/");
		exit();
	}
}

$heading = new Heading_Area(__('request a new password'));
echo $heading->show();

if (isset($_GET['token'])) {
	echo "<div id=\"password_sent\">";

	echo __('new password sent')."<br /><br />";
	echo Html_Tag::a(link_content("content=login.php"), __('login'));
	
	echo "</div>";
} else {
	$login_email = new Html_Input("login_email");
	$send = new Html_Input("send", __('send new password'), "submit");
	
	echo "<div id=\"password_request\">";
	echo "<form method=\"post\" action=\"index.php?content=passwordrequest.php\" name=\"form_login\" id=\"form_login\">";
	
	$grid = array(
		'login_email' => array(
			array(
				'value' => $login_email->label(__('username')." ".__('or')." ".__('email')),
				'type' => "th",
				'scope' => "row",
			),
			array(
				'value' => $login_email->input(),
			),
		),
		'spacer' => array(
			array(
				'value' => "&nbsp;",
				'class' => "small",
				'colspan' => 2,
			),
		),
		'submit' => array(
			array(
				'value' => "&nbsp;",
			),
			array(
				'value' => $send->input(),
			),
		),
	);
	$table = new Html_table(array('lines' => $grid,'class' => "rowform"));
	echo $table->show();
	
	echo "</form>";
	echo "</div>";
}
