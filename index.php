<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require dirname(__FILE__)."/inc/require.inc.php";

session_start();

if (!isset($_REQUEST['method']) || $_REQUEST['method'] != 'json') {
	$theme = new Theme_Default();

	$location = clean_location($_SERVER['PHP_SELF']);
	header('Cache-control: private');
	header('Content-Type: text/html; charset=UTF-8');
	header('X-UA-Compatible: IE=edge');

	echo $theme->html_top();
	echo $theme->head();
	echo $theme->body_top($location);

	echo $theme->content_top();

	if (isset($_GET['content']) and !empty($_GET['content'])) {
		include("contents/".$_GET['content']);
	} else {
		include("contents/lines.php");
	}
	echo $theme->content_bottom();

	echo $theme->body_bottom();
	echo $theme->html_bottom();
} else {
	if (isset($_GET['content']) and !empty($_GET['content'])) {
		include("contents/".$_GET['content']);
	} else {
		include("contents/lines.php");
	}
}