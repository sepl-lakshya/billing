<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

  	if(isLoggedIn())
  	{  

		if(isset($_POST['projectid']) && $_POST['projectid'] != "" && isset($_POST['billid']) && $_POST['billid'] != "" && isset($_POST['invoiceid']) && $_POST['invoiceid'] != "")
		{
		  $projectid = $_POST['projectid'];
		  $billid = $_POST['billid'];
		  $invoiceid = $_POST['invoiceid'];
		        
		  $con = connectMySQL();
		  $sqlgetbill = "select year,
		  (select name from month where value = b.month) as month_name,
		  (select name from project where id = b.project_id) as project_name,
		  (select SUM(portal_price) from bill_item where bill_id = b.id) as portal_total,
      (select SUM(amount) from bill_purchase_header where bill_id = b.id AND project_id = ".$projectid.") as purchase_total
		  from bill as b where id = ".$billid." AND project_id = ".$projectid;
		  $rowgetbill = mysqli_query($con, $sqlgetbill);

		  if (mysqli_num_rows($rowgetbill) > 0)
		  {
		    $resgetbill = mysqli_fetch_array($rowgetbill);
		?>
			<div class="modal-heading-wrapper">
		        Invoice Cloud Inward Acknowledgement
		      </div>
		      <form method="post" class="ce-form" id="ce-create-invoice-cloud-inward-form" >
		        <input type="hidden" id="create-invoice-ci-project-id" name="create-invoice-ci-project-id" value="<?php echo $projectid; ?>">
		        <input type="hidden" id="create-invoice-ci-bill-id" name="create-invoice-ci-bill-id" value="<?php echo $billid; ?>">
		        <input type="hidden" id="create-invoice-ci-invoice-id" name="create-invoice-ci-invoice-id" value="<?php echo $invoiceid; ?>">
		        <input type="hidden" id="create-invoice-ci-created-by" name="create-invoice-ci-created-by" value="<?php echo $_SESSION['user_id']; ?>">
		        <input type="hidden" id="create-invoice-ci-portal-total" name="create-invoice-ci-portal-total" value="<?php echo round($resgetbill['portal_total'],2); ?> ">
		        <input type="hidden" id="create-invoice-ci-purchase-total" name="create-invoice-ci-purchase-total" value="<?php echo round($resgetbill['purchase_total'],2); ?> ">
	            
		        <div class="ce-form-controls ce-form-control-col-1">
	              <div class="ce-form-label">Project Name :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetbill['project_name'] ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-2">
	              <div class="ce-form-label">Verified By :</div>
	              <div class="ce-form-input">
	                <?php echo $_SESSION['full_name'] ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-4">
	              <div class="ce-form-label">Month :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetbill['month_name'] ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-4">
	              <div class="ce-form-label">Year :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetbill['year'] ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-2">
	              <div class="ce-form-label">Portal Total :</div>
	              <div class="ce-form-input">
	                <?php echo "Rs. ".numberFormat(round($resgetbill['portal_total'],2),2); ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-2">
	              <div class="ce-form-label">Purchase Total :</div>
	              <div class="ce-form-input">
	                <?php echo "Rs. ".numberFormat(round($resgetbill['purchase_total'],2),2); ?>
	              </div>
	            </div>
	            <?php 

		          	$sqlgetpurchaseinvoice = "select number,amount,invoice_date from purchase_invoice where id = ".$invoiceid;
		          	$rowgetpurchaseinvoice = mysqli_query($con, $sqlgetpurchaseinvoice);

		          	if (mysqli_num_rows($rowgetpurchaseinvoice) > 0)
		          	{
		            	$resgetpurchaseinvoice = mysqli_fetch_array($rowgetpurchaseinvoice);
		            
		        	?>  
	            <div class="ce-form-controls ce-form-control-col-2">
	              <div class="ce-form-label">Invoice Number :</div>
	              <div class="ce-form-input">
		            		<?php echo $resgetpurchaseinvoice['number']; ?>
		           	</div>
		    			</div>
		    			<div class="ce-form-controls ce-form-control-col-4">
	              <div class="ce-form-label">Invoice Amount :</div>
	              <div class="ce-form-input">
		            		<?php echo "Rs.".numberFormat(round($resgetpurchaseinvoice['amount'],2),2); ?>
		           	</div>
		    			</div> 
		    			<div class="ce-form-controls ce-form-control-col-4">
	              <div class="ce-form-label">Invoice Date :</div>
	              <div class="ce-form-input">
		            	<?php echo date("d-m-Y",strtotime($resgetpurchaseinvoice['invoice_date'])); ?>
		           	</div>
		    			</div>  
		        <?php
		            }
		        ?>
		          
	            <div class="ce-form-controls ce-form-control-col-1">
	              <div class="ce-form-label">Remark (Max 999 characters) :</div>
	              <div class="ce-form-input">
	                <textarea id="create-invoice-ci-remark" name="create-invoice-ci-remark" maxlength="999"></textarea>
	              </div>
	            </div>


	            <div class="ce-form-controls ce-form-control-col-1	" >
	              <div class="ce-form-input align-right">
	                  <input type="submit" id="ce-create-invoice-cloud-inward-form-submit" name="ce-create-invoice-cloud-inward-form-submit" value="Create CI">
	              </div>
	            </div>  
		      </form>
		<?php 
		  }
		  else
		  {
		    echo "Error : Bill not found!";
		  }
		}
		else
		{
		  echo "Error : Missing Parameters!";
		}
	}
	else
  	{ 
      	header("location:".getDomain());
  	}
?>