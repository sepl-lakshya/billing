<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["projectid"]));
	$projecthash = mysqli_real_escape_string($con, clean_input($_POST["projecthash"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($projectid == "" || $projecthash == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	else
    	{	

			$sqldeleteproject = "delete from licence_project where id = ".$projectid." AND hash = '".$projecthash."'";
			if(mysqli_query($con,$sqldeleteproject))
			{	

				$sqldeleteprojectitem = "delete from licence_project_item where project_id = ".$projectid;
				if(mysqli_query($con,$sqldeleteprojectitem))
				{	
					
					$sqldeleteprojectsubpurchase = "delete from licence_sub_purchase where project_id = ".$projectid;
					if(mysqli_query($con,$sqldeleteprojectsubpurchase))
					{
						$sqldeleteprojectassignment = "delete from licence_project_user_mapping where project_id = ".$projectid;
						if(mysqli_query($con,$sqldeleteprojectassignment))
						{	
							$res["status"] = 1;
							$res['msg'] = 'Project Deleted';		
						}
						else
						{
							$res['msg'] = 'Error : Failed to delete project bill item!';		
						}
					}
					else
					{
						$res['msg'] = 'Error : Failed to delete project item!';		
					}
					
				}
				else
				{
					$res['msg'] = 'Error : Failed to delete project discount!';		
				}
			}
			else
			{
				$res['msg'] = 'Error : Failed to delete project!';		
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