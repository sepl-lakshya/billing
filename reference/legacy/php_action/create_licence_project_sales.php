<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["create-sale-project-id"]));
	$projectitemid = mysqli_real_escape_string($con, clean_input($_POST["create-sale-project-item-id"]));
	$unitprice = mysqli_real_escape_string($con, clean_input($_POST["sale-unit-price"]));
	$quantity = mysqli_real_escape_string($con, clean_input($_POST["sale-quantity"]));
	$deploymentstart = mysqli_real_escape_string($con, clean_input($_POST["deployment-start"]));
	$deploymentend = mysqli_real_escape_string($con, clean_input($_POST["deployment-end"]));
	$subscriptionterm = mysqli_real_escape_string($con, clean_input($_POST["sale-subscription-term"]));
	$billingterm = mysqli_real_escape_string($con, clean_input($_POST["sale-billing-term"]));
	$contractyear = mysqli_real_escape_string($con, clean_input($_POST["contract-year"]));
	$contractmonth = mysqli_real_escape_string($con, clean_input($_POST["contract-month"]));
	
	if($deploymentend == "")
	{
		$deploymentend = "NULL";
	}

    $res['msg'] = "";
    $res['status'] = 0;
    $uniqueOk = 0;

    
    if(isset($_POST))
    {
    	if($projectid == "" || $projectitemid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters';		
    	}
    	elseif($unitprice == "")
    	{
    		$res['msg'] = 'Please enter unit price';		
    	}
    	elseif($quantity == "")
    	{
    		$res['msg'] = 'Please enter product quantity';		
    	}
    	elseif($deploymentstart == "")
    	{
    		$res['msg'] = 'Please select start date';		
    	}
    	elseif($subscriptionterm == "0")
    	{
    		$res['msg'] = 'Please select subscription term';		
    	}
    	elseif($billingterm == "0")
    	{
    		$res['msg'] = 'Please select billing term';		
    	}
    	elseif($contractyear == "0" && $contractmonth == "0")
    	{
    		$res['msg'] = 'Please select product deployment date';		
    	}
    	else
    	{	

				$sqladdproduct = "update licence_project_item 
					set
					deployment_start = '".$deploymentstart."',
					deployment_end = '".$deploymentend."',
					sale_unit_price = '".$unitprice."',
					sale_quantity = '".$quantity."',
					sale_subscription_term = ".$subscriptionterm.",
					sale_billing_term = ".$billingterm.",
					contract_year = ".$contractyear.",
					contract_month = ".$contractmonth.",
					status = 1
					where
					project_id = ".$projectid." AND id = ".$projectitemid;

				if(mysqli_query($con,$sqladdproduct))
				{	

					$res["status"] = 1;
					$res['msg'] = 'Sales Created';		
				}
				else
				{
					$res['msg'] = 'Error : Failed to craete sales!';		
				}
			// }
		}
	}
	else
	{
		$res['msg'] = "Error : Invalid form submission method";
	}


	echo mysqli_error($con);
	mysqli_close($con);
	echo json_encode($res);

?>