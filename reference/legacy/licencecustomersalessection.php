<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    if(isset($_POST['projectid']) && $_POST['projectid'] != "")
    {
      $pid = $_POST['projectid'];


?>
      
          <table class="table-element align-center" cellpadding="0" cellspacing="0" style="font-size: .8em;">
            
                <?php
                  $con = connectMySQL();
                  $sqlgetprojectitem = "select id,licence_category,
                  (select name from licence_category where value = pi.licence_category) as licence_category_name,
                  (select name from product where id = pi.product) as product_name,
                  (select name from subscription_term where id = pi.sale_subscription_term) as subscription_term_name,
                  (select name from billing_term where id = pi.sale_billing_term) as billing_term_name,
                  description,status,sale_unit_price,sale_quantity,deployment_start,deployment_end,contract_month,contract_year 
                   from licence_project_item as pi where project_id = ".$pid." AND is_deleted = 0 AND status >= 1";
                  $rowgetprojectitem = mysqli_query($con, $sqlgetprojectitem);

                  if (mysqli_num_rows($rowgetprojectitem) > 0)
                  {
              ?>
            <thead>
              <tr>
                <th>SNo</th>
                <th>Product</th>
                <th>Category</th>
                <th>Unit Price</th>
                <th>Quantity</th>
                <th>Subscription Term</th>
                <th>Billing Term</th>
                <th>Contract Period</th>
                <th>Start Date</th>
                <th>End Date</th>
              </tr>
            </thead>
            <tbody>
              <?php
                    $i = 0;
                    while ($i <= ($resgetprojectitem = mysqli_fetch_array($rowgetprojectitem)))
                    {
                ?>
                <tr>
                  <td><div class="table-value-wrapper"><?php echo $i+1; ?>&nbsp;&nbsp;</div></td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['product_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['licence_category_name']; ?>
                    </div>
                  </td>  
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['sale_unit_price']; ?>
                    </div>
                  </td>                 
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['sale_quantity']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['subscription_term_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['billing_term_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php 
                    $contractperiod = "";
                    if($resgetprojectitem['contract_year'] != "0")
                    {
                      if($resgetprojectitem['contract_year'] == "1")
                      {
                        $contractperiod .= $resgetprojectitem['contract_year']." Year ";
                      }
                      else
                      {
                        $contractperiod .= $resgetprojectitem['contract_year']." Years ";
                      }
                    }

                    if($resgetprojectitem['contract_month'] != "0")
                    {
                      if($resgetprojectitem['contract_month'] == "1")
                      {
                        $contractperiod .= " &nbsp;&nbsp;&nbsp;&nbsp;".$resgetprojectitem['contract_month']." Month ";
                      }
                      else
                      {
                        $contractperiod .= " &nbsp;&nbsp;&nbsp;&nbsp;".$resgetprojectitem['contract_month']." Months ";
                      }
                    }

                  echo $contractperiod;
                ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['deployment_start']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['deployment_end']; ?>
                    </div>
                  </td>              
                </tr>
                <?php
                      $i++;
                    }
                ?>
            </tbody>
                <?php
                  }
                  else
                  {
              ?>
                <tbody>
                  <tr>
                    <td class="align-center">No Products Found !</td>
                  </tr>
                </tbody>
              <?php
                  }
                  echo mysqli_error($con);
                  mysqli_close($con);

                ?>
          </table>
<?php 
  }
?>
