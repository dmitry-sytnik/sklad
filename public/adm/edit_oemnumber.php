<?php require_once("../../includes/session.php"); ?>
<?php require_once("../../includes/db_connection.php"); ?>
<?php require_once("../../includes/functions.php"); // здесь заложена навигация?>
<?php require_once("../../includes/validation_functions.php"); ?>
<?php 	
	if(isset($_SESSION["admin_id"])) { 	// Если задана сессия админа,
	$layout_context = "admin";			// то $layout_context = admin
	} else {
	redirect_to("../tecdoc_poisk.php");} // если  сюда пришел пользователь без $_SESSION["admin_id"], он будет перенаправлен
	
		
	if (isset($_GET["brand"])) {
	$brand = $_GET["brand"]; 
	$_SESSION["brand"] = $brand;
	// $company = $_SESSION["brand"];
	//$session_brand = $_SESSION["brand"];
	} else {
		//$_SESSION["brand"] = null;
	}
?><?php
		if (isset($_POST['edit_oemsubmit'])){
		// Если отправлено, то обработка формы
		
		// валидации
			$required_fields = array("mainART_CODE_PARTS", "BRANDS", "CODE_PARTS");	
			validate_presences($required_fields);	
			
			$fields_with_max_lengths = array("CODE_PARTS" => 30);
			validate_max_lengths($fields_with_max_lengths);
			
			$fields_with_min_lengths = array("CODE_PARTS" => 3); //Здесь знак присвоения, а не математическое больше или равно. Скрипт PHP Работает только если вбиваемый артикул будет больше трёх знаков.
			validate_min_lengths($fields_with_min_lengths);

			$fields_with_min_lengths = array("mainART_CODE_PARTS" => 3);
			validate_min_lengths($fields_with_min_lengths);
			
				if (empty($errors)){
		
				//Если errors пуста, выполнить обновление из пост-запроса
				
				$id = mysql_prep($_POST["id"]);
				$name_parts = mysql_prep($_POST["NAME_PARTS"]);
				$mainart_brands = mysql_prep($_POST["mainART_BRANDS"]);
				$mainart_code_parts = mysql_prep($_POST["mainART_CODE_PARTS"]);	
				$ttc_art_id = mysql_prep($_POST["TTC_ART_ID"]);	
				$brands = mysql_prep($_POST["BRANDS"]);	
				$code_parts = mysql_prep($_POST["CODE_PARTS"]);	
				$code_parts_advanced = mysql_prep($_POST["CODE_PARTS_ADVANCED"]);

				$safe_brand = mysqli_real_escape_string($connection,$_SESSION["brand"]);
				
				// 2. Perform database query
				$query  = "UPDATE {$safe_brand}_oem SET";
				$query .= " NAME_PARTS = '{$name_parts}',";
				$query .= " mainART_BRANDS = '{$mainart_brands}',";
				$query .= " mainART_CODE_PARTS = '{$mainart_code_parts}',";
				$query .= " TTC_ART_ID = '{$ttc_art_id}',";
				$query .= " BRANDS = '{$brands}',";
				$query .= " CODE_PARTS = '{$code_parts}',";
				$query .= " CODE_PARTS_ADVANCED = '{$code_parts_advanced}'";
								
				$query .= " WHERE id = {$id}"; 
				$query .= " LIMIT 1";	
				
				$result = mysqli_query($connection, $query);

				if ($result && mysqli_affected_rows($connection) == 1) {
					// Success
					$_SESSION["message"] = "Oem stroka updated.";
					//redirect_to("manage_content.php");		
				} else {
					// Failure
					$_SESSION["message"] = "Oem stroka update failed."."(SQL state: ".mysqli_sqlstate($connection)."). Error: ".mysqli_errno($connection).". ".mysqli_error($connection);				
				}
			} else { // Иначе, если были ошибки
				
				
			}//конец if (empty($errors))
				
		} 	else {
				// Иначе, в случае GET запроса, 
				// введённого напрямую в адресную строку url 
				// ничего не делать и выводить всё последующее
			 
		}   // конец: if (isset($_POST['submit']))
			
		
			if (isset($_POST['new_oemsubmit'])){
			// Если отправлено, то обработка формы
				
			// валидации
			$required_fields = array("mainART_CODE_PARTS", "BRANDS", "CODE_PARTS");	
			validate_presences($required_fields);	
					
			$fields_with_max_lengths = array("CODE_PARTS" => 30);
			validate_max_lengths($fields_with_max_lengths);
					
			$fields_with_min_lengths = array("CODE_PARTS" => 3); //Здесь знак присвоения, а не математическое больше или равно. Скрипт PHP Работает только если вбиваемый артикул будет больше трёх знаков.
			validate_min_lengths($fields_with_min_lengths);

			$fields_with_min_lengths = array("mainART_CODE_PARTS" => 3);
			validate_min_lengths($fields_with_min_lengths);
			
					if (empty($errors)){
				
					//Если errors пуста, выполнить обновление из пост-запроса
						
						
					$name_parts = mysql_prep($_POST["NAME_PARTS"]);
					$mainart_brands = mysql_prep($_POST["mainART_BRANDS"]);
					$mainart_code_parts = mysql_prep($_POST["mainART_CODE_PARTS"]);	
					$ttc_art_id = mysql_prep($_POST["TTC_ART_ID"]);	
					$brands = mysql_prep($_POST["BRANDS"]);	
					$code_parts = mysql_prep($_POST["CODE_PARTS"]);	
					$code_parts_advanced = mysql_prep($_POST["CODE_PARTS_ADVANCED"]);

					$safe_brand = mysqli_real_escape_string($connection,$_SESSION["brand"]);
						
					// 2. Perform database query
					$query  = "INSERT INTO {$safe_brand}_oem (";
					$query .= " NAME_PARTS,";
					$query .= " mainART_BRANDS,";
					$query .= " mainART_CODE_PARTS,";
					$query .= " TTC_ART_ID,";
					$query .= " BRANDS,";
					$query .= " CODE_PARTS,";
					$query .= " CODE_PARTS_ADVANCED";
					$query .= ") VALUES (";
					$query .= "  '{$name_parts}', '{$mainart_brands}', '{$mainart_code_parts}', '{$ttc_art_id}', '{$brands}', '{$code_parts}', '{$code_parts_advanced}'";
					$query .= ")";
						
					$result = mysqli_query($connection, $query);

					if ($result && mysqli_affected_rows($connection) >= 0) {
						// Success
						$_SESSION["message"] = "Oem stroka created.";
						//redirect_to("manage_content.php");		
					} else {
							// Failure
							$_SESSION["message"] = "Oem stroka creation failed."."(SQL state: ".mysqli_sqlstate($connection)."). Error: ".mysqli_errno($connection).". ".mysqli_error($connection);				
					}
					} else { // Иначе, если были ошибки при добавлении новоой строки в базу данных
				
						
				
					
					}//конец if (empty($errors))
			} 	else {
						// Иначе, в случае GET запроса, 
						// введённого напрямую в адресную строку url 
						// ничего не делать и выводить всё последующее
					 
			}   // конец: if (isset($_POST['new_oemsubmit']))

