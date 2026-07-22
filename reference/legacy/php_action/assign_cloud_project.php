<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["assign-project-id"]));
	$userid = mysqli_real_escape_string($con, clean_input($_POST["assign-user-id"]));
	
    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($projectid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	elseif($userid == "0")
    	{
    		$res['msg'] = 'Please select user';		
    	}
    	else
    	{	

			$sqlassignuser = "insert into project_user_mapping 
				(
					project_id,
					user_id,
					assigned_by,
					created_on	
				) 
				values 
				(
					".$projectid.",
					".$userid.",
					".$_SESSION['user_id'].",
					'".date('Y-m-d H:i:s')."'
				)";

			if(mysqli_query($con,$sqlassignuser))
			{	

				$res["status"] = 1;
				$res['msg'] = 'User Assigned';		
			}
			else
			{
				$res['msg'] = 'Error : Failed to assign!';		
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