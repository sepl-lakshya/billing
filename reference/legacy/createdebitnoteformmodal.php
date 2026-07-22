<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

  	if(isLoggedIn())
  	{  
	   
	    $con = connectMySQL();

	    if($_SESSION['user_type'] == "1")
      {
        $createdby = "";
      }
      else
      {
        $createdby = " AND created_by = ".$_SESSION['user_id'];
      }

		if(isset($_POST['projectid']) && $_POST['projectid'] != "")
		{
		  $projectid = $_POST['projectid'];
		  $hasprojectid = true;
		}
		else
		{
		  $hasprojectid = false;
		}
		  
	  if(isset($_POST['billid']) && $_POST['billid'] != "" && isset($_POST['invoiceid']) && $_POST['invoiceid'] != "" && isset($_POST['difference']) && $_POST['difference'] != "" && isset($_POST['hasnegativediff']) && $_POST['hasnegativediff'] != "")
	  {
		  $billid = $_POST['billid'];
		  $invoiceid = $_POST['invoiceid'];
		  $difference = $_POST['difference'];
		  $hasnegativedifference = $_POST['hasnegativediff'];
		  $isgeneraldn = false;
	  }
	  else
	  {
		  $billid = 0;
		  $invoiceid = "";
		  $difference = "";
		  $hasnegativedifference = "";
		  $isgeneraldn = true;
	  }


		  
		?>
			<div class="modal-heading-wrapper">
		        Create Debit Note
		      </div>
		      <form method="post" class="ce-form" id="ce-create-debit-note-form" style="width:100%;">
		        <input type="hidden" id="create-debit-note-bill-id" name="create-debit-note-bill-id" value="<?php echo $billid; ?>">
	            

		        	<div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">DN Relation :</div>
	              <div class="ce-form-input">
	              	<?php 

	              		if($isgeneraldn)
	              		{
	              				if($hasprojectid)
	              				{
	              					$projectidval = $projectid;
	              				}
	              				else
	              				{
	              					$projectidval = "0";
	              				}
	              		?>
	              			<select id="create-debit-note-type" name="create-debit-note-type" data-project-id="<?php echo $projectidval; ?>">
	              				<option value="0">Select DN Relation</option>
	              		<?php
	              				$sqlgetdntype = "select value,name from debit_note_type";
									      $rowgetdntype = mysqli_query($con, $sqlgetdntype);

									      if(mysqli_num_rows($rowgetdntype) > 0)
									      {
									        $pri = 0;
									        while ($pri <= ($resgetdntype = mysqli_fetch_array($rowgetdntype)))
									        {
									    ?>
									        <option value="<?php echo $resgetdntype['value']; ?>" ><?php echo $resgetdntype['name']; ?></option>
									    <?php      
									          $pri++;
									        }
									      }
									  ?>
									  	</select>
									 <?php
	              		}
	              		else
	              		{
	              			echo "Related to Invoice";
									?>
		        					<input type="hidden" id="create-debit-note-type" name="create-debit-note-type" value="2">
									<?php
										}
	              	?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">Remaining Amount :</div>
	              <div class="ce-form-input">
	                <?php
	                	if(!$isgeneraldn)
	                	{
	                		echo "Rs.".round($difference,2);
	                	}
	                	else
	                	{
	                		echo "N/A";
	                	}
	              	?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">DN Amount (Rs.) :</div>
	              <div class="ce-form-input">
	                <input type="text" id="create-debit-note-amount" name="create-debit-note-amount">
	              </div>
	            </div>
		        	<div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">Project Name :</div>
	              <div class="ce-form-input">
	                <?php 
				    			if($hasprojectid)
				    			{
				    				$sqlgetprojectname = "select name from project where id = ".$projectid;
		                $rowgetprojectname = mysqli_query($con, $sqlgetprojectname);
		                $resgetprojectname = mysqli_fetch_array($rowgetprojectname);

				    		?>
				    				<input type="hidden" id="create-debit-note-project-id" name="create-debit-note-project-id" value="<?php echo $projectid; ?>">
				    				<?php echo $resgetprojectname['name']; ?>
				    		<?php		
				    			} 
				    			else
				    			{
				    		?>
                <select id="create-debit-note-project-id" name="create-debit-note-project-id">
                  <option value="0">Select Project</option>
                  <?php

                      $sqlgetproject = "select id,name
                        from project as p 
                        where (is_active = 1 AND product_category = 1 ".$createdby.") OR id IN (select project_id from project_user_mapping where user_id = ".$_SESSION['user_id'].") order by created_on desc";
                        $rowgetproject = mysqli_query($con, $sqlgetproject);

                        if (mysqli_num_rows($rowgetproject) > 0)
                        {
                          $phn = 0;
                          while ($phn <= ($resgetproject = mysqli_fetch_array($rowgetproject)))
                          {
                      ?>
                        <option value="<?php echo $resgetproject['id']; ?>" >
                          <?php echo $resgetproject['name']; ?>
                        </option>
                      <?php
                            $phn++;
                          }
                        }
                  ?>
                </select>
		            <?php
		            	}
		            ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3">
	              <div class="ce-form-label">DN Type :</div>
	              <div class="ce-form-input">
	                <select id="create-debit-note-credit-type" name="create-debit-note-credit-type">
	                	<option value="0">Select DN Type</option>
	              		<?php
	              				$sqlgetcntype = "select value,name from credit_note_type";
									      $rowgetcntype = mysqli_query($con, $sqlgetcntype);

									      if(mysqli_num_rows($rowgetcntype) > 0)
									      {
									        $cnt = 0;
									        while ($cnt <= ($resgetcntype = mysqli_fetch_array($rowgetcntype)))
									        {
									    ?>
									        <option value="<?php echo $resgetcntype['value']; ?>" ><?php echo $resgetcntype['name']; ?></option>
									    <?php      
									          $cnt++;
									        }
									      }
									  ?>
	                </select>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-3" id="create-debit-note-invoice-id-wrapper">
	              <div class="ce-form-label">Invoice :</div>
	              <div class="ce-form-input" id="create-debit-note-invoice-id-input-wrapper">
	              	<?php 

	              		if($isgeneraldn)
	              		{
	              			if($hasprojectid)
	              			{
	              		?>
	              			<select id="create-debit-note-invoice-id" name="create-debit-note-invoice-id">
	              		<?php
	              				$sqlgetinvoice = "select id,number,amount from purchase_invoice as pi where pi.is_deleted = 0 AND pi.project_id = ".$projectid." AND id NOT IN (select invoice_id from bill_invoice_mapping where project_id = ".$projectid.")";
									      $rowgetinvoice = mysqli_query($con, $sqlgetinvoice);

									      if(mysqli_num_rows($rowgetinvoice) > 0)
									      {
									        $pri = 0;
									        while ($pri <= ($resgetinvoice = mysqli_fetch_array($rowgetinvoice)))
									        {
									          $invoicelabel = $resgetinvoice['number']." (Rs. ".numberFormat(round($resgetinvoice['amount'],2),2).")";
									    ?>
									        <option label="<?php echo $invoicelabel; ?>" value="<?php echo $resgetinvoice['id']; ?>" data-ref-no="<?php echo $resgetinvoice['number']; ?>" data-amount="<?php echo $resgetinvoice['amount']; ?>"><?php echo $invoicelabel; ?></option>
									    <?php      
									          $pri++;
									        }
									      }
									  ?>
									  	</select>
									  	<script type="text/javascript">
									  		$('#create-debit-note-invoice-id').select2({
								            placeholder: "Select Invoice",
								        });
									  	</script>
									  <?php
	              			}
	              			else
	              			{
	              		?>
	              			<select id="create-debit-note-invoice-id" name="create-debit-note-invoice-id">
                    	</select>
	              	<?php
	              			}
	              	?>
	              	<?php
	              		}
	              		else
	              		{
										  $sqlgetinvoice = "select number
										  from purchase_invoice as pi where id = ".$invoiceid." AND project_id = ".$projectid;
										  $rowgetinvoice = mysqli_query($con, $sqlgetinvoice);

										  if (mysqli_num_rows($rowgetinvoice) > 0)
										  {
										    $resgetinvoice = mysqli_fetch_array($rowgetinvoice);
										    echo $resgetinvoice['number'];
										  }
									?>
		        					<input type="hidden" id="create-debit-note-invoice-id" name="create-debit-note-invoice-id" value="<?php echo $invoiceid; ?>">

									<?php
										}
	              	?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-1">
	              <div class="ce-form-label">Remark (Max 999 characters) :</div>
	              <div class="ce-form-input">
	                <textarea id="create-debit-note-remark" name="create-debit-note-remark"></textarea>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-1" >
	              <div class="ce-form-input align-right">
	                  <input type="submit" id="ce-create-debit-note-form-submit" name="ce-create-debit-note-form-submit" value="Create Debit Note">
	              </div>
	            </div>  
		      </form>
		<?php 
		}
		else
  	{ 
      	header("location:".getDomain());
  	}
?>