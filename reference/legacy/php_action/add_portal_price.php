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
	$projectid = mysqli_real_escape_string($con, clean_input($_POST["projectidpost"]));
	$month = mysqli_real_escape_string($con, clean_input($_POST["pricemonthpost"]));
	$year = mysqli_real_escape_string($con, clean_input($_POST["priceyearpost"]));
	$ridiscount = mysqli_real_escape_string($con, clean_input($_POST["portalridiscountpost"]));
	$paygdiscount = mysqli_real_escape_string($con, clean_input($_POST["portalpaygdiscountpost"]));
	$projectitemidarrval = clean_input($_POST["projectitemidarrvalpost"]);
	$portalpricearrval = clean_input($_POST["portalpricearrvalpost"]);
			

	//Product Arrays Explode
	if(!($projectitemidarrval == "" && $portalpricearrval == ""))
	{
		$projectitemidarr = explode(",",$projectitemidarrval);
		$portalpricearr = explode(",",$portalpricearrval);
		$pricearrayempty = false;
	}
	else
	{
		$pricearrayempty = true;
	}


    
    if(isset($_POST))
	{

		if($pricearrayempty)
		{
			$res['msg'] = "Error : No data recieved from client !";
		}
		elseif($ridiscount == "")
		{
			$res['msg'] = "Please enter RI discount";
		}
		elseif($paygdiscount == "")
		{
			$res['msg'] = "Please enter PAYG discount";
		}
		else
		{
			$sqlgetbill = "select id,hash,status from bill where project_id = ".$projectid." AND month = ".$month." AND year = ".$year." AND is_deleted = 0";
	      	$rowgetbill = mysqli_query($con, $sqlgetbill);

	      	if(mysqli_num_rows($rowgetbill) > 0)
	      	{
        		$resgetbill = mysqli_fetch_array($rowgetbill);

	      		//Add Bill
				$sqlupdatebill = "update bill set ri_discount = ".$ridiscount.",  payg_discount = ".$paygdiscount." where project_id = ".$projectid." AND month = ".$month." AND year = ".$year;

		      	if(mysqli_query($con, $sqlupdatebill))
		      	{		
					for($i = 0; $i < count($projectitemidarr); $i++) 
					{
	                    $sqlupdatebillitem = "update bill_item set portal_price = ".mysqli_real_escape_string($con, $portalpricearr[$i])." where bill_id = ".$resgetbill['id']." AND project_id = ".$projectid." AND project_item_id = ".mysqli_real_escape_string($con, $projectitemidarr[$i]);


						if(mysqli_query($con,$sqlupdatebillitem))	
						{
							$res['status'] = 1;
						}
						else
						{
							$res['status'] = 0;
						}
					}
				}
				else
				{
					$res['msg'] = "Error : Failed to update discount !";
				}
	      	}
	      	else
	      	{

				do 
				{
					$billhash = getName(64);

					$sqlcheckhashexist = "select id from bill where hash = '".$billhash."'";
			        $rowcheckhashexist = mysqli_query($con, $sqlcheckhashexist);

			        if (!(mysqli_num_rows($rowcheckhashexist) > 0))
			        {
			        		$uniqueOk = 1;
			        }

				} while ($uniqueOk == 0);


				$sqlcreatebill = "insert into bill (hash,project_id,month,year, ri_discount, payg_discount, status,created_on) values ('".$billhash."',".$projectid.",".$month.",".$year.",".$ridiscount.",".$paygdiscount.",1,'".date('Y-m-d H:i:s')."')";

				if(mysqli_query($con,$sqlcreatebill))
				{	

					$sqlgetbillid = "select id from bill where hash = '".$billhash."'";
			        $rowgetbillid = mysqli_query($con, $sqlgetbillid);
			        $resgetbillid = mysqli_fetch_array($rowgetbillid);

			        $billid = $resgetbillid["id"];			

			        $headervalues = "";

			        $sqlgetprojectheader = "select id from project_header where project_id = ".$projectid." AND is_deleted = 0";
			      	$rowgetprojectheader = mysqli_query($con, $sqlgetprojectheader);

			      	if(mysqli_num_rows($rowgetprojectheader) > 0)
			      	{
			      		$h = 0;
			            while ($h <= ($resgetprojectheader = mysqli_fetch_array($rowgetprojectheader)))
			            {
			            	if($h == 0)
			            	{
			            		$headervalues .= "(".$billid.",".$projectid.",".$resgetprojectheader['id'].",0,0)";
			            	}
			            	else
			            	{
			            		$headervalues .= ",(".$billid.",".$projectid.",".$resgetprojectheader['id'].",0,0)";
			            	}
			            	$h++;
			            }
			      	}

			      	if($headervalues != "")
			      	{

				      	$sqladdbillheader = "insert into bill_header (bill_id,project_id,header_id,amount,is_deleted) values ".$headervalues;

						if(mysqli_query($con,$sqladdbillheader))
						{

							$billitemvalues = "";

							//Add Bill						
							for($i = 0; $i < count($projectitemidarr); $i++) 
							{

								$projectitemid = mysqli_real_escape_string($con, $projectitemidarr[$i]);

								if($i == 0)
								{
									$billitemvalues .= " (
				                    ".$billid.",
				                    ".$projectid.",
				                    ".$projectitemid.",
				                    (select purchase_header_id from project_item where id = ".$projectitemid."),
				                    ".mysqli_real_escape_string($con, $portalpricearr[$i]).") ";
								}
								else
								{
				                    $billitemvalues .= " , (
				                    ".$billid.",
				                    ".$projectid.",
				                    ".$projectitemid.",
				                    (select purchase_header_id from project_item where id = ".$projectitemid."),
				                    ".mysqli_real_escape_string($con, $portalpricearr[$i]).") ";
								}

							}

							if($billitemvalues != "")
							{

								$sqladdbillitem = "insert into bill_item (bill_id, project_id, project_item_id,purchase_header_id, portal_price) 
				                    values ".$billitemvalues;


								if(mysqli_query($con,$sqladdbillitem))	
								{
									$billpurchaseheadervalues = "";

									$sqlgetpurchaseheader = "select distinct(purchase_header_id) from bill_item where project_id = ".$projectid." AND bill_id = ".$billid." order by id";
							      	$rowgetpurchaseheader = mysqli_query($con, $sqlgetpurchaseheader);

							      	if(mysqli_num_rows($rowgetpurchaseheader) > 0)
							      	{
							      		$ph = 0;
							            while ($ph <= ($resgetpurchaseheader = mysqli_fetch_array($rowgetpurchaseheader)))
							            {
							            	if($ph == 0)
							            	{

							            		$billpurchaseheadervalues .= " (
							            		".$billid.",
							            		".$projectid.",
							            		".$resgetpurchaseheader['purchase_header_id'].",
							            		0
							            		) ";
							            	}
							            	else
							            	{
							            		$billpurchaseheadervalues .= " , (
							            		".$billid.",
							            		".$projectid.",
							            		".$resgetpurchaseheader['purchase_header_id'].",
							            		0
							            		) ";
							            	}

							            	$ph++;
							            }

							            $sqladdbillitem = "insert into bill_purchase_header (bill_id, project_id, purchase_header_id,amount) 
						                    values ".$billpurchaseheadervalues;


										if(mysqli_query($con,$sqladdbillitem))	
										{
											$res["status"] = 1;
										}
										else
										{
											$res['msg'] = 'Error : Failed to add purchase header';		
										}
							        }
								}
								else
								{
									$res['msg'] = "Error : Failed to add bill item";
								}
							}
							else
							{
								$res['msg'] = "Error : No item found";
							}
						}
						else
						{
							$res['msg'] = "Error : Failed to add bill header";
						}
					}
					else
					{
						$res['msg'] = "Error : No Header Found!";
					}
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

	echo mysqli_error($con);

	mysqli_close($con);
	echo json_encode($res);


?>