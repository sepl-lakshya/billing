<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["add-adjustment-project-id"]));
	$adjustmenttype = mysqli_real_escape_string($con, clean_input($_POST["add-adjustment-type"]));
	$amount = mysqli_real_escape_string($con, clean_input($_POST["add-adjustment-amount"]));
	$referenceno = mysqli_real_escape_string($con, clean_input($_POST["add-adjustment-reference-number"]));

    $res['msg'] = "";
    $res['status'] = 0;
    $uniqueOk = 0;

    
    if(isset($_POST))
    {
    	if($projectid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters';		
    	}
    	elseif($adjustmenttype == "")
    	{
    		$res['msg'] = 'Please select adjustment type.';		
    	}
    	elseif($amount == "")
    	{
    		$res['msg'] = 'Please enter amount.';		
    	}
    	elseif (!preg_match('/^[0-9]+(\.[0-9]+)?$/', $amount))
    	{
    		$res['msg'] = 'Please enter a valid amount.';		
    	}
    	elseif($referenceno == "")
    	{
    		$res['msg'] = 'Please enter reference number.';		
    	}
    	else
    	{

    		$sqlcheckname = "select id from project_adjustment where reference_no = '".$referenceno."' AND project_id = ".$projectid;
            $rowcheckname = mysqli_query($con, $sqlcheckname);

            if (mysqli_num_rows($rowcheckname) > 0)
            {
            	$res['msg'] = 'Reference number already exist';	   
            }
            else
            {
	            $sqladdadjustment = "insert into project_adjustment 
				(
					project_id,
					adjustment_type_id,
					amount,
					reference_no,
					created_on
				) 
				values 
				(
					".$projectid.",
					".$adjustmenttype.",
					".$amount.",
					'".$referenceno."',
					'".date('Y-m-d H:i:s')."'	
				)";

				
				if(mysqli_query($con,$sqladdadjustment))	
				{
					$res["status"] = 1;
					$res['msg'] = 'Adjustment Added';		
				}			
				else
				{
					$res['msg'] = "Error : Failed to add";
				}
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