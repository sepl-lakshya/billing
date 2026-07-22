<?php
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    

	$mcImageManagerConfig['upload.maxsize'] = "1MB";
	// $con = mysqli_connect($host, $user, $password, $dbname) or die("Something goes wrong try again later..!");

	function connectMySQL()
	{
		$host="localhost:3306";
    	$user="seplroot";
    	$dbname="billing";
    	$password="Sepldb_812";
		$con = mysqli_connect($host, $user, $password, $dbname) or die("Something goes wrong try again later..!");
		return $con;

	}
	
	function connectMySQLDatabase($dbname)
	{
		$host="localhost:3306";
    	$user="seplroot";
    	$password="Sepldb_812";
		$con = mysqli_connect($host, $user, $password, $dbname) or die("Something goes wrong try again later..!");
		return $con;

	}

	


	date_default_timezone_set("Asia/Kolkata");

	function time_elapsed_string($datetime, $full = false) 
	{
	    $now = new DateTime;
	    $ago = new DateTime($datetime);
	    $diff = $now->diff($ago);

	    $diff->w = floor($diff->d / 7);
	    $diff->d -= $diff->w * 7;

	    $string = array(
	        'y' => 'y',
	        'm' => 'm',
	        'w' => 'w',
	        'd' => 'd',
	        'h' => 'h',
	        'i' => 'm',
	        's' => 's',
	    );
	    foreach ($string as $k => &$v) {
	        if ($diff->$k) {
	            $v = $diff->$k . '' . $v . ($diff->$k > 1 ? '' : '');
	        } else {
	            unset($string[$k]);
	        }
	    }

	    if (!$full) $string = array_slice($string, 0, 1);
	    return $string ? implode(', ', $string) . '' : 'just now';
	}
	
	function numberFormat($number, $decimals=0)
	{
		if($number >= 1000)
		{
		    if (strpos($number,'.')!=null)
		    {
		        $decimalNumbers = substr($number, strpos($number,'.'));
		        $decimalNumbers = substr($decimalNumbers, 1, $decimals);
		    }
		    else
		    {
		        $decimalNumbers = 0;
		        for ($i = 2; $i <=$decimals ; $i++)
		        {
		            $decimalNumbers = $decimalNumbers.'0';
		        }
		    }


		    $number = (int) $number;
		    $number = strrev($number);  // reverse

		    $n = '';
		    $stringlength = strlen($number);

		    for ($i = 0; $i < $stringlength; $i++)
		    {
		        // from digit 3, every 2 digit () add comma
		        if($i==2 || ($i>2 && $i%2==0) ) $n = $n.$number[$i].','; 
		        else $n = $n.$number[$i];
		    }

		    $number = $n;    
		    $number = strrev($number); // reverse

		    ($decimals!=0)? $number=$number.'.'.$decimalNumbers : $number ;
		    if ($number[0] == ',') $number = substr_replace($number, '', 0, 1);
		    if ($number[1] == ',' && $number[0] == '-') $number = substr_replace($number, '', 1, 1);
		}      

	    return $number;
	}

	function getDomain()
	{
	    global $con;
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
			$url = "https://";   
		else  
			$url = "http://";   
		$url.= $_SERVER['HTTP_HOST'];
		$url.= "";
		return $url;
	}
	
	function getImageDomain()
	{
	    $url = "https://projects.surbhi.net";
	    return $url;
	}

	function getPageExt()
	{
		return "";
	}

	function getDefaultTheme()
	{
		return "";
		// return " class='dark' ";
	}
	

	function clean_input($data) 
	{
	  $data = trim($data);
	  // $data = addslashes($data);
	  return $data;
	}

	function getName($n) { 
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
        $randomString = ''; 
  
        for ($i = 0; $i < $n; $i++) { 
            $index = rand(0, strlen($characters) - 1); 
            $randomString .= $characters[$index]; 
        } 
        return $randomString; 
    }

    function getNumber($n) { 
        $characters = '0123456789'; 
        $randomString = ''; 
  
        for ($i = 0; $i < $n; $i++) { 
            $index = rand(0, strlen($characters) - 1); 
            $randomString .= $characters[$index]; 
        } 
        return $randomString; 
    }
	

	
	function isLoggedIn()
	{
		if
		(
			isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "" &&
			isset($_SESSION["full_name"]) && $_SESSION["full_name"] != "" && 
			isset($_SESSION["user_type"]) && $_SESSION["user_type"] != "" &&
			isset($_SESSION["user_type_name"]) && $_SESSION["user_type_name"] != "" &&
			isset($_SESSION["user_email"]) && $_SESSION["user_email"] != "" &&
			isset($_SESSION["azure_display_name"]) && $_SESSION["azure_display_name"] != "" &&
			isset($_SESSION["azure_object_id"]) && $_SESSION["azure_object_id"] != "" &&
			isset($_SESSION["unique_token_id"]) && $_SESSION["unique_token_id"] != ""
		)	
		{
			return true;
		}
		else
		{
			return false;
		}

	}


	function compressImage($source, $destination, $quality) { 
		// Get image info 
		$imgInfo = getimagesize($source); 
		$mime = $imgInfo['mime']; 

		// Create a new image from file 
		switch($mime){ 
			case 'image/jpeg': 
				$image = imagecreatefromjpeg($source); 
				break; 
			case 'image/png': 
				$image = imagecreatefrompng($source); 
				break; 
			case 'image/gif': 
				$image = imagecreatefromgif($source); 
				break; 
			default: 
				$image = imagecreatefromjpeg($source); 
		} 

		// Save image 
		$uploaded = imagejpeg($image, $destination, $quality);
		imagedestroy($image); 
		// Return compressed image 
		return $uploaded;
	} 
					
					
?>