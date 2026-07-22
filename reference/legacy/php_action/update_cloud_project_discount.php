<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["change-discount-project-id"]));
	$ridiscount = mysqli_real_escape_string($con, clean_input($_POST["change-ri-discount"]));
	$paygdiscount = mysqli_real_escape_string($con, clean_input($_POST["change-payg-discount"]));
	$creditdays = mysqli_real_escape_string($con, clean_input($_POST["change-credit-days"]));
	$year = mysqli_real_escape_string($con, clean_input($_POST["discount-year"]));
	$month = mysqli_real_escape_string($con, clean_input($_POST["discount-month"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($projectid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	elseif($paygdiscount == "")
    	{
    		$res['msg'] = 'Please enter PAYG discount';		
    	}
    	elseif($ridiscount == "")
    	{
    		$res['msg'] = 'Please enter RI discount';		
    	}
    	elseif($creditdays == "")
    	{
    		$res['msg'] = 'Please enter credit days';		
    	}
    	elseif($year == "0")
    	{
    		$res['msg'] = 'Please select year';		
    	}
    	elseif($month == "0")
    	{
    		$res['msg'] = 'Please select month';		
    	}
    	else
    	{	
    		$todate = $year."-".$month;
		  	$todate = date_create($todate);
		  	date_sub($todate,date_interval_create_from_date_string("1 month"));
		  	$todateyear = date_format($todate,"Y");
		  	$todatemonth = date_format($todate,"m");
		  	$daysinmonth = cal_days_in_month(CAL_GREGORIAN, $todatemonth, $todateyear);
		  	
		  	$todatefinal = $todateyear."-".$todatemonth."-".$daysinmonth;
		  	$fromdatefinal = $year."-".$month."-01";


			$sqlupdateolddiscount = "update project_discount set to_date = '".$todatefinal."' where project_id = ".$projectid." AND to_date IS NULL";

			if(mysqli_query($con,$sqlupdateolddiscount))
			{	

				$sqlcreatenewdiscount = "insert into project_discount 
				(
					project_id,
					from_date,
					to_date,	
					ri_discount,		
					payg_discount,				
					credit_days,				
					created_on	
				) 
				values 
				(
					".$projectid.",
					'".$fromdatefinal."',
					NULL,
					".$ridiscount.",
					".$paygdiscount.",
					".$creditdays.",
					'".date('Y-m-d')."'
				)";

				if(mysqli_query($con,$sqlcreatenewdiscount))
				{	

					$res["status"] = 1;
					$res['msg'] = 'Discount Updated';		
				}
				else
				{
					$res['msg'] = 'Error : Failed to update!';		
				}		
			}
			else
			{
				$res['msg'] = 'Error : Failed to update!';		
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