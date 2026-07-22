<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["create-credit-note-project-id"]));
	$billid = mysqli_real_escape_string($con, clean_input($_POST["create-credit-note-bill-id"]));
	$invoiceid = mysqli_real_escape_string($con, clean_input($_POST["create-credit-note-invoice-id"]));
	$debitnoteid = mysqli_real_escape_string($con, clean_input($_POST["create-credit-note-dn-id"]));
	$referenceno = mysqli_real_escape_string($con, clean_input($_POST["create-credit-note-reference-no"]));
	$amount = mysqli_real_escape_string($con, clean_input($_POST["create-credit-note-amount"]));
	$remark = mysqli_real_escape_string($con, clean_input($_POST["create-credit-note-remark"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($projectid == "" || $billid == "" || $invoiceid == "" || $debitnoteid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters';		
    	}
    	elseif($referenceno == "")
    	{
    		$res['msg'] = "Please enter reference number";
    	}
    	elseif($amount == "")
    	{
    		$res['msg'] = "Please enter credit amount";
    	}
    	else
    	{	

			$sqlcreateci = "insert into credit_note 
			(
				debit_note_id,
				project_id,
				bill_id,
				invoice_id,
				reference_no,
				amount,
				remark,
				created_on	
			) 
			values 
			(
				".$debitnoteid.",
				".$projectid.",
				".$billid.",
				".$invoiceid.",
				'".$referenceno."',
				".$amount.",
				'".$remark."',
				'".date('Y-m-d H:i:s')."'
			)";

			if(mysqli_query($con,$sqlcreateci))
			{	

				$res["status"] = 1;
				$res['msg'] = 'Credit Note Created';		
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