?><?php 
	// function find_oemstroku_by_id() была тут
	
	
	function find_new_oemstroku($brands, $code_parts, $mainart_code_parts) { // Эта функция работает только для уже созданных строк. Для той, которую только пытаемся создать, но например, не смогли, ничего не будет возвращено . 
		global $connection;
		global $query2;

		
		$safe_oembrand = mysqli_real_escape_string($connection, $_SESSION["brand"]);
		
		$query2  = "SELECT * ";
		$query2 .= "FROM {$safe_oembrand}_oem ";
		$query2 .= "WHERE BRANDS = '{$brands}' AND ";
		$query2 .= " CODE_PARTS = '{$code_parts}' AND ";
		$query2 .= " mainART_CODE_PARTS = '{$mainart_code_parts}' ";
		$query2 .= "LIMIT 1";
		$newoem_set = mysqli_query($connection, $query2);
		// Test if there was a query error
		 //ec = "<div id=\"form\">".$query2."<hr/><div id=\"form\">";
		 
		 
		if (!$newoem_set){
		die("NOT newoem_set in the func(find_new_oemstroku).Database do not returned anything.");}
		
		/*if (!mysqli_affected_rows($connection) == 1) {		
			$_SESSION["message"] = "Not find stroka for edition.";		
			// redirect_to("index_admin.php");	
			} */
		
		if ($oemstroka = mysqli_fetch_assoc($newoem_set)) {
			return $oemstroka;
		} else {
			return null;
		}
	}

