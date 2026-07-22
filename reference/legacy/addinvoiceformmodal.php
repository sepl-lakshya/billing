<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

  	if(isLoggedIn())
  	{  
  		$con = connectMySQL();
			if(isset($_POST['projectid']) && $_POST['projectid'] != "" && isset($_POST['loadselect']) && $_POST['loadselect'] != "")
			{
        $projectid = $_POST['projectid'];
			  $loadselect = $_POST['loadselect'];
			  $hasprojectid = true;
			}
			else
			{
			  $projectid = "";
        $loadselect = "0";
			  $hasprojectid = false;
			}		

      if($_SESSION['user_type'] == "1")
      {
        $createdby = "";
      }
      else
      {
        $createdby = " AND created_by = ".$_SESSION['user_id'];
      }        
		?>
				<div class="modal-heading-wrapper">
		        Add New Invoice
		    </div>
		    <form method="post" class="ce-form" id="ce-add-invoice-form">

          <input type="hidden" id="add-invoice-load-select" name="add-invoice-load-select" value="<?php echo $loadselect; ?>">
            <div class="ce-form-controls ce-form-control-col-2">
              <div class="ce-form-label">Project :</div>
              <div class="ce-form-input">
              	<?php 
				    			if($hasprojectid)
				    			{
				    				$sqlgetprojectname = "select name from project where id = ".$projectid;
		                $rowgetprojectname = mysqli_query($con, $sqlgetprojectname);
		                $resgetprojectname = mysqli_fetch_array($rowgetprojectname);

				    		?>
				    				<input type="hidden" id="add-invoice-project-id" name="add-invoice-project-id" value="<?php echo $projectid; ?>">
				    				<?php echo $resgetprojectname['name']; ?>
				    		<?php		
				    			} 
				    			else
				    			{
				    		?>
                <select id="add-invoice-project-id" name="add-invoice-project-id">
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

            <div class="ce-form-controls ce-form-control-col-2">
              <div class="ce-form-label">Invoice Reference Number :</div>
              <div class="ce-form-input">
                  <input type="text" id="add-invoice-reference-no" name="add-invoice-reference-no">
              </div>
            </div>

            <div class="ce-form-controls ce-form-control-col-2">
              <div class="ce-form-label">Invoice Total Amount (excl. GST) :</div>
              <div class="ce-form-input">
                  <input type="text" id="add-invoice-amount" name="add-invoice-amount">
              </div>
            </div>

            <div class="ce-form-controls ce-form-control-col-2">
              <div class="ce-form-label">GST Slab :</div>
              <div class="ce-form-input">
                <select id="add-invoice-gst-slab" name="add-invoice-gst-slab">
                  <option value="0">Select GST Slab</option>
                  <?php

                      $sqlgetgstslab = "select name,percentage from gst_slab";
                      $rowgetgstslab = mysqli_query($con, $sqlgetgstslab);

                      if (mysqli_num_rows($rowgetgstslab) > 0)
                      {
                        $phn = 0;
                        while ($phn <= ($resgetgstslab = mysqli_fetch_array($rowgetgstslab)))
                        {
                      ?>
                        <option value="<?php echo $resgetgstslab['percentage']; ?>" >
                          <?php echo $resgetgstslab['name']; ?>
                        </option>
                      <?php
                            $phn++;
                          }
                        }
                  ?>
                </select>
              </div>
            </div>

            <div class="ce-form-controls ce-form-control-col-2">
              <div class="ce-form-label">Invoice Type :</div>
              <div class="ce-form-input">
                  <input type="radio" id="add-invoice-type-recurring" name="add-invoice-type" value="1"><label for="add-invoice-type-recurring">Recurring</label>&nbsp;&nbsp;
                  <input type="radio" id="add-invoice-type-onetime" name="add-invoice-type" value="2"><label for="add-invoice-type-onetime">One time</label>
              </div>
            </div>

            <div class="ce-form-controls ce-form-control-col-2">
              <div class="ce-form-label">Invoice Date :</div>
              <div class="ce-form-input">
                  <input type="date" id="add-invoice-date" name="add-invoice-date">
              </div>
            </div>

            <div class="ce-form-controls ce-form-control-col-1">
              <div class="ce-form-label">Description (Max 999 characters):</div>
              <div class="ce-form-input">
                  <input type="text" id="add-invoice-desc" name="add-invoice-desc">
              </div>
            </div>

            <div class="ce-form-controls ce-form-control-col-1">
              <div class="ce-form-input">
                <input type="submit" id="ce-add-invoice-form-submit" name="ce-add-invoice-form-submit" value="Add Invoice">
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