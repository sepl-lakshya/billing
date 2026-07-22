<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$cloudinwardid = mysqli_real_escape_string($con, clean_input($_POST["cancel-invoice-ci-id"]));
	$projectid = mysqli_real_escape_string($con, clean_input($_POST["cancel-invoice-ci-project-id"]));
	$reason = mysqli_real_escape_string($con, clean_input($_POST["cancel-invoice-ci-reason"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($cloudinwardid == "" || $projectid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	elseif($reason == "")
    	{
    		$res['msg'] = 'Please enter reason for cancelling this CI';		
    	}
    	else
    	{	

			$sqlcancelcloudinward = "update invoice_cloud_inward set is_cancelled = 1, cancel_reason = '".$reason."' where id = ".$cloudinwardid." AND project_id = ".$projectid;

			if(mysqli_query($con,$sqlcancelcloudinward))
			{	

				$res["status"] = 1;
				$res['msg'] = 'Cloud Inward Cancelled';		
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