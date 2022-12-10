<?php

define('IN_PAGE', TRUE);

require_once "engine/classes/class.Mysql.php";
require_once "engine/classes/class.Users.php";

require_once "engine.php";

CEngineDB::Init();

$g_Users = new CUsers();

$is_hwid = isset($_GET['serial']);
$is_day = isset($_GET['day']);
$is_version = isset($_GET['version']);

$random_md5 = rand(1,10);

if ( $is_version )
{
	$check_version = $_GET['version'];
	
	if ( $check_version == "ok" )
	{
		echo GetSettingsValueByName("version_build");
		die();
	}
	else
	{
		echo "error: 1";
		die();
	}
}
else if ( $is_day )
{
	$user_day_hwid = base64_decode($_GET['day']);
	
	if( ValidUserHWID($user_day_hwid) )
	{
		echo $g_Users->GetUserDay($user_day_hwid);
		die();
	}
}
else if ( $is_hwid && is_base64_encoded($_GET['serial']) && FindSettingsValueByName("cheat_enable") && GetSettingsValueByName("cheat_enable") >= 1 )
{
	$user_hwid = base64_decode($_GET['serial']);
	
	if ( ValidUserHWID($user_hwid) )
	{
		$search_user = $g_Users->GetUserByHwid($user_hwid);
		
		if ( $search_user && array_key_exists('id',$search_user) && ValidLicense($search_user['date']) )
		{
			echo md5("D2DF62F3E61D4696-".$_GET['serial']."-".$random_md5);
			die();
		}
		else
			echo md5("license-error-3"."-".$random_md5);
	}
	else
		echo md5("license-error-2"."-".$random_md5);
}
else
		echo md5("license-error-1"."-".$random_md5);

?>