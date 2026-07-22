<?php
	include "include/config.php";
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
	unset($_SESSION["login_id"]);
	unset($_SESSION["company_id"]);
	unset($_SESSION["department_id"]);
	unset($_SESSION["role_id"]);

	session_destroy();
	header("location:".getDomain());
?>