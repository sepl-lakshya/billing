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
		  (select SUM(amount) from bill_header where bill_id = b.id AND project_id = ".$projectid.") as sales_total
		  from bill as b where id = ".$billid." AND project_id = ".$projectid;
		  $rowgetbill = mysqli_query($con, $sqlgetbill);

		  if (mysqli_num_rows($rowgetbill) > 0)
		  {
		    $resgetbill = mysqli_fetch_array($rowgetbill);
		?>
			<div class="modal-heading-wrapper">
		        Cloud Outward Acknowledgement
		      </div>
		      <form method="post" class="ce-form" id="ce-create-cloud-outward-form" style="width:100%;">
		        <input type="hidden" id="create-co-project-id" name="create-co-project-id" value="<?php echo $projectid; ?>">
		        <input type="hidden" id="create-co-bill-id" name="create-co-bill-id" value="<?php echo $billid; ?>">
		        <input type="hidden" id="create-co-created-by" name="create-co-created-by" value="<?php echo $_SESSION['user_id']; ?>">
		        <input type="hidden" id="create-co-sales-total" name="create-co-sales-total" value="<?php echo $resgetbill['sales_total']; ?> ">
	            
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
	              <div class="ce-form-label">Sales Total :</div>
	              <div class="ce-form-input">
	                <?php echo "Rs. ".numberFormat(round($resgetbill['sales_total'],2),2); ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-1">
	              <div class="ce-form-label">Remark (Max 999 characters) :</div>
	              <div class="ce-form-input">
	                <textarea id="create-co-remark" name="create-co-remark" maxlength="999"></textarea>
	              </div>
	            </div>


	            <div class="ce-form-controls ce-form-control-col-1	" >
	              <div class="ce-form-input align-right">
	                  <input type="submit" id="ce-create-cloud-outward-form-submit" name="ce-create-cloud-outward-form-submit" value="Create CO">
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