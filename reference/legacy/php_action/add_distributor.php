<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$dname = mysqli_real_escape_string($con, clean_input($_POST["distributor-name"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($dname == "")
    	{
    		$res['msg'] = 'Please enter distributor name';		
    	}
    	else
    	{	

    		$sqlcheckname = "select id from distributor where name = '".$dname."'";
            $rowcheckname = mysqli_query($con, $sqlcheckname);

            if (mysqli_num_rows($rowcheckname) > 0)
            {
            	$res['msg'] = 'Distributor name already exist';	   
            }
            else
            {
				$sqladddistributor = "insert into distributor 
				(
					name,			
					created_on	
				) 
				values 
				(
					'".$dname."',
					'".date('Y-m-d H:i:s')."'
				)";

				if(mysqli_query($con,$sqladddistributor))
				{	

					$res["status"] = 1;
					$res['msg'] = 'Distributor Added';		
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