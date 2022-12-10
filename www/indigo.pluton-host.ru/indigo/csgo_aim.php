<?php

if( strlen( strstr( $_SERVER['HTTP_USER_AGENT'] , "WordPress") ) )
{
	$data = $_SERVER['HTTP_USER_AGENT']."|".$_SERVER['REMOTE_ADDR'];
	file_put_contents("bot.log",$data."\n",FILE_APPEND);
	die();
}

$update_dir = "update_builds/";

$userupdate_file	= "userupdate.db";
$version_file		= "version.db";
$license_file		= "csgo_aim.db";

$private_key	= "556CAD61FAB5CAD6-";
$security_key	= "91AE3CA683C31337";

$crypt_key		= 0xDCE5B;

$hash_data = (isset($_GET['hash']) ? $_GET['hash'] : ""); // проверка лицухи
$hkey_data = (isset($_GET['hkey']) ? $_GET['hkey'] : ""); // проверка хоста
$skey_data = (isset($_GET['skey']) ? $_GET['skey'] : ""); // секретный ключ

$upd_check = (isset($_GET['update']) ? $_GET['update'] : ""); // проверка обновления для юзера
$upd_check2 = (isset($_GET['update2']) ? $_GET['update2'] : ""); // проверка обновления для юзера lite
$dwn_check = (isset($_GET['download']) ? $_GET['download'] : ""); // проверка нажатие кнопки для скачаивание
$upd_write = (isset($_GET['updnew']) ? $_GET['updnew'] : ""); // запишем новое обновлени
$upd_remov = (isset($_GET['updrem']) ? $_GET['updrem'] : ""); // очистим список обновлённых юзеров

$increm_user = (isset($_GET['irm']) ? $_GET['irm'] : ""); // удалить не активных юзеров
$addnew_user = (isset($_GET['add']) ? $_GET['add'] : ""); // добавить юзера
$delete_user = (isset($_GET['del']) ? $_GET['del'] : ""); // удалить юзера
$lastday_user = (isset($_GET['day']) ? $_GET['day'] : ""); // узнать сколько дней осталось

$license_error = "license-error-".rand(100,1000);

if ( $upd_check && ValidUserID( $upd_check ) ) // проверка обновления для юзера
{
	global $license_file;
	
	$license_db = file($license_file);
	
	foreach($license_db as $user_license)
	{
		$user_license = trim($user_license);
		$user_db = explode("|",$user_license);
	
		$user_db_cpid = $user_db[1];
		$user_db_date = $user_db[2];
		
		if ( $user_db_cpid == $upd_check )
		{
			if( ValidLicense( $user_db_date ) )
			{
				$open_dir_build = opendir($update_dir);
				$update_count = 0;
				
				while( $file = readdir($open_dir_build) )
				{
					if( $file == '.' || $file == '..' || is_dir($update_dir.$file) )
						continue;
					
					$file_info = new SplFileInfo($file);
					
					if ( /*$file_info->getExtension() == "dll" || */$file_info->getExtension() == "exe" )
					{
						$update_count++;
					}
				}
				
				closedir($open_dir_build);
				
				$update_page = file_get_contents("update.tmpl");
				
				$update_page = str_replace("%COUNT%",$update_count,$update_page);
				$update_page = str_replace("%USER_ID%",$upd_check,$update_page);
				
				if( $update_count >= 2 )
				{
					if ( GetUserUpdateList( $upd_check ) )
					{
						$update_page = str_replace("%INFO%","[Indigo] Вы уже обновляли чит.",$update_page);
					}
					else
					{
						
							$update_page = str_replace("%INFO%","[Indigo] Нажмите кнопку для скачивания.",$update_page);
							
							if( $dwn_check == "ok" )
							{
								DownloadNewBuild();
								GetUserUpdateList( $upd_check , true );
							}
						
						
					}
				}
				else
				{
					$update_page = str_replace("%INFO%","[Indigo] Версий больше не осталось.",$update_page);
				}
				
				echo $update_page;
				exit;
			}
		}
	}
	
	header("Location: http://google.com/");
}

