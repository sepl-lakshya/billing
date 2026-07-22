<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$fullname = mysqli_real_escape_string($con, clean_input($_POST["full-name"]));
	$email = mysqli_real_escape_string($con, clean_input($_POST["user-email"]));
	$azureobjectid = mysqli_real_escape_string($con, clean_input($_POST["azure-object-id"]));

    $res['msg'] = "";
    $res['status'] = 0;
    $uniqueOk = 0;

    
    if(isset($_POST))
    {
    	if($fullname == "")
    	{
    		$res['msg'] = 'Please enter full name';		
    	}
    	elseif($email == "")
    	{
    		$res['msg'] = 'Please enter E-mail';		
    	}
    	elseif($azureobjectid == "")
    	{
    		$res['msg'] = 'Please enter Azure Object ID of user';		
    	}
    	else
    	{	



    		$sqlcheckname = "select id,is_active from login_detail where azure_object_id = '".$azureobjectid."' AND email = '".$email."'";
            $rowcheckname = mysqli_query($con, $sqlcheckname);
            if (mysqli_num_rows($rowcheckname) > 0)
            {
            	$rescheckname = mysqli_fetch_array($rowcheckname);
            	
            	if($rescheckname['is_active'] == 1)
            	{
            		$res['msg'] = 'User already exist';	   
            	}
            	else
            	{
            		$sqlactivateuser = "update login_detail set 
            		is_active = 1,
            		full_name = '".$fullname."',
            		created_on = '".date('Y-m-d H:i:s')."' 
            		where azure_object_id = '".$azureobjectid."' AND email = '".$email."'
            		";

					if(mysqli_query($con,$sqlactivateuser))
					{
						$res["status"] = 1;
            			$res['msg'] = 'User Added';	   
					}
            	}
            }
            else
            {
	    		do 
				{
					$userhash = getName(64);

					$sqlcheckhashexist = "select id from login_detail where hash = '".$userhash."'";
			        $rowcheckhashexist = mysqli_query($con, $sqlcheckhashexist);

			        if (!(mysqli_num_rows($rowcheckhashexist) > 0))
			        {
			        	$uniqueOk = 1;
			        }

				} while ($uniqueOk == 0);

				$sqladduser = "insert into login_detail 
				(
					hash,
					azure_object_id,
					email,	
					username,		
					password,		
					full_name,
					user_type,		
					is_active,		
					created_on	
				) 
				values 
				(
					'".$userhash."',
					'".$azureobjectid."',
					'".$email."',
					'',
					'".password_hash("123", PASSWORD_DEFAULT)."',
					'".$fullname."',
					2,
					1,
					'".date('Y-m-d H:i:s')."'
				)";

				if(mysqli_query($con,$sqladduser))
				{	
					$res["status"] = 1;
					$res['msg'] = 'User Added';			
				}
				else
				{
					$res['msg'] = 'Error : Failed to Add!';		
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