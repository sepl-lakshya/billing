<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$projectid = mysqli_real_escape_string($con, clean_input($_POST["add-invoice-project-id"]));
	$referenceno = mysqli_real_escape_string($con, clean_input($_POST["add-invoice-reference-no"]));
	$amount = mysqli_real_escape_string($con, clean_input($_POST["add-invoice-amount"]));
	$gstslab = mysqli_real_escape_string($con, clean_input($_POST["add-invoice-gst-slab"]));
	$date = mysqli_real_escape_string($con, clean_input($_POST["add-invoice-date"]));
	$desc = mysqli_real_escape_string($con, clean_input($_POST["add-invoice-desc"]));

	if(isset($_POST["add-invoice-type"]))
	{
		$invoicetype = mysqli_real_escape_string($con, clean_input($_POST["add-invoice-type"]));
	}
	else
	{
		$invoicetype = "";
	}

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	if($projectid == "")
    	{
    		$res['msg'] = 'Please select project';		
    	}
    	elseif($referenceno == "")
    	{
    		$res['msg'] = 'Please enter invoice reference number';		
    	}
    	elseif($amount == "")
    	{
    		$res['msg'] = 'Please enter invoice total amount';		
    	}
    	elseif($gstslab == "0")
    	{
    		$res['msg'] = 'Please select GST Slab';		
    	}
    	elseif($invoicetype == "")
    	{
    		$res['msg'] = 'Please select invoice type';		
    	}
    	// elseif($date == "")
    	// {
    	// 	$res['msg'] = 'Please enter invoice date';		
    	// }
    	else
    	{	

    		$sqlcheckname = "select id from purchase_invoice where number = '".$referenceno."' AND is_deleted = 0";
            $rowcheckname = mysqli_query($con, $sqlcheckname);
            if (mysqli_num_rows($rowcheckname) > 0)
            {
            	$res['msg'] = 'Invoice reference number already exist';	   
            }
            else
            {

            	if($date == "")
            	{
            		$date = date('Y-m-d H:i:s');
            	}

				$sqladdinvoice = "insert into purchase_invoice 
				(
					project_id,					
					number,			
					amount,			
					gst_slab,
					invoice_date,			
					description,			
					type,			
					is_deleted,			
					created_on	
				) 
				values 
				(
					".$projectid.",
					'".$referenceno."',
					".$amount.",
					".$gstslab.",
					'".$date."',
					'".$desc."',
					'".$invoicetype."',
					0,
					'".date('Y-m-d H:i:s')."'
				)";

				if(mysqli_query($con,$sqladdinvoice))
				{	

					$res["status"] = 1;
					$res['msg'] = 'Invoice Added';		
				}
				else
				{
					$res['msg'] = 'Error : Failed to add!';		
				}
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