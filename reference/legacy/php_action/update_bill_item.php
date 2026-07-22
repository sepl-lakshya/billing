	<?php 
		if (session_status() == PHP_SESSION_NONE) 
	  	{
	    	session_start();
	  	}
	  	include "../include/config.php";

	  	$con = connectMySQL();
		$res['status'] = 0;
		$res['item_status'] = 0;
	    $res['msg'] = "";
	    $uniqueOk = 0;

	  
	  	// General Info
		$projectid = mysqli_real_escape_string($con, clean_input($_POST["projectid"]));			
		$billid = mysqli_real_escape_string($con, clean_input($_POST["billid"]));			
		$month = mysqli_real_escape_string($con, clean_input($_POST["billmonth"]));			
		$year = mysqli_real_escape_string($con, clean_input($_POST["billyear"]));			



	    
	    if(isset($_POST))
		{

			if($projectid == "" || $billid == "" || $month == "" || $year == "")
			{
				$res['msg'] = "Error : Missing Parameters !";
			}
			else
			{

				$daysinmonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

				$monthstartdate = $year."-".$month."-1";
				$monthenddate = $year."-".$month."-".$daysinmonth;

				$monthstartdate = date("Y-m-d",strtotime($monthstartdate));
				$monthenddate = date("Y-m-d",strtotime($monthenddate));


				$sqlgetprojectitem = "select pi.id,pi.header_id,deployment_start, quantity, deployment_end, status
	              from project_item as pi INNER JOIN product pr ON pr.id = pi.product where pi.project_id = ".$projectid." AND pi.is_deleted = 0 order BY pr.sub_category, pi.id asc";
	            $rowgetprojectitem = mysqli_query($con, $sqlgetprojectitem);

	            if (mysqli_num_rows($rowgetprojectitem) > 0)
	            {
	            	$i = 0;
	                while ($i <= ($resgetprojectitem = mysqli_fetch_array($rowgetprojectitem)))
	                {
	                  	$deployenddate = strtotime($resgetprojectitem['deployment_end']);
	                  	$deploystartdate = strtotime($resgetprojectitem['deployment_start']);

	                  	if($resgetprojectitem['status'] == 0)
	                  	{
	                    	if(($deployenddate >= strtotime($monthstartdate)) && ($deployenddate <= strtotime($monthenddate)) || (
	                      	($deployenddate >= strtotime($monthstartdate)) && ($deploystartdate <= strtotime($monthenddate))))
	                    	{
	                      		$showrow = true;
	                    	}
	                    	else
	                    	{
	                      	$showrow = false;
	                    	}
	                  	}
	                  	else
	                  	{
	                    	if($deploystartdate <= strtotime($monthenddate))
	                    	{  
	                      		$showrow = true;
	                    	}
	                    	else
	                    	{
	                      		$showrow = false;
	                    	}
	                  	}

	                  	if($showrow)
	                  	{
	                  		$projectitemid = $resgetprojectitem['id'];
	                  		$headerid = $resgetprojectitem['header_id'];

	                  		$sqlgetbillitem = "select id from bill_item where bill_id = ".$billid." AND project_id = ".$projectid." AND project_item_id = ".$projectitemid;
						    $rowgetbillitem = mysqli_query($con, $sqlgetbillitem);

						    if (mysqli_num_rows($rowgetbillitem) > 0)
						    {

						    }
						    else
						    {
						    	$sqladdnewitem = "insert into bill_item (bill_id, project_id, project_item_id, purchase_header_id, portal_price, sales_price, purchase_price) values (".$billid.",".$projectid.",".$projectitemid.",0,0,NULL,NULL)";
						    	if(mysqli_query($con, $sqladdnewitem))
						    	{
						    		$sqlgetbillheader = "select id from bill_header where bill_id = ".$billid." AND project_id = ".$projectid." AND header_id = ".$headerid;
								    $rowgetbillheader = mysqli_query($con, $sqlgetbillheader);

								    if (mysqli_num_rows($rowgetbillheader) > 0)
								    {

								    }
								    else
								    {
								    	$sqladdnewheader = "insert into bill_header (bill_id, project_id, header_id, amount, is_deleted) values (".$billid.",".$projectid.",".$headerid.",0,0)";
								    	if(mysqli_query($con, $sqladdnewheader))
								    	{

								    	}
								    }
						    	}
						    	else
						    	{
						    		$res['msg'] = "Error : Failed to add new item.";
						    	}

						    }
	                  	}

	                  	$i++;
	                }

	                // Reset Puchase Price

	                $sqlgetbillstatus = "select status from bill where id = ".$billid." AND project_id = ".$projectid;
		            $rowgetbillstatus = mysqli_query($con, $sqlgetbillstatus);

		            if (mysqli_num_rows($rowgetbillstatus) > 0)
		            {
		            	$resgetbillstatus = mysqli_fetch_array($rowgetbillstatus); 
		            
		            	$billstatus = (int)$resgetbillstatus['status'];

		            	if($billstatus > 2)
		            	{
		            		$statusvalue = 2;
		            	}
		            	else
		            	{
		            		$statusvalue = $billstatus;
		            	}


		                $sqlupdatebill = "update bill set purchase_header_status = 0, status = ".$statusvalue." where id = ".$billid;

						if(mysqli_query($con,$sqlupdatebill))
						{	
							$sqldeletebillitem = "update bill_item set purchase_price = NULL where bill_id = ".$billid;

							if(mysqli_query($con,$sqldeletebillitem))
							{	
								$sqldeleteinvoice = "delete from bill_invoice_mapping where bill_id = ".$billid;

								if(mysqli_query($con,$sqldeleteinvoice))
								{	
									$sqldeletedebitnote = "delete from debit_note where bill_id = ".$billid;

									if(mysqli_query($con,$sqldeletedebitnote))
									{	
										$sqldeletecreditnote = "delete from credit_note where bill_id = ".$billid;

										if(mysqli_query($con,$sqldeletecreditnote))
										{	

											$res["status"] = 1;
											$res['msg'] = 'Item Deleted';		
										}
										else
										{
											$res['msg'] = 'Error : Failed to delete!';		
										}
									}
									else
									{
										$res['msg'] = 'Error : Failed to delete!';		
									}
								}
								else
								{
									$res['msg'] = 'Error : Failed to delete!';		
								}
							}
							else
							{
								$res['msg'] = 'Error : Failed to update bill items !';		
							}		
						}
						else
						{
							$res['msg'] = 'Error : Failed to update bill !';		
						}
					}
	            }

	            // Add New Header

	            $sqlgetprojectheader = "select id from project_header where project_id = ".$projectid." AND  is_deleted = 0 AND id NOT IN (select header_id from bill_header where bill_id = ".$billid." AND project_id = ".$projectid.")";
	            $rowgetprojectheader = mysqli_query($con, $sqlgetprojectheader);

	            if (mysqli_num_rows($rowgetprojectheader) > 0)
	            {
	            	$ph = 0;
	                while ($ph <= ($resgetprojectheader = mysqli_fetch_array($rowgetprojectheader)))
	                {
	             		$sqladdnewheader = "insert into bill_header (bill_id, project_id, header_id,amount,is_deleted) values (".$billid.",".$projectid.",".$resgetprojectheader['id'].",0,0)";
				    	if(mysqli_query($con, $sqladdnewheader))
				    	{

				    	}
				    	else
				    	{
				    		$res['status'] = 0;
				    		$res['msg'] = "Error : Failed to add new header.";
				    	}
	             		$ph++;
	                }
	            }

			}
		}


		if($res['status'] == 1)
		{
			$res['msg'] = "Portal Price Saved";
		}
		else
		{
			$res['msg'] = "Error : Failed to Portal Price";
		}

		// echo mysqli_error($con);

		mysqli_close($con);
		echo json_encode($res);


	?>