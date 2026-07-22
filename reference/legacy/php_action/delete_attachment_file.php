<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$pid = mysqli_real_escape_string($con, clean_input($_POST["projectid"]));
	$aid = mysqli_real_escape_string($con, clean_input($_POST["attachmentid"]));


    $res['msg'] = "";
    $res['status'] = 0;
   

    
    if(isset($_POST))
    {

    	$sqlgetattachment = "select file_name from project_attachment where id = ".$aid." AND project_id = ".$pid;
        $rowgetattachment = mysqli_query($con, $sqlgetattachment);

        if (mysqli_num_rows($rowgetattachment) > 0)
        {
            $resgetattachment = mysqli_fetch_array($rowgetattachment);
            
            $filepath = "../uploads/attachment/".$resgetattachment['file_name'];

            if(unlink($filepath))
            {

                $sqldeleteattachment = "delete from project_attachment where id = ".$aid." AND project_id = ".$pid;

                if(mysqli_query($con,$sqldeleteattachment))
                {   

                    $res["status"] = 1;
                    $res['msg'] = 'Attachment Deleted';       
                }
                else
                {
                    $res['msg'] = 'Error : Failed to delete!';      
                }
            }
            else
            {
                $res['msg'] = 'Error : Failed to delete file!';     
            }
        }
        else
        {
            $res['msg'] = 'Error : Failed to get file!';        
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