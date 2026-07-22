<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$billid = mysqli_real_escape_string($con, clean_input($_POST["billid"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($billid == "")
    	{
    		$res['msg'] = 'Error : Missing Parameters !';		
    	}
    	else
    	{	

			$sqlupdatebill = "update bill set purchase_header_status = 0, status = 2 where id = ".$billid;

			if(mysqli_query($con,$sqlupdatebill))
			{	
				$sqldeletebillitem = "update bill_item set purchase_price = NULL where bill_id = ".$billid;

				if(mysqli_query($con,$sqldeletebillitem))
				{	
					$sqldeleteinvoice = "delete from bill_invoice_mapping where bill_id = ".$billid;

					if(mysqli_query($con,$sqldeleteinvoice))
					{	
						$sqldeletedebitnote = "delete from debit_note where bill_id = ".$billid;

						if(mysqli_query($con,$sqldeletedebitnote))
						{	
							$sqldeletecreditnote = "delete from credit_note where bill_id = ".$billid;

							if(mysqli_query($con,$sqldeletecreditnote))
							{	
								$sqldeleteinvoiceci = "delete from invoice_cloud_inward where bill_id = ".$billid;

								if(mysqli_query($con,$sqldeleteinvoiceci))
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
				else
				{
					$res['msg'] = 'Error : Failed to update bill items !';		
				}		
			}
			else
			{
				$res['msg'] = 'Error : Failed to update bill !';		
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