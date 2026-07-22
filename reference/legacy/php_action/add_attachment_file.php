<?php 
	if (session_status() == PHP_SESSION_NONE) 
  	{
    	session_start();
  	}
  	include "../include/config.php";
	$con = connectMySQL();

	$pid = mysqli_real_escape_string($con, clean_input($_POST["attachment-project-id"]));
	$title = mysqli_real_escape_string($con, clean_input($_POST["file-title"]));

    $res['msg'] = "";
    $res['status'] = 0;
    $uniqueOk = 0;
    $uploadOk = 0;

    
    if(isset($_POST))
    {

    	if($pid == "")
	    {
	        $res['msg'] = 'Error : Missing Parameters';
	    }
        elseif($title == "")
        {
            $res['msg'] = 'Please enter file title';
        }
		elseif(empty($_FILES['attachment-file']['name']))
        {
            $res['msg'] = "Please select a file.";    
        }
    	else
    	{	


			$sqlchecktitle = "select id from project_attachment where project_id = ".$pid." AND title = '".$title."'";
            $rowchecktitle = mysqli_query($con, $sqlchecktitle);

            if (mysqli_num_rows($rowchecktitle) > 0)
            {
                $res['msg'] = 'Title already exist';       
            }
            else
            {
            
    			if(isset($_FILES['attachment-file']) && $_FILES['attachment-file']['error'] === UPLOAD_ERR_OK)
                {
                    $fileuploaded = 1;            

                    $fileTmpPath = $_FILES['attachment-file']['tmp_name'];
                    $fileName = $_FILES['attachment-file']['name'];
                    $fileSize = $_FILES['attachment-file']['size'];
                    $fileType = $_FILES['attachment-file']['type'];
                    $fileNameCmps = explode(".", $fileName);
                    $fileExtension = strtolower(end($fileNameCmps));
                    $fileNameWithoutExtension = strtolower(reset($fileNameCmps));
                    $fileNameWithoutExtension  = preg_replace('/\s+/', '_', $fileNameWithoutExtension);
                    $newFileName = $fileNameWithoutExtension.'_'.getName(10).time().'.'.$fileExtension;

                    // file extensions allowed
                    $allowedfileExtensions = array('pdf','xlsx','xls','doc','docx', 'png','jpg','jpeg');
                    if (in_array($fileExtension, $allowedfileExtensions))
                    {
                        if($fileSize < 2000000)
                        {

                            // directory where file will be moved
                            $uploadFileDir = "../uploads/attachment/";
                            $dest_path = $uploadFileDir.$newFileName;

                            if($fileerror = move_uploaded_file($fileTmpPath, $dest_path))
                            {
                                $uploadOk = 1;
                            }
                            else 
                            {
                                $res['msg'] = 'Error uploading your file .';
                            }
                        }
                        else
                        {
                            $res['msg'] = 'File size should be less than 2MB';
                        }
                    }
                    else
                    {
                       $res['msg'] = 'Invalid file type.';
                    }

                }            

                if($uploadOk == 1)
                {
                    $sqladdfile = "insert into project_attachment (project_id,title,original_name, file_name,created_on) values (".$pid.",'".$title."','".$fileName."','".$newFileName."','".date('Y-m-d H:i:s')."')";

                    if(mysqli_query($con,$sqladdfile))   
                    {

                        $res['status'] = "1";
                        $res['msg'] = "File Uploaded";
                    }
                    else
                    {
                        $res['msg'] = "Error : Failed to add file.";
                    }
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