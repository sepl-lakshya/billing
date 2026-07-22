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
	$billid = mysqli_real_escape_string($con, clean_input($_POST["billidpost"]));
	$projectitemidarrval = clean_input($_POST["projectitemidarrvalpost"]);
	$salespricearrval = clean_input($_POST["salespricearrvalpost"]);
			

	//Product Arrays Explode
	if(!($projectitemidarrval == "" && $salespricearrval == ""))
	{
		$projectitemidarr = explode(",",$projectitemidarrval);
		$salespricearr = explode(",",$salespricearrval);
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

			for($i = 0; $i < count($projectitemidarr); $i++) 
			{

                $sqlupdatebillitem = "update bill_item set sales_price = ".mysqli_real_escape_string($con, $salespricearr[$i])." where bill_id = ".$billid." AND project_id = ".$projectid." AND project_item_id = ".mysqli_real_escape_string($con, $projectitemidarr[$i]);


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

	if($res["item_status"] == 1)
	{
		$sqlupdatebillstatus = "update bill set status = 2 where id = ".$billid." AND project_id = ".$projectid." AND status <= 2";


		if(mysqli_query($con,$sqlupdatebillstatus))	
		{
			$res["status"] = 1;
			$res['msg'] = 'Sales Price Saved';	
		}
		else
		{
			$res['msg'] = 'Error : Failed to update status !';		
		}	
	}
	else
	{
		$res["status"] = 0;
		$res['msg'] = 'Error : Failed to update items !';		
	}

	// echo mysqli_error($con);

	mysqli_close($con);
	echo json_encode($res);


?>