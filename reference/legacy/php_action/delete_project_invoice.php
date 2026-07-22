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

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($billid == "" && $invoiceid == "" && $projectid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	else
    	{	

			$sqlunassigninvoice = "delete from bill_invoice_mapping where invoice_id = ".$invoiceid." AND project_id = ".$projectid." AND bill_id = ".$billid;

			if(mysqli_query($con,$sqlunassigninvoice))
			{	
				$sqldeletedebitnote = "delete from debit_note where invoice_id = ".$invoiceid." AND project_id = ".$projectid." AND bill_id = ".$billid;

				if(mysqli_query($con,$sqldeletedebitnote))
				{	
					$sqldeletecreditnote = "delete from credit_note where invoice_id = ".$invoiceid." AND project_id = ".$projectid." AND bill_id = ".$billid;

					if(mysqli_query($con,$sqldeletecreditnote))
					{	
						$sqldeleteinvoice = "delete from purchase_invoice where id = ".$invoiceid." AND project_id = ".$projectid;

						if(mysqli_query($con,$sqldeleteinvoice))
						{	
							$res["status"] = 1;
							$res['msg'] = 'Item Deleted';		
						}
						else
						{
							$res['msg'] = 'Error : Failed to delete!';		
						}
					}
					else
					{
						$res['msg'] = 'Error : Failed to delete!';		
					}
				}
				else
				{
					$res['msg'] = 'Error : Failed to delete!';		
				}
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