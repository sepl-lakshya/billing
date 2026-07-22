<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

  	if(isLoggedIn())
  	{  

		if(isset($_POST['projectid']) && $_POST['projectid'] != "" && isset($_POST['cloudinwardid']) && $_POST['cloudinwardid'] != "")
		{
		  $projectid = $_POST['projectid'];
		  $cloudinwardid = $_POST['cloudinwardid'];
		        
		  $con = connectMySQL();
		  $sqlgetcloudinward = "select b.year, ici.portal_total, ici.purchase_total, ici.created_by, ici.id, ici.bill_id, remark,	ici.invoice_id,
		  (select name from month where value = b.month) as month_name,
		  (select name from project where id = ici.project_id) as project_name,
		  (select full_name from portal.login_detail where id = ici.created_by) as created_by_name
		  from invoice_cloud_inward as ici INNER JOIN bill as b where ici.id = ".$cloudinwardid." AND ici.project_id = ".$projectid;
		  $rowgetcloudinward = mysqli_query($con, $sqlgetcloudinward);

		  if (mysqli_num_rows($rowgetcloudinward) > 0)
		  {
		    $resgetcloudinward = mysqli_fetch_array($rowgetcloudinward);
		?>
			<div class="modal-heading-wrapper">
		        Cancel Invoice Cloud Inward
		      </div>
		      <form method="post" class="ce-form" id="ce-cancel-invoice-cloud-inward-form" style="width:100%;">
		        <input type="hidden" id="cancel-invoice-ci-id" name="cancel-invoice-ci-id" value="<?php echo $resgetcloudinward['id']; ?>">
		        <input type="hidden" id="cancel-invoice-ci-project-id" name="cancel-invoice-ci-project-id" value="<?php echo $projectid; ?>">
	            
		        <div class="ce-form-controls ce-form-control-col-1">
	              <div class="ce-form-label">Project Name :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudinward['project_name'] ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-2">
	              <div class="ce-form-label">Verified By :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudinward['created_by_name'] ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-4">
	              <div class="ce-form-label">Month :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudinward['month_name'] ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-4">
	              <div class="ce-form-label">Year :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudinward['year'] ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-2">
	              <div class="ce-form-label">Portal Total :</div>
	              <div class="ce-form-input">
	                <?php echo "Rs. ".numberFormat(round($resgetcloudinward['portal_total'],2),2); ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-2">
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
	              <div class="ce-form-label">Cancel Reason (Max 999 characters) :</div>
	              <div class="ce-form-input">
	                <textarea id="cancel-invoice-ci-reason" name="cancel-invoice-ci-reason" maxlength="999"></textarea>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-1	" >
	              <div class="ce-form-input align-right">
	                  <input type="submit" id="ce-cancel-cloud-inward-form-submit" name="ce-cancel-cloud-inward-form-submit" value="Cancel CI">
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