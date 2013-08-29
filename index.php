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
if (isset($_GET['content']) and !empty($_GET['content'])) {
	$content = $_GET['content'];
} else {
	$content = "lines.php";
}
$location = clean_location($_SERVER['PHP_SELF']);
if (!isset($_REQUEST['method']) && !preg_match("/ajax/", $content)) {
	$theme = new Theme_Default();

	header('Cache-control: private');
	header('Content-Type: text/html; charset=UTF-8');
	header('X-UA-Compatible: IE=edge');

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