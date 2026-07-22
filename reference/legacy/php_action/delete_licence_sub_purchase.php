<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["projectid"]));
	$projectitemid = mysqli_real_escape_string($con, clean_input($_POST["projectitemid"]));
	$subpurchaseid = mysqli_real_escape_string($con, clean_input($_POST["subpurchaseid"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($projectid == "" || $subpurchaseid == "" || $projectitemid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	else
    	{	

			$sqlupdatequantity = "update licence_project_item set purchase_quantity = (purchase_quantity - (select quantity from licence_sub_purchase where id = ".$subpurchaseid." AND project_id = ".$projectid.")) where id = ".$projectitemid." AND project_id = ".$projectid;

			if(mysqli_query($con,$sqlupdatequantity))
			{	
				$sqldeletesubpurchase = "delete from licence_sub_purchase where id = ".$subpurchaseid." AND project_id = ".$projectid;

				if(mysqli_query($con,$sqldeletesubpurchase))
				{	

					$res["status"] = 1;
					$res['msg'] = 'Sub Purchase Deleted';		
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