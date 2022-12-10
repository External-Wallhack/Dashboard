<?php

if( !defined("IN_PAGE") )
{
	header("location: https://www.google.ru/");
	die();
}

require_once "engine/config.php";

if ((!isset($_SERVER['PHP_AUTH_USER'])) || (!isset($_SERVER['PHP_AUTH_PW'])))
{
	header('WWW-Authenticate: Basic realm="Secured Area"');
	header('HTTP/1.0 401 Unauthorized');
	exit;
}
else if ((isset($_SERVER['PHP_AUTH_USER'])) && (isset($_SERVER['PHP_AUTH_PW'])))
{
	if (($_SERVER['PHP_AUTH_USER'] != PANEL_LOGIN) || ($_SERVER['PHP_AUTH_PW'] != PANEL_PASS))
	{
	   header('WWW-Authenticate: Basic realm="Secured Area"');
	   header('HTTP/1.0 401 Unauthorized');
	   exit;
	}
}

?>
