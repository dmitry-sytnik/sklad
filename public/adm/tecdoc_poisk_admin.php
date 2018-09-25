<?php /* 
Вкратце:

- вбиваем артикул, с ним идём в таблицу baza
- определяем соответствующий этому артикулу брэнд
- т.к. таблицы с кроссами начинаются с названия брэнда, теперь мы знаем куда идти: идём к таблицам oem и nooem этого брэнда
- в них находим все кроссы, соответствующие вбитому артикулу
- и, наконец, запросом номер 3 (посредством JOIN, путём сопоставления всех выбранных кроссов с остатками) создаём результирующую таблицу с имеющимися остатками.

-----------------
Подробно:

Необходимо 
1) наличие таблицы baza. В ней артикулы должны указываться без слешей и т.п., а название фирм должно писаться одним словом, например, с подчеркиванием. Название фирмы потом используется для обращения к соответствующим таблицам.
2) в baza - наличие вбиваемого кросса с названием производителя
3) наличие текдоковских таблиц оем и nooem под эту конкретную фирму 
4) таблица с остатками.

Поиск в принципе не предназначен и не разрабатывался как поиск по оригинальному номеру.

 Если номерок есть в базе и остатках, но таблицы текдока под него не существует, то результат выводится как поиск (не отточено) по прямому артикулу. Этим занимаются последние запросы, так как они напрямую просматривают вбитый артикул в остатках. В этом есть и проблема, так как под оригинальные номера таблиц текдока нет. Это значит, что, например, вбитый 15208-65F0С будет искать только его в остатках и ничего больше.
 -------------
 08-2018:
 Судя по всему, headartikul, изначально задумавшийся в базе данных как связующий головной артикул между артикулами из baza и соответствующим им артикулам из таблиц оем и noоеm (для связки в отношениях многие ко многим), оказался не нужным и незадействованным. Справился без него.
 Этой хитростью занимается 3 запрос (Вообще, из кода этот запрос берет только $prep_brand и $prep_artikul. Больше ничего. Никаких headartikul. Только анти-хакерски подготовленный для базы данных брэнд и артикул. По брэнду он узнаёт, к каким таблицам обращаться. По артикулу  он узнаёт, какие именно кроссы потом искать среди остатков.):
 
				 SELECT DISTINCT profit_oem.CODE_PARTS, _ostatki.firma, artikul, naimenovanie, hash_artikul, zakup, kolvo, postavschik  FROM  profit_oem
				   JOIN _ostatki
				   ON profit_oem.CODE_PARTS = _ostatki.hash_artikul
				   WHERE profit_oem.mainART_CODE_PARTS = 15120706 AND kolvo > 0
				UNION
				SELECT DISTINCT profit_nooem.CODE_PARTS, _ostatki.firma, artikul, naimenovanie, hash_artikul, zakup, kolvo, postavschik  FROM profit_nooem 
				   JOIN _ostatki 
				   ON profit_nooem.CODE_PARTS = _ostatki.hash_artikul 
				   WHERE profit_nooem.mainART_CODE_PARTS = 15120706 AND kolvo > 0;
 
 В нём для связи используется hash_artikul из остатков, который при сравнении может совпасть с искомым кроссом (CODE_PARTS) из таблицы оем или нооем. В последних столбец CODE_PARTS тоже выполнен как хэш, без всяких тире, пробелов, точек, слэшей. 
 Итак: profit_oem.CODE_PARTS - это кросс под вбиваемый артикул (есть оем-кроссы и неоем-кроссы), profit_oem.mainART_CODE_PARTS - соответствует вбиваемому артикулу.
 
 Таким образом, для итоговой таблицы с имеющимися сейчас остатками нужны, видимо, только 3 и 4 запросы. 1 и 2 нужны только ради информации для админа.
 
 Здесь по коду используется предварительный этап и потом четыре этапа, связанных с кроссами:
 0. После некоторых проверок (залогиненный админ, отправленность форм и т.п.) выясняем искомого производителя вбитого артикула. Причем выясняем до последнего, даже если вбит артикул, под который подпадает несколько производителей. Для этого задействуется так называемая "вторая форма".
 1. Здесь начинается запрос к таблице oem
 1.1.1 Запрос, когда нужны только оем кроссы без брэндов
 1.1.2 Запрос для админской таблицы, когда нужны и оем кроссы, и оем брэнды
 2. Здесь начинается запрос к таблице nooem
 3. Запрос для итоговой таблицы, где сопоставляются oem кроссы, nooem кроссы и остатки
 4.0 Здесь начинаем проверку вбитого номера на прямое совпадение с самим собой в остатках: так как номер в текдоковских таблицах (оем, nooem) сам на себя не ссылается.
 В самом конце Если при вбитии артикула производителей по базе оказалось больше одного, строим форму под выбор необходимого производителя.
  
*/

