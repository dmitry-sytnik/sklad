<?php require_once("../includes/session.php"); ?>
<?php require_once("../includes/db_connection.php"); ?>
<?php require_once("../includes/functions.php") ?>
<?php //require_once("../includes/validation_functions.php") ?>
<?php
$username = "";

if (logged_in_manager()){
			redirect_to("index.php"); // если менеджер уже вошел, редирект
		}
		
if (logged_in_user()){
			redirect_to("index.php"); // если юзер уже вошел, редирект
		}


if (isset($_POST['submit'])) {
  // Process the form
  
  // validations
  //$required_fields = array("username", "password");
  //validate_presences($required_fields);  
  
  if (empty($errors)) { // errors объявляется в файле сессии как функция, а также в validation_functions.php как массив, где и добавляются в него различные значения.
    // Пробуем войти
	
	$username = $_POST["username"];
	$password = $_POST["password"];

	
	
	$found_user = attempt_login_user($username, $password); // array
	
    if ($found_user) {
      // Success
	  // Пометить пользователя как вошедшего     
	  $_SESSION["user_id"] = $found_user["id"];
	  $_SESSION["username"] = $found_user["username"];
      redirect_to("index.php"); // ПРИ Входе, отправляем сюда
    } else { 
				// если не нашли пользователя, проверяем, может это пытается войти менеджер
	
				$found_manager = attempt_login_manager($username, $password); // array
	
				if ($found_manager) {
				  // Success
				  // Пометить пользователя как вошедшего     
				  $_SESSION["manager_id"] = $found_manager["id"];
				  $_SESSION["managername"] = $found_manager["username"];
				  redirect_to("index.php"); // При входе, отправляем сюда
				} else {
				  // Failure
				  // $_SESSION["message"] = "Username/password not found.";
				}
		
      // Failure
      $_SESSION["message"] = "Username/password not found."; 
    }
	
	
	
  } // конец if empty errors
} // конец if (isset($_POST['submit']))
	else {
  // This is probably a GET request
  
} // end: if (isset($_POST['submit']))

?><?php include("../includes/layouts/header.php") ?>

	<div id="page"> 	
	<br>
 
		<?php echo message(); ?>
		<?php //echo form_errors($errors); ?>
		
		<h2>Login User</h2>
		<form action="login.php" method="post">
		   <p>Username: <input type="text" name="username" value="<?php echo htmlentities($username);?>" /></p>
		   <p>Password: <input type="password" name="password" value="" /></p>
			<input type="submit" name="submit" value="Log in" />
		</form>
	 
	 </div> <!-- конец page -->
	</div> 	<!-- конец main -->
	 
<?php include("../includes/layouts/footer.php"); ?>