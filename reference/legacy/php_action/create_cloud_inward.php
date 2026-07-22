<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["create-ci-project-id"]));
	$billid = mysqli_real_escape_string($con, clean_input($_POST["create-ci-bill-id"]));
	$createdby = mysqli_real_escape_string($con, clean_input($_POST["create-ci-created-by"]));
	$portaltotal = mysqli_real_escape_string($con, clean_input($_POST["create-ci-portal-total"]));
	$purchasetotal = mysqli_real_escape_string($con, clean_input($_POST["create-ci-purchase-total"]));
	$remark = mysqli_real_escape_string($con, clean_input($_POST["create-ci-remark"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($projectid == "" || $billid == "" || $createdby == "" || $portaltotal == "" || $purchasetotal == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters';		
    	}
    	else
    	{	

			$sqlcreateci = "insert into cloud_inward 
			(
				project_id,
				bill_id,
				portal_total,
				purchase_total,
				remark,
				is_cancelled,
				created_by,			
				created_on	
			) 
			values 
			(
				".$projectid.",
				".$billid.",
				".$portaltotal.",
				".$purchasetotal.",
				'".$remark."',
				0,
				".$createdby.",
				'".date('Y-m-d H:i:s')."'
			)";

			if(mysqli_query($con,$sqlcreateci))
			{	

				$res["status"] = 1;
				$res['msg'] = 'CI Created';		
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