?>
<?php require_once("../../includes/session.php"); ?>
<?php require_once("../../includes/db_connection.php"); ?>
<?php require_once("../../includes/functions.php"); // здесь заложена навигация?>
<?php require_once("../../includes/validation_functions.php"); ?>

<?php 
$time_start = microtime(true); // Пример взят с http://php.net/manual/ru/function.microtime.php
?>

<?php if(isset($_SESSION["admin_id"])) {	// Если задана сессия админа,
	$layout_context = "admin";				// то $layout_context = admin
	} else {
	redirect_to("../tecdoc_poisk.php");} // если  сюда пришел пользователь без $_SESSION["admin_id"], он будет перенаправлен
?>

<?php include("../../includes/layouts/header.php"); ?>
 
 <div id="page"> 
 <br/>
 
<?php echo message(); ?>
 
<form id="crossnumpoisk" name="crossnumpoisk" method="post" action="tecdoc_poisk_admin.php">
  Поиск по кроссам Текдока &nbsp;
  <input type="text" name="artikul" id="artikul" /> &nbsp;
  <input type="submit" name="crossnumsubmit" id="crossnumsubmit" value="Найти" />  &nbsp;
  <input type="checkbox" name="nuli" id="nuli"/> Показать и то, чего нет в наличии
</form>
<!--  <div id="form">  -->
<table id="crosstable" class="tablesorter" >
			  <thead>
			  <tr>
				<th><strong>Firma</strong></th>
				<th><strong>Artikul</strong></th>
				<th><strong>Naimenovanie</strong></th>
				<th><strong>Kolvo</strong></th>
				<th><strong>zakup</strong></th>
				<th><strong>RUB</strong></th>
				<th><strong>Magaz</strong></th>
			  </tr>
			  </thead>
			  <tbody>
				
