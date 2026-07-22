<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["projectid"]));
	$projecthash = mysqli_real_escape_string($con, clean_input($_POST["projecthash"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($projectid == "" || $projecthash == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	else
    	{

    		$sqlgetattachments = "select * from project_attachment where project_id = ".$projectid;
            $rowgetattachments = mysqli_query($con, $sqlgetattachments);

            if (mysqli_num_rows($rowgetattachments) > 0)
            {
            	$f = 0;
	        	while ($f <= ($resgetattachments = mysqli_fetch_array($rowgetattachments)))
	            {
	         		
	            	$filepath = "../uploads/attachment/".$resgetattachments['file_name'];

		            if(unlink($filepath))
		            {
		                $sqldeleteattachment = "delete from project_attachment where id = ".$resgetattachments['id']." AND project_id = ".$projectid;

		                if(mysqli_query($con,$sqldeleteattachment))
		                {   
		                }
		                else
		                {
		                    $res['msg'] = 'Error : Failed to delete file!';      
		                }
		            }
		            else
		            {
		                $res['msg'] = 'Error : Failed to delete file!';     
		            }

	         		$f++;
	            }
	        }


			$sqldeleteproject = "delete from project where id = ".$projectid." AND hash = '".$projecthash."'";
			if(mysqli_query($con,$sqldeleteproject))
			{	

				$sqldeleteprojectdiscount = "delete from project_discount where project_id = ".$projectid;
				if(mysqli_query($con,$sqldeleteprojectdiscount))
				{	
					
					$sqldeleteprojectitem = "delete from project_item where project_id = ".$projectid;
					if(mysqli_query($con,$sqldeleteprojectitem))
					{

						$sqldeleteprojectbill = "delete from bill where project_id = ".$projectid;
						if(mysqli_query($con,$sqldeleteprojectbill))
						{	
							$sqldeleteprojectbillitem = "delete from bill_item where project_id = ".$projectid;
							if(mysqli_query($con,$sqldeleteprojectbillitem))
							{	
								$sqldeleteprojectassignment = "delete from project_user_mapping where project_id = ".$projectid;
								if(mysqli_query($con,$sqldeleteprojectassignment))
								{	
									$sqldeleteprojectassignment = "delete from bill_header where project_id = ".$projectid;
									if(mysqli_query($con,$sqldeleteprojectassignment))
									{	
										$sqldeleteprojectassignment = "delete from bill_invoice_mapping where project_id = ".$projectid;
										if(mysqli_query($con,$sqldeleteprojectassignment))
										{	
											$sqldeletebillpurchaseheader = "delete from bill_purchase_header where project_id = ".$projectid;
											if(mysqli_query($con,$sqldeletebillpurchaseheader))
											{	
												$sqldeletecloudinward = "delete from cloud_inward where project_id = ".$projectid;
												if(mysqli_query($con,$sqldeletecloudinward))
												{	
													$sqldeletecloudoutward = "delete from cloud_outward where project_id = ".$projectid;
													if(mysqli_query($con,$sqldeletecloudoutward))
													{	
														$sqldeletedebitnote = "delete from debit_note where project_id = ".$projectid;
														if(mysqli_query($con,$sqldeletedebitnote))
														{	
															$sqldeleteinvoicecloudinward = "delete from invoice_cloud_inward where project_id = ".$projectid;
															if(mysqli_query($con,$sqldeleteinvoicecloudinward))
															{	
																$sqldeleteprojectheader = "delete from project_header where project_id = ".$projectid;
																if(mysqli_query($con,$sqldeleteprojectheader))
																{	
																	$sqldeletepurchaseheader = "delete from purchase_header where project_id = ".$projectid;
																	if(mysqli_query($con,$sqldeletepurchaseheader))
																	{	
																		$sqldeletepurchaseinvoice = "delete from purchase_invoice where project_id = ".$projectid;
																		if(mysqli_query($con,$sqldeletepurchaseinvoice))
																		{	
																			$res["status"] = 1;
																			$res['msg'] = 'Project Deleted';		
																		}
																		else
																		{
																			$res['msg'] = 'Error : Failed to delete purchase invoice!';		
																		}			
																	}
																	else
																	{
																		$res['msg'] = 'Error : Failed to delete purchase header!';		
																	}			
																}
																else
																{
																	$res['msg'] = 'Error : Failed to delete project header!';		
																}			
															}
															else
															{
																$res['msg'] = 'Error : Failed to delete invoice cloud inward!';		
															}			
														}
														else
														{
															$res['msg'] = 'Error : Failed to delete debit note!';		
														}		
													}
													else
													{
														$res['msg'] = 'Error : Failed to delete cloud outward item!';		
													}		
												}
												else
												{
													$res['msg'] = 'Error : Failed to delete cloud inward!';		
												}		
											}
											else
											{
												$res['msg'] = 'Error : Failed to delete bill purchase header!';		
											}		
										}
										else
										{
											$res['msg'] = 'Error : Failed to delete bill invoice mapping!';		
										}		
									}
									else
									{
										$res['msg'] = 'Error : Failed to delete project bill header!';		
									}
								}
								else
								{
									$res['msg'] = 'Error : Failed to delete project bill item!';		
								}
							}
							else
							{
								$res['msg'] = 'Error : Failed to delete project bill item!';		
							}
						}
						else
						{
							$res['msg'] = 'Error : Failed to delete project bill!';		
						}
					}
					else
					{
						$res['msg'] = 'Error : Failed to delete project item!';		
					}
					
				}
				else
				{
					$res['msg'] = 'Error : Failed to delete project discount!';		
				}
			}
			else
			{
				$res['msg'] = 'Error : Failed to delete project!';		
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