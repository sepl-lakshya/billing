<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$productitemid = mysqli_real_escape_string($con, clean_input($_POST["productitemid"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($productitemid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	else
    	{	

			$sqldeleteproductitem = "update project_item set is_deleted = 1 where id = ".$productitemid;

			if(mysqli_query($con,$sqldeleteproductitem))
			{	

				$res["status"] = 1;
				$res['msg'] = 'Item Deleted';		
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