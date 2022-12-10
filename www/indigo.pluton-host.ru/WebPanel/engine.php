<?php

if( !defined("IN_PAGE") )
{
	header("location: https://www.google.ru/");
	die();
}

require_once "engine/config.php";

class CEngineDB
{
	public static $EngineDB = null;

	public static function Init()
	{
		self::$EngineDB = new MysqliDb(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
	}
}

function ValidUserHWID( $user_hwid = "" )
{
	if( preg_match('/^[0-9]{4}-[0-9]{4}-[0-9]{4}-[0-9]{4}$/', $user_hwid) )
		return true;

	return false;
}

function FindSettingsValueByName($name)
{
	$ResultRow = CEngineDB::$EngineDB->where('name', $name)->get('settings');

	if ( CEngineDB::$EngineDB->count == 1 && array_key_exists('id',$ResultRow[0]) )
		return true;

	return false;
}

function GetSettingsByName($name)
{
	$ResultRow = CEngineDB::$EngineDB->where('name', $name)->get('settings');

	if ( CEngineDB::$EngineDB->count == 1 && array_key_exists('id',$ResultRow[0]) )
		return $ResultRow[0];

	return array();
}

function GetSettingsValueByName($name)
{
	$ResultRow = CEngineDB::$EngineDB->where('name', $name)->get('settings');

	if ( CEngineDB::$EngineDB->count == 1 && array_key_exists('id',$ResultRow[0]) )
		return $ResultRow[0]['value'];

	return 0;
}

function SetSettingsValueByName($name,$value)
{
	$ResultRow = GetSettingsByName($name);

	if ( !empty($ResultRow) )
	{
		$SettingsData = array(
						'id' => $ResultRow['id'],
						'name' => $ResultRow['name'],
						'value' => $value,
						'info' => $ResultRow['info']);

		CEngineDB::$EngineDB->where('name', $name);

		if ( CEngineDB::$EngineDB->update('settings',$SettingsData) )
		{
			return true;
		}
		else
			echo 'settings update failed: ' . $this->db->getLastError();
	}

	return false;
}

function AddDateDay($date,$type)
{
	$date1 = new DateTime( $date );
	$date2 = new DateTime( date("d.m.Y") );

	if ( ValidLicense($date) )
		$end_date = $date1;
	else
		$end_date = $date2;

	if ( $type == 1 )
		$end_date->add(date_interval_create_from_date_string('1 days'));
	else if ( $type == 2 )
		$end_date->add(date_interval_create_from_date_string('5 days'));
	else if ( $type == 3 )
		$end_date->add(date_interval_create_from_date_string('10 days'));
	else if ( $type == 4 )
		$end_date->add(date_interval_create_from_date_string('30 days'));
	else if ( $type == 5 )
		$end_date->add(date_interval_create_from_date_string('10 year'));

	return $end_date->format("d.m.Y");
}

function GetAllUserCount()
{
	global $g_Users;

	$UsersRow = $g_Users->db->get('users');

	return  $g_Users->db->count;
}

function GetUserActiveCount()
{
	global $g_Users;

	$count = 0;

	$UsersRow = $g_Users->db->get('users');

	foreach ($UsersRow as $user)
	{
		if( ValidLicense($user['date']) )
		{
			$count++;
		}
	}

	return $count;
}

function GetUserExpireCount()
{
	global $g_Users;

	$count = 0;

	$UsersRow = $g_Users->db->get('users');

	foreach ($UsersRow as $user)
	{
		if( !ValidLicense($user['date']) )
		{
			$count++;
		}
	}

	return $count;
}

function GetUserUpdateCount()
{
	global $g_Users;

	$UsersRow = $g_Users->db->get('users_update');

	return  $g_Users->db->count;
}

function AddActiveUserDays($action)
{
	global $g_Users;

	$UsersRow = $g_Users->db->get('users');

	foreach ($UsersRow as $user)
	{
		if( ValidLicense($user['date']) )
		{
			if ( $action >= 1 && $action <= 5 )
			{
				$user_new_date = AddDateDay($user['date'],$action);

				$user_data = array(
					'name' => $user['name'],
					'hwid' => $user['hwid'],
					'date' => $user_new_date
				);

				if ( !$g_Users->UpdateUserById($user['id'],$user_data) )
					return false;
			}
		}
	}

	return true;
}

function RemoveExpiredUsers()
{
	global $g_Users;

	$UsersRow = $g_Users->db->get('users');

	foreach ($UsersRow as $user)
	{
		if( !ValidLicense($user['date']) )
		{
			if ( !$g_Users->DeleteUserById($user['id']) )
				return false;
		}
	}

	return true;
}

function RemoveUsersUpdate()
{
	global $g_Users;
	$TruncateQuery = $g_Users->db->rawQuery("TRUNCATE TABLE users_update;");
	return true;
}

function ValidDate( $date )
{
	if ( !preg_match("/^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}$/",$date) )
		return false;

	$stamp = strtotime($date);

	if (!is_numeric($stamp))
		return false;

	return true;
}

function ValidLicense( $date )
{
	if ( !ValidDate($date) )
		return false;

	$stamp = strtotime($date);

	$day = date( "d", $stamp );
	$month = date( "m", $stamp );
	$year = date( "Y", $stamp );

	if ( $year < date("Y") )
		return false;
	else if( $year == date("Y") )
	{
		if ( $month < date("m") )
			return false;
		else if ( $month == date("m") )
		{
			if ( $day < date("d") )
				return false;
		}
		else
			return true;
	}

	return true;
}

function UserLicenseValid($hwid)
{
	global $g_Users;
	
	$user = $g_Users->GetUserByHwid($hwid);
	
	if ( array_key_exists('id',$user) )
	{
		return ValidLicense($user['date']);
	}
	
	return false;
}

function AddUserDownloadList($hwid)
{
	global $g_Users;
	
	$data = array("hwid" => $hwid);

	if ( $g_Users->db->insert("users_update", $data) )
		return true;

	echo 'add user downnload list failed: ' . $this->db->getLastError();

	return false;
}

function IsUserDownloadBuild($hwid)
{
	global $g_Users;
	
	$g_Users->db->where("hwid", $hwid);

	$users = $g_Users->db->get("users_update");

	if ( $g_Users->db->count > 0 )
	{
		return true;
	}
	
	return false;
}

function GetBuildCount()
{
	$update_dir = BUILD_PATH;
	
	$open_dir_build = opendir($update_dir);
	$update_count = 0;

	while( $file = readdir($open_dir_build) )
	{
		if( $file == '.' || $file == '..' || is_dir($update_dir.$file) )
			continue;
			
		$file_info = new SplFileInfo($file);
				
		if ( $file_info->getExtension() == "dll" )
		{
			$update_count++;
		}
	}

	closedir($open_dir_build);
	
	return $update_count;
}

function is_base64_encoded($data)
{
	if ( preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $data) )
	{
		return true;
	}
	else
	{
		return false;
	}
}

?>
