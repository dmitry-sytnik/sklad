
<div id="line">	</div>
<div id="footer">Copyright <?php echo date("Y"); ?>, Poisk po skladu</div>
	</body>
</html>

<?php
  // 5. Close database connection
  if (isset($connection)){
	  mysqli_close($connection);}
  
?>