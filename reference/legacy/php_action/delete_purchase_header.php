<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$purchaseheaderid = mysqli_real_escape_string($con, clean_input($_POST["purchaseheaderid"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($purchaseheaderid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	else
    	{	

			$sqldeleteoem = "update purchase_header set is_active = 0 where id = ".$purchaseheaderid;

			if(mysqli_query($con,$sqldeleteoem))
			{	

				$res["status"] = 1;
				$res['msg'] = 'Purchase Header Deleted';		
			}
			else
			{
				$res['msg'] = 'Error : Failed to delete!';		
			}
		}
	}
	else
	{
		$res['msg'] = "Error : Invalid form submission method";
	}


	// echo mysqli_error($con);
	mysqli_close($con);
	echo json_encode($res);

?>