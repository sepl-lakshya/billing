<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$productid = mysqli_real_escape_string($con, clean_input($_POST["productid"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($productid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	else
    	{	

			$sqldeleteproduct = "update product set is_active = 0 where id = ".$productid;

			if(mysqli_query($con,$sqldeleteproduct))
			{	

				$res["status"] = 1;
				$res['msg'] = 'Product Deleted';		
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