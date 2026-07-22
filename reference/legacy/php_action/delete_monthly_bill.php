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

			$sqldeletebill = "update bill set is_deleted = 1 where id = ".$billid;

			if(mysqli_query($con,$sqldeletebill))
			{	

				$sqldeletebillitem = "delete from bill_item where bill_id = ".$billid;

				if(mysqli_query($con,$sqldeletebillitem))
				{	

					$sqldeletebillheader = "delete from bill_header where bill_id = ".$billid;

					if(mysqli_query($con,$sqldeletebillheader))
					{	
						$sqldeletebillpurchaseheader = "delete from bill_purchase_header where bill_id = ".$billid;

						if(mysqli_query($con,$sqldeletebillpurchaseheader))
						{
							$sqlupdateci = "update cloud_inward set is_cancelled = 1 where bill_id = ".$billid;

							if(mysqli_query($con,$sqlupdateci))
							{	
								$sqlupdateco = "update cloud_outward set is_cancelled = 1 where bill_id = ".$billid;

								if(mysqli_query($con,$sqlupdateco))
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
									$res['msg'] = 'Error : Failed to update CO !';		
								}
							}
							else
							{
								$res['msg'] = 'Error : Failed to update CI !';		
							}		
						}
						else
						{
							$res['msg'] = 'Error : Failed to delete bill purchase header !';		
						}
					}
					else
					{
						$res['msg'] = 'Error : Failed to delete bill header !';		
					}
				}
				else
				{
					$res['msg'] = 'Error : Failed to delete bill items !';		
				}		
			}
			else
			{
				$res['msg'] = 'Error : Failed to delete bill !';		
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