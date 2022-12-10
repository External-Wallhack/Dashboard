<?php

define('IN_PAGE', TRUE);

require_once "engine/classes/class.Mysql.php";
require_once "engine/classes/class.Users.php";

require_once "engine.php";

CEngineDB::Init();

$g_Users = new CUsers();

$hack_name = GetSettingsValueByName("cheat_name");

$is_hwid = isset($_GET['update']);

if ($is_hwid) {
	$user_hwid = base64_decode($_GET['update']);

	if ($user_hwid && ValidUserHWID($user_hwid) && UserLicenseValid($user_hwid)) {
		$all_build_count = GetBuildCount();

		if ($all_build_count > 0) {
			$user_buidl_url = "?update=" . base64_encode($user_hwid) . "&download=ok";

			if (IsUserDownloadBuild($user_hwid)) {
				header("Location: http://google.com/");
			} else {
				$check_download = (isset($_GET['download']) ? $_GET['download'] : "");

				if ($check_download == "ok") {
					DownloadNewBuild();
					AddUserDownloadList($user_hwid);
				}
			}
		} else {
			header("Location: http://google.com/");
		}
	} else {
		header("Location: http://google.com/");
	}
} else {
	header("Location: http://google.com/");
}

function DownloadNewBuild()
{
	$update_dir = BUILD_PATH;

	$open_dir_build = opendir($update_dir);

	$array_exe_files = array();

	while ($file = readdir($open_dir_build)) {
		if ($file == '.' || $file == '..' || is_dir($update_dir . $file))
			continue;

		$file_info = new SplFileInfo($file);

		if ($file_info->getExtension() == "dll") {
			array_push($array_exe_files, $file);
		}
	}

	closedir($open_dir_build);

	$rand_build_id = rand(0, count($array_exe_files) - 1);

	$exe_file = new SplFileInfo($update_dir . $array_exe_files[$rand_build_id]);

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"" . $exe_file . "\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: " . filesize($exe_file));
	readfile($exe_file);
	unlink($exe_file);
}

?>