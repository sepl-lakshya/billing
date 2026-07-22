<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$distributorid = mysqli_real_escape_string($con, clean_input($_POST["distributorid"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($distributorid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	else
    	{	

			$sqldeletedistributor = "update distributor set is_active = 0 where id = ".$distributorid;

			if(mysqli_query($con,$sqldeletedistributor))
			{	

				$res["status"] = 1;
				$res['msg'] = 'Distributor Deleted';		
			}
			else
			{
				$res['msg'] = 'Error : Failed to delete!';		
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