<?php

define('IN_PAGE', TRUE);

require_once "login.php";
require_once "engine.php";

require_once "engine/classes/class.Mysql.php";
require_once "engine/classes/class.Users.php";

CEngineDB::Init();

if ( !session_id() )
{
	@session_start();

	if ( !isset($_SESSION['active_user_page']) )
		$_SESSION['active_user_page'] = 1;

	if ( !isset($_SESSION['expired_user_page']) )
		$_SESSION['expired_user_page'] = 1;
}

$g_Users = new CUsers();

$add_page = "add.php";
$edit_page = "edit.php";
$users_page = "users.php";
$settings_page = "settings.php";

$current_page = $users_page;

$cheat_name = GetSettingsValueByName("cheat_name");

if ( array_key_exists('page',$_GET) )
{
	$current_page = $_GET['page'];

	switch ($current_page) {
		case 'add':
			$current_page = $add_page;
		break;
		case 'users':
			$current_page = $users_page;
		break;
		case 'edit':
			$current_page = $edit_page;
		break;
		case 'settings':
			$current_page = $settings_page;
		break;
		default:
			$current_page = $users_page;
		break;
	}
}

include $current_page;

?>