<?php
	$admininfo = "";

	if (isset($_POST['crossnumsubmit'])) { // Если была отправлена либо первая, либо вторая форма со страницы (в обоих используется submit как crossnumsubmit)
		
		print_r ($_POST);
		$prep_artikul = mysql_crossnum_prep($_POST['artikul']);
		
		//if(empty($prep_artikul)) {redirect_to("tecdoc_poisk_admin.php");}
		
		if (isset($_POST['firma'])) { // Если была отправлена вторая форма (форма, где выбирается артикул из нескольких возможных производителей с таким артикулом)
		$prep_brand = mysql_prep($_POST['firma']);
		$proizvod_array = array("proizvoditel" => $prep_brand);
		//$brand = $proizvod_array["proizvoditel"]; Этот способ присвоения значения и использования переменной $brand потом выводит несколько ошибок на странице
		$admininfo.= "Производитель из второй формы: ".$proizvod_array["proizvoditel"]."<br/><br/>";
		
		} else { // Если не отправлялась вторая форма с фирмой
		
		// 1. Выясняем количество совпадений по базе со вбитым артикулом
			$query0 = "SELECT COUNT(*)"; 
			$query0 .= " FROM baza ";
			$query0 .= " WHERE crossnum = '{$prep_artikul}'";
			
			$nabor_chisla_artikulov = mysqli_query($connection, $query0);
			if (!$nabor_chisla_artikulov){
				die("Database did not return query0.");
			};
		
			$count_array = mysqli_fetch_assoc($nabor_chisla_artikulov);
			$admininfo.= "Массив COUNT: ";
			$admininfo.= $count_array['COUNT(*)']; 
			$admininfo.= "<br/>";
			
			mysqli_free_result($nabor_chisla_artikulov);
			
			if (!empty($prep_artikul) and $count_array['COUNT(*)'] == 0) {$admininfo.= "- Не найден артикул по базе";}
		
				if ($count_array['COUNT(*)'] == 1) {  // если артикулов в базе ровно 1
		
					// Выясняем производителя вбитого номера
				
					$query00 = "SELECT proizvoditel ";
					$query00.= " FROM baza ";
					$query00.= " WHERE crossnum = '{$prep_artikul}' ";	
					$proizvoditela_nabor = mysqli_query($connection, $query00);		
				//	confirm_query($proizvoditela_nabor);
					if (!$proizvoditela_nabor){
					die("Database did not return anything. Error query00");}
				
					$proizvod_array = mysqli_fetch_assoc($proizvoditela_nabor);	// Этой строчкой вместо цикла мы единожды отбираем только одно первое найденное значение для выяснения производителя вбитого номера. Получается, согласно запросу, $proizvod_array["proizvoditel"]
					
					$admininfo.= "Производитель из первой формы: ".$proizvod_array["proizvoditel"];
					$admininfo.= "<br/><br/>";	

					mysqli_free_result($proizvoditela_nabor);
					
				// Производитель вбитого номера выяснен.
					
				} else { // если артикулов в базе 0 или болье 1, то массив объявляем null, т.к. требуется выяснить определенно одну фирму, чтобы производить поиск
					$proizvod_array = null; // Если не выяснена конкретная фирма, этому массиву будет присвоено null.
				} 
			}  
		
		$prep_brand = mysql_prep($proizvod_array["proizvoditel"]); // Это значение берется либо из первой части условия if (isset($_POST['firma'])), либо из второй части. В обоих случаях выясняется одна конкретная фирма, которую пытаются найти. Или же, если не выяснена конкретная фирма, этому значению будет присвоено null.
		
		// 08-2018: Здесь я зачем-то перезаписываю $prep_brand, если в дело вступает первый блок if (во втором блоке else переменная $prep_brand отсутствует). Это немного запутанно. Вероятно, Или можно было применять другое название переменной $prep_brand в первом блоке if. Или сократить запись.
		
		if (empty($prep_artikul)) {$admininfo.= "- Ничего не вбито<br/>";} 
		
?>
  
<?php	if(!is_null($proizvod_array)) { // Если производитель не null (а следовательно, определена фирма, по которой хотят искать), то производим поиск по базе, по кроссам и по остаткам в теле html

			//////////
			// 1. Здесь начинается запрос к таблице oem
			/////////////
			
			// Здесь так же, как и у неоем-кроссов, нужно сделать разделение mysql-запросов с использованием в одном из них DISTINCT при обращении к остаткам. Потому что у оригинальных номеров тоже бывают совпадения по артикулу, например, такое бывает у ниссановских и субаровских номеров.
			
			
			///	1.1 Запрос для админской таблицы oem кроссов
			//////
			
			/// 1.1.1 Запрос, когда нужны только оем кроссы без брэндов
			//////
			// Заметка: DISTINCT сам по себе еще и без особых указаний проводит сортировку результата. Обычно по первому заданному столбцу.
							
					$query11 = "SELECT DISTINCT mainART_BRANDS, mainART_CODE_PARTS, CODE_PARTS ";
					$query11.= " FROM ";
					$query11.= " {$prep_brand}";
					$query11.= "_oem";  // Запрос 1.1 от запроса 2.1 отличается заменой nooem на oem
					$query11.= " WHERE mainART_CODE_PARTS = '{$prep_artikul}'";
					//$admininfo.= "query11 nooem: ".$query11."<br/>";
					$crossov_nabor = mysqli_query($connection, $query11);
					
					//Здесь мы не делаем confirm_query и не обрываем построение дальнейшего кода, если запрос не успешен. Вместо этого выводятся соответствующие сообщения.
					if(!$crossov_nabor) {
						$admininfo.= "<br/>NOT oem_nabor. Possibly, it is not like table. Database did not return anything.<br/>";
					} elseif ($crossov_nabor && mysqli_affected_rows($connection) == 0) {
						$admininfo.= "NOT oem_nabor. But Table is. Net zapisey.<br/>";						
					}	else {
								$admininfo.= "IS oem_nabor. Table is.<br/>";
							}
					$admininfo.= "<br/>";
					
					// Так как мы не совершаем обрыв кода в случае, если запрос к базе был не успешен, то, поэтому, проверяем: если был ответ от базы данных и набор затронутых строк больше, чем ноль, то строим дальнейший код на основании этих полученных данных
					if ($crossov_nabor && mysqli_affected_rows($connection) > 0) {
						
						// тут начинаем строить oem_набор для админа в одну строчку
						$admininfo.= "<br/>";
						$admininfo.= "Oem_nabor:";
						$admininfo.= "<br/>";
						$admininfo.= "<br/>";
						$admininfo.= "<table width=\"400\" border=\"0\">
										 <tr>
										";
													
						while ($crossarray = mysqli_fetch_assoc($crossov_nabor)) {	// $crossarray - это массив строки из таблицы oem	 
						// пока мы получаем этот массив
						
						// продолжаем строить набор oem-кроссов для админа в одну строчку			
						
							$admininfo.= "<td>";
							$admininfo.= $crossarray['CODE_PARTS'];					
							$admininfo.= "</td>";						
																
						} // конец while
						$admininfo.= "<tr>";
						$admininfo.= "		</table><br/>"; // конец админской таблицы oem-кроссномеров
						
						mysqli_free_result($crossov_nabor);	
						
					} // конец if ($crossov_nabor && mysqli_affected_rows($connection) > 0)
			
			/// Конец 1.1.1 Запроса, когда нужны только оем кроссы без брэндов
			//////
			
			/// 1.1.2 Запрос для адмиснкой таблицы, когда нужны и оем кроссы, и оем брэнды
			//////
							
					$query11 = "SELECT * ";
					$query11.= " FROM ";
					$query11.= " {$prep_brand}";
					$query11.= "_oem";  // Запрос 1.1 от запроса 2.1 отличается заменой nooem на oem
					$query11.= " WHERE mainART_CODE_PARTS = '{$prep_artikul}'";
					//$admininfo.= "query11 nooem: ".$query11."<br/>";
					$crossov_nabor = mysqli_query($connection, $query11);
					
					//Здесь мы не делаем confirm_query и не обрываем построение дальнейшего кода, если запрос не успешен. Вместо этого выводятся соответствующие сообщения.
					if(!$crossov_nabor) {
						$admininfo.= "<br/>NOT oem_nabor. Possibly, it is not like table. Database did not return anything.<br/>";
					}  elseif ($crossov_nabor && mysqli_affected_rows($connection) == 0) {
						$admininfo.= "NOT oem_nabor. But Table is. Net zapisey.<br/>";	
					}	else {
								$admininfo.= "IS oem_nabor. Table is.<br/>";
							}
					$admininfo.= "<br/>";
					
					// Так как мы не совершаем обрыв кода в случае, если запрос к базе был не успешен, то, поэтому, проверяем: если был ответ от базы данных и набор затронутых строк больше, чем ноль, то строим дальнейший код на основании этих полученных данных
					if ($crossov_nabor && mysqli_affected_rows($connection) > 0) {
						
						// тут начинаем строить oem_набор для админа в таблицу
						$admininfo.= "<br/>";
						$admininfo.= "Oem_nabor:";
						$admininfo.= "<br/>";
						$admininfo.= "<br/>";
						$admininfo.= "<table width=\"400\" border=\"0\">
										<tr>
										<td><strong>Brands</strong></td>
											<td><strong>Oems</strong></td>
										<td><strong>Actions</strong></td>
										</tr>";
													
						while ($crossarray = mysqli_fetch_assoc($crossov_nabor)) {	// $crossarray - это массив строки из таблицы oem	 
						// пока мы получаем этот массив
						
						// продолжаем строить набор oem-кроссов для админа	в таблицу		
						//	print_r($crossnooem);
							$admininfo.= "<tr>";
							$admininfo.= "<td>";
							$admininfo.= $crossarray['BRANDS'];					
							$admininfo.= "</td>";  
		
							$admininfo.= "<td>";
							$admininfo.= $crossarray['CODE_PARTS'];					
							$admininfo.= "</td>";
							$admininfo.= "<td>";
												
							$admininfo.= "<a href=\"edit_oemnumber.php?id={$crossarray['id']}&brand={$prep_brand}\" target=\"_blank\">Edit</a>&nbsp; ";
							$admininfo.= "<a href=\"delete_oemnumber.php?id={$crossarray['id']}&brand={$prep_brand}\" target=\"_blank\" 	onclick=\"return confirm('Are you sure? (It will open new tab)');\">Delete</a> ";
							$admininfo.= "		</td>";
			
							$admininfo.= "</tr>";											
						} // конец while
						
						$admininfo.= "		</table><br/>"; // конец админской таблицы oem-кроссномеров
						
						mysqli_free_result($crossov_nabor);	
						
					} // конец if ($crossov_nabor && mysqli_affected_rows($connection) > 0)
			
			
			/// Конец 1.1.2 Запроса, когда нужны и оем кроссы, и оем брэнды
			//////
			
			///		Конец 1.1 Запроса для админской таблицы оем кроссов
			//////				
				
				
			//	Теперь мы отдельно оем-кроссы и остатки не сопоставляем (раньше сопоставляли), а сопоставляем всё сразу (и оем, и неоем кроссы с остатками) с помощью метода UNION
				
					//////////
					// Закончили 1. Запрос к таблице oem
					//////////	
					


					
					//////////
					// 2. Здесь начинается запрос к таблице nooem
					//////////
			
				/// Правило1. Запрос должен исключить повторяемость строк из остатков. Это происходит, например, если последовательно брать кроссы (например, OC196 Knecht, далее OC196 Mahle) и, сравнивая такой кросс с остатками, выводить результат в итоговую таблицу. Тогда дважды будет выведена строка из остатков, например, Knecht OC196, после первого сравнения с OC196 Knecht и после второго - с OC196 Mahle, даже если в действительности она была одна-единственнная в остатках.
				/// Правило2. При этом похожие позиции разных фирм (AIKO C225, VIC C225) или разных поставщиков (AIKO C225 у FN и AIKO C225 у TK), естественно должны все выводиться в таблицу.
				/// Правило3. Так же должна решаться проблема вывода неправильного артикула вместо правильного. Нельзя добиться такого результата, при котором вбиваешь, например, некий номерок фильтра салонного, который должен среди прочего показать K1230 Filtron, но код выведет и покажет K1230 Kashiyama, а салонник Filtron не попадет в выдачу.

				
				
					$query21 = "SELECT * ";
					$query21.= " FROM ";
					$query21.= " $prep_brand";
					$query21.= "_nooem";
					$query21.= " WHERE mainART_CODE_PARTS = '{$prep_artikul}'";
					//$admininfo.= "query21 nooem: ".$query21."<br/>";
					$crossov_nabor = mysqli_query($connection, $query21);
					
					//Здесь мы не делаем confirm_query и не обрываем построение дальнейшего кода, если запрос не успешен. Вместо этого выводятся соответствующие сообщения.
					if(!$crossov_nabor) {
						$admininfo.= "<br/>NOT nooem_nabor. Possibly, it is not like table. Database did not return anything.<br/>";
					}  elseif ($crossov_nabor && mysqli_affected_rows($connection) == 0) {
						$admininfo.= "NOT nooem_nabor. But Table is. Net zapisey.<br/>";						
					}					
						else {
								$admininfo.= "IS nooem_nabor. Table is.<br/>";
							}
					$admininfo.= "<br/>";
					
					// Так как мы не совершаем обрыв кода в случае, если запрос к базе был не успешен, то, поэтому, проверяем: если был ответ от базы данных и набор затронутых строк больше, чем ноль, то строим дальнейший код на основании этих полученных данных
					if ($crossov_nabor && mysqli_affected_rows($connection) > 0) {
						
						// тут начинаем строить nooem_набор для админа в таблицу
						$admininfo.= "<br/>";
						$admininfo.= "Nooem_nabor:";
						$admininfo.= "<br/>";
						$admininfo.= "<br/>";
						$admininfo.= "<table width=\"400\" border=\"0\">
										<tr>
											<td><strong>Brands</strong></td>
											<td><strong>NoOems</strong></td>
											<td><strong>Actions</strong></td>
										</tr>";
													
						while ($crossarray = mysqli_fetch_assoc($crossov_nabor)) {	// $crossarray - это массив строки из таблицы nooem	 
						// пока мы получаем этот массив
						
						// продолжаем строить набор nooem-кроссов для админа в таблицу			
						//	print_r($crossnooem);
							$admininfo.= "<tr>";
							$admininfo.= "<td>";
							$admininfo.= $crossarray['BRANDS'];					
							$admininfo.= "</td>";  
							$admininfo.= "<td>";
							$admininfo.= $crossarray['CODE_PARTS'];					
							$admininfo.= "</td><td>";
												
							$admininfo.= "<a href=\"edit_nooemnumber.php?id={$crossarray['id']}&brand={$prep_brand}\" target=\"_blank\">Edit</a>&nbsp; ";
							$admininfo.= "<a href=\"delete_nooemnumber.php?id={$crossarray['id']}&brand={$prep_brand}\" target=\"_blank\" 	onclick=\"return confirm('Are you sure? (It will open new tab)');\">Delete</a> ";
							$admininfo.= "		</td>
											</tr>";											
						} // конец while
						
						$admininfo.= "		</table><br/>"; // конец админской таблицы nooem-кроссномеров
						
						mysqli_free_result($crossov_nabor);	
						
					} // конец if ($crossov_nabor && mysqli_affected_rows($connection) > 0)
				
				
				
				//////////
				// Закончили 2. запрос к таблице nooem
				//////////
				
				
				///		3. Запрос для итоговой таблицы, где сопоставляются oem кроссы, nooem кроссы и остатки
				//////
				
				// Здесь была решена следующая проблема:
				// если некий артикул повторялся и у оем-кроссов, и у неоем-кроссов (такое бывает, когда неоригинальнвый производитель ничего не выдумывает, а перенимает как есть оригинальный номер), то тогда будет задвоение выводимых остатков по этому номеру.  Показательный артикул в этом: Profit 1014-2075.
				
				/*  
					(UNION сама по себе работает так, как если бы оба запроса начинались SELECT DISTINCT: как SELECT DISTINCT... UNION ... SELECT DISTINCT. Поэтому слово DISTINCT можно пропустить)
					
					SELECT mainART_BRANDS, mainART_CODE_PARTS, CODE_PARTS 
					FROM profit_oem WHERE mainART_CODE_PARTS = '10142075'

					UNION

					SELECT mainART_BRANDS, mainART_CODE_PARTS, CODE_PARTS 
					FROM profit_nooem WHERE mainART_CODE_PARTS = '10142075';
				
				Использование этого запроса, выбирающего нам список кроссов, заставит потом каждый кросс сравнивать с остатками, и поэтому каждый раз в цикле while придется создавать запрос к базе данных для сравнения взятого кросса с остатками. В зависимости от количества найденных кроссов, таких запросов в цикле while может быть и 20, и 50, и 100, что не может не сказаться на скорости обработки страницы.
				
				Поэтому используем подход со всё собирающим запросом:
				
				SELECT DISTINCT profit_oem.CODE_PARTS, _ostatki.firma, artikul, naimenovanie, hash_artikul, zakup, kolvo, postavschik  FROM  profit_oem
				   JOIN _ostatki
				   ON profit_oem.CODE_PARTS = _ostatki.hash_artikul
				   WHERE profit_oem.mainART_CODE_PARTS = 15120706 AND kolvo > 0
				UNION
				SELECT DISTINCT profit_nooem.CODE_PARTS, _ostatki.firma, artikul, naimenovanie, hash_artikul, zakup, kolvo, postavschik  FROM profit_nooem 
				   JOIN _ostatki 
				   ON profit_nooem.CODE_PARTS = _ostatki.hash_artikul 
				   WHERE profit_nooem.mainART_CODE_PARTS = 15120706 AND kolvo > 0;
				   
				Так мы получаем не список, который нужно сравнивать каждый с каждым, а готовый список из имеющихся остатков, соответствующих заданным кроссам. Единственное - сюда не попадает сам вбитый артикул (даже если он есть в остатках), т.к. здесь сравниваются только кроссы с остатками, а сам артикул с остатками будет сравниваться в отдельном запросе в конце кода.
				DISTINCT здесь, кстати, тоже не обязателен.
				
				*/
				
					$query22 = "SELECT DISTINCT {$prep_brand}_oem.CODE_PARTS, _ostatki.firma, artikul, naimenovanie, hash_artikul, zakup, kolvo, postavschik ";
					// Что касается anytable_oem.CODE_PARTS, то тут можно и больше включить из этой таблицы, но 1) если в этом есть необходимость и 2) если это не сделает схожие строчки вдруг разными. Например, строчки станут разными, если добавить anytable_oem.id (id у всех всегда разный). В этом случае будет нарушено Правило1.
					$query22.= " FROM ";
					$query22.= " $prep_brand";
					$query22.= "_oem";
					$query22.= " JOIN _ostatki";
					$query22.= " ON {$prep_brand}_oem.CODE_PARTS = _ostatki.hash_artikul ";					
					$query22.= " WHERE {$prep_brand}_oem.mainART_CODE_PARTS = '{$prep_artikul}'";
										
					if(!isset($_POST['nuli'])) { // если галочку не отмечали (это по умолчанию), то показывать только то, что в наличии
					$query22.= " AND kolvo > 0"; // Вот эту строчку нужно повторить и здесь, и ниже, иначе обработка становится не такой, как мы ожидаем
					}					
				
					$query22.= " UNION";
				
					$query22.= " SELECT DISTINCT {$prep_brand}_nooem.CODE_PARTS, _ostatki.firma, artikul, naimenovanie, hash_artikul, zakup, kolvo, postavschik ";
					// Что касается anytable_nooem.CODE_PARTS, то тут можно и больше включить из этой таблицы, но 1) если в этом есть необходимость и 2) если это не сделает строчки вдруг разными. Например, строчки станут разными, если добавить anytable_nooem.id. В этом случае будет нарушено Правило1.
					$query22.= " FROM ";
					$query22.= " {$prep_brand}";
					$query22.= "_nooem";
					$query22.= " JOIN _ostatki";
					$query22.= " ON {$prep_brand}_nooem.CODE_PARTS = _ostatki.hash_artikul ";					
					$query22.= " WHERE {$prep_brand}_nooem.mainART_CODE_PARTS = '{$prep_artikul}'";
					
					if(!isset($_POST['nuli'])) { // если галочку не отмечали (это по умолчанию), то показывать только то, что в наличии
					$query22.= " AND kolvo > 0";
					}					
					
					//$admininfo.= "query22 nooem: ".$query22."<br/>";
					$union_nabor = mysqli_query($connection, $query22);
					
					// Не обрываем код функцией confirm_query в случае неуспешного запроса, а проверяем, был ли ответ от базы данных и не равно ли количество затронутых строк нулю.					
					if ($union_nabor && mysqli_affected_rows($connection) > 0) {			
					// Если ответ был и затронутых строчек не ноль, то 
													
						while ($resultat = mysqli_fetch_assoc($union_nabor)) {	// $resultat - это массив объединенной строки после запроса UNION	
						// И пока мы получаем что-то (а это уже почти готовая строка для вывода), то выводим полученное в итоговую таблицу на сайт.
															
?>								<tr>
									<td><?php echo htmlentities($resultat["firma"]);?></td>			
									<td><?php echo htmlentities($resultat["artikul"]);?></td>
									<td><?php echo htmlspecialchars($resultat["naimenovanie"]);?></td>
									<td><?php echo htmlentities($resultat["kolvo"]);?></td>
									<td><?php echo htmlentities($resultat["zakup"]);?></td>
									<td><?php $sell = (int)htmlentities($resultat['zakup'])*1.4;
												$sell = $sell + 15;
												//echo $sell;
												echo ceil($sell/50) * 50; // округление до кратного 50 вверх;?></td>
									<td><?php echo htmlentities($resultat["postavschik"]);?></td>
								</tr>
					 
<?php 					} // конец while ($resultat = mysqli_fetch_assoc($union_nabor))
						
						mysqli_free_result($union_nabor);	
						
					} // конец if ($union_nabor && mysqli_affected_rows($connection) > 0)
				
				///		Конец 3. Запроса для итоговой таблицы, где сопоставляются oem кроссы, nooem кроссы и остатки
				//////
									
						
				
				
				
				//////////
				// 4.0 Здесь начинаем проверку вбитого номера на прямое совпадение с самим собой в остатках: так как номер в текдоковских таблицах (оем, nooem) сам на себя не ссылается.
				// Здесь есть и проблема: код ведет к тому, что будут выведены все возможные совпадения с данным номером, например, K1123 Filtron И K1123 Kashiyama, все AIKO, KITTO и VIC под одним и тем же номером и т.д. и т.п.
				//////////	

				$query31 = "SELECT * ";	
				$query31.= " FROM _ostatki";
				$query31.= " WHERE hash_artikul = '{$prep_artikul}'";
				// $query31.= " AND firma = '{$samkross['proizvoditel']}'";				
					if(!isset($_POST['nuli'])) { // если галочку не отмечали (по умолчанию), то показывать всё, что в наличии
						$query31 .= " AND kolvo > 0";
					}
				$ostatki_nabor = mysqli_query($connection, $query31);
				
				if($ostatki_nabor && mysqli_affected_rows($connection) > 0) { // Если есть ответ от базы данных и количество строк не ноль, а больше, то сообщение
						$admininfo.= "IS samkross in ostatki. It may be 0 by kolvo.<br/>";
						
					} else { // иначе другое сообщение
						$admininfo.= "<br/>NOT samkross in ostatki.<br/>";		
							}
				
				//confirm_query($ostatki_nabor); // Не будем делать confirm_query, дадим странице html дозавершиться.
				
				while ($ostatkikrossa = mysqli_fetch_assoc($ostatki_nabor)) {
?>
					<tr>
						<td><?php echo htmlentities($ostatkikrossa["firma"]);?></td>			
						<td><?php echo htmlentities($ostatkikrossa["artikul"]);?></td>
						<td><?php echo htmlspecialchars($ostatkikrossa["naimenovanie"]);?></td>
						<td><?php echo htmlentities($ostatkikrossa["kolvo"]);?></td>
						<td><?php echo htmlentities($ostatkikrossa["zakup"]);?></td>
						<td><?php $sell = (int)htmlentities($ostatkikrossa['zakup'])*1.4;
									$sell = $sell + 15;
									//echo $sell;
									echo ceil($sell/50) * 50; // округление до кратного 50 вверх;?></td>
						<td><?php echo htmlentities($ostatkikrossa["postavschik"]);?></td>
					</tr>
	<?php
				}
				mysqli_free_result($ostatki_nabor); 
							
				//////////
				// Закончили 4.0 проверку вбитого номера на прямое совпадение с самим собой в остатках.
				/////////
	?>				
			
<?php
		} // конец if(!is_null($proizvod_array))
?>

<?php
    } // конец if (isset($_POST['crossnumsubmit']))?>	
	</tbody>
	</table>
	<!-- </div> -->
	<br/>

