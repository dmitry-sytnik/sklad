<?php /* Необходимо 1) наличие таблицы baza 2)  в ней - наличие вбиваемого кросса с названием производителя и самим артикулом 3) наличие текдоковской таблицы под эту конкретную фирму 4) таблица с остатками.
 Если номерок есть в базе и остатках, но таблицы текдока под него не существует, то результат выводится как поиск (не отточено) по прямому артикулу. Этим занимаются последние запросы, так как они напрямую просматривают вбитый артикул в остатках. В этом есть и проблема, так как под оригинальные номера таблиц текдока вроде бы нет. Это значит, что, например, вбитый 15208-65F0С будет искать только его в остатках и ничего больше.
*/

?><?php require_once("../includes/session.php"); ?>
<?php require_once("../includes/db_connection.php"); ?>
<?php require_once("../includes/functions.php"); // здесь заложена навигация?>
<?php 
	 // $found_admin[] = "";
	if(isset($_SESSION["manager_id"])) {
	$layout_context = "manager";} 
	else
	{$layout_context = "public";}
?>
<?php 
	$manageinfo = "";
?>
<?php include("../includes/layouts/header.php"); ?>


 
 <div id="page"> 
 <br>

<form id="crossnumpoisk" name="crossnumpoisk" method="post" action="tecdoc_poisk.php">
  Поиск по кроссам Текдока &nbsp;
  <input type="text" name="artikul" id="artikul" /> &nbsp;
  <input type="submit" name="crossnumsubmit" id="crossnumsubmit" value="Найти" />  &nbsp;
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
				<th><strong>M</strong></th>
			  </tr>
			  </thead>
			  <tbody>

