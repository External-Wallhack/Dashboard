<?php

if( !defined("IN_PAGE") )
{
	header("location: https://www.google.ru/");
	die();
}

$status_code = 0;

$is_id = isset($_GET['id']);

if ( $is_id )
{
	$data_user_id = $_GET['id'];

	if ( is_numeric($data_user_id) )
	{
		$user_edit = $g_Users->GetUserById($data_user_id);

		if( $user_edit && array_key_exists('id',$user_edit) )
		{
			$edit_user_data_name = $user_edit['name'];
			$edit_user_data_hwid = $user_edit['hwid'];
			$edit_user_data_date = $user_edit['date'];
		}
		else { $status_code = 6; }
	}
	else { $status_code = 5; }
}
else { $status_code = 5; }

$is_name = isset($_POST['user_name']);
$is_hwid = isset($_POST['user_hwid']);
$is_date = isset($_POST['user_date']);

if( !$status_code && $is_name && $is_hwid && $is_date )
{
	$data_user_name = $_POST['user_name'] ? $_POST['user_name'] : 0;
	$data_user_hwid = $_POST['user_hwid'] ? $_POST['user_hwid'] : 0;
	$data_user_date = $_POST['user_date'] ? $_POST['user_date'] : 0;

	if( $data_user_name && $data_user_hwid && $data_user_date )
	{
		if( ValidDate($data_user_date) )
		{
			if( ValidUserHWID($data_user_hwid) )
			{
				$data_user_name = htmlspecialchars($data_user_name,ENT_QUOTES);

				$user_data = array(
					'name' => $data_user_name,
					'hwid' => $data_user_hwid,
					'date' => $data_user_date
				);

				if ( $g_Users->UpdateUserById($data_user_id,$user_data) )
					$status_code = 4;
			}
			else {$status_code = 3;}
		}
		else {$status_code = 2;}
	}
	else {$status_code = 1;}
}

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
					<a class="navbar-brand" href="#"><?php echo $cheat_name; ?></a>
				</div>
				<div id="navbar" class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<li><a href="?page=users">Пользователи</a></li>
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
	echo "\t\t<div class='alert alert-danger'>Заполните все поля</div>";
else if ( $status_code == 2 )
	echo "\t\t<div class='alert alert-danger'>Дата введена некорректно</div>";
else if ( $status_code == 3 )
	echo "\t\t<div class='alert alert-danger'>HWID введён некорректно</div>";
else if ( $status_code == 4 )
	echo "\t\t<div class='alert alert-success'>Пользователь успешно отредактирован</div>";
else if ( $status_code == 5 )
	echo "\t\t<div class='alert alert-danger'>Ошибка ID</div>";
else if ( $status_code == 6 )
	echo "\t\t<div class='alert alert-danger'>Ошибка в поиске ID</div>";
?>
		<div class="card card-nav-tabs">
			<div class="header header-success">
				<h1>Редактирование пользовательских данных</h1>
			</div>
			<?php
			echo "<form action='?page=edit&id=$data_user_id' method='post'>\n";
			?>
				<div class="content">
					<div class="form-group label-floating">
						<label class="control-label">Имя пользователя</label>
						<?php

						if ( $status_code < 5 && !$is_name && $edit_user_data_name )
							$user_name_value = $edit_user_data_name;
						else if ( $status_code < 5 && $is_name && $data_user_name )
							$user_name_value = $data_user_name;
						else
							$user_name_value = "";

						echo "<input type='text' class='form-control' name='user_name' value='$user_name_value'>";
						?>
						<span class="material-input"></span>
					</div>
					<div class="form-group label-floating">
						<label class="control-label">Пользовательский HWID</label>
						<?php

						if ( $status_code < 5 && !$is_hwid && $edit_user_data_hwid )
							$user_hwid_value = $edit_user_data_hwid;
						else if ( $status_code < 5 && $is_hwid && $data_user_hwid )
							$user_hwid_value = $data_user_hwid;
						else
							$user_hwid_value = "";

						echo "<input type='text' class='form-control' name='user_hwid' value='$user_hwid_value'>";
						?>
						<span class="material-input"></span>
					</div>

					<script>
						function ChangeUserDate()
						{
							var today = new Date(document.getElementById('static_user_date').value);

							var self = document.getElementById("user_date_type");
							var self_value = self.options[self.selectedIndex].value;

							if ( self_value == "1" )
								today.setDate(today.getDate() + 1);
							else if ( self_value == "2" )
								today.setDate(today.getDate() + 5);
							else if ( self_value == "3" )
								today.setDate(today.getDate() + 10);
							else if ( self_value == "4" )
								today.setDate(today.getDate() + 30);
							else if ( self_value == "5" )
								today.setFullYear(today.getFullYear() + 10);

							var dd = today.getDate();
							var mm = today.getMonth() + 1;
							var yyyy = today.getFullYear();

							if( dd < 10 ){ dd = '0' + dd; }
							if( mm < 10 ){ mm = '0' + mm; }

							today = dd+'.'+mm+'.'+yyyy;

							document.getElementById('user_date').value = today;
							document.getElementById('user_date_class').className = "form-group label-floating is-focused";
						}
					</script>

					<div class="form-group label-floating">
						<label class="control-label">Длительность подписки</label>
						<select class="form-control" name="user_date_type" id="user_date_type" OnChange="ChangeUserDate()">
							<option value="0" ></option>
							<option value="1" >1 день</option>
							<option value="2" >5 дней</option>
							<option value="3" >10 дней</option>
							<option value="4" >1 месяц</option>
							<option value="5" >Бессрочно (10 лет)</option>
						</select>
					</div>

					<?php

					if ( $status_code < 5 && !$is_date && $edit_user_data_date )
						$user_date_value = $edit_user_data_date;
					else if ( $status_code < 5 && $is_date && $data_user_date )
						$user_date_value = $data_user_date;
					else
						$user_date_value = "";

					if ( $user_date_value )
					{
						if ( !ValidLicense($user_date_value) )
							$new_user_date_value = date("d.m.Y");
						else
							$new_user_date_value = $user_date_value;

						$DateTimeFormat = DateTime::createFromFormat('d.m.Y', $new_user_date_value);
						$JsDateTimeFormat = $DateTimeFormat->format('Y.m.d');
						
						echo "<input type='hidden' id='static_user_date' value='$JsDateTimeFormat'/>";
					}
					else
						echo "<input type='hidden' id='static_user_date' value=''/>";
					
					$current_path = parse_url("http://". $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
					$user_update_url = "http://".$current_path['host'].$current_path['path']."update.php?update=".base64_encode($user_hwid_value);

					?>

					<div class="form-group label-floating"  id="user_date_class">
						<label class="control-label">Дата окончания подписки</label>
						<?php
							echo "<input type='text' class='form-control' name='user_date' id='user_date' value='$user_date_value'>";
						?>
						<span class="material-input"></span>
					</div>
					
					<div class="form-group label-floating"  id="user_date_class">
						<label class="control-label">Ссылка для скачки билда</label>
						<?php
							echo "<input type='text' class='form-control' value='$user_update_url'>";
						?>
						<span class="material-input"></span>
					</div>
					
					<div class="form-group">
						<input type="submit" class="btn btn-raised btn-success" value="сохранить">
						<?php
						if ( $status_code < 5 && $is_id && $data_user_id )
							echo "<a class='btn btn-raised btn-danger' href='?page=users&delete=$data_user_id' role='button'>удалить пользователя</a>";
						?>
					</div>
					
				</div>
			</form>
		</div>
	</div>
</body>
</html>