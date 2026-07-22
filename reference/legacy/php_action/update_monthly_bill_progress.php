<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["progress-project-id"]));
	$billid = mysqli_real_escape_string($con, clean_input($_POST["progress-bill-id"]));
	$progress = mysqli_real_escape_string($con, clean_input($_POST["bill-progress"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($projectid == "" || $billid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	elseif($progress == "0")
    	{
    		$res['msg'] = 'Please select progress';		
    	}
    	else
    	{	

			$sqlupdateprogress = "update bill set progress = ".$progress." where id = ".$billid." AND project_id = ".$projectid;

			if(mysqli_query($con,$sqlupdateprogress))
			{	

				$res["status"] = 1;
				$res['msg'] = 'Status Updated';		
			}
			else
			{
				$res['msg'] = 'Error : Failed to update!';		
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