<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["create-purchase-project-id"]));
	$projectitemid = mysqli_real_escape_string($con, clean_input($_POST["create-purchase-project-item-id"]));
	$subscriptionterm = mysqli_real_escape_string($con, clean_input($_POST["purchase-subscription-term"]));
	$billingterm = mysqli_real_escape_string($con, clean_input($_POST["purchase-billing-term"]));
	$distributor = mysqli_real_escape_string($con, clean_input($_POST["purchase-distributor"]));
	$itemstatus = mysqli_real_escape_string($con, clean_input($_POST["create-purchase-item-status"]));
	

    $res['msg'] = "";
    $res['status'] = 0;
    $uniqueOk = 0;

    
    if(isset($_POST))
    {
    	if($projectid == "" || $projectitemid == "" || $itemstatus == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters';		
    	}
    	elseif($subscriptionterm == "0")
    	{
    		$res['msg'] = 'Please select subscription term';		
    	}
    	elseif($billingterm == "0")
    	{
    		$res['msg'] = 'Please select billing term';		
    	}
    	elseif($distributor == "0")
    	{
    		$res['msg'] = 'Please select distributor';		
    	}
    	else
    	{	
    		if($itemstatus == "1")
    		{
    			$statusvalue = ", status = 2 ";
    		}
    		else
    		{
    			$statusvalue = "";
    		}

				$sqlcreatepurchase = "update licence_project_item 
					set
					purchase_subscription_term = ".$subscriptionterm.",
					purchase_billing_term = ".$billingterm.",
					distributor = ".$distributor."
					".$statusvalue."
					where
					project_id = ".$projectid." AND id = ".$projectitemid;

				if(mysqli_query($con,$sqlcreatepurchase))
				{	
					$res["status"] = 1;
					$res['msg'] = 'Purchase Created';	
				}
				else
				{
					$res['msg'] = 'Error : Failed to create purchase!';		
				}
			// }
		}
	}
	else
	{
		$res['msg'] = "Error : Invalid form submission method";
	}


	mysqli_close($con);
	echo json_encode($res);

?>