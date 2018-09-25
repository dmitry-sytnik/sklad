<?php /* Файл использует поиск с кроссами в таблице baza по столбцу crossnum. 
Если в столбце не будет искомого артикула (даже если он есть в соседнем столбце headartikul), 
ничего не будет найдено. Поиск регистронезависимый.
 crossnum в baze должен быть "чистый": без слэшей, без пробелов, точек и т.д. Регистр не имеет значения, разве что только для красоты и однообразия. 
 Если некоторые оригинальные или неоригинальные номера не вбиты в базу, то появляться в результате они не будут. Несмотря на то, что они могут появляться в результате выдачи по кроссам текдока.*/

?>
<?php require_once("../../includes/session.php"); ?>
<?php require_once("../../includes/db_connection.php"); ?>
<?php require_once("../../includes/functions.php"); // здесь заложена навигация ?>

<?php 
	 // $found_admin[] = "";
	if(isset($_SESSION["admin_id"])) { // Если задана сессия админа,
	$layout_context = "admin"; 			// то layout_context = admin
	 } else {  // Иначе
	redirect_to("../index.php");} // если  сюда пришел пользователь без $_SESSION["admin_id"], он будет перенаправлен
?>

<?php 
	$admininfo = "";
?>

<?php include("../../includes/layouts/header.php"); ?>

 
 <div id="page"> 
 </br>

<?php echo message();?>

<form id="crossnumpoisk" name="crossnumpoisk" method="post" action="cross_poisk.php">
  Поиск по Кроссу &nbsp;
  <input type="text" name="artikul" id="artikul" /> &nbsp;
  <input type="submit" name="crossnumsubmit" id="crossnumsubmit" value="Найти" />  &nbsp;
  <input type="checkbox" name="nuli" id="nuli"/> Показать и то, чего нет в наличии
