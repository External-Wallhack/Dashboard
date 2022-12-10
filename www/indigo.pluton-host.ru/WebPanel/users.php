<?php

if( !defined("IN_PAGE") )
{
	header("location: https://www.google.ru/");
	die();
}

$status_code = 0;

// User delete

$is_delete_id = isset($_GET['delete']);

if ( $is_delete_id )
{
	$delete_user_id = $_GET['delete'];

	if ( is_numeric($delete_user_id) )
	{
		$user_edit = $g_Users->GetUserById($delete_user_id);

		if( $user_edit && array_key_exists('id',$user_edit) )
		{
			if ( $g_Users->DeleteUserById($delete_user_id) )
			{
				$status_code = 3;
			}
		}
		else { $status_code = 2; }
	}
	else { $status_code = 1; }
}

// Search User

$is_search_user = isset($_POST['search_name_hwid']);

if ( $is_search_user )
{
	$search_user = $_POST['search_name_hwid'];
	$search_user = htmlspecialchars($search_user,ENT_QUOTES);

	if ( $g_Users->FindByName($search_user) || $g_Users->FindByHwid($search_user) )
	{
		$user_search_data = $g_Users->GetUserByName($search_user);
		
		if ( !$user_search_data )
		{
			$user_search_data = $g_Users->GetUserByHwid($search_user);
		}
		
		if ( array_key_exists('id',$user_search_data) )
		{
			header('Location: '."?page=edit&id=".$user_search_data['id']);
		}
		else { $status_code = 9; }
	}
	else { $status_code = 9; }
}

// PageNation

if ( FindSettingsValueByName("active_user_per_page") )
{
	$ResultActiveLimitSettings = GetSettingsValueByName("active_user_per_page");
	
	if ( is_numeric($ResultActiveLimitSettings) && $ResultActiveLimitSettings > 0 )
		$ActiveUserPageLimit = $ResultActiveLimitSettings;
	else
		$ActiveUserPageLimit = 0;
}
else
{
	$user_active_license_count = 0;
	$ActiveUserPageLimit = 0;
}

if (  FindSettingsValueByName("expired_user_per_page") )
{
	$ResultExpiredLimitSettings = GetSettingsValueByName("expired_user_per_page");
	
	if ( is_numeric($ResultExpiredLimitSettings) && $ResultExpiredLimitSettings > 0 )
		$ExpiredUserPageLimit = $ResultExpiredLimitSettings;
	else
		$ExpiredUserPageLimit = 0;
}
else
{
	$user_expire_license_count = 0;
	$ExpiredUserPageLimit = 0;
}

// Action

$is_action = isset($_GET['action']);

