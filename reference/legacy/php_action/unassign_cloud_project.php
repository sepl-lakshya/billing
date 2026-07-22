<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["projectid"]));
	$userid = mysqli_real_escape_string($con, clean_input($_POST["userid"]));
	
    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($projectid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	else
    	{	
			$sqlunassignuser = "delete from project_user_mapping where project_id = ".$projectid." AND user_id = ".$userid;

			if(mysqli_query($con,$sqlunassignuser))
			{	

				$res["status"] = 1;
				$res['msg'] = 'User Unassigned';		
			}
			else
			{
				$res['msg'] = 'Error : Failed to unassign!';		
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