<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["add-header-project-id"]));
	$headername = mysqli_real_escape_string($con, clean_input($_POST["add-header-name"]));
	$quantity = mysqli_real_escape_string($con, clean_input($_POST["add-header-quantity"]));
	$desc = mysqli_real_escape_string($con, clean_input($_POST["add-header-description"]));
	
    $res['msg'] = "";
    $res['status'] = 0;
    $uniqueOk = 0;

    
    if(isset($_POST))
    {
    	if($projectid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters';		
    	}
    	elseif($headername == "")
    	{
    		$res['msg'] = 'Please enter header name.';		
    	}
    	elseif($quantity == "")
    	{
    		$res['msg'] = 'Please enter header quantity.';		
    	}
    	else
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
				quantity,
				description,
				is_deleted		
			) 
			values 
			(
				'".$headerhash."',
				".$projectid.",
				'".$headername."',
				".$quantity.",
				'".$desc."',
				0				
			)";

			
			if(mysqli_query($con,$sqladdheader))	
			{
				$res["status"] = 1;
				$res['msg'] = 'Header Added';		
			}			
			else
			{
				$res['msg'] = "Error : Failed to add";
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