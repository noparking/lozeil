<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require dirname(__FILE__)."/inc/require.inc.php";

$application = new Application();
$application->boot();

$global_status = false;

if ($GLOBALS['config']['db_profiler']) {
	$dbInst = new db_perf();
} else {
	$dbInst = new db();
}

$timer = new Benchmark_Timer;
$timer->start();

$content_object = new Content();

if (isset($_SESSION['userdatabase']) and $_SESSION['userdatabase'] != $GLOBALS['dbconfig']['name']) {
	session_destroy();
}

if (isset($_POST['username']) and $_POST['username'] != '') {
	$auth = new User_Authentication();
	if ($auth->is_authorized($_POST['username'], $_POST['password'])) {
		$_SESSION += $auth->session_headers();
	}
}

$authenticated_user = new User_Authenticated();
if (isset($_SESSION['userid'])) {
	$authenticated_user->load(array('id' => (int)$_SESSION['userid']));
	$_SESSION['accountant_view'] = $authenticated_user->is_expert() ? "1" : "0";
	$content_object->user($authenticated_user);
}

if (isset($_SESSION['username']) and $_SESSION['username']) {
	if (isset($_GET['content']) and !empty($_GET['content']) and $_GET['content'] != 'login.php' ) {
		$content_object->filename($_GET['content']);
	} else {
		$content_object->filename($authenticated_user->defaultpage());
	}
	$content = $content_object->filename();
	$content_included = $content_object->pathname();
	
	$location = clean_location($_SERVER['PHP_SELF']);
	
	if (!isset($_REQUEST['method']) and !preg_match("/ajax/", $content)  ) {

		if(isset($_GET['content']) and $_GET['content'] == "login.php") {
			header("Location: index.php");
		} else if(preg_match("/export/", $content) and isset($_POST['date_picker_from']) and isset($_POST['menu_actions_export_submit']) ) {
			include($content_included);
			exit();
		}
		
		else if ($content_object->check_access_denied() === true)
			$content_included = dirname(__FILE__)."/contents/".Content::access_denied;

		$theme = new Theme_Default();
		echo $theme->html_top();
		echo $theme->head();
		echo $theme->body_top($location, $content);

		echo $theme->content_top();

		include($content_included);

		echo $theme->content_bottom();
		echo $theme->show_status();

		echo $theme->body_bottom();
		echo $theme->html_bottom();
		
	} else {
		include($content_included);
	}
	
	
} else {
	$location = clean_location($_SERVER['PHP_SELF']);
	if (isset($_GET['content']) and $_GET['content'] == "passwordrequest.php") {
		$content_object->filename($_GET['content']);
	} else {
		$content_object->filename_login();
	}
	$content = $content_object->filename();
	$content_included = $content_object->pathname();
	
	$theme = new Theme_Default();
	echo $theme->html_top();
	echo $theme->head();
	echo $theme->body_top($location, $content);

	echo $theme->content_top();
	include($content_included);
	echo $theme->content_bottom();
	echo $theme->show_status();
	echo $theme->body_bottom();
	echo $theme->html_bottom();
}

$timer->stop();
register_shutdown_function(array($application, "shutdown"));
