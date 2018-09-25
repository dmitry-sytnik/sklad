<?php

	session_start();
	
	function message(){
		if(isset($_SESSION["message"])){
		  $output = "<div class=\"message\">";
		  $output.= htmlentities($_SESSION["message"]);
		  $output.= "</div>";
		  
		  // Очистка сообщения
		  $_SESSION["message"] = null;
	      return $output;
		}
	}
	
	function errors(){
		if(isset($_SESSION["errors"])){
		   $errors = $_SESSION["errors"];
				  
		  // После использования очистить сообщение.
		  // Для этого в php.ini должны быть выключены (Off)
		  // или вообще отсутствовать как таковые параметры
		  // session.bug_compat_42 и session.bug_compat_warn
		  $_SESSION["errors"] = null;
		  // И вернуть $errors
	      return $errors;
		}
	}
	
	
?>