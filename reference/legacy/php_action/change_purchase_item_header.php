<?php 
  if (session_status() == PHP_SESSION_NONE) 
    {
      session_start();
    }
    include "../include/config.php";

    $con = connectMySQL();
    $res['status'] = 0;
    $res['item_header_status'] = 0;
    $res['header_status'] = 0;
    $res['msg'] = "";
    $uniqueOk = 0;
  
    // General Info
  $projectid = mysqli_real_escape_string($con, clean_input($_POST["projectidpost"]));
  $month = mysqli_real_escape_string($con, clean_input($_POST["pricemonthpost"]));
  $year = mysqli_real_escape_string($con, clean_input($_POST["priceyearpost"]));
  $billid = mysqli_real_escape_string($con, clean_input($_POST["billidpost"]));
  $projectitemidarrval = clean_input($_POST["projectitemidarrvalpost"]);
  $purchaseheaderidarrval = clean_input($_POST["purchaseheaderidarrvalpost"]);

  //Product Arrays Explode
  if(!($projectitemidarrval == "" && $purchaseheaderidarrval == ""))
  {
    $projectitemidarr = explode(",",$projectitemidarrval);
    $purchaseheaderidarr = explode(",",$purchaseheaderidarrval);
    $headerarrayempty = false;
  }
  else
  {
    $headerarrayempty = true;
  }

 
  if(isset($_POST))
  {

    if($headerarrayempty)
    {
      $res['msg'] = "Error : No data recieved from client !";
    }
    else
    {

      // Add Purchase Price
      for($i = 0; $i < count($projectitemidarr); $i++) 
      {

        $sqlupdateprojectitem = "update project_item set purchase_header_id = ".mysqli_real_escape_string($con, $purchaseheaderidarr[$i])." where project_id = ".$projectid." AND id = ".mysqli_real_escape_string($con, $projectitemidarr[$i]);

        if(mysqli_query($con,$sqlupdateprojectitem)) 
        {
          $sqlupdatebillitem = "update bill_item set purchase_header_id = ".mysqli_real_escape_string($con, $purchaseheaderidarr[$i])." where bill_id = ".$billid." AND project_id = ".$projectid." AND project_item_id = ".mysqli_real_escape_string($con, $projectitemidarr[$i]);

          if(mysqli_query($con,$sqlupdatebillitem)) 
          {
            $res['item_header_status'] = 1;
          }
          else
          {
            $res['item_header_status'] = 0;
          }
        }
        else
        {
          $res['item_header_status'] = 0;
        }
      }

      if($res['item_header_status'] == 1)
      {
        $sqlgetbillitemheader = "select distinct(pi.purchase_header_id) from project_item as pi INNER JOIN bill_item as bi ON pi.id = bi.project_item_id where bi.bill_id = ".$billid." AND pi.project_id = ".$projectid." order by pi.id";
        $rowgetbillitemheader = mysqli_query($con, $sqlgetbillitemheader);

        if (mysqli_num_rows($rowgetbillitemheader) > 0)
        {

          $j = 0;
          $sqlsetpurchaseitemheadervalues = "";
          while ($j <= ($resgetbillitemheader = mysqli_fetch_array($rowgetbillitemheader)))
          {
            if($j == 0)
            {
              $sqlsetpurchaseitemheadervalues .= "(".$billid.",".$projectid.",".$resgetbillitemheader['purchase_header_id'].",NULL)";
            }
            else
            {
              $sqlsetpurchaseitemheadervalues .= " , (".$billid.",".$projectid.",".$resgetbillitemheader['purchase_header_id'].",NULL)";
            }

            $j++;
          }

          $sqldeletepurchaseitemheader = "delete from bill_purchase_header where bill_id = ".$billid." AND project_id = ".$projectid;
          if(mysqli_query($con,$sqldeletepurchaseitemheader)) 
          {
            $sqlsetpurchaseitemheader = "insert into bill_purchase_header (bill_id,project_id, purchase_header_id,amount) values ".$sqlsetpurchaseitemheadervalues;
            if(mysqli_query($con,$sqlsetpurchaseitemheader)) 
            {
              $res['header_status'] = 1;
            }
            else
            {
              $res['msg'] = "Error : Failed to add header";
            }
          }
          else
          {
            $res['msg'] = "Error : Failed to delete header";
          }
        }
        else
        {
          $res['msg'] = "Error : Failed to get header";
        }
      }
      else
      {
        $res['msg'] = "Error : Failed to update header";
      }

      
    }
  }

  if($res["header_status"] == 1)
  {
    // $sqlupdatebillstatus = "update bill set purchase_header_status = 1 where id = ".$billid." AND project_id = ".$projectid;

    // if(mysqli_query($con,$sqlupdatebillstatus)) 
    // {
      $res['billid'] = $billid;
      $res["status"] = 1;
      $res['msg'] = 'Purchase Header Saved';  
    // }
    // else
    // {
    //   $res['msg'] = 'Error : Failed to update status !';    
    // } 
  }
  else
  {
    // $res['msg'] = 'Error : Failed to update header !';   
  }

  echo mysqli_error($con);

  mysqli_close($con);
  echo json_encode($res);


?>