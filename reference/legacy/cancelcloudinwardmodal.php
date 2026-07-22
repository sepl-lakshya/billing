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
		  $sqlgetcloudinward = "select b.year, ci.portal_total, ci.purchase_total, ci.created_by, ci.id,
		  ci.bill_id, remark,	
		  (select name from month where value = b.month) as month_name,
		  (select name from project where id = ci.project_id) as project_name,
		  (select full_name from portal.login_detail where id = ci.created_by) as created_by_name
		  from cloud_inward as ci INNER JOIN bill as b where ci.id = ".$cloudinwardid." AND ci.project_id = ".$projectid;
		  $rowgetcloudinward = mysqli_query($con, $sqlgetcloudinward);

		  if (mysqli_num_rows($rowgetcloudinward) > 0)
		  {
		    $resgetcloudinward = mysqli_fetch_array($rowgetcloudinward);
		?>
			<div class="modal-heading-wrapper">
		        Cancel Cloud Inward
		      </div>
		      <form method="post" class="ce-form" id="ce-cancel-cloud-inward-form" style="width:100%;">
		        <input type="hidden" id="cancel-ci-id" name="cancel-ci-id" value="<?php echo $resgetcloudinward['id']; ?>">
		        <input type="hidden" id="cancel-ci-project-id" name="cancel-ci-project-id" value="<?php echo $projectid; ?>">
	            
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
	            <div class="ce-form-controls ce-form-control-col-1">
	              <div class="ce-form-label">Purchase Invoice/s :</div>
	              <div class="ce-form-input">
	            <?php 

		          $sqlgetpurchaseinvoice = "select number,amount from purchase_invoice where id IN (select invoice_id from bill_invoice_mapping where project_id = ".$projectid." AND bill_id = ".$resgetcloudinward['bill_id'].")";
		          $rowgetpurchaseinvoice = mysqli_query($con, $sqlgetpurchaseinvoice);

		          if (mysqli_num_rows($rowgetpurchaseinvoice) > 0)
		          {

		        ?>
		        
		        <table class="table-element align-center" cellpadding="0" cellspacing="0" style="font-size: 1em;margin-top: 10px;">
		          <thead>
		            <tr>
		              <th>Invoice Number</th>
		              <th>Invoice Amount</th>
		            </tr>
		          </thead>
		          <tbody>
		        <?php
		            $pi = 0;
		            $rowcount = 1;
		            while ($pi <= ($resgetpurchaseinvoice = mysqli_fetch_array($rowgetpurchaseinvoice)))
		            {
		        ?>  
		          <tr>
		            <td>
		              	<div class="table-value-wrapper">
		            		<?php echo $resgetpurchaseinvoice['number']; ?>
		            	</div>
		            </td>
		            <td>
		              	<div class="table-value-wrapper">
		              		<?php echo "Rs.".$resgetpurchaseinvoice['amount']; ?>
		              	</div>
		            </td>
		          </tr>
		        <?php
		              $pi++;
		            }
		        ?>
		          </tbody>
		        </table>
		        <?php
		          }
		        ?>
		    		</div>
		    	</div>
	            <div class="ce-form-controls ce-form-control-col-1">
	              <div class="ce-form-label">Remark:</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudinward['remark']; ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-1">
	              <div class="ce-form-label">Cancel Reason (Max 999 characters) :</div>
	              <div class="ce-form-input">
	                <textarea id="cancel-ci-reason" name="cancel-ci-reason" maxlength="999"></textarea>
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