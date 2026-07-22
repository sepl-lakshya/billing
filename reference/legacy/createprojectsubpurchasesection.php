<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    $con = connectMySQL();
    if(isset($_POST['piid']) && $_POST['piid'] != "" && isset($_POST['pid']) && $_POST['pid'] != "")
    {
      $projectid = mysqli_real_escape_string($con, clean_input($_POST['pid']));
      $projectitemid = mysqli_real_escape_string($con, clean_input($_POST['piid']));

      $sqlgetprojectitem = "select purchase_quantity , sale_quantity, (sale_quantity - purchase_quantity) as remaining_quantity from licence_project_item where project_id = ".$projectid." AND id = ".$projectitemid;
      $rowgetprojectitem = mysqli_query($con, $sqlgetprojectitem);

      // If Portal Price Exists 
      if(mysqli_num_rows($rowgetprojectitem) > 0)
      {
        $resgetprojectitem = mysqli_fetch_array($rowgetprojectitem);
?>
      <form method="post" id="ce-create-sub-purchase-form">
          <input type="hidden" id="sub-purchase-project-item-id" name="sub-purchase-project-item-id" value="<?php echo $projectitemid ?>">
          <input type="hidden" id="sub-purchase-project-id" name="sub-purchase-project-id" value="<?php echo $projectid ?>">

          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-label">Year :</div>
            <div class="ce-form-input">
                <select id="sub-purchase-year" name="sub-purchase-year" data-project-item-id="<?php echo $projectitemid ?>" data-project-id="<?php echo $projectid ?>">
                  <option value="0">Select Year</option>
                  <?php

                    $sqlgetyear = "select MIN(YEAR(deployment_start)) as year from licence_project_item where project_id = ".$projectid;
                    $rowgetyear = mysqli_query($con, $sqlgetyear);

                    if (mysqli_num_rows($rowgetyear) > 0)
                    {
                      $resgetyear = mysqli_fetch_array($rowgetyear);
                      $yearval = (int)$resgetyear['year'];

                      $i = 0;
                      while ($i <= 10)
                      {

                  ?>
                    <option value="<?php echo $yearval; ?>">
                      <?php echo $yearval; ?>
                    </option>
                  <?php
                        $i++;
                        $yearval++;
                      }
                    }
                ?>
                </select>
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-label">Month :</div>
            <div class="ce-form-input">
                <select id="sub-purchase-month" name="sub-purchase-month">
                  <option value="0">Select Month</option>
                </select>
            </div>
          </div>
          
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-label">Quantity (Remaining : <?php echo $resgetprojectitem['remaining_quantity']; ?>) :</div>

            <div class="ce-form-input">
               <input type="number" id="sub-purchase-quantity" name="sub-purchase-quantity" min="0" max="<?php echo $resgetprojectitem['remaining_quantity']; ?>">
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-1">
            <div class="ce-form-input align-right">
                <input type="submit" id="ce-create-sub-purchase-form-submit" name="ce-create-sub-purchase-form-submit" value="Submit">
            </div>
          </div>
        </form>
<?php 

      }
      
        mysqli_close($con);
    }
    else
    {
      echo "Error : Missing Parameters";
    }

?>