</form>
<table id="crosstable" class="tablesorter" >
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
	
	// echo 'отправлено';
	
	$prep_artikul = mysql_crossnum_prep($_POST['artikul']);
	
	if (empty($prep_artikul)) {echo "- Ничего не вбито";} // не обязательно делать else: просто при истинном условии добавляется сообщение.
	
	$admininfo.= "</br>";
	$admininfo.= "----- Служебная информация -----";
	$admininfo.= "</br>";
	
	if (isset($_POST['nuli'])) { // Заметка: чекбокс не отправляется с формой, если он не был отмечен
        $admininfo.= "Нулевые позиции были отмечены галкой";
		$admininfo.= "</br>";}
	
	$admininfo.= "Вбито: ";
	$admininfo.= $_POST['artikul'];
	$admininfo.= "</br>";
	
	$admininfo.= "Обработано как: ";
	$admininfo.= $prep_artikul;
	$admininfo.= "</br>";
	
	if (!empty($prep_artikul)) { 
			// 1. Выясняем количество совпадений по базе со вбитым артикулом
			$query0 = "SELECT COUNT(*)"; 
			$query0 .= " FROM baza ";
			$query0 .= " WHERE crossnum = '{$prep_artikul}'";
			
			$nabor_chisla_artikulov = mysqli_query($connection, $query0);
			if (!$nabor_chisla_artikulov){
				die("Database do not returned query0.");};
			
			
			$count_array = mysqli_fetch_assoc($nabor_chisla_artikulov);
			$admininfo.= "Массив COUNT: ";
			$admininfo.= $count_array['COUNT(*)'];
			$admininfo.= "</br>";
			
			if ($count_array['COUNT(*)'] == 0) {echo "- Не найден артикул по базе";} // Сообщение для всех в случае истинности условия

			if ($count_array['COUNT(*)'] > 1) {// Если количество совпадений больше 1.
				$admininfo.= "Число строк в базе больше 1"."</br>";
				$admininfo.= "----------------------------------";
			
				$query01 = "SELECT * ";
				$query01 .= " FROM baza ";
				$query01 .= " WHERE crossnum = '{$prep_artikul}'";
				$nabor_izzaprosa = mysqli_query($connection, $query01);
				confirm_query($nabor_izzaprosa);
		?>
		<table ><tr></tr>
		<?php		
			
				while ($stroka_bazy = mysqli_fetch_assoc($nabor_izzaprosa)) {
					// Строим табличку под выбор нужного артикула с фирмой
		?>		
					<tr><td>
						<?php echo htmlentities($stroka_bazy["proizvoditel"]);?>
						</td>			
						<td>
						<?php echo htmlentities($stroka_bazy["crossnum"]);?>
						</td>
						<td>
						<a href="index_admin.php?proizvoditel=<?php echo htmlentities($stroka_bazy["proizvoditel"]);?>&crossnum=<?php echo htmlentities($stroka_bazy["crossnum"]);?>&nalichie=da">Перейти к наличию</a>
						</td>
						<td>
						<a href="index_admin.php?proizvoditel=<?php echo htmlentities($stroka_bazy["proizvoditel"]);?>&crossnum=<?php echo htmlentities($stroka_bazy["crossnum"]);?>">Перейти ко всей номенклатуре</a>
						</td>	
					</tr>						
					
		<?php			
				};
				mysqli_free_result($nabor_izzaprosa);
		?>		
		</table>

		<?php		
			mysqli_free_result($nabor_chisla_artikulov);
			} else { //  Если количество совпадений не больше 1.
			
			$admininfo.= "----------------------------------";
			
			$query1  = "SELECT headartikul ";
			$query1 .= "FROM baza ";
			$query1 .= "WHERE crossnum = '{$prep_artikul}' ";		
			
			$headartikula_nabor = mysqli_query($connection, $query1);		
			confirm_query($headartikula_nabor);	
			
			$headlink_array = mysqli_fetch_assoc($headartikula_nabor);	// Этой строчкой вместо цикла мы единожды отбираем только один первый найденный хед-артикул. Но ведь и совпадений не больше 1.
			
			$query2 = "SELECT DISTINCT baza.crossnum, _ostatki.firma, artikul, naimenovanie, hash_artikul, zakup, kolvo, postavschik ";
			// С помощью Дистинкта полностью одинаковые строчки будут исключены. Однако этот запрос не дает возможности сразу автоматически вывести список кроссов из базы без повторений, т.к. один найденный кросс, если он будет находить несколько похожих хэш-артикулов в остатках (что естественно при большом ассортименте), будет повторяться в результате этого запроса.
			$query2.= "	FROM baza";
			$query2.= "	JOIN _ostatki ON baza.crossnum = _ostatki.hash_artikul";
			$query2.= "	WHERE baza.headartikul = '{$headlink_array['headartikul']}'";
			if(!isset($_POST['nuli'])) { // если галочку не отмечали (по умолчанию), то показывать всё, что в наличии
			$query2 .= " AND kolvo > 0";}
			$join_nabor = mysqli_query($connection, $query2);
			confirm_query($join_nabor);
			
				//$admininfo.= "</br>";
				//$admininfo.= "Cross_nabor:";
				//$admininfo.= "</br>";
				//$admininfo.= "</br>";
			
			
			while ($resultat = mysqli_fetch_assoc($join_nabor)) {// $resultat - это массив строки из остатков	
				
				
				//$admininfo.= $resultat["crossnum"];
				//$admininfo.= "</br>";
				
				
			// 5. Последовательно, в цикле, выводим в табличку найденную строку из join-таблицы.
		?>
			<tr><td>
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
		 
		<?php 
				} // конец while
			mysqli_free_result($headartikula_nabor);	
			mysqli_free_result($join_nabor); 	
				
			// запрос к базе данных для вывода оем_кроссномеров для админа	
				
			$query4 = "SELECT * ";
			$query4.= " FROM baza";
			$query4.= " WHERE headartikul = '{$headlink_array['headartikul']}'";
			$query4.= " AND oem0nooem1 = 0";
			$oem_nabor = mysqli_query($connection, $query4);
			confirm_query($oem_nabor);
			
			$admininfo.= "</br>";
			$admininfo.= "Oem_nabor:";
			$admininfo.= "</br>";
			$admininfo.= "</br>";
			
			while ($oem_resultat = mysqli_fetch_assoc($oem_nabor)) { // oem_resultat - это массив полученной строки
				
			$admininfo.= $oem_resultat['crossnum'];
			$admininfo.= "</br>";	
				
			}	
			
			mysqli_free_result($oem_nabor); // конец запроса к базе данных для вывода оем_кроссномеров для админа	
				
			} // конец else в условии if ($count_array['COUNT(*)'] > 1)
			
			
	} // конец 	if (!empty($prep_artikul))
} // конец $_POST['crossnumssubmit']
 
