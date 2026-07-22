<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["project-id"]));
	
	if(isset($_POST["add-item-header-id"]))
	{
		$hasheaderid = true;
		$headerid = mysqli_real_escape_string($con, clean_input($_POST["add-item-header-id"]));
	}
	else
	{
		$hasheaderid = false;
		$headername = mysqli_real_escape_string($con, clean_input($_POST["header-name"]));
	}

    $res['msg'] = "";
    $res['status'] = 0;
    $uniqueOk = 0;
    $uniqueItemOk = 0;

    
    if(isset($_POST))
    {
    	if($projectid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters';		
    	}
    	elseif($hasheaderid == false && $headername == "")
    	{
    		$res['msg'] = 'Please enter header name.';		
    	}
    	else
    	{	

    		if(!$hasheaderid)
    		{
    			do 
				{
					$headerhash = getName(32);

					$sqlcheckhashexist = "select id from project_header where hash = '".$headerhash."'";
			        $rowcheckhashexist = mysqli_query($con, $sqlcheckhashexist);

			        if (!(mysqli_num_rows($rowcheckhashexist) > 0))
			        {
			        	$sqlitemcheckhashexist = "select id from project_item where hash = '".$headerhash."'";
				        $rowitemcheckhashexist = mysqli_query($con, $sqlitemcheckhashexist);

				        if (!(mysqli_num_rows($rowitemcheckhashexist) > 0))
				        {
			        		$uniqueOk = 1;
			        	}
			        }

				} while ($uniqueOk == 0);

				$sqladdheader = "insert into project_header 
				(
					hash,
					project_id,
					name,
					amount,
					is_deleted		
				) 
				values 
				(
					'".$headerhash."',
					".$projectid.",
					'".$headername."',
					0,
					0
					
				)";

				if(mysqli_query($con,$sqladdheader))
				{
					$sqlgetnewid = "select id from project_header where hash = '".$headerhash."'";
                	$rowgetnewid = mysqli_query($con, $sqlgetnewid);
                	$resgetnewid = mysqli_fetch_array($rowgetnewid);
                	$headerid = $resgetnewid["id"];
				}
				else
				{
					$res['msg'] = 'Error : Failed to add product!';		
				}
			}


				if($headerid != "")
				{
					//Product Arrays
					$productarrval = clean_input($_POST["productarrpost"]);
					$producttypearrval = clean_input($_POST["producttypearrpost"]);
					$distributorarrval = clean_input($_POST["distributorarrpost"]);
					$deploymentstartarrval = clean_input($_POST["deploymentstartarrpost"]);
					$deploymentendarrval = clean_input($_POST["deploymentendarrpost"]);
					$unitmeasurearrval = clean_input($_POST["unitmeasurearrpost"]);
					$unitpricearrval = clean_input($_POST["unitpricearrpost"]);
					$quantityarrval = clean_input($_POST["quantityarrpost"]);
					$productdeployedarrval = clean_input($_POST["productdeployedarrpost"]);
					$productdiscoveryarrval = clean_input($_POST["productdiscoveryarrpost"]);
					$purchaseheaderarrval = clean_input($_POST["purchaseheaderarrpost"]);
					$productdescarrval = clean_input($_POST["productdescarrpost"]);
					$resourceidarrval = clean_input($_POST["resourceidarrpost"]);
					$modelarrval = clean_input($_POST["modelarrpost"]);
					
					


					//Product Arrays Explode

					if(!($productarrval == "" && $producttypearrval == "" && $distributorarrval == "" && $deploymentstartarrval == "" && $unitmeasurearrval == "" && $unitpricearrval == "" && $quantityarrval == "" && $productdeployedarrval == "" && $productdiscoveryarrval == "" && $modelarrval == "" && $resourceidarrval == ""))
					{
						$productarr = explode(",",$productarrval);
						$producttypearr = explode(",",$producttypearrval);
						$distributorarr = explode(",",$distributorarrval);
						$deploymentstartarr = explode(",",$deploymentstartarrval);
						$deploymentendarr = explode(",",$deploymentendarrval);
						$unitmeasurearr = explode(",",$unitmeasurearrval);
						$unitpricearr = explode(",",$unitpricearrval);
						$quantityarr = explode(",",$quantityarrval);
						$productdeployedarr = explode(",",$productdeployedarrval);
						$productdiscoveryarr = explode(",",$productdiscoveryarrval);
						$purchaseheaderarr = explode(",",$purchaseheaderarrval);
						$productdescarr = explode(",",$productdescarrval);
						$resourceidarr = explode(",",$resourceidarrval);
						$modelarr = explode(",",$modelarrval);


						$sqladdproductvalues = "";

						for($i = 0; $i < count($productarr); $i++) 
						{

							$deploymentend = mysqli_real_escape_string($con, $deploymentendarr[$i]);

							if($deploymentend == "")
				    		{
				    			$productstatus = 1;
				    			$deploymentenddate = "NULL";
				    		}
				    		else
				    		{
				    			$productstatus = 0;
								$deploymentenddate = "'".$deploymentend."'";
				    		}


				    		if($i == 0)
				    		{
				    			$sqlcomma = "";
				    		}
				    		else
				    		{
				    			$sqlcomma = ",";
				    		}

    						$uniqueItemOk = 0;

				    		do 
							{
								$itemhash = getName(32);

								$sqlcheckhashexist = "select id from project_header where hash = '".$itemhash."'";
						        $rowcheckhashexist = mysqli_query($con, $sqlcheckhashexist);

						        if (!(mysqli_num_rows($rowcheckhashexist) > 0))
						        {
						        	$sqlitemcheckhashexist = "select id from project_item where hash = '".$itemhash."'";
							        $rowitemcheckhashexist = mysqli_query($con, $sqlitemcheckhashexist);

							        if (!(mysqli_num_rows($rowitemcheckhashexist) > 0))
							        {
						        		$uniqueItemOk = 1;
						        	}
						        }

							} while ($uniqueItemOk == 0);

		                    $sqladdproductvalues .= $sqlcomma."
		                    (
								'".$itemhash."',
								'".mysqli_real_escape_string($con, $resourceidarr[$i])."',
								".$projectid.",
								".$headerid.",
								1,
								".mysqli_real_escape_string($con, $productarr[$i]).",
								".mysqli_real_escape_string($con, $distributorarr[$i]).",
								'".mysqli_real_escape_string($con, $deploymentstartarr[$i])."',
								".$deploymentenddate.",
								'".mysqli_real_escape_string($con, $modelarr[$i])."',
								".mysqli_real_escape_string($con, $unitmeasurearr[$i]).",
								'".mysqli_real_escape_string($con, $unitpricearr[$i])."',
								'".mysqli_real_escape_string($con, $quantityarr[$i])."',
								".mysqli_real_escape_string($con, $productdeployedarr[$i]).",
								".mysqli_real_escape_string($con, $productdiscoveryarr[$i]).",
								".mysqli_real_escape_string($con, $purchaseheaderarr[$i]).",
								'".mysqli_real_escape_string($con, $productdescarr[$i])."',
								".$productstatus."
							)";
						}
					}
					else
					{
						$res['prod_status'] = 0;
					}



					$sqladdproduct = "insert into project_item 
					(
						hash,
						resource_id,
						project_id,
						header_id,
						category,	
						product,		
						distributor,		
						deployment_start,		
						deployment_end,		
						model,		
						unit_measure,		
						unit_price,		
						quantity,		
						deployed_product,
						discovery_status,
						purchase_header_id,
						description,
						status			
					) 
					values ".$sqladdproductvalues;


					if(mysqli_query($con,$sqladdproduct))	
					{
						$res["status"] = 1;
						$res['msg'] = 'Product Added';		
					}

				}
				else
				{
					$res['msg'] = "Error : Missing header ID";
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