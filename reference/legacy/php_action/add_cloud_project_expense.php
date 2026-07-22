<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["add-expense-project-id"]));
	$expensetype = mysqli_real_escape_string($con, clean_input($_POST["add-expense-type"]));
	$amount = mysqli_real_escape_string($con, clean_input($_POST["add-expense-amount"]));

    $res['msg'] = "";
    $res['status'] = 0;
    $uniqueOk = 0;

    
    if(isset($_POST))
    {
    	if($projectid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters';		
    	}
    	elseif($expensetype == "")
    	{
    		$res['msg'] = 'Please select expense type.';		
    	}
    	elseif($amount == "")
    	{
    		$res['msg'] = 'Please enter expense amount.';		
    	}
    	elseif (!preg_match('/^[0-9]+(\.[0-9]+)?$/', $amount))
    	{
    		$res['msg'] = 'Please enter a valid expense amount.';		
    	}
    	else
    	{	


			$sqladdexpense = "insert into project_expense 
			(
				project_id,
				expense_type_id,
				amount
			) 
			values 
			(
				".$projectid.",
				".$expensetype.",
				".$amount."	
			)";

			
			if(mysqli_query($con,$sqladdexpense))	
			{
				$res["status"] = 1;
				$res['msg'] = 'Expense Added';		
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