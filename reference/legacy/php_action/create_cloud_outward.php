<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["create-co-project-id"]));
	$billid = mysqli_real_escape_string($con, clean_input($_POST["create-co-bill-id"]));
	$createdby = mysqli_real_escape_string($con, clean_input($_POST["create-co-created-by"]));
	$salestotal = mysqli_real_escape_string($con, clean_input($_POST["create-co-sales-total"]));
	$remark = mysqli_real_escape_string($con, clean_input($_POST["create-co-remark"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($projectid == "" || $billid == "" || $createdby == "" || $salestotal == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters';		
    	}
    	else
    	{	

			$sqlcreateco = "insert into cloud_outward 
			(
				project_id,
				bill_id,
				sales_total,
				remark,
				is_cancelled,
				created_by,			
				created_on	
			) 
			values 
			(
				".$projectid.",
				".$billid.",
				".$salestotal.",
				'".$remark."',
				0,
				".$createdby.",
				'".date('Y-m-d H:i:s')."'
			)";

			if(mysqli_query($con,$sqlcreateco))
			{	

				$res["status"] = 1;
				$res['msg'] = 'CO Created';		
			}
			else
			{
				$res['msg'] = 'Error : Failed to create!';		
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