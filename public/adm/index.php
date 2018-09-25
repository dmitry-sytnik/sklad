<?php require_once("../../includes/session.php"); ?>
<?php require_once("../../includes/db_connection.php"); ?>
<?php require_once("../../includes/functions.php"); // здесь заложена навигация ?><?php 
	 // $found_admin[] = "";
	if(isset($_SESSION["admin_id"])) { // Если задана сессия админа,
	$layout_context = "admin"; 			// то layout_context = admin
	 } else {  // Иначе
	redirect_to("../index.php");} // если  сюда пришел пользователь без $_SESSION["admin_id"], он будет перенаправлен
?><?php 
	$admininfo = "";
?><?php include("../../includes/layouts/header.php"); ?>

 
	<div id="page"> 
	</br>
	<h3>Welcome to admin's Poisk po skladu!</h3>

<?php echo message();?>


	</div> <!-- конец page -->
</div> 	<!-- конец main -->

<?php include("../../includes/layouts/footer.php"); ?>
	