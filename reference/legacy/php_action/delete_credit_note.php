<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$billid = mysqli_real_escape_string($con, clean_input($_POST["billid"]));
	$invoiceid = mysqli_real_escape_string($con, clean_input($_POST["invoiceid"]));
	$projectid = mysqli_real_escape_string($con, clean_input($_POST["projectid"]));
	$creditnoteid = mysqli_real_escape_string($con, clean_input($_POST["creditnoteid"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($billid == "" || $invoiceid == "" || $projectid == "" || $creditnoteid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	else
    	{	

			$sqldeletecreditnote = "delete from credit_note where id = ".$creditnoteid." AND invoice_id = ".$invoiceid." AND project_id = ".$projectid." AND bill_id = ".$billid;

			if(mysqli_query($con,$sqldeletecreditnote))
			{	
				$res["status"] = 1;
				$res['msg'] = 'CN Deleted';		
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