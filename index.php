<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

//error_reporting(E_ALL);

ob_start();
require dirname(__FILE__)."/inc/require.inc.php";

if(!isset($_SESSION)) {
	session_start();
}
if (isset($_POST['loginname']) and $_POST['loginname'] != '') {
	$auth = new User_Authentication();
	if ($auth->is_authorized($_POST['loginname'], $_POST['password'])) {
		$_SESSION['username'] = $_POST['loginname'];
	} else {
		$status = $_SESSION['global_status'][0];
	}
}

if (isset($_SESSION['username']) and $_SESSION['username']) {
	
	if (isset($_GET['content']) and !empty($_GET['content']) and $_GET['content'] != 'login.php') {
		$content = $_GET['content'];
	} else {
		$content = "writings.php";
	}

	$location = clean_location($_SERVER['PHP_SELF']);
	if (!isset($_REQUEST['method']) and !preg_match("/ajax/", $content) and !preg_match("/export/", $content)) {
		$theme = new Theme_Default();

		echo $theme->html_top();
		echo $theme->head();
		echo $theme->body_top($location);

		echo $theme->content_top();

		include("contents/".$content);

		echo $theme->content_bottom();

		echo $theme->body_bottom();
		echo $theme->html_bottom();
	} else {
		include("contents/".$content);
	}
} else {
	$location = clean_location($_SERVER['PHP_SELF']);
	$GLOBALS['content'] = "login.php";
	$theme = new Theme_Default();
	echo $theme->html_top();
	echo $theme->head();
	echo $theme->body_top($location);

	echo $theme->content_top();
	include("contents/login.php");
	echo $theme->content_bottom();
	echo $theme->body_bottom();
	echo $theme->html_bottom();
}