?><?php include("../../includes/layouts/header.php"); ?>
<div id="page"> 

<?php   
		
	echo form_errors($errors);
	echo message();
	
	//$brands = "";
	//$code_parts = "";
	//$mainart_code_parts = "";
	
	/////////
	// Блок для предзаполнения полей формы
	/////////
	if (isset($_GET['id'])) { // Если получали Гет id, то берем значения из гет-запроса
	$oemstroka = find_oemstroku_by_GET_id(); 
	} elseif (isset($_POST['new_oemsubmit'])) {  // Иначе, если получили POST new_oemsubmit - получаем значения 
		if (empty($errors)){ // если нет ошибок - из новой строки
		$oemstroka = find_new_oemstroku($brands, $code_parts, $mainart_code_parts); //В этой функции делается SELECT, чтобы сделать выбор получившейся строки и в итоге получить массив $oemstroka для предзаполнения полей в форме. 
			
			} else { // а если есть ошибки - из существующего в полях id
				$oemstroka = find_oemstroku_by_POST_id();
			}  
		//print_r($oemstroka);
	} else { // Иначе, если это был POST edit_oemsubmit
		
		if (isset($_POST['edit_oemsubmit'])) { // Получаем значения из имеющегося в полях post id
			$oemstroka = find_oemstroku_by_POST_id();
		}
		
	}	
	//print_r ($oemstroka);
?>
	<div id="form">
<?php	if(isset($query)) {echo $query;} 
		echo "<br/>";
		echo "<br/>";
		if(isset($query2)) {echo $query2;} 

?>
	<form id="edit_oemnumber" name="edit_oemnumber" method="post" action="edit_oemnumber.php">
			<br/>
			  Редактировать оем кросс-номер <br/><br/>
			  id: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <input type="text" name="id" id="id" value="<?php echo urlencode($oemstroka["id"])?>" readonly/><br/><br/>
			  NAME_PARTS: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <input type="text" name="NAME_PARTS" id="NAME_PARTS" value="<?php echo htmlspecialchars($oemstroka["NAME_PARTS"])?>"/><br/><br/> 
			  mainART_BRANDS:  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <input type="text" name="mainART_BRANDS" id="mainART_BRANDS" value="<?php echo htmlentities($oemstroka["mainART_BRANDS"])?>"/><br/><br/>
			  mainART_CODE_PARTS: &nbsp;&nbsp;
			  <input type="text" name="mainART_CODE_PARTS" id="mainART_CODE_PARTS" value="<?php echo htmlentities($oemstroka["mainART_CODE_PARTS"])?>"/><br/><br/>
			  TTC_ART_ID:  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <input type="text" name="TTC_ART_ID" id="TTC_ART_ID" value="<?php echo htmlentities($oemstroka["TTC_ART_ID"])?>"/><br/><br/>
			  BRANDS:  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <input type="text" name="BRANDS" id="BRANDS" value="<?php echo htmlentities($oemstroka["BRANDS"])?>"/><br/><br/>
			  CODE_PARTS: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <input type="text" name="CODE_PARTS" id="CODE_PARTS" value="<?php echo htmlentities($oemstroka["CODE_PARTS"])?>"/><br/><br/>
			  CODE_PARTS_ADVANCED:  
			  <input type="text" name="CODE_PARTS_ADVANCED" id="CODE_PARTS_ADVANCED" value="<?php echo htmlentities($oemstroka["CODE_PARTS_ADVANCED"])?>"/><br/><br/>
			  <input type="submit" name="edit_oemsubmit" id="edit_oemsubmit" onclick="return confirm('Редактировать этот?');" value="Редактировать этот" /><br/><br/>
			  <input type="submit" name="new_oemsubmit" id="new_oemsubmit" value="Создать Новый" /><br/><br/>
			  
	</form>
	</div>
	<br/><br/>
<a href="" onclick="window.close();">Close</a>	<!-- Пока что работает кривовато. При прямом входе на страницу - не закрывает вкладку. Но при прямом входе и нет бренда для добавления. При переходе сюда по ссылке, закрывает нормально. Само по себе закрытие не уничтожает сессию бренда, а просто закрывает страницу. И если заходить напрямую - сессии нет согласно файлу php, а не потому, что была закрыта страница. -->		
									
</div> <!-- конец page -->
	</div> 	<!-- конец main --> 

<?php include("../../includes/layouts/footer.php"); ?>