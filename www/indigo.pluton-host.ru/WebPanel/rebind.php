<?php

define('IN_PAGE', TRUE);

require_once "engine/classes/class.Mysql.php";
require_once "engine/classes/class.Users.php";

require_once "engine.php";

CEngineDB::Init();

$old_license_db_url =  "http://csxr.ru/vermillion/A5BD691BBFE85.db";
$old_license_del_url = "http://csxr.ru/vermillion/csgo_aim.php?skey=91AE3CA683C31337&del=";

$old_db_use_base64 = true;

$g_Users = new CUsers();

$is_old_new_hwid = isset($_GET['data']);

if ( $is_old_new_hwid && is_base64_encoded($_GET['data']) )
{
	$user_old_and_new_hwid = base64_decode($_GET['data']);
	$user_hwid_array = explode("|",$user_old_and_new_hwid);
	
	if ( !$user_old_and_new_hwid )
	{
		header("Location: http://google.com/");
		die();
	}
	
	$user_hwid_old = $user_hwid_array[0];
	$user_hwid_new = $user_hwid_array[1];

	if ( $g_Users->FindByHwid($user_hwid_new) )
	{
		echo "error: 1";
		die();
	}

	$license_db = file($old_license_db_url);

	foreach($license_db as $user_license)
	{
		$user_license = trim($user_license);
		$user_db = explode("|",$user_license);

		if ( $old_db_use_base64 )
		{
			$user_db_name = base64_decode($user_db[0]);
			$user_db_hwid = base64_decode($user_db[1]);
			$user_db_date = base64_decode($user_db[2]);
		}
		else
		{
			$user_db_name = $user_db[0];
			$user_db_hwid = $user_db[1];
			$user_db_date = $user_db[2];
		}

		if ( $user_hwid_old == $user_db_hwid )
		{
			if ( ValidLicense($user_db_date) )
			{
				$g_Users->AddUser($user_db_name,$user_hwid_new,$user_db_date);
				
				if ( $old_db_use_base64 )
					file_get_contents($old_license_del_url.base64_encode($user_db_name));
				else
					file_get_contents($old_license_del_url.$user_db_name);
				
				echo "success";
				die();
			}
			else
			{
				echo "error: 2";
				die();
			}
		}
	}
	
	echo "error: 3";
	die();
}
else
{
	header("Location: http://google.com/");
}

?>