<?php
	function redirect_to($new_location) {
		header("Location: ".$new_location);
		exit;
	}
	
	function form_errors($errors=array()) {
	$output = "";
	if (!empty($errors)) {
	  $output .= "<div class=\"error\">";
	  $output .= "Please fix the following errors:";
	  $output .= "<ul>";
	  foreach ($errors as $key => $error) {
	    $output .= "<li>";
		$output .= htmlentities($error);
		$output .= "</li>";
	  }
	  $output .= "</ul>";
	  $output .= "</div>";
	}
	return $output;
}
	
	function mysql_prep($string){
		global $connection;
		
		$escaped_string = mysqli_real_escape_string($connection, $string);
				
		return 	$escaped_string;
	}
	
	function mysql_crossnum_prep($string){
		global $connection;
		
		$escaped_string = mysqli_real_escape_string($connection, $string);
		
		$udalyaemoe = array("-",",", "	", "=", "+", " ", "*", "?", "_", "(", ")", ".", "/", "\\"); //обратный слэш ставится с предваряющим обратным слэшем, иначе php отключает дальнейший код.
		$prep_artikul  = str_replace($udalyaemoe, "", $escaped_string);
		
		return 	$prep_artikul;
	}

	function confirm_query($result_set) {
		if (!$result_set){
		die("Database do not returned anything. Function confirm_query failed or its using in others func-s failed.");}
	}

	function public_navigation() {
		$output ="<ul class=\"subjects\">";
						
		$output .= "<li";
		$output .= ">"; 
			if(isset($_SESSION["admin_id"])) { // Если есть админ в сессии, то ссылки на админские страницы, иначе - на обычные
				$output .= "<a href=\"#";  // временно отключено: cross_poisk.php
				$output .= "\">";
				} else {
				$output .= "<a href=\"#";
				$output .= "\">";
			}				
		$output .= "Поиск по кроссу</a></li><br/>";
		
		$output .= "<li";
		$output .= ">";
			if(isset($_SESSION["admin_id"])) { // Если есть админ в сессии, то ссылки на админские страницы, иначе - на обычные
				$output .= "<a href=\"pryam_poisk_admin.php";
				$output .= "\">";
				} else {
				$output .= "<a href=\"pryam_poisk.php";
				$output .= "\">";
			}		
		$output .= "Поиск по прямому артикулу</a></li><br/>";
		
		$output .= "<li";
		$output .= ">"; 
			if(isset($_SESSION["admin_id"])) { // Если есть админ в сессии, то ссылки на админские страницы, иначе - на обычные
				$output .= "<a href=\"tecdoc_poisk_admin.php";
				$output .= "\">";
				} else {
				$output .= "<a href=\"tecdoc_poisk.php";
				$output .= "\">";
			}				
		$output .= "Поиск по неоригинальным кроссам TecDoc</a></li><br/>";
		
		$output .= "<li";
		$output .= ">"; 
		$output .= "<a href=\"#";
		$output .= "\">";
		$output .= "Просмотр всего склада</a></li><br/>";
		
		
		
		
		// Начало ссылок по управлению для админов
		$output .= "<li";
		$output .= ">";
			if(isset($_SESSION["admin_id"])) { // Если есть админ в сессии, то ссылки на админские страницы
				$output .= "-------------";
				$output .= "</br>";
				$output .= "<a href=\"manage_users.php";
				$output .= "\">";
				$output .= "Управление пользователями (в разработке)</a></li><br/>";
				} else {			}		
		
		
		$output.= "</ul>";
		
		return $output;
	}
	/////////
	// Здесь начинаются функции входа админов
	/////////
	function attempt_login_admin($username, $password) {
		$admin = find_admin_by_username($username);
		if ($admin) {
			// если админ найден, то проверка его пароля
			if (password_check($password, $admin["hashed_password"])) {
				// пароль подходит
				return $admin;
			} else {
				// пароль не подходит
				return false;
			}
		
		} else {
			// если админ не найден, то false
			return false;			
		}
	}
	
	function find_admin_by_username($username) {
		global $connection;
		
		$safe_username = mysqli_real_escape_string($connection, $username);
		
		$query  = "SELECT * ";
		$query .= "FROM _admins ";
		$query .= "WHERE username = '{$safe_username}' ";
		$query .= "LIMIT 1";
		$admina_nabor = mysqli_query($connection, $query);
		// Test if there was a query error
		confirm_query($admina_nabor);
		
		if ($admin = mysqli_fetch_assoc($admina_nabor)) {
			return $admin; // $admin == $admin_array
		} else {
			return null;
		}
		
	}
	
	function password_check($password, $existing_hash) {
		
		$hash = sha1($password); 
		// упрощенный вариант, без соли; следует использовать функцию crypt и т.д.
		if ($hash === $existing_hash) {
			return true;
		} else {
			return false;
		}
	}
	/////////
	// Здесь заканчиваются функции входа админов
	/////////
	
	function find_all_admins() {
		global $connection;
		
		$query  = "SELECT * ";
		$query .= "FROM _admins ";		
		$query .= "ORDER BY username ASC";
		$adminov_nabor = mysqli_query($connection, $query);
		// Test if there was a query error
		confirm_query($adminov_nabor);
		return $adminov_nabor;
	}
	
	function find_all_managers() {
		global $connection;
		
		$query  = "SELECT * ";
		$query .= "FROM _managers ";		
		$query .= "ORDER BY username ASC";
		$managerov_nabor = mysqli_query($connection, $query);
		// Test if there was a query error
		confirm_query($managerov_nabor);
		return $managerov_nabor;
	}
	
	function find_all_users() {
		global $connection;
		
		$query  = "SELECT * ";
		$query .= "FROM _users ";		
		$query .= "ORDER BY username ASC";
		$userov_nabor = mysqli_query($connection, $query);
		// Test if there was a query error
		confirm_query($userov_nabor);
		return $userov_nabor;
	}
	
	/////////
	// Здесь начинаются функции входа пользователей
	/////////
	function attempt_login_user($username, $password) {
		$user = find_user_by_username($username);
		if ($user) {
			// если пользователь найден, то проверка его пароля
			if (password_check($password, $user["hashed_password"])) {
				// пароль подходит
				return $user;
			} else {
				// пароль не подходит
				return false;
			}
		
		} else {
			// если пользователь не найден, то false
			return false;			
		}
	}
	
	function find_user_by_username($username) {
		global $connection;
		
		$safe_username = mysqli_real_escape_string($connection, $username);
		
		$query  = "SELECT * ";
		$query .= "FROM _users ";
		$query .= "WHERE username = '{$safe_username}' ";
		$query .= "LIMIT 1";
		$usera_nabor = mysqli_query($connection, $query);
		// Test if there was a query error
		confirm_query($usera_nabor);
		
		if ($user = mysqli_fetch_assoc($usera_nabor)) {
			return $user; // $user == $user_array
		} else {
			return null;
		}
		
	}
	
	// Функция function password_check($password, $existing_hash) одинакова у пользователей, менеджеров и админов, поэтому она больше не повторяется
	
	function attempt_login_manager($username, $password) {
		$manager = find_manager_by_username($username);
		if ($manager) {
			// если пользователь найден, то проверка его пароля
			if (password_check($password, $manager["hashed_password"])) {
				// пароль подходит
				return $manager;
			} else {
				// пароль не подходит
				return false;
			}
		
		} else {
			// если пользователь не найден, то false
			return false;			
		}
	}
	
	function find_manager_by_username($username) {
		global $connection;
		
		$safe_username = mysqli_real_escape_string($connection, $username);
		
		$query  = "SELECT * ";
		$query .= "FROM _managers ";
		$query .= "WHERE username = '{$safe_username}' ";
		$query .= "LIMIT 1";
		$managerov_nabor = mysqli_query($connection, $query);
		// Test if there was a query error
		confirm_query($managerov_nabor);
		
		if ($manager = mysqli_fetch_assoc($managerov_nabor)) {
			return $manager; // $manager == $manager_array
		} else {
			return null;
		}
		
	}
		
	/////////
	// Здесь заканчиваются функции входа пользователей
	/////////
	
	function logged_in_admin() {
		return isset($_SESSION['admin_id']);
	}
	
	function confirm_logged_in_admin() {
		if (!logged_in_admin() ){
			redirect_to("index.php"); // ошибка входа у админов возвращает на главную страницу 
		}
	}
	
	function logged_in_manager() {
		return isset($_SESSION['manager_id']);
	}
	
	function confirm_logged_in_manager() {
		if (!logged_in_manager()){
			redirect_to("login.php"); // ошибка входа у менеджерей возвращает их на страницу входа для пользователей
		}
	}	
	
	function logged_in_user() {
		return isset($_SESSION['user_id']);
	}
	
	function confirm_logged_in_user() {
		if (!logged_in_user()){
			redirect_to("login.php"); // ошибка входа у пользователей возвращает на страницу входа для пользователей
		}
	}	
	
	function find_oemstroku_by_GET_id() {
		global $connection;

		$safe_oemnumber_id = mysqli_real_escape_string($connection, $_GET['id']);
		$safe_oembrand = mysqli_real_escape_string($connection, $_GET['brand']);
		
		$query  = "SELECT * ";
		$query .= "FROM {$safe_oembrand}_oem ";
		$query .= "WHERE id = {$safe_oemnumber_id} ";		
		$query .= "LIMIT 1";
		$oemnumber_set = mysqli_query($connection, $query);
		// Test if there was a query error
		confirm_query($oemnumber_set);
		
		if (!mysqli_affected_rows($connection) == 1) {		
			$_SESSION["message"] = "Not id for edition or deleted.";		
			redirect_to("delete_oemnumber.php");	} 
		
		
		if ($oemstroka = mysqli_fetch_assoc($oemnumber_set)) {
			return $oemstroka;
		} else {
			return null;
		}
	}
	
	function find_oemstroku_by_POST_id() {
		global $connection;

		$safe_oemnumber_id = mysqli_real_escape_string($connection, $_POST['id']);
		$safe_oembrand = mysqli_real_escape_string($connection, $_SESSION['brand']);
		
		$query  = "SELECT * ";
		$query .= "FROM {$safe_oembrand}_oem ";
		$query .= "WHERE id = {$safe_oemnumber_id} ";		
		$query .= "LIMIT 1";
		$oemnumber_set = mysqli_query($connection, $query);
		// Test if there was a query error
		
		if (!$oemnumber_set){
		die("NOT oemnumber_set in the func(find_oemstroku_by_POST_id). Database do not returned anything.<br/> Возможно, был удален id из поля.");}
		
		
		if (!mysqli_affected_rows($connection) == 1) {		
			$_SESSION["message"] = "Not found stroku by POST id.";		
			redirect_to("index_admin.php");	} 
		
		
		if ($oemstroka = mysqli_fetch_assoc($oemnumber_set)) {
			return $oemstroka;
		} else {
			return null;
		}
	}
	
	function find_nooemstroku_by_GET_id() {
		global $connection;

		$safe_oemnumber_id = mysqli_real_escape_string($connection, $_GET['id']);
		$safe_oembrand = mysqli_real_escape_string($connection, $_GET['brand']);
		
		$query  = "SELECT * ";
		$query .= "FROM {$safe_oembrand}_nooem ";
		$query .= "WHERE id = {$safe_oemnumber_id} ";		
		$query .= "LIMIT 1";
		$oemnumber_set = mysqli_query($connection, $query);
		// Test if there was a query error
		confirm_query($oemnumber_set);
		
		if (!mysqli_affected_rows($connection) == 1) {		
			$_SESSION["message"] = "Not id for edition or deleted.";		
			redirect_to("delete_nooemnumber.php");	} 
		
		
		if ($oemstroka = mysqli_fetch_assoc($oemnumber_set)) {
			return $oemstroka;
		} else {
			return null;
		}
	}
	
	function find_nooemstroku_by_POST_id() {
		global $connection;

		$safe_oemnumber_id = mysqli_real_escape_string($connection, $_POST['id']);
		$safe_oembrand = mysqli_real_escape_string($connection, $_SESSION['brand']);
		
		$query  = "SELECT * ";
		$query .= "FROM {$safe_oembrand}_nooem ";
		$query .= "WHERE id = {$safe_oemnumber_id} ";		
		$query .= "LIMIT 1";
		$oemnumber_set = mysqli_query($connection, $query);
		// Test if there was a query error
		
		if (!$oemnumber_set){
		die("NOT nooemnumber_set in the func(find_oemstroku_by_POST_id). Database do not returned anything.<br/> Возможно, был удален id из поля.");}
		
		
		if (!mysqli_affected_rows($connection) == 1) {		
			$_SESSION["message"] = "Not found stroku by POST id.";		
			redirect_to("index.php");	} 
		
		
		if ($oemstroka = mysqli_fetch_assoc($oemnumber_set)) {
			return $oemstroka;
		} else {
			return null;
		}
	}
	
	