<?php
if (isset($_POST['crossnumsubmit'])) {
	
	$prep_artikul = mysql_crossnum_prep($_POST['artikul']);
		
	if (empty($prep_artikul)) {echo "- Ничего не вбито<br>";} // не обязательно делать else: просто при истинном условии добавляется сообщение.
		
		$manageinfo.= "<br>";
		$manageinfo.= "----- Служебная информация -----";
		$manageinfo.= "<br>";
		
		
		
			// 1. Выясняем количество совпадений по базе со вбитым артикулом
			$query0 = "SELECT COUNT(*)"; 
			$query0 .= " FROM baza ";
			$query0 .= " WHERE crossnum = '{$prep_artikul}'";
			
			$nabor_chisla_artikulov = mysqli_query($connection, $query0);
			if (!$nabor_chisla_artikulov){
			die("Database do not returned query0.");};
	
	
			$count_array = mysqli_fetch_assoc($nabor_chisla_artikulov);
			//$admininfo.= "Массив COUNT: ";
			//$admininfo.= $count_array['COUNT(*)']; 
			//$admininfo.= "<br>";
			
			if (!empty($prep_artikul) and $count_array['COUNT(*)'] == 0) {echo "- Не найден артикул по базе";} // Сообщение для всех в случае истинности условия
		
			if ($count_array['COUNT(*)'] > 1) {
				// Если количество совпадений больше 1.
				// что-то делаем	
				} else {
			
				$query00 = "SELECT proizvoditel ";
				$query00.= " FROM baza ";
				$query00.= " WHERE crossnum = '{$prep_artikul}' ";	
				$proizvoditela_nabor = mysqli_query($connection, $query00);		
			//	confirm_query($proizvoditela_nabor);
				if (!$proizvoditela_nabor){
				die("Database do not returned anything. Error query00");}
			

				$proizvod_array = mysqli_fetch_assoc($proizvoditela_nabor);	// Этой строчкой вместо цикла мы единожды отбираем только одно первое найденное значение. $proizvod_array['proizvoditel']
				
				$manageinfo.= $proizvod_array['proizvoditel'];
				$manageinfo.= "<br>";				
				
				
				
				//////////
				// здесь начинается запрос к таблице oem
				/////////////
				
					$query10 = "SELECT CODE_PARTS ";
					$query10.= " FROM";
					$query10.= " {$proizvod_array['proizvoditel']}";
					$query10.= "_oem";
					$query10.= " WHERE mainART_CODE_PARTS = '{$prep_artikul}'";
					$oem_nabor = mysqli_query($connection, $query10);
					
				//	$admininfo.= $query10;
				//	$admininfo.= "<br>";
				//	if (!$oem_nabor){
				//	$admininfo.= "Database do not returned anything. Not oem_nabor in query10.";}
				
				
					if ($oem_nabor && mysqli_affected_rows($connection) > 0) {
						
						$manageinfo.= "<br>";
						$manageinfo.= "Oem_nabor:";
						$manageinfo.= "<br>";
						$manageinfo.= "<br>";
						
						while ($crossnum = mysqli_fetch_assoc($oem_nabor)) {	// $crossnum - это массив строки из таблицы	_oem
						
						// обращаемся с оем_набором к остаткам
						
						$manageinfo.= $crossnum['CODE_PARTS'];
						$manageinfo.= "<br>";
						
						$query11 = "SELECT firma, artikul, naimenovanie, zakup, kolvo, postavschik ";	
						$query11.= " FROM _ostatki";
						$query11.= " WHERE hash_artikul = '{$crossnum['CODE_PARTS']}'";
						if(!isset($_POST['nuli'])) { // если галочку не отмечали (по умолчанию), то показывать всё, что в наличии
						$query11.= " AND kolvo > 0";}
						$ostatki_nabor = mysqli_query($connection, $query11);
						confirm_query($ostatki_nabor);
						
						if ($ostatki_nabor && mysqli_affected_rows($connection) > 0) {
						
						// если в остатках найдены строчки, совпдающие со взятым оем_номером, то выводим в табличку
						
						$resultat = mysqli_fetch_assoc($ostatki_nabor);
						
						?>
						<tr>
							<td>
							<?php echo htmlentities($resultat["firma"]);?>
							</td>			
							<td>				
							<?php echo htmlentities($resultat["artikul"]);?>				
							</td>
							<td>
							<?php echo htmlspecialchars($resultat["naimenovanie"]);?>
							</td>
							<td>
							<?php echo htmlentities($resultat["kolvo"]);?>
							</td>
							<td>
							<?php echo htmlentities($resultat["zakup"]);?>
							</td>
							<td>
							<?php echo htmlentities($resultat["postavschik"]);?>
							</td>
						</tr>
					 
					<?php 	} // строим таблицу
						mysqli_free_result($ostatki_nabor); 
							} // конец while
						
						mysqli_free_result($oem_nabor);	
							
					} // конец if ($oem_nabor && mysqli_affected_rows($connection) > 0)
					
					//////////
					// Закончили запрос к таблице oem
					//////////	
					
				
					//////////
					// Здесь начинается запрос к таблице nooem
					//////////
					
					$query12 = "SELECT CODE_PARTS ";
					$query12.= " FROM ";
					$query12.= " {$proizvod_array['proizvoditel']}";
					$query12.= "_nooem";
					$query12.= " WHERE mainART_CODE_PARTS = '{$prep_artikul}'";
					$nooem_nabor = mysqli_query($connection, $query12);
						
					if ($nooem_nabor && mysqli_affected_rows($connection) > 0) {			
					// Если есть ответ от базы данных как nooem_nabor и возникших строчек не ноль, то идем с набором nooem номеров в таблицу с остатками
													
						while ($ostatkinooem = mysqli_fetch_assoc($nooem_nabor)) {	// $crossnum - это массив строки из таблицы	
						// И последовательно, с каждым nooem номерком сравниваем его с имеющимися остатками, пока не получим совпадения. 
										
						$query13 = "SELECT firma, artikul, naimenovanie, zakup, kolvo, postavschik ";	
						$query13.= " FROM _ostatki";
						$query13.= " WHERE hash_artikul = '{$ostatkinooem['CODE_PARTS']}'";
						if(!isset($_POST['nuli'])) { // если галочку не отмечали (по умолчанию), то показывать всё, что в наличии
						$query13 .= " AND kolvo > 0";}
						$ostatki_nabor = mysqli_query($connection, $query13);
						confirm_query($ostatki_nabor);
						
						if ($ostatki_nabor && mysqli_affected_rows($connection) > 0) {
						// Если есть ответ от базы данных как 	ostatki_nabor и полученных строчек не ноль, то выводим результат в табличку
							
						$resultat = mysqli_fetch_assoc($ostatki_nabor);
						
						?>
						<tr>
							<td>
							<?php echo htmlentities($resultat["firma"]);?>
							</td>			
							<td>				
							<?php echo htmlentities($resultat["artikul"]);?>				
							</td>
							<td>
							<?php echo htmlspecialchars($resultat["naimenovanie"]);?>
							</td>
							<td>
							<?php echo htmlentities($resultat["kolvo"]);?>
							</td>
							<td>
							<?php echo htmlentities($resultat["zakup"]);?>
							</td>
							<td>
							<?php echo htmlentities($resultat["postavschik"]);?>
							</td>
						</tr>
					 
					<?php 	} // строим таблицу
						mysqli_free_result($ostatki_nabor); 
							} // конец while
						
						mysqli_free_result($nooem_nabor);	
						
					} // конец if ($nooem_nabor && mysqli_affected_rows($connection) > 0)
					
					//////////
					// Закончили запрос к таблице nooem
					//////////	
					
					
				//////////
				// Здесь начинаем проверку вбитого номера на прямое совпадение с самим собой: так как номер в текдоковских таблицах сам на себя не ссылается.
				//////////	


				$query20  = "SELECT crossnum ";
				$query20 .= "FROM baza ";
				$query20 .= "WHERE crossnum = '{$prep_artikul}' ";	
				$crossnum_nabor = mysqli_query($connection, $query20);		
				confirm_query($crossnum_nabor);

				$samkross = mysqli_fetch_assoc($crossnum_nabor);	// Этой строчкой вместо цикла мы единожды отбираем только одно первое найденное значение. $samkross['crossnum']

				$query21 = "SELECT firma, artikul, naimenovanie, zakup, kolvo, postavschik ";	
				$query21.= " FROM _ostatki";
				$query21.= " WHERE hash_artikul = '{$samkross['crossnum']}'";
				if(!isset($_POST['nuli'])) { // если галочку не отмечали (по умолчанию), то показывать всё, что в наличии
				$query21 .= " AND kolvo > 0";}
				$ostatki_nabor = mysqli_query($connection, $query21);
				confirm_query($ostatki_nabor);
				
				while ($ostatkikrossa = mysqli_fetch_assoc($ostatki_nabor)) {
					?>
					<tr>
					<td>
					<?php echo htmlentities($ostatkikrossa["firma"]);?>
					</td>			
					<td>				
					<?php echo htmlentities($ostatkikrossa["artikul"]);?>				
					</td>
					<td>
					<?php echo htmlspecialchars($ostatkikrossa["naimenovanie"]);?>
					</td>
					<td>
					<?php echo htmlentities($ostatkikrossa["kolvo"]);?>
					</td>
					<td>
					<?php echo htmlentities($ostatkikrossa["zakup"]);?>
					</td>
					<td>
					<?php echo htmlentities($ostatkikrossa["postavschik"]);?>
					</td>
				</tr>
					<?php
				}
				mysqli_free_result($ostatki_nabor); 
				mysqli_free_result($crossnum_nabor); 
				mysqli_free_result($proizvoditela_nabor);
			}	 // конец else внутри if ($count_array['COUNT(*)'] > 1) 

		
	} // конец if (isset($_POST['crossnumsubmit']))
	
if (isset($_GET['crossnum'])) {
	
	} // конец if (isset($_GET['artikul']))
?>
	</tbody>
	</table>
	
<?php 
	
if ($layout_context == "manager") {
	echo $manageinfo;
};
?>		
	
	</div> <!-- конец page -->
	</div> 	<!-- конец main --> 

<?php include("../includes/layouts/footer.php"); ?>
	