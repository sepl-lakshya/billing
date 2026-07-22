<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

  	if(isLoggedIn())
  	{  

		if(isset($_POST['projectid']) && $_POST['projectid'] != "" && isset($_POST['cloudoutwardid']) && $_POST['cloudoutwardid'] != "")
		{
		  $projectid = $_POST['projectid'];
		  $cloudoutwardid = $_POST['cloudoutwardid'];
		        
		  $con = connectMySQL();
		  $sqlgetcloudoutward = "select b.year, co.sales_total, co.created_by, co.id,
		  co.bill_id, remark,	
		  (select name from month where value = b.month) as month_name,
		  (select name from project where id = co.project_id) as project_name,
		  (select full_name from portal.login_detail where id = co.created_by) as created_by_name
		  from cloud_outward as co INNER JOIN bill as b where co.id = ".$cloudoutwardid." AND co.project_id = ".$projectid;
		  $rowgetcloudoutward = mysqli_query($con, $sqlgetcloudoutward);

		  if (mysqli_num_rows($rowgetcloudoutward) > 0)
		  {
		    $resgetcloudoutward = mysqli_fetch_array($rowgetcloudoutward);
		?>
			<div class="modal-heading-wrapper">
		        Cancel Cloud Outward
		      </div>
		      <form method="post" class="ce-form" id="ce-cancel-cloud-outward-form" style="width:100%;">
		        <input type="hidden" id="cancel-co-id" name="cancel-co-id" value="<?php echo $resgetcloudoutward['id']; ?>">
		        <input type="hidden" id="cancel-co-project-id" name="cancel-co-project-id" value="<?php echo $projectid; ?>">
	            
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
	                <?php echo $resgetcloudoutward['year'] ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-2">
	              <div class="ce-form-label">Sales Total :</div>
	              <div class="ce-form-input">
	                <?php echo "Rs. ".numberFormat(round($resgetcloudoutward['sales_total'],2),2); ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-1">
	              <div class="ce-form-label">Remark:</div>
	              <div class="ce-form-input">
	                <?php echo $resgetcloudoutward['remark']; ?>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-1">
	              <div class="ce-form-label">Cancel Reason (Max 999 characters) :</div>
	              <div class="ce-form-input">
	                <textarea id="cancel-co-reason" name="cancel-co-reason" maxlength="999"></textarea>
	              </div>
	            </div>
	            <div class="ce-form-controls ce-form-control-col-1	" >
	              <div class="ce-form-input align-right">
	                  <input type="submit" id="ce-cancel-cloud-outward-form-submit" name="ce-cancel-cloud-outward-form-submit" value="Cancel CO">
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