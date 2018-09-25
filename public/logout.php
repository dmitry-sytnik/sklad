<?php require_once("../includes/session.php") ?>
<?php require_once("../includes/functions.php") ?>
<?php
	// версия 1: простой выход
	// должна быть запущена сессия: session_start();
	$_SESSION["user_id"] = null;
	$_SESSION["username"] = null;
	
	
	$_SESSION["manager_id"] = null;
	$_SESSION["managername"] = null;
	redirect_to("index.php");
?><?php
/*
	// версия 2: удаление сессии
	// удаление сессии полностью вместе с ссылками куков на неё
	
	session_start();  //должна быть запущена сессия
	$_SESSION = array(); // сессия превращается в пустой массив, то есть стираются все значения из неё
	if (isset($_COOKIE[session_name()])){	//если мы имеем куки с именем сессии
		setcookie(session_name(), '', time()-42000, '/'); // то задать куки: с этим именем, без значений, 
														// и переместить их таковыми назад в прошлое
														// с целью удаления по истечению сроков хранения
		}
	session_destroy(); // удалить сессию на сервере
	redirect_to("login.php");
*/
?>