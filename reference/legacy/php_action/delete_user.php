<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$userid = mysqli_real_escape_string($con, clean_input($_POST["userid"]));
	$azureobjectid = mysqli_real_escape_string($con, clean_input($_POST["azureobjectid"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($userid == "" || $azureobjectid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	else
    	{	

			$sqldeleteuser = "update login_detail set is_active = 0 where id = ".$userid." AND azure_object_id = '".$azureobjectid."'";

			if(mysqli_query($con,$sqldeleteuser))
			{	

				$res["status"] = 1;
				$res['msg'] = 'User Deleted';		
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