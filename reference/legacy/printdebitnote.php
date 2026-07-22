<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

  	if(isLoggedIn())
  	{  

		if(isset($_GET['pid']) && $_GET['pid'] != "" && isset($_GET['bid']) && $_GET['bid'] != "" && isset($_GET['dnid']) && $_GET['dnid'] != "")
		{
		  $projectid = $_GET['pid'];
		  $billid = $_GET['bid'];
		  $dnid = $_GET['dnid'];
		        
		  $con = connectMySQL();
		  $sqlgetdebitnote = "select *,
		  (select name from debit_note_type where value = dn.type) as dn_type,
		  (select name from credit_note_type where value = dn.credit_type) as credit_type,
		  (select name from project where id = dn.project_id) as project_name
		  from debit_note as dn where id = ".$dnid." AND bill_id = ".$billid." AND project_id = ".$projectid;
		  $rowgetdebitnote = mysqli_query($con, $sqlgetdebitnote);

		  if (mysqli_num_rows($rowgetdebitnote) > 0)
		  {
		    $resgetdebitnote = mysqli_fetch_array($rowgetdebitnote);
		?>

<!DOCTYPE html>
<html <?php echo getDefaultTheme(); ?> >
<head>
  <title>Debit Note</title>

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
          Debit Note - SEPL/DN/<?php echo $resgetdebitnote['id']; ?>
        </div>
        <div class="section-content-wrapper">

		      <div class="ce-form" style="width:100%;margin: auto;">
  
		        	<div class="ce-form-controls ce-form-control-col-1">
	              <div class="ce-form-label">Project Name :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetdebitnote['project_name']; ?>
	              </div>
	            </div>
		        	<div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">DN Relation :</div>
	              <div class="ce-form-input">
	              	<?php 
										echo $resgetdebitnote['dn_type'];
	              	?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">DN Amount :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetdebitnote['amount']; ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">DN Type :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetdebitnote['credit_type']; ?>
	              </div>
	            </div>
	            	
	            <?php 

	            if($resgetdebitnote['type'] == "2")
	            {
		          	$sqlgetpurchaseinvoice = "select number,amount,invoice_date from purchase_invoice where id = ".$resgetdebitnote['invoice_id'];
		          	$rowgetpurchaseinvoice = mysqli_query($con, $sqlgetpurchaseinvoice);

		          	if (mysqli_num_rows($rowgetpurchaseinvoice) > 0)
		          	{
		            	$resgetpurchaseinvoice = mysqli_fetch_array($rowgetpurchaseinvoice);
		            
		        	?>
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
		          <?php
		          	}
		          }
		          if($resgetdebitnote['remark'] != "")
		          {
		          ?>  
	            <div class="ce-form-controls ce-form-control-col-1">
	              <div class="ce-form-label">Remarks</div>
	              <div class="ce-form-input">
	                <?php echo $resgetdebitnote['remark']; ?>
	              </div>
	            </div>	
	            <?php
	            }
	            ?>           
		      </div>
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
