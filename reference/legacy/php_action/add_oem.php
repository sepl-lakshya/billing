<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$oemname = mysqli_real_escape_string($con, clean_input($_POST["oem-name"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($oemname == "")
    	{
    		$res['msg'] = 'Please enter OEM name';		
    	}
    	else
    	{	

    		$sqlcheckname = "select id from oem where name = '".$oemname."'";
            $rowcheckname = mysqli_query($con, $sqlcheckname);

            if (mysqli_num_rows($rowcheckname) > 0)
            {
            	$res['msg'] = 'OEM name already exist';	   
            }
            else
            {
				$sqladdoem = "insert into oem 
				(
					name,			
					created_on	
				) 
				values 
				(
					'".$oemname."',
					'".date('Y-m-d H:i:s')."'
				)";

				if(mysqli_query($con,$sqladdoem))
				{	

					$res["status"] = 1;
					$res['msg'] = 'OEM Added';		
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