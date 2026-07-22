<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

  	if(isLoggedIn())
  	{  

		if(isset($_GET['pid']) && $_GET['pid'] != "" && isset($_GET['bid']) && $_GET['bid'] != "" && isset($_GET['ciid']) && $_GET['ciid'] != "")
		{
		  $projectid = $_GET['pid'];
		  $billid = $_GET['bid'];
		  $ciid = $_GET['ciid'];
		        
		  $con = connectMySQL();
		  $sqlgetcloudinward = "select *,
		  (select name from month where value = (select month from bill where id = ici.bill_id)) as month_name,
		  (select year from bill where id = ici.bill_id) as year_val,
		  (select payg_discount from bill where id = ici.bill_id) as payg_discount,
		  (select ri_discount from bill where id = ici.bill_id) as ri_discount,
		  (select full_name from portal.login_detail where id = ici.created_by) as created_by_name,
		  (select name from project where id = ici.project_id) as project_name
		  from invoice_cloud_inward as ici where id = ".$ciid." AND bill_id = ".$billid." AND project_id = ".$projectid;
		  $rowgetcloudinward = mysqli_query($con, $sqlgetcloudinward);

		  if (mysqli_num_rows($rowgetcloudinward) > 0)
		  {
		    $resgetcloudinward = mysqli_fetch_array($rowgetcloudinward);
		?>

<!DOCTYPE html>
<html <?php echo getDefaultTheme(); ?> >
<head>
  <title>Cloud Inward</title>

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
          Cloud Inward - SEPL/CI/<?php echo $resgetcloudinward['id']; ?>
        </div>
        <div class="section-content-wrapper">

		      <form method="post" class="ce-form" id="ce-create-cloud-inward-form" style="width:100%;">
  
		        <div class="ce-form-controls ce-form-control-col-1">
	              <div class="ce-form-label">Project Name :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudinward['project_name'] ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">Verified By :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudinward['created_by_name'] ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">Month :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudinward['month_name'] ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">Year :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudinward['year_val'] ?>
	              </div>
	            </div>

	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">PAYG Discount :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudinward['payg_discount']." %"; ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">RI Discount :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudinward['ri_discount']." %"; ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label"></div>
	              <div class="ce-form-input">
		           	</div>
		    			</div>  

	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">Portal Total :</div>
	              <div class="ce-form-input">
	                <?php echo "Rs. ".numberFormat(round($resgetcloudinward['portal_total'],2),2); ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">Purchase Total :</div>
	              <div class="ce-form-input">
	                <?php echo "Rs. ".numberFormat(round($resgetcloudinward['purchase_total'],2),2); ?>
	              </div>
	            </div>
	            <?php 

		          	$sqlgetpurchaseinvoice = "select number,amount,invoice_date from purchase_invoice where id = ".$resgetcloudinward['invoice_id'];
		          	$rowgetpurchaseinvoice = mysqli_query($con, $sqlgetpurchaseinvoice);

		          	if (mysqli_num_rows($rowgetpurchaseinvoice) > 0)
		          	{
		            	$resgetpurchaseinvoice = mysqli_fetch_array($rowgetpurchaseinvoice);
		            
		        	?>
		        	<div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label"></div>
	              <div class="ce-form-input">
		           	</div>
		    			</div>  
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">Invoice Number :</div>
	              <div class="ce-form-input">
		            	<?php echo $resgetpurchaseinvoice['number']; ?>
		           	</div>
		    			</div>
		    			<div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">Invoice Amount :</div>
	              <div class="ce-form-input">
		            	<?php echo "Rs.".numberFormat(round($resgetpurchaseinvoice['amount'],2),2); ?>
		           	</div>
		    			</div> 
		    			<div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">Invoice Date :</div>
	              <div class="ce-form-input">
		            	<?php echo date("d-m-Y",strtotime($resgetpurchaseinvoice['invoice_date'])); ?>
		           	</div>
		    			</div>
		    			<div class="ce-form-controls ce-form-control-col-3">
		            <div class="ce-form-label">Status :</div>
		            <div class="ce-form-input">
		              <?php  
		              	if($resgetcloudinward['is_cancelled'] == "1"){echo "Cancelled";}
		              	if($resgetcloudinward['is_cancelled'] == "0"){echo "Active";}
		              ?>
		            </div>
		          </div>
		          <?php
		          	if($resgetcloudinward['is_cancelled'] == "1")
              	{	
		          ?>
		          <div class="ce-form-controls ce-form-control-col-1">
		            <div class="ce-form-label">Reason for Cancelling :</div>
		            <div class="ce-form-input">
		              <?php  
		              	echo $resgetcloudinward['cancel_reason'];
		              ?>
		            </div>
		          </div>
		          <?php
		          	}
		          ?>  
		        <?php
		            }

		            if($resgetcloudinward['remark'] != "")
		            {
		        ?>
		        	<div class="ce-form-controls ce-form-control-col-1">
		            <div class="ce-form-label">Remark (Max 999 characters) :</div>
		            <div class="ce-form-input">
		              <?php echo $resgetcloudinward['remark']; ?>
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
        echo "Error : CI Not Found";
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
