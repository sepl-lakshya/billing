	<?php
	if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }
	include "../include/config.php";	
	
	$username = clean_input($_POST["username"]);
	$password = clean_input($_POST["password"]);
	

	if(empty($username))
	{
		$_SESSION["login-error"] = "Please enter email.";
		header("location:".getDomain());
	}
	elseif(empty($password))
	{
		$_SESSION["login-error"] = "Please enter password.";
		header("location:".getDomain());
	}
	else
	{	
		 
		if(isset($_POST['login-submit']) && $_POST['login-submit'] == "Login")
		{
			$con = connectMySQL();
			$sql = "select *,(select name from user_type where value = ld.user_type) as user_type_name from login_detail as ld  where username = '".$username."'";
			$row = mysqli_query($con,$sql);
			if(mysqli_num_rows($row) > 0)
			{
				$res = mysqli_fetch_array($row);
				if($res["is_active"] == "1")
				{ 
					if(password_verify($password, $res["password"]))
					{
						$_SESSION["user_id"] = $res["id"];
						$_SESSION["username"] = $res["username"];
						$_SESSION["full_name"] = $res["full_name"];
						$_SESSION["user_type"] = $res["user_type"];
						$_SESSION["user_type_name"] = $res["user_type_name"];
						header("location:".getDomain()."/dashboard".getPageExt());
					}
					else
					{
						$_SESSION["login-error"] = "Invalid Credentials";
						header("location:".getDomain());
					}
				}
				else
				{
					$_SESSION["login-error"] = "Your Account has been deactivated !";
					header("location:".getDomain());
				}

			}
			else
			{
				$_SESSION["login-error"] = "Invalid Credentials! User not found.";
				header("location:".getDomain());
			}

		}
		else
		{
			$_SESSION["login-error"] = "Error : Invalid submission method";
			header("location:".getDomain());
		}

	}

	mysqli_error($con);
	mysqli_close($con);

?>