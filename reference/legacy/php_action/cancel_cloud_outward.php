<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$cloudoutwardid = mysqli_real_escape_string($con, clean_input($_POST["cancel-co-id"]));
	$projectid = mysqli_real_escape_string($con, clean_input($_POST["cancel-co-project-id"]));
	$reason = mysqli_real_escape_string($con, clean_input($_POST["cancel-co-reason"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($cloudoutwardid == "" || $projectid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	elseif($reason == "")
    	{
    		$res['msg'] = 'Please enter reason for cancelling this CO';		
    	}
    	else
    	{	

			$sqlcancelcloudinward = "update cloud_outward set is_cancelled = 1, cancel_reason = '".$reason."' where id = ".$cloudoutwardid." AND project_id = ".$projectid;

			if(mysqli_query($con,$sqlcancelcloudinward))
			{	

				$res["status"] = 1;
				$res['msg'] = 'Cloud Outward Cancelled';		
			}
			else
			{
				$res['msg'] = 'Error : Failed to cancel!';		
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