<?php /* 
 Файл предназначен для пользователей, вошедших и невошедших, а также для менеджеров*/

?><?php require_once("../includes/session.php"); ?>
<?php require_once("../includes/db_connection.php"); ?>
<?php require_once("../includes/functions.php"); // здесь заложена навигация ?>
<?php 
	 // $found_admin[] = "";
	if(isset($_SESSION["manager_id"])) {
	$layout_context = "manager"; 
	 } else {
	$layout_context = "public";}
?>
<?php 
	$manageinfo = "";
?>
<?php include("../includes/layouts/header.php"); ?>

 
	<div id="page"> 
	<br>
	
	<h3>Welcome to Poisk po skladu!</h3>



	</div> <!-- конец page -->
</div> 	<!-- конец main -->

<?php include("../includes/layouts/footer.php"); ?>
	