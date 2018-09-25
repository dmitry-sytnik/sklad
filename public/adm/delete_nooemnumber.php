<?php require_once("../../includes/session.php"); ?>
<?php require_once("../../includes/db_connection.php"); ?>
<?php require_once("../../includes/functions.php"); // здесь заложена навигация?>
<?php require_once("../../includes/validation_functions.php"); ?>
<?php 	
	if(isset($_SESSION["admin_id"])) {	// Если задана сессия админа,
	$layout_context = "admin";			// то $layout_context = admin
	} else {
	redirect_to("../tecdoc_poisk.php");} // если  сюда пришел пользователь без $_SESSION["admin_id"], он будет перенаправлен
	
	/*$current_stroka = find_oemstroku_by_id();
	if (!$current_stroka) {
		redirect_to("tecdoc_poisk_admin.php");
	}*/
	
	
	if (isset($_GET["brand"])) {
	$brand = $_GET["brand"]; 
	$_SESSION["brand"] = $brand;
	// $company = $_SESSION["brand"];
	//$session_brand = $_SESSION["brand"];
	} else {
		//$_SESSION["brand"] = null;
	}
	
	if (isset($_GET["id"])) {
	$id = $_GET["id"]; 
	$_SESSION["id"] = $id;
	// $company = $_SESSION["brand"];
	//$session_brand = $_SESSION["brand"];
	} else {
		//$_SESSION["brand"] = null;
	}

	if (isset($_GET["id"])) {
	$oemstroka = find_nooemstroku_by_GET_id(); }
	
	$safe_brand = mysqli_real_escape_string($connection, $_SESSION["brand"]);
	$safe_id = mysqli_real_escape_string($connection, $_SESSION["id"]);
?><?php	

		if (isset($_POST['delete'])){
	
			//$id = $oemstroka["id"];
	
			$query = "DELETE FROM {$safe_brand}_nooem WHERE id = {$safe_id} LIMIT 1";
			$result = mysqli_query($connection, $query);
			
			if ($result && mysqli_affected_rows($connection) == 1) {
				// Success
					$_SESSION["message"] = "NoOemstroka deleted.";
					// redirect_to("tecdoc_poisk_admin.php");		
			}   else {
					// Неудача
					$_SESSION["message"] = "NoOemstroka deletion failed."."(SQL state: ".mysqli_sqlstate($connection)."). Error: ".mysqli_errno($connection).". ".mysqli_error($connection);
					// redirect_to("tecdoc_poisk_admin.php");		
				}		
		}	
?><?php include("../../includes/layouts/header.php"); ?>
<div id="page"> 

<?php  
	
	echo form_errors($errors);
	echo message();
?>	
	<div id="form">
	<form id="delete_nooemnumber" name="delete_nooemnumber" method="post" action="delete_nooemnumber.php">
		<label></label><br/><br/>
		     id: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <label><?php if (isset($oemstroka)){echo urlencode($oemstroka["id"]);}?></label><br/><br/>
			  NAME_PARTS: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <label><?php if (isset($oemstroka)) {echo htmlspecialchars($oemstroka["NAME_PARTS"]);}?></label><br/><br/>
			  mainART_BRANDS:  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <label><?php if (isset($oemstroka)){echo htmlentities($oemstroka["mainART_BRANDS"]);}?></label><br/><br/>
			  mainART_CODE_PARTS: &nbsp;&nbsp;
			  <label><?php if (isset($oemstroka)){echo htmlentities($oemstroka["mainART_CODE_PARTS"]);}?></label><br/><br/>
			  TTC_ART_ID:  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <label><?php if (isset($oemstroka)){echo htmlentities($oemstroka["TTC_ART_ID"]);}?></label><br/><br/>
			  BRANDS:  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <label><?php if (isset($oemstroka)){echo htmlentities($oemstroka["BRANDS"]);}?></label><br/><br/>
			  CODE_PARTS: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <label><?php if (isset($oemstroka)){echo htmlentities($oemstroka["CODE_PARTS"]);}?></label><br/><br/>
			  CODE_PARTS_ADVANCED:  
			  <label><?php if (isset($oemstroka)){echo htmlentities($oemstroka["CODE_PARTS_ADVANCED"]);}?></label><br/><br/>
			  
			  <input type="submit" name="delete" id="delete" value="Удалить" /><br/><br/>

	
	
	</form>
	</div>
	<br/><br/>
<a href="" onclick="window.close();">Close</a>	<!-- Пока что работает кривовато. При прямом входе на страницу - не закрывает вкладку. Но при прямом входе и нет бренда для добавления. При переходе сюда по ссылке, закрывает нормально. Само по себе закрытие не уничтожает сессию бренда, а просто закрывает страницу. И если заходить напрямую - сессии нет согласно файлу php, а не потому, что была закрыта страница. -->		
									
</div> <!-- конец page -->
	</div> 	<!-- конец main --> 

<?php include("../../includes/layouts/footer.php"); ?>
	
	
	