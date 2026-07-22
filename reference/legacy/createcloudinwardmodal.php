<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

  	if(isLoggedIn())
  	{  

		if(isset($_POST['projectid']) && $_POST['projectid'] != "" && isset($_POST['billid']) && $_POST['billid'] != "")
		{
		  $projectid = $_POST['projectid'];
		  $billid = $_POST['billid'];
		        
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
		        Cloud Inward Acknowledgement
		      </div>
		      <form method="post" class="ce-form" id="ce-create-cloud-inward-form" style="width:100%;">
		        <input type="hidden" id="create-ci-project-id" name="create-ci-project-id" value="<?php echo $projectid; ?>">
		        <input type="hidden" id="create-ci-bill-id" name="create-ci-bill-id" value="<?php echo $billid; ?>">
		        <input type="hidden" id="create-ci-created-by" name="create-ci-created-by" value="<?php echo $_SESSION['user_id']; ?>">
		        <input type="hidden" id="create-ci-portal-total" name="create-ci-portal-total" value="<?php echo $resgetbill['portal_total']; ?> ">
		        <input type="hidden" id="create-ci-purchase-total" name="create-ci-purchase-total" value="<?php echo $resgetbill['purchase_total']; ?> ">
	            
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
	            <div class="ce-form-controls ce-form-control-col-1">
	              <div class="ce-form-label">Purchase Invoice/s :</div>
	              <div class="ce-form-input">
	            <?php 

		          $sqlgetpurchaseinvoice = "select number,amount from purchase_invoice where id IN (select invoice_id from bill_invoice_mapping where project_id = ".$projectid." AND bill_id = ".$billid.")";
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
	              <div class="ce-form-label">Remark (Max 999 characters) :</div>
	              <div class="ce-form-input">
	                <textarea id="create-ci-remark" name="create-ci-remark" maxlength="999"></textarea>
	              </div>
	            </div>


	            <div class="ce-form-controls ce-form-control-col-1	" >
	              <div class="ce-form-input align-right">
	                  <input type="submit" id="ce-create-cloud-inward-form-submit" name="ce-create-cloud-inward-form-submit" value="Create CI">
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