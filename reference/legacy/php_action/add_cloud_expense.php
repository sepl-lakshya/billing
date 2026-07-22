<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$expensetype = mysqli_real_escape_string($con, clean_input($_POST["add-expense-type"]));
	$name = mysqli_real_escape_string($con, clean_input($_POST["add-expense-name"]));
	$amount = mysqli_real_escape_string($con, clean_input($_POST["add-expense-amount"]));
	$desc = mysqli_real_escape_string($con, clean_input($_POST["add-expense-desc"]));

    $res['msg'] = "";
    $res['status'] = 0;
    $uniqueOk = 0;

    
    if(isset($_POST))
    {

    	if($expensetype == "0")
    	{
    		$res['msg'] = 'Please select expense type.';		
    	}
    	elseif($expensetype == "")
    	{
    		$res['msg'] = 'Please enter expense name.';		
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


			$sqladdexpense = "insert into expense 
			(
				expense_type_id,
				name,
				amount,
				description,
				created_on
			) 
			values 
			(
				".$expensetype.",
				'".$name."',
				".$amount.",	
				'".$desc."',	
				'".date('Y-m-d H:i:s')."'
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