if ( $is_action )
{
	$action_id = $_GET['action'];

	if ( is_numeric($action_id) )
	{
		if ( $action_id >= 1 && $action_id <= 4 ) // Add 1,5,10 day or 1 month user
		{
			if ( AddActiveUserDays($action_id) )
			{
				$status_code = 6;
			}
			else { $status_code = 5; }
		}
		else if ( $action_id == 5 ) // Add One Month User
		{
			$is_user_id = isset($_GET['id']);

			if ( $is_user_id )
			{
				$user_id = $_GET['id'];

				if ( is_numeric($user_id) )
				{
					$user_extend = $g_Users->GetUserById($user_id);

					if( $user_extend && array_key_exists('id',$user_extend) )
					{
						$user_new_date = AddDateDay($user_extend['date'],4);

						$user_data = array(
							'name' => $user_extend['name'],
							'hwid' => $user_extend['hwid'],
							'date' => $user_new_date
						);

						if ( $g_Users->UpdateUserById($user_extend['id'],$user_data) )
						{
							$status_code = 6;
						}
						else { $status_code = 5; }
					}
					else { $status_code = 2; }
				}
				else { $status_code = 4; }
			}
			else { $status_code = 4; }
		}
		else if ( $action_id == 6 )
		{
			if ( RemoveExpiredUsers() )
			{
				$status_code = 7;
			}
			else { $status_code = 8; }
		}
		else if ( $action_id == 7 ) // Prev Active User Page
		{
			if ( $_SESSION['active_user_page'] > 1 )
			{
				$_SESSION['active_user_page']--;
			}
		}
		else if ( $action_id == 8 ) // Next Active User Page
		{
			if ( $ActiveUserPageLimit )
				$ActiveUserTotalPageAction = ceil(GetUserActiveCount() / $ActiveUserPageLimit);
			else
				$ActiveUserTotalPageAction = 1;
			
			if ( $_SESSION['active_user_page'] > 0 && $_SESSION['active_user_page'] < $ActiveUserTotalPageAction )
			{
				$_SESSION['active_user_page']++;
			}
		}
		else if ( $action_id == 9 ) // Prev Expired User Page
		{
			if ( $_SESSION['expired_user_page'] > 1 )
			{
				$_SESSION['expired_user_page']--;
			}
		}
		else if ( $action_id == 10 ) // Next Expired User Page
		{
			if ( $ExpiredUserPageLimit )
				$ExpiredUserTotalPageAction = ceil(GetUserExpireCount() / $ExpiredUserPageLimit);
			else
				$ExpiredUserTotalPageAction = 1;
			
			if ( $_SESSION['expired_user_page'] > 0 && $_SESSION['expired_user_page'] < $ExpiredUserTotalPageAction )
			{
				$_SESSION['expired_user_page']++;
			}
		}
		else if ( $action_id == 11 ) // Сделать обновление
		{
			if ( FindSettingsValueByName("version_build") )
			{
				$ResultRow = GetSettingsByName("version_build");
				
				if ( !empty($ResultRow) )
				{
					$SettingsData = array(
						'id' => $ResultRow['id'],
						'name' => $ResultRow['name'],
						'value' => ++$ResultRow['value'],
						'info' => $ResultRow['info']);
						
					CEngineDB::$EngineDB->where('name', 'version_build');

					if ( CEngineDB::$EngineDB->update('settings',$SettingsData) )
					{
						$status_code = 11;
					}
				}
				else { $status_code = 10; }
			}
			else { $status_code = 10; }
		}
		else if ( $action_id == 12 ) // Очистить список кто скачал
		{
			RemoveUsersUpdate();
			$status_code = 12;
		}
		else if( $action_id == 13 ) // Включить чит
		{
			if ( SetSettingsValueByName("cheat_enable","1") )
			{
				$status_code = 13;
			}
			else { $status_code = 14; }
		}
		else if( $action_id == 14 ) // Отключить чит
		{
			if ( SetSettingsValueByName("cheat_enable","0") )
			{
				$status_code = 15;
			}
			else { $status_code = 16; }
		}
		else { $status_code = 4; }
	}
	else { $status_code = 4; }
}

// PageNation

$user_active_license_count = GetUserActiveCount();
$user_expire_license_count = GetUserExpireCount();

if ( $ActiveUserPageLimit )
	$ActiveUserTotalPage = ceil($user_active_license_count / $ActiveUserPageLimit);
else
	$ActiveUserTotalPage = 1;

if ( $ExpiredUserPageLimit )
	$ExpiredUserTotalPage = ceil($user_expire_license_count / $ExpiredUserPageLimit);
else
	$ExpiredUserTotalPage = 1;

$ActiveUserPage = $_SESSION['active_user_page'];
$ExpiredUserPage = $_SESSION['expired_user_page'];

if ( $ActiveUserPageLimit )
	$OffsetUserActive = $ActiveUserPageLimit * ($ActiveUserPage - 1);

if ( $ExpiredUserPageLimit )
	$OffsetUserExpired = $ExpiredUserPageLimit * ($ExpiredUserPage - 1);

?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<title>Cheat Panel</title>

	<link rel="stylesheet" href="style/bootstrap.min.css">
	<link rel="stylesheet" href="style/material-kit.css">
	<link rel="stylesheet" href="style/vacban.css">

	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:100,300,400,700&subset=latin,latin-ext">

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>

	<script src="style/bootstrap.min.js"></script>
	<script src="style/material.min.js"></script>
	<script src="style/material-kit.js"></script>

	<style type="text/css">
	 .btn-table {margin: auto;}
	 .btn-pages {margin: auto;margin-left: 3px;}
	 .btn-search {margin: auto;margin-left: 9px;}
	 .btn-users {margin-bottom: -5px;margin-top: 5px;}
	 </style>
</head>

