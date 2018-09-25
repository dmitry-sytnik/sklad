<?php require_once("../../includes/session.php"); ?>
<?php require_once("../../includes/db_connection.php"); ?>
<?php require_once("../../includes/functions.php") ?>
<?php //require_once("../../includes/validation_functions.php") ?><?php	// Все админские страницы настроены на редирект с них как вошедшего, так и невошедшего пользователя. Только эта страница имеет доступ, и то, только для невошедшего пользователя.

		if (logged_in_user()){ // если  сюда пришел вошедший пользователь (пользователь с сессией)
			redirect_to("index.php"); // возвращаем его на главную страницу для пользователей
		}
		if (logged_in_manager()){ // если  сюда пришел вошедший менеджер (менеджер с сессией)
			redirect_to("index.php"); // возвращаем его на главную страницу для пользователей
		}
?><?php
$username = "";

if (isset($_POST['submit'])) {
  // Process the form
  
  // validations
  //$required_fields = array("username", "password");
  //validate_presences($required_fields);  
  
  if (empty($errors)) {
    // Пробуем войти
	
	$username = $_POST["username"];
	$password = $_POST["password"];

	$found_admin = attempt_login_admin($username, $password); // array
	
    if ($found_admin) {
      // Success
	  // Пометить пользователя как вошедшего     
	  $_SESSION["admin_id"] = $found_admin["id"];
	  $_SESSION["username"] = $found_admin["username"];
      redirect_to("index.php"); // При входе, отправляем сюда
    } else {
      // Failure
      $_SESSION["message"] = "Username/password not found.";
    }
  }
} else {
  // This is probably a GET request
  
} // end: if (isset($_POST['submit']))

?>

<?php //$layout_context = "admin";?>
<?php include("../../includes/layouts/header.php") ?>

	<div id="page"> 	
	<br>
 
		<?php echo message(); ?>
		<?php //echo form_errors($errors); ?>
		
		<h2>Login admin</h2>
		<form action="login_admin.php" method="post">
		   <p>Username: <input type="text" name="username" value="<?php echo htmlentities($username);?>" /></p>
		   <p>Password: <input type="password" name="password" value="" /></p>
			<input type="submit" name="submit" value="Log in" />
		</form>
	 
	 </div> <!-- конец page -->
	</div> 	<!-- конец main -->
	 
<?php include("../../includes/layouts/footer.php"); ?>