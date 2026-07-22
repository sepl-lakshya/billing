<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$purchaseheadername = mysqli_real_escape_string($con, clean_input($_POST["purchase-header-name"]));
	$projectid = mysqli_real_escape_string($con, clean_input($_POST["purchase-header-project-id"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($projectid == "")
    	{
    		$res['msg'] = "Please select project";
    	}
    	elseif($purchaseheadername == "")
    	{
    		$res['msg'] = 'Please enter purchase header name';		
    	}
    	else
    	{	

    		$sqlcheckname = "select id from purchase_header where name = '".$purchaseheadername."' AND project_id = ".$projectid;
            $rowcheckname = mysqli_query($con, $sqlcheckname);

            if (mysqli_num_rows($rowcheckname) > 0)
            {
            	$res['msg'] = 'Header name already exist';	   
            }
            else
            {
				$sqladdpurchaseheader = "insert into purchase_header 
				(
					name,
					project_id,			
					created_on	
				) 
				values 
				(
					'".$purchaseheadername."',
					".$projectid.",
					'".date('Y-m-d H:i:s')."'
				)";

				if(mysqli_query($con,$sqladdpurchaseheader))
				{	

					$res["status"] = 1;
					$res['msg'] = 'Purchase Header Added';		
				}
				else
				{
					$res['msg'] = 'Error : Failed to add!';		
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