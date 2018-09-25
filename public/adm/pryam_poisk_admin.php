<?php /* Файл использует поиск с кроссами в таблице baza по столбцу crossnum. 
Если в столбце не будет искомого артикула (даже если он есть в соседнем столбце headartikul), 
ничего не будет найдено. Поиск регистронезависимый.
 crossnum в baze должен быть "чистый": без слэшей, без пробелов, точек и т.д. Регистр не имеет значения, разве что только для красоты и однообразия. */

?>
<?php require_once("../../includes/session.php"); ?>
<?php require_once("../../includes/db_connection.php"); ?>
<?php require_once("../../includes/functions.php"); // здесь заложена навигация?> 

<?php 
	 // $found_admin[] = "";
	if(isset($_SESSION["admin_id"])) {
		$layout_context = "admin";
	} else {
	redirect_to("../pryam_poisk.php");} // если  сюда пришел пользователь с сессией, как и без сессии, то у него нет $_SESSION["admin_id"], следовательно, он будет перенаправлен
?>

<?php 
	$admininfo = "";
?>

<?php include("../../includes/layouts/header.php"); ?>


 
 <div id="page">

 
 </br>
<form id="poisk" name="poisk" method="post" action="pryam_poisk_admin.php">
  Поиск по прямому Артикулу &nbsp;
  <input type="text" name="artikul" id="artikul" /> &nbsp;
  <input type="submit" name="submit" id="submit" value="Найти" /> &nbsp;
  <input type="checkbox" name="nuli" id="nuli"/> Показать и то, чего нет в наличии
</form>

<table id="table" class="tablesorter" >
			  <thead>
			  <tr>
				<th><strong>Firma</strong></th>
				<th><strong>Artikul</strong></th>
				<th><strong>Naimenovanie</strong></th>
				<th><strong>Kolvo</strong></th>
				<th><strong>RUB</strong></th>
				<th><strong>Magazin</strong></th>
				<th><strong>id</strong></th>
				<th><strong>For</strong></th>
			  </tr>
			  </thead>
			  <tbody>

<?php

    if (isset($_POST['submit'])) {	
	
	$prep_artikul = mysql_crossnum_prep($_POST['artikul']);	
	
	$query  = "SELECT * ";
	$query .= "FROM _ostatki ";
	$query .= "WHERE hash_artikul = '{$prep_artikul}' "; // Здесь даже введённый с большими/маленькими буквами запрос без разницы сравнится с хэш-артикулом записанным как большими, так и маленькими буквами. То есть поиск регистронезависимый, насколько удалось протестировать.
	if(!isset($_POST['nuli'])) { // если галочку не отмечали (по умолчанию), то показывать всё, что в наличии
	$query .= " AND kolvo > 0";}
	$artikulov_nabor = mysqli_query($connection, $query);
		// Test if there was a query error
	confirm_query($artikulov_nabor);			  
?>
			  
<?php   while($result = mysqli_fetch_assoc($artikulov_nabor)) {  ?>
<tr><td>
	<?php echo htmlentities($result["firma"]);?>
	</td>			
	<td>				
	<?php echo htmlentities($result["artikul"]);?>				
	</td>
	<td>
	<?php echo htmlspecialchars($result["naimenovanie"]);?>
	</td>
	<td>
	<?php echo htmlentities($result["kolvo"]);?>
	</td>
	<td>
	<?php echo htmlentities($result["zakup"]);?>
	</td>
	<td>
	<?php echo htmlentities($result["postavschik"]);?>
	</td>
	<td>
	<?php echo htmlentities($result["id"]);?>
	</td>
	<td>
	
	</td>
	
 </tr>
 
	<?php	} 	// конец цикла while($result = mysqli_fetch_assoc($artikulov_nabor))
		
		mysqli_free_result($artikulov_nabor);
	}	// конец if (isset($_POST['submit']))	  
	 ?>
	
	</tbody>		
	</table>	

	
	</div> <!-- конец page -->
	</div> 	<!-- конец main -->
	

<?php include("../../includes/layouts/footer.php"); ?>
	