?>

<?php
	if (isset($_GET['crossnum'])) {
		//	echo "вижу гет запрос";
		$get_artukul = mysql_crossnum_prep($_GET['crossnum']);
		$get_firma = mysqli_real_escape_string($connection, $_GET['proizvoditel']);
		
		
		$query1  = "SELECT headartikul ";
		$query1 .= "FROM baza ";
		// берем обработанный после вбития артикул и ищем его в столбце всех артикулов
		$query1 .= "WHERE crossnum = '{$get_artukul}' ";
		$query1 .= "AND proizvoditel = '{$get_firma}' ";
		
		$headartikula_nabor = mysqli_query($connection, $query1);
		// Test if there was a query error
		if (!$headartikula_nabor){
		die("Database do not returned headartikula_nabor in the get.");}
		$headlink_array = mysqli_fetch_assoc($headartikula_nabor); // Этой строчкой вместо цикла мы единожды отбираем только один первый найденный артикул
		
		$admininfo.= "</br>";
		$admininfo = "----- Служебная информация -----";
		$admininfo.= "</br>";
		
		$admininfo.= "Headlink founded: ";
		$admininfo.= $headlink_array['headartikul'];
		$admininfo.= "</br>";
		
		
		$query2  = "SELECT DISTINCT crossnum";
		$query2 .= " FROM baza";	
		$query2 .= " WHERE headartikul = '{$headlink_array['headartikul']}'"; 
		$query2 .= " ORDER BY proizvoditel ASC"; // сортируем фирмы по алфавиту для дальнейшего перебора в третьем запросе
		$crossnum_nabor = mysqli_query($connection, $query2);
		if (!$crossnum_nabor){
		die("Database do not returned crossnum_nabor in the get.2.");}
		/*	if ($crossnum_nabor){
		echo "returned crossnum_nabor in the get.2.";
		echo "</br>";		
		}	*/
		
		$admininfo.= "Найденные кроссы в гет-запросе:";
		$admininfo.= "</br>";
		
		while($crosses = mysqli_fetch_assoc($crossnum_nabor)) { 
		$admininfo.= "$crosses[crossnum]";
		$admininfo.= "</br>";
		
			$query3  = "SELECT * ";
			$query3 .= " FROM _ostatki";
			$query3 .= " WHERE hash_artikul = '{$crosses['crossnum']}'";
			// Если запросили показать только наличие
			if(isset($_GET['nalichie'])) { // Заметка - указывать надо ключ, а не значение
			$query3 .= " AND kolvo > 0";}
			
			$stroka_nabor = mysqli_query($connection, $query3);
			confirm_query($stroka_nabor);
		
				while ($resultat = mysqli_fetch_assoc($stroka_nabor)) {
?>
					<tr><td>
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
 
<?php 
				
				} // конец внутреннего цикла
				mysqli_free_result($stroka_nabor); 	
		} // конец внешнего цикла
		 mysqli_free_result($crossnum_nabor);
		 mysqli_free_result($headartikula_nabor);
	} // конец if (isset($_GET['artikul']))
?>
	</tbody>
	</table>

<?php 
	
		
if ($layout_context == "admin") {
	echo $admininfo;
};
?>	
	</div> <!-- конец page -->
	</div> 	<!-- конец main -->

<?php include("../../includes/layouts/footer.php"); ?>
	