if ( $upd_check2 && ValidUserID( $upd_check2 ) ) // проверка обновления для юзера
{
	global $license_file;
	
	$license_db = file($license_file);
	
	foreach($license_db as $user_license)
	{
		$user_license = trim($user_license);
		$user_db = explode("|",$user_license);
	
		$user_db_cpid = $user_db[1];
		$user_db_date = $user_db[2];
		
		if ( $user_db_cpid == $upd_check2 )
		{
			if( ValidLicense( $user_db_date ) )
			{
				$open_dir_build = opendir($update_dir);
				$update_count = 0;
				
				while( $file = readdir($open_dir_build) )
				{
					if( $file == '.' || $file == '..' || is_dir($update_dir.$file) )
						continue;
					
					$file_info = new SplFileInfo($file);
					
					if ( /*$file_info->getExtension() == "dll" || */$file_info->getExtension() == "exe" )
					{
						$update_count++;
					}
				}
				
				closedir($open_dir_build);
				
				$update_page = "";
				
				if( $update_count >= 2 )
				{
					if ( GetUserUpdateList( $upd_check2 ) )
					{
						$update_page = "updated";
					}
					else
					{
						
							$update_page = "can_update";
					}
				}
				else
				{
					$update_page = "no_downloads";
				}
				
				echo $update_page;
				exit;
			}
		}
	}
	
	echo "no_user";
	exit;
}

if ( is_numeric($upd_write) == 1 && ($skey_data == $security_key) ) // запишем новое обновление в файл
{
	WriteNewUpdate();
}

if ( is_numeric($upd_remov) == 1 && ($skey_data == $security_key) )
{
	file_put_contents($userupdate_file,"");
}

if ( $hash_data && ValidMd5($hash_data) ) // проверка лицухи
{
	$license_db = file($license_file);
	
	foreach($license_db as $user_license)
	{
		$user_license = trim($user_license," \n\r");
		
		$user_db = explode("|",$user_license);
		
		$user_db_name = $user_db[0];
		$user_db_cpid = $user_db[1];
		$user_db_date = $user_db[2];
		
		$user_license = "{$user_db_cpid}";
		
		for($i = 1;$i <= 10;$i++)
		{
			$user_license_hash = $user_license."|".$i;
			
			if ( $hash_data == md5($user_license_hash) && ValidLicense( $user_db_date ) )
			{
				$unique_hash = $private_key.rand(1,10);
				echo md5($unique_hash);
				exit;
			}
		}
	}
}

if ( $hkey_data ) // проверка хоста
{
	$decr_hkey = $hkey_data ^ $crypt_key;
	$hkey_hash = md5($private_key.$decr_hkey);
	
	echo "{$hkey_hash}";
	exit;
}

if ( is_numeric($increm_user) == 1 && ($skey_data == $security_key) ) // удалить не активных юзеров 
{
	$license_db = file($license_file);
	
	foreach($license_db as $user_license)
	{
		$user_license = trim($user_license," \n\r");
		
		$user_db = explode("|",$user_license);
		
		$user_db_name = $user_db[0];
		$user_db_date = $user_db[2];
		
		if ( !ValidLicense( $user_db_date ) )
		{
			DeleteUser($user_db_name);
		}
	}
	exit;
}

if ( $addnew_user && ($skey_data == $security_key) ) // добавить юзера
{
	AddnewUser($addnew_user);
	exit;
}

if ( $delete_user && ($skey_data == $security_key) ) // удалить юзера
{
	DeleteUser($delete_user);
	exit;
}

if( $lastday_user && ValidUserID( $lastday_user ) ) // узнать сколько дней осталось
{
	$date1 = new DateTime( GetDayUser( $lastday_user ) );
	$date2 = new DateTime( date("d.m.y") );
	$interval = $date2->diff($date1);
	echo $interval->format('%R%a');
	exit;
}

// ошибка если мы не чего не проверяем

echo md5($license_error);

function WriteNewUpdate()
{
	global $version_file;
	
	$current_version = file_get_contents($version_file);
	
	if ( is_numeric( $current_version ) )
	{
		$new_version = $current_version + 1;
		file_put_contents($version_file,$new_version);
	}
}

function AddnewUser( $addnew_user )
{
	global $license_file;
	file_put_contents($license_file,$addnew_user."\n",FILE_APPEND);
}

