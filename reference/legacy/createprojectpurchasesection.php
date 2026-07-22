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

      $sqlgetprojectitem = "select id, licence_category, product, distributor, purchase_unit_price, purchase_quantity, purchase_subscription_term, purchase_billing_term, status from licence_project_item where project_id = ".$projectid." AND id = ".$projectitemid;
      $rowgetprojectitem = mysqli_query($con, $sqlgetprojectitem);

      // If Portal Price Exists 
      if(mysqli_num_rows($rowgetprojectitem) > 0)
      {
        $resgetprojectitem = mysqli_fetch_array($rowgetprojectitem);
?>
      <form method="post" id="ce-create-purchase-form">
          <input type="hidden" id="create-purchase-project-id" name="create-purchase-project-id" value="<?php echo $projectid; ?>">
          <input type="hidden" id="create-purchase-project-item-id" name="create-purchase-project-item-id" value="<?php echo $projectitemid; ?>">                   
          <input type="hidden" id="create-purchase-item-status" name="create-purchase-item-status" value="<?php echo $resgetprojectitem['status']; ?>">                   
                    
          <div class="ce-form-controls ce-form-control-col-2" id="subscription-term-wrapper">
            <div class="ce-form-label">Subscription Term :</div>
            <div class="ce-form-input">
              <select id="purchase-subscription-term" name="purchase-subscription-term" data-row-id="1">
                <option value="0">Select Subscription Term</option>
                <?php

                    $sqlgetsubterm = "select * from subscription_term";
                    $rowgetsubterm = mysqli_query($con, $sqlgetsubterm);

                    if (mysqli_num_rows($rowgetsubterm) > 0)
                    {
                      $i = 0;
                      while ($i <= ($resgetsubterm = mysqli_fetch_array($rowgetsubterm)))
                      {
                        if($resgetprojectitem['purchase_subscription_term'] == $resgetsubterm['value'])
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
              <select id="purchase-billing-term" name="purchase-billing-term" data-row-id="1">
                <option value="0">Select Billing Term</option>
                <?php

                    $sqlgetbillingterm = "select * from billing_term";
                    $rowgetbillingterm = mysqli_query($con, $sqlgetbillingterm);

                    if (mysqli_num_rows($rowgetbillingterm) > 0)
                    {
                      $i = 0;
                      while ($i <= ($resgetbillingterm = mysqli_fetch_array($rowgetbillingterm)))
                      {
                        if($resgetprojectitem['purchase_billing_term'] == $resgetbillingterm['value'])
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
            <div class="ce-form-label">Distributor :</div>
              <div class="ce-form-input">
                <select id="purchase-distributor" name="purchase-distributor" data-row-id="1">
                  <option value="0">Select Distributor</option>
                  <?php

                      $sqlgetdistributor = "select * from distributor where is_active = 1";
                      $rowgetdistributor = mysqli_query($con, $sqlgetdistributor);

                      if (mysqli_num_rows($rowgetdistributor) > 0)
                      {
                        $i = 0;
                        while ($i <= ($resgetdistributor = mysqli_fetch_array($rowgetdistributor)))
                        {
                          if($resgetprojectitem['distributor'] == $resgetdistributor['id'])
                          {
                            $distributorselected = "selected";
                          }
                          else
                          {
                            $distributorselected = "";
                          }
                    ?>
                      <option value="<?php echo $resgetdistributor['id']; ?>" <?php echo $distributorselected; ?> >
                        <?php echo $resgetdistributor['name']; ?>
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
                  <input type="submit" id="ce-create-purchase-form-submit" name="ce-create-purchase-form-submit" value="Create">
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
