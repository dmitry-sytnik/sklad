<?php 
	if (!isset($layout_context)){
		$layout_context = "public";
	}
	//$layout_context = "manager";	
?>
<!DOCTYPE html>

<html lang="ru">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Poisk po skladu <?php 
				if ($layout_context == "admin") {echo "Admin";}
				if ($layout_context == "manager") {echo "Manager";} ?>
		</title>
		
		<link rel="stylesheet" type="text/css" href="css/tablesorter.css"> 					
		<link href="css/public.css" media="all" rel="stylesheet" type="text/css" />
		
		
		<script type="text/javascript" src="javascript/jquery-latest.js"></script>
		<script type="text/javascript" src="javascript/jquery.tablesorter.min.js"></script>
	
	<!-- Подсветка пункта меню. Работает только при идентичном совпадении того, что в адресной строке и ссылки "a href". 
		Неправильно работает, если ее поставить после функций сортировки таблицы: не работает в отношении странички, где таблица будет заведомо отсортирована-->
	<script type="text/javascript">
	$(document).ready(function() {
		$('.subjects a').each(function() {
        if ('http://sklad/public/'+$(this).attr('href') == window.location.href)
        {
            $(this).addClass('selected');
        }
		if ('http://sklad/public/adm/'+$(this).attr('href') == window.location.href)
        {
            $(this).addClass('selected');
        }
		});
	}); 
	</script>
	
	<!-- Функции сортировки таблицы. -->
	<script type="text/javascript">
	$(document).ready(function(){
  	$("#table").tablesorter(); 
	});
	$(document).ready(function(){
  	$("#crosstable").tablesorter({sortList: [[0,0]]}); // Сортировка первого[0] столбца по возрастанию[0]
	});
	</script>
	
	</head>
	
<body>
	
	<!-- Шапка -->
	<div id="mainheader">
		<div id="headerlh">
		<br/>
		
		<h2><a href="index.php">Poisk po skladu 
					<?php if ($layout_context == "admin") {echo "<br/><br/>"; echo " Admin";}?>
					<?php if ($layout_context == "manager") {echo "<br/><br/>"; echo " Manager";}?>
		</a></h2>
		</div>
		<div id="headerrh"> 
		<br/>
		
<!-- -->	
<?php // если админ в сессии, то показывать Log out admin, иначе ничего не делать 
if (isset($_SESSION["admin_id"])) { ?>
	<p style="text-align:right;"><a href="logout_admin.php" style="color: #D4E6F4;">Log out admin</a>&nbsp;<p>
<?php } else {  } ?>

<!-- -->
<?php // если пользователь в сессии, то показывать Log out для пользователей,  иначе ничего не делать  
if (isset($_SESSION["user_id"])) { ?>
	<p style="text-align:right;"><a href="logout.php" style="color: #D4E6F4;">Log out user</a>&nbsp;<p>
<?php } else { }?>

<?php // если менеджер в сессии, то показывать Log out для менеджеров,  иначе ничего не делать  
if (isset($_SESSION["manager_id"])) { ?>
	<p style="text-align:right;"><a href="logout.php" style="color: #D4E6F4;">Log out manager</a>&nbsp;<p>
<?php } else { }?>

<?php // если нет ни админа, ни менеджера, ни юзера в сессии, то показывать Log in (для пользователей)
if (!isset($_SESSION["admin_id"]) && !isset($_SESSION["user_id"]) && !isset($_SESSION["manager_id"])) { ?>
	<p style="text-align:right;"><a href="login.php" style="color: #D4E6F4;">Log in</a>&nbsp;<p>
<?php } ?>

		
		</div>	
	</div>
	
	<div id="line">
	</div>
	
	<!-- Навигация, страница, реклама -->
	 <div id="main">
	
	
	
	<div id="navigation">
			<?php echo public_navigation(); ?>
		</div>   
	<div id="reklama">
		ads
		</div>
	
	