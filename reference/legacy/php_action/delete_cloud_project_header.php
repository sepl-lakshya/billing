<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$headerid = mysqli_real_escape_string($con, clean_input($_POST["headerid"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($headerid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	else
    	{	

			$sqldeleteprojectitem = "update project_item set is_deleted = 1 where header_id = ".$headerid;

			if(mysqli_query($con,$sqldeleteprojectitem))
			{	
				$sqldeleteprojectheader = "update project_header set is_deleted = 1 where id = ".$headerid;
				if(mysqli_query($con,$sqldeleteprojectheader))
				{	

					$res["status"] = 1;
					$res['msg'] = 'Header Deleted';		
				}
				else
				{
					$res['msg'] = 'Error : Failed to delete!';		
				}
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