<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$name = mysqli_real_escape_string($con, clean_input($_POST["project-name"]));
	$city = mysqli_real_escape_string($con, clean_input($_POST["project-city"]));
	$state = mysqli_real_escape_string($con, clean_input($_POST["project-state"]));
	$tenderno = mysqli_real_escape_string($con, clean_input($_POST["tender-ref-no"]));
	$startdate = mysqli_real_escape_string($con, clean_input($_POST["project-start-date"]));
	$productcategory = mysqli_real_escape_string($con, clean_input($_POST["product-category"]));
	$contractyear = mysqli_real_escape_string($con, clean_input($_POST["contract-year"]));
	$contractmonth = mysqli_real_escape_string($con, clean_input($_POST["contract-month"]));
	$desc = mysqli_real_escape_string($con, clean_input($_POST["project-description"]));

    $res['msg'] = "";
    $res['status'] = 0;
    $uniqueOk = 0;

    
    if(isset($_POST))
    {
    	if($name == "")
    	{
    		$res['msg'] = 'Please enter project name';		
    	}
    	elseif($city == "")
    	{
    		$res['msg'] = 'Please enter project city';		
    	}
    	elseif($state == "0")
    	{
    		$res['msg'] = 'Please select project state';		
    	}
    	elseif($tenderno == "")
    	{
    		$res['msg'] = 'Please enter tender number';		
    	}
    	elseif($startdate == "")
    	{
    		$res['msg'] = 'Please select project start date';		
    	}
    	elseif($contractyear == "0" && $contractmonth == "0")
    	{
    		$res['msg'] = 'Please select contract period';		
    	}
    	else
    	{	


    		do 
			{
				$projecthash = getName(64);

				$sqlcheckhashexist = "select id from licence_project where hash = '".$projecthash."'";
		        $rowcheckhashexist = mysqli_query($con, $sqlcheckhashexist);

		        if (!(mysqli_num_rows($rowcheckhashexist) > 0))
		        {
		        	$uniqueOk = 1;
		        }

			} while ($uniqueOk == 0);

    		$sqlcheckname = "select id from licence_project where name = '".$name."'";
            $rowcheckname = mysqli_query($con, $sqlcheckname);

            if (mysqli_num_rows($rowcheckname) > 0)
            {
            	$res['msg'] = 'Project name already exist';	   
            }
            else
            {
				$sqlcreateproject = "insert into licence_project 
				(
					hash,
					created_by,
					product_category,
					name,	
					city,		
					state,		
					tender_ref_no,
					start_date,	
					contract_year,
					contract_month,	
					description,
					is_active,		
					created_on	
				) 
				values 
				(
					'".$projecthash."',
					".$_SESSION['user_id'].",
					".$productcategory.",
					'".$name."',
					'".$city."',
					'".$state."',
					'".$tenderno."',
					'".$startdate."',
					'".$contractyear."',
					'".$contractmonth."',
					'".$desc."',
					1,
					'".date('Y-m-d H:i:s')."'
				)";

				if(mysqli_query($con,$sqlcreateproject))
				{	

					$res["status"] = 1;
					$res['msg'] = 'Project Created';		
				}
				else
				{
					$res['msg'] = 'Error : Failed to create!';		
				}
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