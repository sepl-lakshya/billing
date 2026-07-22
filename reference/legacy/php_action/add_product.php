<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$name = mysqli_real_escape_string($con, clean_input($_POST["product-name"]));
	$oem = mysqli_real_escape_string($con, clean_input($_POST["product-oem"]));
	$category = mysqli_real_escape_string($con, clean_input($_POST["product-category"]));
	$licencecategory = mysqli_real_escape_string($con, clean_input($_POST["licence-category"]));
	$cloudcategory = mysqli_real_escape_string($con, clean_input($_POST["cloud-category"]));

    $res['msg'] = "";
    $res['status'] = 0;
    
    if(isset($_POST))
    {
    	
    	if($oem == "0")
    	{
    		$res['msg'] = 'Please select product OEm';		
    	}
    	elseif($category == "0")
    	{
    		$res['msg'] = 'Please select product category';		
    	}
    	elseif($category == "2" && $licencecategory == "0")
    	{
    		$res['msg'] = 'Please select licence category';		
    	}
    	elseif($category == "1" && $cloudcategory == "0")
    	{
    		$res['msg'] = 'Please select licence category';		
    	}
    	elseif($name == "")
    	{
    		$res['msg'] = 'Please enter product name';		
    	}
    	else
    	{	

    		$subcategory = "0";
    		if($category == "1")
    		{
    			$subcategory = $cloudcategory;
    		}

    		if($category == "2")
    		{
    			$subcategory = $licencecategory;
    		}

    		$sqlcheckname = "select id from product where name = '".$name."' AND category = ".$category." AND sub_category = ".$subcategory;
            $rowcheckname = mysqli_query($con, $sqlcheckname);

            if (mysqli_num_rows($rowcheckname) > 0)
            {
            	$res['msg'] = 'Product name already exist';	   
            }
            else
            {
				$sqladdproduct = "insert into product 
				(
					name,	
					oem,
					category,
					sub_category,		
					created_on	
				) 
				values 
				(
					'".$name."',
					".$oem.",
					".$category.",
					".$subcategory.",
					'".date('Y-m-d H:i:s')."'
				)";

				if(mysqli_query($con,$sqladdproduct))
				{	

					$res["status"] = 1;
					$res['msg'] = 'Product Added';		
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