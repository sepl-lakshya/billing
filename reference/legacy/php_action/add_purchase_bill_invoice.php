<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["add-purchase-bill-invoice-project-id"]));
	$billid = mysqli_real_escape_string($con, clean_input($_POST["add-purchase-bill-invoice-bill-id"]));
	$invoiceid = mysqli_real_escape_string($con, clean_input($_POST["add-purchase-bill-invoice-id"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($invoiceid == "0" || $invoiceid == "")
    	{
    		$res['msg'] = 'Please select invoice';		
    	}
    	else
    	{	
			$sqladdinvoice = "insert into bill_invoice_mapping 
			(
				project_id,			
				bill_id,			
				invoice_id,			
				created_on	
			) 
			values 
			(
				".$projectid.",
				".$billid.",
				".$invoiceid.",
				'".date('Y-m-d H:i:s')."'
			)";

			if(mysqli_query($con,$sqladdinvoice))
			{	
				$sqlupdatedn = "update debit_note set bill_id = ".$billid." where project_id = ".$projectid." AND invoice_id = ".$invoiceid;

				if(mysqli_query($con,$sqlupdatedn))
				{	

					$res["status"] = 1;
					$res['msg'] = 'Invoice Added';		
				}
				else
				{
					$res['msg'] = 'Error : Failed to add!';		
				}
			}
			else
			{
				$res['msg'] = 'Error : Failed to add!';		
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