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
	$projectitemidarrval = clean_input($_POST["projectitemidarrvalpost"]);
	$portalpricearrval = clean_input($_POST["portalpricearrvalpost"]);
	$ridiscount = mysqli_real_escape_string($con, clean_input($_POST["portalridiscountpost"]));

			

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
		else
		{
			$sqlgetbill = "select id,hash,status from bill where project_id = ".$projectid." AND month = ".$month." AND year = ".$year;
	      	$rowgetbill = mysqli_query($con, $sqlgetbill);

	      	if(mysqli_num_rows($rowgetbill) > 0)
	      	{
        		$resgetbill = mysqli_fetch_array($rowgetbill);
	      		//Add Bill
        		$sqlupdatebill = "update bill set ri_discount = ".$ridiscount." where project_id = ".$projectid." AND month = ".$month." AND year = ".$year;

		      	if(mysqli_query($con, $sqlupdatebill))
		      	{
					for($i = 0; $i < count($projectitemidarr); $i++) 
					{
	                    $sqlupdatebillitem = "update bill_item set portal_price = ".mysqli_real_escape_string($con, $portalpricearr[$i])." where bill_id = ".$resgetbill['id']." AND project_id = ".$projectid." AND project_item_id = ".mysqli_real_escape_string($con, $projectitemidarr[$i]);

						if(mysqli_query($con,$sqlupdatebillitem))	
						{
							$res['item_status'] = 1;
						}
						else
						{
							$res['item_status'] = 0;
						}
					}
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


				$sqlcreatebill = "insert into bill (hash,project_id,month,year, ri_discount, status,created_on) values ('".$billhash."',".$projectid.",".$month.",".$year.",".$ridiscount.",1,'".date('Y-m-d H:i:s')."')";

				if(mysqli_query($con,$sqlcreatebill))
				{	

					$sqlgetbillid = "select id from bill where hash = '".$billhash."'";
			        $rowgetbillid = mysqli_query($con, $sqlgetbillid);
			        $resgetbillid = mysqli_fetch_array($rowgetbillid);

			        $billid = $resgetbillid["id"];			

					//Add Bill
						
					for($i = 0; $i < count($projectitemidarr); $i++) 
					{

	                    $sqladdbillitem = "insert into bill_item (bill_id, project_id, project_item_id, portal_price) 
	                    values (
	                    ".$billid.",
	                    ".$projectid.",
	                    ".mysqli_real_escape_string($con, $projectitemidarr[$i]).",
	                    ".mysqli_real_escape_string($con, $portalpricearr[$i]).")";


						if(mysqli_query($con,$sqladdbillitem))	
						{
							$res['item_status'] = 1;
						}
						else
						{
							$res['item_status'] = 0;
						}
					}
				}
			}

		}
	}

	if($res["item_status"] == 1)
	{
		$res["status"] = 1;
		$res['msg'] = 'Portal price saved';		
	}
	else
	{
		$res["status"] = 0;
		$res['msg'] = 'Error : Failed to save!';		
	}

	echo mysqli_error($con);

	mysqli_close($con);
	echo json_encode($res);


?>