<body>

	<div class="container">
	
		<nav class="navbar navbar-inverse">
			<div class="container-fluid">
				<div class="navbar-header">
					<a class="navbar-brand" href="#"><?php echo $cheat_name; ?></a>
				</div>
				<div class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<li class="active"><a href="?page=users">Пользователи</a></li>
						<li><a href="?page=add">Добавить пользователя</a></li>
						<li><a href="?page=settings">Настройки</a></li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Действие<span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="?page=users&action=11">Сделать обновление</a></li>
								<li><a href="?page=users&action=12">Очистить список кто скачал</a></li>
								<li role="separator" class="divider"></li>
								<li><a href="?page=users&action=13">Включить чит</a></li>
								<li><a href="?page=users&action=14">Отключить чит</a></li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
		</nav>
<?php
if ( $status_code == 1 )
	echo "\t\t<div class='alert alert-danger'>Ошибка ID</div>";
else if ( $status_code == 2 )
	echo "\t\t<div class='alert alert-danger'>Ошибка в поиске ID</div>";
else if ( $status_code == 3 )
	echo "\t\t<div class='alert alert-success'>Пользователь успешно удалён</div>";
else if ( $status_code == 4 )
	echo "\t\t<div class='alert alert-danger'>Неверный запрос</div>";
else if ( $status_code == 5 )
	echo "\t\t<div class='alert alert-danger'>Ошибка при обновлении пользовательских данных</div>";
else if ( $status_code == 6 )
	echo "\t\t<div class='alert alert-success'>Дни успешно выданны</div>";
else if ( $status_code == 7 )
	echo "\t\t<div class='alert alert-success'>Пользователи успешно удалёны</div>";
else if ( $status_code == 8 )
	echo "\t\t<div class='alert alert-danger'>Ошибка при удалении пользовательских данных</div>";
else if ( $status_code == 9 )
	echo "\t\t<div class='alert alert-danger'>Ошибка при поиске пользователя</div>";
else if ( $status_code == 10 )
	echo "\t\t<div class='alert alert-danger'>Ошибка в значении настройки version_build</div>";
else if ( $status_code == 11 )
	echo "\t\t<div class='alert alert-success'>Версия успешно обновлена</div>";
else if ( $status_code == 12 )
	echo "\t\t<div class='alert alert-success'>Список успешно очищен</div>";
else if ( FindSettingsValueByName("cheat_enable") && GetSettingsValueByName("cheat_enable") <= 0 )
	echo "\t\t<div class='alert alert-danger'>Внимание !!! чит отключён, активные подписки не будут работать.</div>";
else if ( $status_code == 13 )
	echo "\t\t<div class='alert alert-success'>Чит успешно включён</div>";
else if ( $status_code == 14 )
	echo "\t\t<div class='alert alert-danger'>Ошибка при включении чита</div>";
else if ( $status_code == 15 )
	echo "\t\t<div class='alert alert-success'>Чит успешно отключён</div>";
else if ( $status_code == 16 )
	echo "\t\t<div class='alert alert-danger'>Ошибка при отключении чита</div>";
