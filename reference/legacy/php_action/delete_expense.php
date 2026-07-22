<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$expenseid = mysqli_real_escape_string($con, clean_input($_POST["expenseid"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($expenseid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	else
    	{	

			$sqldeleteexpense = "delete from expense where id = ".$expenseid;

			if(mysqli_query($con,$sqldeleteexpense))
			{	

				$res["status"] = 1;
				$res['msg'] = 'Expense Deleted';		
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