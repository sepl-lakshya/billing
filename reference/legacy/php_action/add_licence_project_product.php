<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["project-id"]));
	$licencecategory = mysqli_real_escape_string($con, clean_input($_POST["licence-category"]));
	$product = mysqli_real_escape_string($con, clean_input($_POST["product"]));
	$description = mysqli_real_escape_string($con, clean_input($_POST["product-desc"]));
	// $distributor = mysqli_real_escape_string($con, clean_input($_POST["distributor"]));
	// $unitprice = mysqli_real_escape_string($con, clean_input($_POST["unit-price"]));
	// $quantity = mysqli_real_escape_string($con, clean_input($_POST["quantity"]));
	// $deploymentstart = mysqli_real_escape_string($con, clean_input($_POST["deployment-start"]));
	// $deploymentend = mysqli_real_escape_string($con, clean_input($_POST["deployment-end"]));
	// $subscriptionterm = mysqli_real_escape_string($con, clean_input($_POST["subscription-term"]));
	// $billingterm = mysqli_real_escape_string($con, clean_input($_POST["billing-term"]));
	// if($deploymentend == "")
	// {
	// 	$deploymentend = "NULL";
	// }

    $res['msg'] = "";
    $res['status'] = 0;
    $uniqueOk = 0;

    
    if(isset($_POST))
    {
    	if($projectid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters';		
    	}
    	elseif($licencecategory == "0")
    	{
    		$res['msg'] = 'Please select product';		
    	}
    	elseif($product == "0")
    	{
    		$res['msg'] = 'Please select product';		
    	}
    	// elseif($distributor == "0")
    	// {
    	// 	$res['msg'] = 'Please select distributor';		
    	// }
    	// elseif($unitprice == "")
    	// {
    	// 	$res['msg'] = 'Please enter unit price';		
    	// }
    	// elseif($quantity == "")
    	// {
    	// 	$res['msg'] = 'Please enter product quantity';		
    	// }
    	// elseif($licencecategory == "1" && $deploymentstart == "")
    	// {
    	// 	$res['msg'] = 'Please select product deployment date';		
    	// }
    	// elseif($licencecategory == "1" && $subscriptionterm == "0")
    	// {
    	// 	$res['msg'] = 'Please select product deployment date';		
    	// }
    	// elseif($licencecategory == "1" && $billingterm == "0")
    	// {
    	// 	$res['msg'] = 'Please select product deployment date';		
    	// }
    	else
    	{	
    // 		if($deploymentend == "")
    // 		{
    // 			$productstatus = 1;
    // 			$deploymentenddate = "NULL";
    // 		}
    // 		else
    // 		{
    // 			$productstatus = 0;
				// $deploymentenddate = "'".$deploymentend."'";
    // 		}


    		// $sqlcheckname = "select id from project where name = '".$name."'";
      //       $rowcheckname = mysqli_query($con, $sqlcheckname);

      //       if (mysqli_num_rows($rowcheckname) > 0)
      //       {
      //       	$res['msg'] = 'Project name already exist';	   
      //       }
      //       else
      //       {
				$sqladdproduct = "insert into licence_project_item 
				(
					project_id,
					licence_category,	
					product,		
					description			
				) 
				values 
				(
					".$projectid.",
					".$licencecategory.",
					".$product.",
					'".$description."'
				)";

				if(mysqli_query($con,$sqladdproduct))
				{	

					$res["status"] = 1;
					$res['msg'] = 'Product Added';		
				}
				else
				{
					$res['msg'] = 'Error : Failed to add product!';		
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