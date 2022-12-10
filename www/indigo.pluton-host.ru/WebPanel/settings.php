<?php

if( !defined("IN_PAGE") )
{
	header("location: https://www.google.ru/");
	die();
}

$status_code = 0;

foreach($_POST as $key => $value)
{
	//echo "POST parameter $key has $value"."<br>";

	$ResultRow = CEngineDB::$EngineDB->where('id', $key)->get('settings');

	if ( CEngineDB::$EngineDB->count == 1 )
	{
		$value = htmlspecialchars($value,ENT_QUOTES);

		$SettingsData = array(
			'id' => $key,
			'name' => $ResultRow[0]['name'],
			'value' => $value,
			'info' => $ResultRow[0]['info']);

		CEngineDB::$EngineDB->where('id', $key);

		if ( CEngineDB::$EngineDB->update('settings',$SettingsData) )
		{
			$status_code = 3;
		}
		else { $status_code = 2; }
	}
	else { $status_code = 1; }
}

$SettingsRow = CEngineDB::$EngineDB->get('settings');

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
	 .btn-users {margin-bottom: -5px;margin-top: 5px;}
	 </style>
</head>

<body>
	<div class="container">

		<nav class="navbar navbar-inverse">
			<div class="container-fluid">
				<div class="navbar-header">
					<a class="navbar-brand" href="#"><?php echo GetSettingsValueByName("cheat_name"); ?></a>
				</div>
				<div id="navbar" class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<li><a href="?page=users">Пользователи</a></li>
						<li><a href="?page=add">Добавить пользователя</a></li>
						<li class="active"><a href="?page=settings">Настройки</a></li>
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
	echo "\t\t<div class='alert alert-danger'>Ошибка в поиске параметра</div>";
else if ( $status_code == 2 )
	echo "\t\t<div class='alert alert-danger'>Ошибка при обновлении параметра</div>";
else if ( $status_code == 3 )
	echo "\t\t<div class='alert alert-success'>Параметры успешно обновленны</div>";
else if ( CEngineDB::$EngineDB->count <= 0 )
	echo "\t\t<div class='alert alert-danger'>Ошибка в поиске параметров в базе данных</div>";
?>

	<?php
	if ( CEngineDB::$EngineDB->count > 0 )
	{
		echo '
		<div class="card card-nav-tabs">
			<div class="header header-success">
				<h1>Редактирование настроек</h1>
			</div>
			<form action="?page=settings" method="post">
				<div class="content">';
	}
	?>
				<?php
				if ( CEngineDB::$EngineDB->count > 0 )
				{
					foreach ($SettingsRow as $Sett)
					{
						echo '<div class="form-group label-floating">';
							echo '<label class="control-label">'.$Sett['info'].'</label>';
							echo '<input type="text" class="form-control" name="'.$Sett['id'].'" value="'.$Sett['value'].'">';
							echo '<span class="material-input"></span>';
						echo '</div>';
					}
				}
				?>
	<?php
	if ( CEngineDB::$EngineDB->count > 0 )
	{
		echo '
					<div class="form-group">
						<input type="submit" class="btn btn-raised btn-success" value="сохранить">
					</div>
				</div>
			</form>
		</div>';
	}
	?>

	</div>
</body>
</html>