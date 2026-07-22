<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$dntype = mysqli_real_escape_string($con, clean_input($_POST["create-debit-note-type"]));
	$projectid = mysqli_real_escape_string($con, clean_input($_POST["create-debit-note-project-id"]));
	$billid = mysqli_real_escape_string($con, clean_input($_POST["create-debit-note-bill-id"]));
	$invoiceid = mysqli_real_escape_string($con, clean_input($_POST["create-debit-note-invoice-id"]));
	$amount = mysqli_real_escape_string($con, clean_input($_POST["create-debit-note-amount"]));
	$credittype = mysqli_real_escape_string($con, clean_input($_POST["create-debit-note-credit-type"]));
	$remark = mysqli_real_escape_string($con, clean_input($_POST["create-debit-note-remark"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($projectid == "" || $billid == "" || $invoiceid == "" || $dntype == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters';		
    	}
    	elseif($amount == "")
    	{
    		$res['msg'] = "Please enter debit note amount";
    	}
    	elseif($credittype == "0")
    	{
    		$res['msg'] = "Please select DN type";
    	}
    	else
    	{	

			$sqlcreateci = "insert into debit_note 
			(
				project_id,
				bill_id,
				invoice_id,
				type,
				credit_type,
				amount,
				remark,
				created_on	
			) 
			values 
			(
				".$projectid.",
				".$billid.",
				".$invoiceid.",
				".$dntype.",
				".$credittype.",
				".$amount.",
				'".$remark."',
				'".date('Y-m-d H:i:s')."'
			)";

			if(mysqli_query($con,$sqlcreateci))
			{	

				$res["status"] = 1;
				$res['msg'] = 'Debit Note Created';		
			}
			else
			{
				$res['msg'] = 'Error : Failed to create!';		
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