<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["edit-header-project-id"]));
	$headerid = mysqli_real_escape_string($con, clean_input($_POST["edit-header-id"]));
	$headername = mysqli_real_escape_string($con, clean_input($_POST["edit-header-name"]));
	$quantity = mysqli_real_escape_string($con, clean_input($_POST["edit-header-quantity"]));
	$desc = mysqli_real_escape_string($con, clean_input($_POST["edit-header-description"]));
	
    $res['msg'] = "";
    $res['status'] = 0;
    $uniqueOk = 0;

    
    if(isset($_POST))
    {
    	if($projectid == "" || $headerid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters';		
    	}
    	elseif($headername == "")
    	{
    		$res['msg'] = 'Please enter header name.';		
    	}
    	elseif($quantity == "")
    	{
    		$res['msg'] = 'Please enter header quantity.';		
    	}
    	else
    	{	

			$sqlupdateheader = "update project_header set
				name = '".$headername."',
				quantity = ".$quantity.",
				description = '".$desc."'
				where project_id = ".$projectid." AND id = ".$headerid;

			
			if(mysqli_query($con,$sqlupdateheader))	
			{
				$res["status"] = 1;
				$res['msg'] = 'Header Added';		
			}			
			else
			{
				$res['msg'] = "Error : Failed to add";
			}

		}
	}
	else
	{
		$res['msg'] = "Error : Invalid form submission method";
	}


	echo mysqli_error($con);
	mysqli_close($con);
	echo json_encode($res);

?>