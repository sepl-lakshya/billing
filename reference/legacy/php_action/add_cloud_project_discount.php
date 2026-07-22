<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["discount-project-id"]));
	$ridiscount = mysqli_real_escape_string($con, clean_input($_POST["add-ri-discount"]));
	$paygdiscount = mysqli_real_escape_string($con, clean_input($_POST["add-payg-discount"]));
	$creditdays = mysqli_real_escape_string($con, clean_input($_POST["add-credit-days"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($projectid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	elseif($ridiscount == "")
    	{
    		$res['msg'] = 'Please enter RI discount';		
    	}
    	elseif($paygdiscount == "")
    	{
    		$res['msg'] = 'Please select PAYG discount';		
    	}
    	elseif($creditdays == "")
    	{
    		$res['msg'] = 'Please enter credit days';		
    	}
    	else
    	{	
			$sqladddiscount = "insert into project_discount 
			(
				project_id,
				from_date,
				to_date,	
				ri_discount,		
				payg_discount,				
				credit_days,				
				created_on	
			) 
			values 
			(
				".$projectid.",
				(select start_date from project where id = ".$projectid."),
				NULL,
				".$ridiscount.",
				".$paygdiscount.",
				".$creditdays.",
				'".date('Y-m-d')."'
			)";

			if(mysqli_query($con,$sqladddiscount))
			{	

				$res["status"] = 1;
				$res['msg'] = 'Discount added';		
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