<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectitemid = mysqli_real_escape_string($con, clean_input($_POST["deactivate-project-item-id"]));
	$deployenddate = mysqli_real_escape_string($con, clean_input($_POST["deactivate-item-deployment-end"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($projectitemid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	else
    	{	

			$sqldisableprojectitem = "update project_item set status = 0, deployment_end = '".$deployenddate."' where id = ".$projectitemid;

			if(mysqli_query($con,$sqldisableprojectitem))
			{	

				$res["status"] = 1;
				$res['msg'] = 'Item Deactivated';		
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