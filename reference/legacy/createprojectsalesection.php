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

      $sqlgetprojectitem = "select id, licence_category, product, distributor, deployment_start, deployment_end, sale_unit_price, sale_quantity, sale_subscription_term, sale_billing_term, contract_year, contract_month status from licence_project_item where project_id = ".$projectid." AND id = ".$projectitemid;
      $rowgetprojectitem = mysqli_query($con, $sqlgetprojectitem);

      // If Portal Price Exists 
      if(mysqli_num_rows($rowgetprojectitem) > 0)
      {
        $resgetprojectitem = mysqli_fetch_array($rowgetprojectitem);
?>
      <form method="post" id="ce-create-sales-form">
        <input type="hidden" id="create-sale-project-id" name="create-sale-project-id" value="<?php echo $projectid; ?>">
        <input type="hidden" id="create-sale-project-item-id" name="create-sale-project-item-id" value="<?php echo $projectitemid; ?>">
        <input type="hidden" id="create-sale-item-status" name="create-sale-item-status" value="<?php echo $resgetprojectitem['status']; ?>">                   

        <div class="ce-form-controls ce-form-control-col-2">
          <div class="ce-form-label">Unit Price :</div>
          <div class="ce-form-input">
            <input type="text" id="sale-unit-price" name="sale-unit-price" placeholder="Unit Price" value="<?php echo $resgetprojectitem['sale_unit_price']; ?>">
          </div>
        </div>
        <div class="ce-form-controls ce-form-control-col-2">
          <div class="ce-form-label">Quantity :</div>
          <div class="ce-form-input">
            <input type="number" id="sale-quantity" name="sale-quantity" placeholder="Quantity" min="0" value="<?php echo $resgetprojectitem['sale_quantity']; ?>">
          </div>
        </div>
        <div class="ce-form-controls ce-form-control-col-2" id="deployment-start-wrapper">
          <div class="ce-form-label">Start Date :</div>
          <div class="ce-form-input">
            <input type="date" id="deployment-start" name="deployment-start" placeholder="Deployment Date" value="<?php echo $resgetprojectitem['deployment_start']; ?>">
          </div>
        </div>
        <div class="ce-form-controls ce-form-control-col-2" id="deployment-end-wrapper">
          <div class="ce-form-label">End Date :</div>
          <div class="ce-form-input">
            <input type="date" id="deployment-end" name="deployment-end" placeholder="Deployment Date"  value="<?php echo $resgetprojectitem['deployment_end']; ?>">
          </div>
        </div> 
                  
                  
        <div class="ce-form-controls ce-form-control-col-2" id="subscription-term-wrapper">
          <div class="ce-form-label">Subscription Term :</div>
          <div class="ce-form-input">
            <select id="sale-subscription-term" name="sale-subscription-term" data-row-id="1">
              <option value="0">Select Subscription Term</option>
              <?php

                  $sqlgetsubterm = "select * from subscription_term";
                  $rowgetsubterm = mysqli_query($con, $sqlgetsubterm);

                  if (mysqli_num_rows($rowgetsubterm) > 0)
                  {
                    $i = 0;
                    while ($i <= ($resgetsubterm = mysqli_fetch_array($rowgetsubterm)))
                    {
                      if($resgetprojectitem['sale_subscription_term'] == $resgetsubterm['value'])
                      {
                        $subtermselected = "selected";
                      }
                      else
                      {
                        $subtermselected = "";
                      }
                ?>
                  <option value="<?php echo $resgetsubterm['value']; ?>" <?php echo $subtermselected; ?> >
                    <?php echo $resgetsubterm['name']; ?>
                  </option>
                <?php
                      $i++;
                    }
                  }
              ?>
            </select>
          </div>
        </div>
        <div class="ce-form-controls ce-form-control-col-2" id="billing-term-wrapper">
          <div class="ce-form-label">Billing Term :</div>
          <div class="ce-form-input">
            <select id="sale-billing-term" name="sale-billing-term" data-row-id="1">
              <option value="0">Select Billing Term</option>
              <?php

                  $sqlgetbillingterm = "select * from billing_term";
                  $rowgetbillingterm = mysqli_query($con, $sqlgetbillingterm);

                  if (mysqli_num_rows($rowgetbillingterm) > 0)
                  {
                    $i = 0;
                    while ($i <= ($resgetbillingterm = mysqli_fetch_array($rowgetbillingterm)))
                    {
                      if($resgetprojectitem['sale_billing_term'] == $resgetbillingterm['value'])
                      {
                        $billingtermselected = "selected";
                      }
                      else
                      {
                        $billingtermselected = "";
                      }
                ?>
                  <option value="<?php echo $resgetbillingterm['value']; ?>" <?php echo $billingtermselected; ?> >
                    <?php echo $resgetbillingterm['name']; ?>
                  </option>
                <?php
                      $i++;
                    }
                  }
              ?>
            </select>
          </div>
        </div>

        <div class="ce-form-controls ce-form-control-col-2" id="contract-period-wrapper">
              <div class="ce-form-label">Contract Period :</div>
              <div class="ce-form-input">
                <select id="contract-year" name="contract-year" data-row-id="1" style="width:49%;">
                  <option value="0">Select Year</option>
                  <?php

                      $sqlgetcontractyear = "select * from contract_year";
                      $rowgetcontractyear = mysqli_query($con, $sqlgetcontractyear);

                      if (mysqli_num_rows($rowgetcontractyear) > 0)
                      {
                        $i = 0;
                        while ($i <= ($resgetcontractyear = mysqli_fetch_array($rowgetcontractyear)))
                        {
                          if($resgetprojectitem['contract_year'] == $resgetcontractyear['value'])
                          {
                            $contractyearselected = "selected";
                          }
                          else
                          {
                            $contractyearselected = "";
                          }

                    ?>
                      <option value="<?php echo $resgetcontractyear['value']; ?>"  <?php echo $contractyearselected; ?> >
                        <?php echo $resgetcontractyear['name']; ?>
                      </option>
                    <?php
                          $i++;
                        }
                      }
                  ?>
                </select>
                <select id="contract-month" name="contract-month" data-row-id="1" style="width:49%;">
                  <option value="0">Select Month</option>
                  <?php

                      $sqlgetcontractmonth = "select * from contract_month";
                      $rowgetcontractmonth = mysqli_query($con, $sqlgetcontractmonth);

                      if (mysqli_num_rows($rowgetcontractmonth) > 0)
                      {
                        $i = 0;
                        while ($i <= ($resgetcontractmonth = mysqli_fetch_array($rowgetcontractmonth)))
                        {
                          if($resgetprojectitem['contract_month'] == $resgetcontractmonth['value'])
                          {
                            $contractmonthselected = "selected";
                          }
                          else
                          {
                            $contractmonthselected = "";
                          }
                    ?>
                      <option value="<?php echo $resgetcontractmonth['value']; ?>"  <?php echo $contractmonthselected; ?> >
                        <?php echo $resgetcontractmonth['name']; ?>
                      </option>
                    <?php
                          $i++;
                        }
                      }
                  ?>
                </select>
              </div>
            </div>

            <div class="ce-form-controls ce-form-control-col-2">
              <div class="ce-form-input align-right">
                  <input type="submit" id="ce-create-sales-form-submit" name="ce-create-sales-form-submit" value="Submit">
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