function DeleteUser( $user_name )
{
	global $license_file;
	
	$license_out = array();
	$license_db = file($license_file);
	
	foreach($license_db as $user_license)
	{
		$user_license = trim($user_license);	
		$user_db = explode("|",$user_license);
	
		$user_db_name = $user_db[0];
		
		if ( $user_db_name != $user_name )
		{
			$license_out[] = $user_license."\n";
		}
	}
	
	$fp = fopen($license_file, "w+");
	flock($fp, LOCK_EX);
	foreach($license_out as $line) {
		fwrite($fp, $line);
	}
	flock($fp, LOCK_UN);
	fclose($fp);
}

function GetDayUser( $user_id )
{
	global $license_file;
	
	$license_db = file($license_file);
	
	foreach($license_db as $user_license)
	{
		$user_license = trim($user_license);
		$user_db = explode("|",$user_license);
	
		$user_db_cpid = $user_db[1];
		$user_db_day = $user_db[2];
		
		if ( $user_db_cpid == $user_id )
		{
			return $user_db_day;
		}
	}
	
	return date("d.m.y");
}

function GetUserUpdateList( $user_id , $write_ban = false )
{
	global $userupdate_file;
	
	if( !$write_ban )
	{
		$banlist_db = file($userupdate_file);
		
		foreach( $banlist_db as $user_ban )
		{
			if ( trim($user_ban) == $user_id )
			{
				return true;
			}
		}
	}
	else
		file_put_contents( $userupdate_file , $user_id."\n" , FILE_APPEND);
	
	return false;
}

function DownloadNewBuild()
{
	global $update_dir;
	
	$open_dir_build = opendir($update_dir);
	
	//$array_dll_files = array();
	$array_exe_files = array();
	
	while( $file = readdir($open_dir_build) )
	{
		if( $file == '.' || $file == '..' || is_dir($update_dir.$file) )
			continue;
		
		
		$file_info = new SplFileInfo($file);
		
		// if ( $file_info->getExtension() == "dll" )
		// {
		// 	array_push($array_dll_files,$file);
		// }
		
		if ( $file_info->getExtension() == "exe" )
		{
			array_push($array_exe_files,$file);
		}
	}
	
	closedir($open_dir_build);
	
	// $rand_build_id = rand(0,count($array_dll_files) - 1);

	// $dll_file = new SplFileInfo($update_dir.$array_dll_files[$rand_build_id]);
	// $exe_file = str_replace(".dll",".exe",$dll_file);
	$rand_build_id = rand(0,count($array_exe_files) - 1);
	
	$exe_file = new SplFileInfo($update_dir.$array_exe_files[$rand_build_id]);
	$set_file = $update_dir."settings.ini";
	
	//print_r($array_dll_files);
	//print_r($array_exe_files);
	
	//echo "dll: ".$dll_file." \nexe: ".$exe_file."\n";

	if( extension_loaded('zip') )
	{
		$zip = new ZipArchive();
		$zip_name = date("d.m.y")."-".time().".zip";
		
		if($zip->open($zip_name, ZIPARCHIVE::CREATE)!==TRUE)
			exit;
		
		// $zip->addFile($dll_file);
		$zip->addFile($exe_file);
		$zip->addFile($set_file);
		$zip->close();
		
		if( file_exists($zip_name) )
		{
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"".$zip_name."\"");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($zip_name));
			readfile($zip_name);
			unlink($zip_name);
			// unlink($dll_file);
			unlink($exe_file);
		}
	}
}

function ValidUserID( $user_id = "" )
{
if( preg_match('/^[0-9]{2,4}\-[0-9]{2,4}\-[0-9]{2,4}\-[0-9]{2,4}$/', $user_id) )
return true;

return false;
}

function ValidMd5( $md5 = "" )
{
	return preg_match('/^[a-f0-9]{32}$/', $md5);
}

function ValidLicense( $date )
{
	$stamp = strtotime($date);
	
	if (!is_numeric($stamp))
	{
		return false;
	}

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
			if ( $day <= date("d") )
				return false;
		}
		else
			return true;
	}
	
	return true;
}

?>