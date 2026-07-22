<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    $con = connectMySQL();
    
    if(isset($_POST['projectid']) && $_POST['projectid'] != "")
    {
      $pid = mysqli_real_escape_string($con, clean_input($_POST['projectid']));

      $sqlgetdiscount = "select * from project_discount where project_id = ".$pid." AND to_date IS NULL";
      $rowgetdiscount = mysqli_query($con, $sqlgetdiscount);

      if(mysqli_num_rows($rowgetdiscount) > 0)
      {
        $resgetdiscount = mysqli_fetch_array($rowgetdiscount);        

?>
      <div class="modal-heading-wrapper">
        Enter New Discount Values</span>
      </div>
        <form method="post" id="ce-change-cloud-project-discount-form">
          <input type="hidden" id="change-discount-project-id" name="change-discount-project-id" value="<?php echo $pid; ?>">           

          <div class="ce-form-controls ce-form-control-col-3">
            <div class="ce-form-label">PAYG Discount (%) :</div>
            <div class="ce-form-input">
              <input type="text" id="change-payg-discount" name="change-payg-discount" placeholder="PAYG Discount" value="<?php echo $resgetdiscount['payg_discount']; ?>">
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-3">
            <div class="ce-form-label">RI Discount (%) :</div>
            <div class="ce-form-input">
              <input type="text" id="change-ri-discount" name="change-ri-discount" placeholder="RI Discount" value="<?php echo $resgetdiscount['ri_discount']; ?>">
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-3">
            <div class="ce-form-label">Credit Days :</div>
            <div class="ce-form-input">
              <input type="number" id="change-credit-days" name="change-credit-days" placeholder="Credit Days" value="<?php echo $resgetdiscount['credit_days']; ?>" min="0">
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-label">Starting From :</div>
            <div class="ce-form-input">
                <select id="discount-year" name="discount-year" data-project-id="<?php echo $pid ?>">
                  <option value="0">Select Year</option>
                  <?php

                    $sqlgetyear = "select YEAR(from_date) as year,MONTH(from_date) as month from project_discount where project_id = ".$pid." AND to_date IS NULL";
                    $rowgetyear = mysqli_query($con, $sqlgetyear);

                    if (mysqli_num_rows($rowgetyear) > 0)
                    {
                      $resgetyear = mysqli_fetch_array($rowgetyear);
                      $yearval = (int)$resgetyear['year'];

                      if($resgetyear['month'] == "12")
                      {
                        $yearval++;
                      }

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
            <div class="ce-form-label"></div>
            <div class="ce-form-input">
                <select id="discount-month" name="discount-month">
                  <option value="0">Select Month</option>
                </select>
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-1">
            <div class="ce-form-input align-right">
                <input type="submit" id="ce-change-cloud-project-discount-form-submit" name="ce-change-cloud-project-discount-form-submit" value="Update">
            </div>
          </div>
        </form>
    <?php   
      }

    }
    else
    {

  }
?>
          </table>


  