<?php
	if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }
    include "include/config.php";

    if(!isLoggedIn())
	{
	    header("location:https://portal.surbhi.net");

?>
<!DOCTYPE html>
<html>
<head>
	<title>Billing : Login</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="images/logo/sepl-color-trans.png">
	<link rel="stylesheet" type="text/css" href="css/login.css">
	
	  <script type="text/javascript" src="js/fontawesome.js"></script>

</head>
<body>
<div class="am-login-div">
	<div class="am-login-box">
		<form method="post" action="php_action/loginvalid.php" enctype="multipart/form-data">
			<h2>Welcome User</h2>
			<?php
				if(isset($_SESSION["login-error"]) && isset($_SESSION["login-error"]) != "")
			    {
			?>
				<!--<h4 class="login-error-msg">
				    <?php echo $_SESSION["login-error"]; ?><br>
				    <a href="<?php echo getDomain()."/logout".getPageExt(); ?>">
				         Click here to logout<i class="fa fa-sign-out" aria-hidden="true"></i> 
				    </a>
				</h4>-->
			<?php
			    	unset($_SESSION["login-error"]);
			    }
			?>
		<label>Username</label></br>
		<input type="text" name="username" id="username" placeholder="Username"></br>
		<label>Password</label></br>
		<input type="Password" name="password" id="password" placeholder="Password"></br>
		<span><a href="#">Forgot Password?</a></span>
		<input type="Submit" name="login-submit" id="login-submit" value="Login">
		<button><a href="">Need Help?</a> </button>
		</form>
             <!-- <div class="login-box-logo-wrapper">
    			<img src="<?php echo getImageDomain(); ?>/images/logo/sepl-color-white-text.png" height="100" width="75">
    		</div> -->
    		
		    <!-- <a href="<?php echo getDomain()."/callback".getPageExt(); ?>" class="azure-login-btn">Login</a>  -->
	</div>
</div>

</body>
</html>
<?php
	}
	else
	{
			header("location:".getDomain()."/dashboard".getPageExt());
	}
?>