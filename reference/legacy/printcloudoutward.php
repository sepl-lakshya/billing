<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

  	if(isLoggedIn())
  	{  

		if(isset($_GET['pid']) && $_GET['pid'] != "" && isset($_GET['bid']) && $_GET['bid'] != "" && isset($_GET['coid']) && $_GET['coid'] != "")
		{
		  $projectid = $_GET['pid'];
		  $billid = $_GET['bid'];
		  $ciid = $_GET['coid'];
		        
		  $con = connectMySQL();
		  $sqlgetcloudoutward = "select *,
		  (select name from month where value = (select month from bill where id = co.bill_id)) as month_name,
		  (select year from bill where id = co.bill_id) as year_val,
		  (select payg_discount from bill where id = co.bill_id) as payg_discount,
		  (select ri_discount from bill where id = co.bill_id) as ri_discount,
		  (select full_name from portal.login_detail where id = co.created_by) as created_by_name,
		  (select name from project where id = co.project_id) as project_name
		  from cloud_outward as co where id = ".$ciid." AND bill_id = ".$billid." AND project_id = ".$projectid;
		  $rowgetcloudoutward = mysqli_query($con, $sqlgetcloudoutward);

		  if (mysqli_num_rows($rowgetcloudoutward) > 0)
		  {
		    $resgetcloudoutward = mysqli_fetch_array($rowgetcloudoutward);
		?>

<!DOCTYPE html>
<html <?php echo getDefaultTheme(); ?> >
<head>
  <title>Cloud Outward</title>

  <!-- Head Section Include -->
  <?php include "include/headsection.php"; ?>
  <style type="text/css">
  		
  		@media print
			{    
			    .no-print, .no-print *
			    {
			        display: none !important;
			    }
			}

  </style>
</head>
<body>
<div class="app-container">
  <div class="app-content">    
    <div class="content-container">
      <section class="content-section">
      	<button class="no-print" onclick="window.print();">Print</button>
        <div class="section-heading align-center">
          Cloud Outward - SEPL/CO/<?php echo $resgetcloudoutward['id']; ?>
        </div>
        <div class="section-content-wrapper">

		      <form method="post" class="ce-form" id="ce-create-cloud-inward-form" style="width:100%;">
	            
		        <div class="ce-form-controls ce-form-control-col-1">
	              <div class="ce-form-label">Project Name :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudoutward['project_name'] ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-2">
	              <div class="ce-form-label">Verified By :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudoutward['created_by_name'] ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-4">
	              <div class="ce-form-label">Month :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudoutward['month_name'] ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-4">
	              <div class="ce-form-label">Year :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudoutward['year_val'] ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-2">
	              <div class="ce-form-label">Sales Total :</div>
	              <div class="ce-form-input">
	                <?php echo "Rs. ".numberFormat(round($resgetcloudoutward['sales_total'],2),2); ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-4">
	              <div class="ce-form-label">PAYG Discount :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudoutward['payg_discount']." %"; ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-4">
	              <div class="ce-form-label">RI Discount :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudoutward['ri_discount']." %"; ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-1">
		            <div class="ce-form-label">Remark (Max 999 characters) :</div>
		            <div class="ce-form-input">
		              <?php echo $resgetcloudoutward['remark']; ?>
		            </div>
		          </div>
		          <div class="ce-form-controls ce-form-control-col-2">
		            <div class="ce-form-label">Status :</div>
		            <div class="ce-form-input">
		              <?php  
		              	if($resgetcloudoutward['is_cancelled'] == "1"){echo "Cancelled";}
		              	if($resgetcloudoutward['is_cancelled'] == "0"){echo "Active";}
		              ?>
		            </div>
		          </div>
		          <?php
		          	if($resgetcloudoutward['is_cancelled'] == "1")
              	{	
		          ?>
		          <div class="ce-form-controls ce-form-control-col-2">
		            <div class="ce-form-label">Reason for Cancelling :</div>
		            <div class="ce-form-input">
		              <?php  
		              		echo $resgetcloudoutward['cancel_reason'];
		              ?>
		            </div>
		          </div>
		          <?php
		          	}
		          ?>          
		      </form>

		     </div>
      </section>
    </div>
	</div>
</div>
</body>
</html>
<?php
  		}
      else
      { 
          echo "Error : Bill Not Found";
      }

    }
    else
    { 
        echo "Error : Missing Parameters !";
    }
  }
  else
  { 
      header("location:".getDomain());
  }

  
?>