?>
		<div class="panel panel-info">
			<div class="panel-heading">Статистика</div>
				<div class="panel-body">
				<center>
					<span class="label label-info">Версия чита: <?php echo GetSettingsValueByName("version_build"); ?></span>
					<span class="label label-info">Всего подписок: <?php echo GetAllUserCount(); ?></span>
					<span class="label label-success">Активных подписок: <?php echo $user_active_license_count; ?></span>
					<span class="label label-danger">Не активных подписок: <?php echo $user_expire_license_count ?></span>
					<span class="label label-info">Скачали билдов: <?php echo GetUserUpdateCount(); ?> из <?php echo GetBuildCount(); ?></span>					
				</div>
				</center>
		</div>
		<div class="card" style="margin-bottom: 20px;">
			<form action="?page=users" method="post">
				<div class="content">
					<div class="form-group label-floating is-empty">
						<label class="control-label">Введите имя или hwid пользователя</label>
						<input type="text" class="form-control" name="search_name_hwid" style="float: left;width: 87%;">
					</div>
					<input type="submit" class="btn btn-raised btn-success" style="margin-left: 1%;" value="Найти">
				</div>
			</form>
		</div>

		<div class="panel panel-success"  id="panel_active">
			<div class="panel-heading">Активные подписки <?php echo $user_active_license_count." - страница $ActiveUserPage / $ActiveUserTotalPage"; ?>
			<script>
			function OnPrevActiveUserPage()
			{
				location.reload(true);
				location.replace('?action=7#panel_active');
			}
			function OnNextActiveUserPage()
			{
				location.reload(true);
				location.replace('?action=8#panel_active');
			}
			</script>
			<?php
				if ( $ActiveUserPage > 1 )
					echo '<button onclick="OnPrevActiveUserPage();" class="btn btn-raised btn-small btn-info btn-pages">Предыдущая</button>';
				else
					echo '<a class="btn btn-raised btn-small btn-info btn-pages disabled" href="#" role="button">Предыдущая</a>';

				if ( $ActiveUserPage < $ActiveUserTotalPage )
				{
					echo '<button onclick="OnNextActiveUserPage();" class="btn btn-raised btn-small btn-info btn-pages">Следующая</button >';
				}
				else
					echo '<a class="btn btn-raised btn-small btn-info btn-pages disabled" href="#" role="button">Следующая</a>';
			?>
			</div>
			<div class="panel-body">
				<div class="content content-table content-table-small">
					<div class="row">
						<div class="col-md-3">
							<b>Имя пользователя</b>
						</div>
						<div class="col-md-2">
							<b>HWID</b>
						</div>
						<div class="col-md-2">
							<b>Дата окончания</b>
						</div>
						<div class="col-md-5">
							<b>Действие</b>
						</div>
					</div>
					<?php

					if ( $user_active_license_count > 0 && $ActiveUserPageLimit )
					{
						$g_Users->db->orderBy('id','desc');
						$AllUsersRow = $g_Users->db->get('users');

						$UserActiveLimit = 0;
						$UserActiveOffset = 0;

						foreach ($AllUsersRow as $user)
						{
							if( ValidLicense($user['date']) && $UserActiveLimit < $ActiveUserPageLimit )
							{
								if ( $UserActiveOffset < $OffsetUserActive )
								{
									$UserActiveOffset++;
									continue;
								}
								
								$user_day_count = $g_Users->GetUserDay($user['hwid']);

								echo '<div class="row">';
								echo '<div class="col-md-3" style="text-transform: none;">'.$user['name'].'</div>';
								echo '<div class="col-md-2">'.$user['hwid'].'</div>';
								echo '<div class="col-md-2">'.$user['date'].' ('.$user_day_count.')'.'</div>';
								echo '
								<div class="col-md-5">
								<a class="btn btn-raised btn-small btn-success btn-table" href="?page=users&action=5&id='.$user['id'].'" role="button">Добавить 1 месяц</a>
								<a class="btn btn-raised btn-small btn-info btn-table" href="?page=edit&id='.$user['id'].'" role="button">Редактировать</a>
								<a class="btn btn-raised btn-small btn-danger btn-table" href="?page=users&delete='.$user['id'].'" role="button">Удалить</a>
								</div></div>';

								$UserActiveLimit++;
								$UserActiveOffset++;
							}
						}
					}
					else
					{
						echo '<div class="row">';
						echo '<div class="col-md-3">нету</div>';
						echo '<div class="col-md-2">нету</div>';
						echo '<div class="col-md-2">нету</div>';
						echo '
						<div class="col-md-5">
						<a class="btn btn-raised btn-small btn-success btn-table disabled" href="#" role="button">Добавить 1 месяц</a>
						<a class="btn btn-raised btn-small btn-info btn-table disabled" href="#" role="button">Редактировать</a>
						<a class="btn btn-raised btn-small btn-danger btn-table disabled" href="#" role="button">Удалить</a>
						</div></div>';
					}
					?>
				</div>
				<?php
				if ( $user_active_license_count > 0 )
				{
					echo '<a class="btn btn-raised btn-small btn-success btn-users" href="?action=1" role="button">Всем 1 день</a>';
					echo '<a class="btn btn-raised btn-small btn-success btn-users" href="?action=2" role="button">Всем 5 дней</a>';
					echo '<a class="btn btn-raised btn-small btn-success btn-users" href="?action=3" role="button">Всем 10 дней</a>';
					echo '<a class="btn btn-raised btn-small btn-success btn-users" href="?action=4" role="button">Всем 1 месяц</a>';
				}
				else
				{
					echo '<a class="btn btn-raised btn-small btn-success btn-users disabled" href="#" role="button">Всем 1 день</a>';
					echo '<a class="btn btn-raised btn-small btn-success btn-users disabled" href="#" role="button">Всем 5 дней</a>';
					echo '<a class="btn btn-raised btn-small btn-success btn-users disabled" href="#" role="button">Всем 10 дней</a>';
					echo '<a class="btn btn-raised btn-small btn-success btn-users disabled" href="#" role="button">Всем 1 месяц</a>';
				}
				?>
			</div>
		</div>

		<div class="panel panel-danger" id="panel_expired">
			<div class="panel-heading">Не активные подписки <?php echo $user_expire_license_count." - Страница $ExpiredUserPage / $ExpiredUserTotalPage"; ?>
			<script>
			function OnPrevExpiredUserPage()
			{
				location.reload(true);
				location.replace('?action=9#panel_expired');
			}
			function OnNextExpiredUserPage()
			{
				location.reload(true);
				location.replace('?action=10#panel_expired');
			}
			</script>
			<?php
				if ( $ExpiredUserPage > 1 )
					echo '<button onclick="OnPrevExpiredUserPage();" class="btn btn-raised btn-small btn-info btn-pages" role="button">Предыдущая</button>';
				else
					echo '<a class="btn btn-raised btn-small btn-info btn-pages disabled" href="#">Предыдущая</a>';

				if ( $ExpiredUserPage < $ExpiredUserTotalPage )
					echo '<button onclick="OnNextExpiredUserPage();" class="btn btn-raised btn-small btn-info btn-pages" role="button">Следующая</button>';
				else
					echo '<a class="btn btn-raised btn-small btn-info btn-pages disabled" href="#">Следующая</a>';
			?>
			</div>
			<div class="panel-body">
				<div class="content content-table content-table-small">
					<div class="row">
						<div class="col-md-3">
							<b>Имя пользователя</b>
						</div>
						<div class="col-md-2">
							<b>HWID</b>
						</div>
						<div class="col-md-2">
							<b>Дата окончания</b>
						</div>
						<div class="col-md-5">
							<b>Действие</b>
						</div>
					</div>
					<?php

					if ( $user_expire_license_count > 0 && $ExpiredUserPageLimit )
					{
						$g_Users->db->orderBy('id','desc');
						$AllUsersRow = $g_Users->db->get('users');

						$UserExpiredLimit = 0;
						$UserExpiredOffset = 0;

						foreach ($AllUsersRow as $user)
						{
							if( !ValidLicense($user['date']) && $UserExpiredLimit < $ExpiredUserPageLimit )
							{
								if ( $UserExpiredOffset < $OffsetUserExpired )
								{
									$UserExpiredOffset++;
									continue;
								}

								echo '<div class="row">';
								echo '<div class="col-md-3" style="text-transform: none;">'.$user['name'].'</div>';
								echo '<div class="col-md-2">'.$user['hwid'].'</div>';
								echo '<div class="col-md-2">'.$user['date'].'</div>';
								echo '
								<div class="col-md-5">
								<a class="btn btn-raised btn-small btn-success btn-table" href="?page=users&action=5&id='.$user['id'].'" role="button">Добавить 1 месяц</a>
								<a class="btn btn-raised btn-small btn-info btn-table" href="?page=edit&id='.$user['id'].'" role="button">Редактировать</a>
								<a class="btn btn-raised btn-small btn-danger btn-table" href="?page=users&delete='.$user['id'].'" role="button">Удалить</a>
								</div></div>';

								$UserExpiredLimit++;
								$UserExpiredOffset++;
							}
						}
					}
					else
					{
						echo '<div class="row">';
						echo '<div class="col-md-3">нету</div>';
						echo '<div class="col-md-2">нету</div>';
						echo '<div class="col-md-2">нету</div>';
						echo '
						<div class="col-md-5">
						<a class="btn btn-raised btn-small btn-success btn-table disabled" href="#" role="button">Добавить 1 месяц</a>
						<a class="btn btn-raised btn-small btn-info btn-table disabled" href="#" role="button">Редактировать</a>
						<a class="btn btn-raised btn-small btn-danger btn-table disabled" href="#" role="button">Удалить</a>
						</div></div>';
					}
					?>
				</div>
				<?php
				if ( $user_expire_license_count > 0 )
					echo '<a class="btn btn-raised btn-small btn-danger btn-users" href="?page=users&action=6" role="button">Удалить всех</a>';
				else
					echo '<a class="btn btn-raised btn-small btn-danger btn-users disabled" href="#" role="button">Удалить всех</a>';
				?>
			</div>
		</div>
	</div>

</body>

</html>