<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["sub-purchase-project-id"]));
	$projectitemid = mysqli_real_escape_string($con, clean_input($_POST["sub-purchase-project-item-id"]));
	$month = mysqli_real_escape_string($con, clean_input($_POST["sub-purchase-month"]));
	$year = mysqli_real_escape_string($con, clean_input($_POST["sub-purchase-year"]));
	$quantity = mysqli_real_escape_string($con, clean_input($_POST["sub-purchase-quantity"]));
	
    $res['msg'] = "";
    $res['status'] = 0;
    $uniqueOk = 0;

    
    if(isset($_POST))
    {
    	if($projectid == "" || $projectitemid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters';		
    	}
    	elseif($year == "0")
    	{
    		$res['msg'] = 'Please select year';		
    	}
    	elseif($month == "0")
    	{
    		$res['msg'] = 'Please select month';		
    	}
    	elseif($quantity == "")
    	{
    		$res['msg'] = 'Please enter quantity';		
    	}
    	else
    	{	


    		$sqlgetsubpurchase = "select id from licence_sub_purchase where project_id = ".$projectid." AND project_item_id = ".$projectitemid." AND month = ".$month." AND year = ".$year;
	      	$rowgetsubpurchase = mysqli_query($con, $sqlgetsubpurchase);

	      	if(mysqli_num_rows($rowgetsubpurchase) > 0)
	     	{
				$res['msg'] = 'Sub Purchase already exist for selected month !';	
	     	}
	     	else
	     	{
	    		$sqlgetprojectitem = "select (sale_quantity - purchase_quantity) as remaining_quantity from licence_project_item where project_id = ".$projectid." AND id = ".$projectitemid;
		      	$rowgetprojectitem = mysqli_query($con, $sqlgetprojectitem);

		      	if(mysqli_num_rows($rowgetprojectitem) > 0)
		     	{
		        	$resgetprojectitem = mysqli_fetch_array($rowgetprojectitem);

		        	if((int)$quantity > (int)$resgetprojectitem['remaining_quantity'] || (int)$quantity < 0)
		        	{
						$res['msg'] = 'Invalid quantity value!';	
		        	}
		        	else
		        	{
		        		$sqladdsubpurchase = "insert into licence_sub_purchase 
						(
							project_id,
							project_item_id,
							quantity,	
							month,
							year	
						) 
						values 
						(
							".$projectid.",
							".$projectitemid.",
							".$quantity.",
							".$month.",
							".$year."
						)";

						if(mysqli_query($con,$sqladdsubpurchase))
						{	
							$sqlcreatepurchase = "update licence_project_item 
								set
								purchase_quantity = purchase_quantity  + ".(int)$quantity."
								where
								project_id = ".$projectid." AND id = ".$projectitemid;

							if(mysqli_query($con,$sqlcreatepurchase))
							{	
								$res["status"] = 1;
								$res['msg'] = 'Sub Purchase Created';	
							}
							else
							{
								$res['msg'] = 'Error : Failed to update quantity purchase!';		
							}		
						}
						else
						{
							$res['msg'] = 'Error : Failed to add sub purchase!';		
						}
					}
				}
			}
		}
	}
	else
	{
		$res['msg'] = "Error : Invalid form submission method";
	}


	mysqli_close($con);
	echo json_encode($res);

?>