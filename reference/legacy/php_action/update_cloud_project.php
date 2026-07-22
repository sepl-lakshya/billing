<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["edit-project-id"]));
	$projecthash = mysqli_real_escape_string($con, clean_input($_POST["edit-project-hash"]));
	$city = mysqli_real_escape_string($con, clean_input($_POST["edit-project-city"]));
	$state = mysqli_real_escape_string($con, clean_input($_POST["edit-project-state"]));
	$tenderno = mysqli_real_escape_string($con, clean_input($_POST["edit-tender-ref-no"]));
	$desc = mysqli_real_escape_string($con, clean_input($_POST["edit-project-description"]));

    $res['msg'] = "";
    $res['status'] = 0;
    $uniqueOk = 0;

    
    if(isset($_POST))
    {
    	if($projectid == "" || $projecthash == "")
    	{
    		$res['msg'] = "Error : Missing Parameters !";
    	}
    	elseif($city == "")
    	{
    		$res['msg'] = 'Please enter project city';		
    	}
    	elseif($state == "0")
    	{
    		$res['msg'] = 'Please select project state';		
    	}
    	elseif($tenderno == "")
    	{
    		$res['msg'] = 'Please enter tender number';		
    	}
    	else
    	{	

				$sqlcreateproject = "update project set 
					city = '".$city."',		
					state = '".$state."',		
					tender_ref_no = '".$tenderno."',		
					description = '".$desc."' 
					where 
					id = ".$projectid." AND hash = '".$projecthash."'		
				";

				if(mysqli_query($con,$sqlcreateproject))
				{	
						$res["status"] = 1;
						$res['msg'] = 'Project Updated';		
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