<?php 	// if (isset($_POST['crossnumsubmit'])) закончился, но мы продолжаем пользоваться некоторыми значениями оттуда, например, count_array

	if (isset($count_array) && ($count_array['COUNT(*)'] > 1)) {
			// Если производителей по базе оказалось больше одного, то строим форму под выбор необходимого.
				
			$query01 = "SELECT *"; 
			$query01 .= " FROM baza ";
			$query01 .= " WHERE crossnum = '{$prep_artikul}'";
			echo $query01;
			$nabor_artikulov = mysqli_query($connection, $query01);
			if (!$nabor_artikulov){
			die("Database did not return query01.");};

			while($proizvod_array = mysqli_fetch_assoc($nabor_artikulov)) { 
			
?>

<form name="firmasubmit" method="post" action="tecdoc_poisk_admin.php">
  
  <input type="text" name="firma" id="firma" value="<?php echo htmlentities($proizvod_array["proizvoditel"]);?>" readonly/>
  <input type="text" name="artikul" id="artikul" value="<?php echo htmlentities($proizvod_array["crossnum"]);?>" readonly/> &nbsp;
  <input type="submit" name="crossnumsubmit" id="crossnumsubmit" value="Найти" />  &nbsp;
  <input type="checkbox" name="nuli" id="nuli"/> Показать и то, чего нет в наличии <br/>
   
</form>

<?php 					
			} // конец while($proizvod_array = mysqli_fetch_assoc($nabor_artikulov))
	
			mysqli_free_result($nabor_artikulov); 

?> 
<?php 	

	} // конец if (isset($count_array) && ($count_array['COUNT(*)'] > 1))
		
?>	
	
<?php 
	
if ($layout_context == "admin") {
	echo $admininfo;
};
?>
<br/>
<?php 
	//echo phpversion();	
	echo "<br/>";
	// Замеряем время обработки php-скрипта
	// Пример взят с http://php.net/manual/ru/function.microtime.php
	$time_end = microtime(true);  
	$time = $time_end - $time_start;
	$obrez_time = number_format ($time, 4);
	echo "Запрос занял $obrez_time секунд\n";
 ?>

	</div> <!-- конец page -->
	</div> 	<!-- конец main --> 

<?php include("../../includes/layouts/footer.php"); ?>