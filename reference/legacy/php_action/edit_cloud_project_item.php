<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["edit-item-project-id"]));
	$projectitemid = mysqli_real_escape_string($con, clean_input($_POST["edit-project-item-id"]));
	$unitmeasure = mysqli_real_escape_string($con, clean_input($_POST["edit-unit-measure"]));
	$unitprice = mysqli_real_escape_string($con, clean_input($_POST["edit-unit-price"]));
	$quantity = mysqli_real_escape_string($con, clean_input($_POST["edit-quantity"]));
	$productdeployed = mysqli_real_escape_string($con, clean_input($_POST["edit-product-deployed"]));
	$productdiscovery = mysqli_real_escape_string($con, clean_input($_POST["edit-product-discovery"]));
	$purchaseheader = mysqli_real_escape_string($con, clean_input($_POST["edit-product-purchase-header"]));
	$resourceid = mysqli_real_escape_string($con, clean_input($_POST["edit-product-resource-id"]));
	$desc = mysqli_real_escape_string($con, clean_input($_POST["edit-product-desc"]));
	

    $res['msg'] = "";
    $res['status'] = 0;
    $uniqueOk = 0;

    
    if(isset($_POST))
    {
    	if($projectid == "" || $projectitemid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters';		
    	}
    	elseif($unitmeasure == "0")
    	{
    		$res['msg'] = 'Please select unit of measure';		
    	}
    	elseif($unitprice == "")
    	{
    		$res['msg'] = 'Please enter quoted price';		
    	}
    	elseif($quantity == "")
    	{
    		$res['msg'] = 'Please enter quantity';		
    	}
    	elseif($productdeployed == "0")
    	{
    		$res['msg'] = 'Please select deployed product';		
    	}
    	elseif($productdiscovery == "0")
    	{
    		$res['msg'] = 'Please select discovery status';		
    	}
    	elseif($resourceid == "")
    	{
    		$res['msg'] = 'Please enter resource ID';		
    	}
    	// elseif($purchaseheader == "0")
    	// {
    	// 	$res['msg'] = 'Please select purchase header';		
    	// }
    	else
    	{	

				$sqlupdateproduct = "update project_item set 
					unit_measure = ".$unitmeasure.",		
					unit_price = '".$unitprice."',		
					quantity = '".$quantity."',		
					deployed_product = ".$productdeployed.",
					discovery_status = ".$productdiscovery.",
					purchase_header_id = ".$purchaseheader.",
					resource_id = '".$resourceid."',
					description = '".$desc."' 
				where project_id = ".$projectid." AND id = ".$projectitemid;


				if(mysqli_query($con,$sqlupdateproduct))	
				{
					$res["status"] = 1;
					$res['msg'] = 'Product Updated';		
				}
				else
				{
					$res['msg'] = "Error : Failed to update";
				}



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