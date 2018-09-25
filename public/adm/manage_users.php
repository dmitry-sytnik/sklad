<?php require_once("../../includes/session.php"); ?>
<?php require_once("../../includes/db_connection.php"); ?>
<?php require_once("../../includes/functions.php") ?>

<?php confirm_logged_in_admin(); // эта функция проверяет, существует ли сессия админа, и если нет, то перенаправляет на главную
				
		//if (logged_in_user()){ // если  сюда пришел пользователь с сессией
		//	redirect_to("index.php"); // возвращаем его на главную страницу для пользователей
		//}
		//if (logged_in_manager()){ // если  сюда пришел менеджер с сессией
		//	redirect_to("index.php"); // возвращаем его на главную страницу для пользователей
		//}
?>
<?php //$admin_set = find_all_admins();?>
<?php $manager_set = find_all_managers();?>
<?php $user_set = find_all_users();?>

<?php $layout_context = "admin";?>
<?php include("../../includes/layouts/header.php") ?>

<div id="page">
		<?php echo message(); ?>
        <h2>Manage Managers</h2>
		
			<table width="400" border="0">
			  <tr>
				<td><strong>Managers</strong></td>
				<td><strong>Actions</strong></td>
			  </tr>
			  
			  <?php while($manager = mysqli_fetch_assoc($manager_set)) {  ?>
			  <tr>
				<td><?php echo htmlentities($manager["username"]);?>
				<!-- <br/> -->
				<?php //echo htmlentities($admin["hashed_password"]);?>
				</td>
				<td><a href="edit_manager.php?id=<?php echo urlencode($manager["id"]);?>">Edit </a>
					&nbsp;
					<a href="delete_manager.php?id=<?php echo urlencode($manager["id"]);?>" onclick="return confirm('Are you sure?');"> Delete</a>				
				</td>
			  </tr>			  
			  <?php } ?>
			</table>
			<?php mysqli_free_result($manager_set);?>
			<br/>
			<a href="new_manager.php">Add a new manager</a>
			
			</br>
			</br>
			<hr />
			</br>
			
			<h2>Manage Users</h2>
		
			<table width="400" border="0">
			  <tr>
				<td><strong>Username</strong></td>
				<td><strong>Actions</strong></td>
			  </tr>
			  
			  <?php while($user = mysqli_fetch_assoc($user_set)) {  ?>
			  <tr>
				<td><?php echo htmlentities($user["username"]);?>
				<!-- <br/> -->
				<?php //echo htmlentities($admin["hashed_password"]);?>
				</td>
				<td><a href="edit_user.php?id=<?php echo urlencode($user["id"]);?>">Edit </a>
					&nbsp;
					<a href="delete_user.php?id=<?php echo urlencode($user["id"]);?>" onclick="return confirm('Are you sure?');"> Delete</a>				
				</td>
			  </tr>			  
			  <?php } ?>
			</table>
			<?php mysqli_free_result($user_set);?>
			<br/>
			<a href="new_user.php">Add a new user</a>
			
	</div> <!-- конец page -->
	</div> 	<!-- конец main -->

<?php include("../../includes/layouts/footer.php"); ?>