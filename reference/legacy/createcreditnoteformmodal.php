<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

  	if(isLoggedIn())
  	{  

		if(isset($_POST['projectid']) && $_POST['projectid'] != "" && isset($_POST['billid']) && $_POST['billid'] != "" && isset($_POST['invoiceid']) && $_POST['invoiceid'] != "" && isset($_POST['debitnoteid']) && $_POST['debitnoteid'] != "")
		{
		  $projectid = $_POST['projectid'];
		  $billid = $_POST['billid'];
		  $invoiceid = $_POST['invoiceid'];
		  $debitnoteid = $_POST['debitnoteid'];
		        
		  $con = connectMySQL();
		  $sqlgetdebitnote = "select *,
		  (select number from purchase_invoice where id = dn.invoice_id) as invoice_number,
		  (select SUM(amount) from credit_note where debit_note_id = dn.id) as credit_note_sum,
		  (select name from project where id = dn.project_id) as project_name
		  from debit_note as dn where id = ".$debitnoteid." AND invoice_id = ".$invoiceid." AND project_id = ".$projectid." AND bill_id = ".$billid;
		  $rowgetdebitnote = mysqli_query($con, $sqlgetdebitnote);

		  if (mysqli_num_rows($rowgetdebitnote) > 0)
		  {
		    $resgetdebitnote = mysqli_fetch_array($rowgetdebitnote);
		?>
			<div class="modal-heading-wrapper">
		        Create Credit Note
		      </div>
		      <form method="post" class="ce-form" id="ce-create-credit-note-form" style="width:100%;">
		        <input type="hidden" id="create-credit-note-project-id" name="create-credit-note-project-id" value="<?php echo $projectid; ?>">
		        <input type="hidden" id="create-credit-note-bill-id" name="create-credit-note-bill-id" value="<?php echo $billid; ?>">
		        <input type="hidden" id="create-credit-note-invoice-id" name="create-credit-note-invoice-id" value="<?php echo $invoiceid; ?>">
		        <input type="hidden" id="create-credit-note-dn-id" name="create-credit-note-dn-id" value="<?php echo $resgetdebitnote['id']; ?>">
	            
		        <div class="ce-form-controls ce-form-control-col-1">
	              <div class="ce-form-label">Project Name :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetdebitnote['project_name']; ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">Invoice Number :</div>
	              <div class="ce-form-input">
	                <?php echo $resgetdebitnote['invoice_number']; ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">DN Number :</div>
	              <div class="ce-form-input">
	                <?php echo "SEPL/DN/".$resgetdebitnote['id']; ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">DN Amount :</div>
	              <div class="ce-form-input">
	                <?php echo "Rs. ".round($resgetdebitnote['amount'],2); ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">Reference Number :</div>
	              <div class="ce-form-input">
	                <input type="text" id="create-credit-note-reference-no" name="create-credit-note-reference-no"/>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">DN Amount Due :</div>
	              <div class="ce-form-input">
	              	<?php   
	              		if($resgetdebitnote['amount'] >= $resgetdebitnote['credit_note_sum'])
	              		{
	              			echo $resgetdebitnote['amount'] - $resgetdebitnote['credit_note_sum'];	
	              		}
	              		else
	              		{
	              			echo $resgetdebitnote['amount'];
	              		}

	              	?>
	              		
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3" style="vertical-align:top;">
	              <div class="ce-form-label">Credit Amount (Rs.) :</div>
	              <div class="ce-form-input">
	                <input type="text" id="create-credit-note-amount" name="create-credit-note-amount">
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-2">
	              <div class="ce-form-label">Remark (Max 999 characters) :</div>
	              <div class="ce-form-input">
	                <textarea id="create-credit-note-remark" name="create-credit-note-remark"></textarea>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-1" >
	              <div class="ce-form-input align-right">
	                  <input type="submit" id="ce-create-credit-note-form-submit" name="ce-create-credit-note-form-submit" value="Create Credit Note">
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