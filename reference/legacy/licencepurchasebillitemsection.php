<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    $con = connectMySQL();
    
    if(isset($_POST['month']) && $_POST['month'] != "" && isset($_POST['year']) && $_POST['year'] != "" && isset($_POST['pid']) && $_POST['pid'] != "")
    {
      $month = mysqli_real_escape_string($con, clean_input($_POST['month']));
      $year = mysqli_real_escape_string($con, clean_input($_POST['year']));
      $pid = mysqli_real_escape_string($con, clean_input($_POST['pid']));

       $sqlgetmonth = "select name from month where value = ".$month;
        $rowgetmonth = mysqli_query($con, $sqlgetmonth);
        $resgetmonth = mysqli_fetch_array($rowgetmonth);

        $sqlgetyear = "select name from year where value = ".$year;
        $rowgetyear = mysqli_query($con, $sqlgetyear);
        $resgetyear = mysqli_fetch_array($rowgetyear);


      $sqlgetbill = "select id,hash,status from bill where project_id = ".$pid." AND month = ".$month." AND year = ".$year." AND status >= 2";
      $rowgetbill = mysqli_query($con, $sqlgetbill);

      if(mysqli_num_rows($rowgetbill) > 0)
      {
        $resgetbill = mysqli_fetch_array($rowgetbill);

        // if($resgetbill['status'] == 1)
        // {

          $daysinmonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

          $monthstartdate = $year."-".$month."-1";
          $monthenddate = $year."-".$month."-".$daysinmonth;

          $monthstartdate = date("Y-m-d",strtotime($monthstartdate));
          $monthenddate = date("Y-m-d",strtotime($monthenddate));

         

?>
      <div class="modal-heading-wrapper">
        Enter Purchase Price of Items for <span style="font-size:1.1em; text-decoration: underline;"><?php echo $resgetmonth['name']." ".$resgetyear['name']; ?></span>
      </div>
          <input type="hidden" id="purchase-price-month" name="purchase-price-month" value="<?php echo $month; ?>">      
          <input type="hidden" id="purchase-price-year" name="purchase-price-year" value="<?php echo $year; ?>">      
          <input type="hidden" id="purchase-bill-id" name="purchase-bill-id" value="<?php echo $resgetbill['id']; ?>">      
          <table class="table-element" id="purchase-price-table" cellpadding="0" cellspacing="0" style="font-size:.8em;">
            
                <?php
                  
              $sqlgetbillitem = "select * from bill_item where bill_id = ".$resgetbill['id']." AND project_id = ".$pid;
              $rowgetbillitem = mysqli_query($con, $sqlgetbillitem);

              if (mysqli_num_rows($rowgetbillitem) > 0)
              {
          ?>
            <thead>
              <tr>
                <th>SNo</th>
                <th>Product</th>
                <th>Model</th>
                <th>Description</th>
                <!-- <th>Deployed Product</th> -->
                <th>Deployed On</th>
                <th>Status/Deactivation Date</th>
                <th>Portal Price</th>
                <th>Purchase Price</th>
              </tr>
            </thead>
            <tbody>
          <?php

                $i = 0;
                $rowcount = 1;
                while ($i <= ($resgetbillitem = mysqli_fetch_array($rowgetbillitem)))
                {

                  $sqlgetprojectitem = "select id,licence_category,
                  (select name from licence_category where value = pi.licence_category) as licence_category_name,
                  (select name from product where id = pi.product) as product_name,
                  (select name from subscription_term where id = pi.subscription_term) as subscription_term_name,
                  (select name from billing_term where id = pi.billing_term) as billing_term_name,
                  (select name from distributor where id = pi.distributor) as distributor_name,
                  deployment_start,deployment_end,unit_price,quantity,status,description,
                  case when pi.status = 1 then 'fa-toggle-on' else 'fa-toggle-off' end as product_status
                   from licence_project_item as pi where id = ".$resgetbillitem['project_item_id']." AND project_id = ".$pid;


                  $rowgetprojectitem = mysqli_query($con, $sqlgetprojectitem);

                  if (mysqli_num_rows($rowgetprojectitem) > 0)
                  {
                    $resgetprojectitem = mysqli_fetch_array($rowgetprojectitem);
              
                      $deployenddate = strtotime($resgetprojectitem['deployment_end']);
                      $deploystartdate = strtotime($resgetprojectitem['deployment_start']);

                        if($resgetprojectitem['status'] == 0 && $deployenddate > strtotime($monthenddate))
                        {
                          $itemstatus = "Active";
                        }
                        else
                        {
                          if($resgetprojectitem['status'] == 0 && $resgetprojectitem['deployment_end'] != NULL)
                          {
                            $itemstatus = date("d-m-Y",strtotime($resgetprojectitem['deployment_end'])); 
                          }
                          else
                          {
                            $itemstatus = "Active";
                          }
                        }

                ?>
                <tr class="purchase-price-row" id="purchase-price-row-<?php echo $rowcount; ?>" data-row-no="<?php echo $rowcount; ?>" data-project-item-id="<?php echo $resgetprojectitem['id']; ?>" >
                  <td><div class="table-value-wrapper"><?php echo $rowcount; ?>&nbsp;&nbsp;</div></td>
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
                      <?php echo $resgetprojectitem['description']; ?>
                    </div>
                  </td>
                  <!-- <td>
                    <div class="table-value-wrapper">
                      <?php
                        if($resgetprojectitem['deployed_product_name'] == "")
                        {
                          echo "N/A";
                        }
                        else
                        {
                         echo $resgetprojectitem['deployed_product_name']; 
                        }
                      ?>
                    </div>
                  </td> -->
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo date("d-m-Y",strtotime($resgetprojectitem['deployment_start'])); ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                    <?php 
                      // if($resgetprojectitem['status'] == 0 && $resgetprojectitem['deployment_end'] != NULL)
                      // {
                      //   echo date("d-m-Y",strtotime($resgetprojectitem['deployment_end'])); 
                      // }
                      // else
                      // {
                      //   echo "Active";
                      // }

                      echo $itemstatus;
                    ?>
                    </div>
                  </td>                                      
                  <td>
                    <div class="table-value-wrapper">
                      <?php
                        echo $resgetbillitem['portal_price'];
                      ?>
                    </div>
                  </td>
                  <td>
                    <?php 
                      if($resgetbillitem['purchase_price'] == null || $resgetbillitem['purchase_price'] == "")
                      {
                        $purchasepricevalue = "";
                      }
                      else
                      {
                        $purchasepricevalue = $resgetbillitem['purchase_price'];
                      }
                    ?>

                    <input type="text" id="purchase-price-value-<?php echo $rowcount; ?>" name="purchase-price-value-<?php echo $rowcount; ?>" value="<?php echo $purchasepricevalue; ?>">
                  </td>
                </tr>
                <?php
                        $rowcount++;
                      }
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

                  mysqli_close($con);
                ?>
<?php 
      // }
      // elseif($resgetbill['status'] >= 2)
      // {
        ?>
      <!-- <tbody>
        <tr>
          <td class="align-center">Send to purchase price !</td>
        </tr>
      </tbody> -->
    <?php
      // }
      // else
      // {
    ?>
      <!-- <tbody>
        <tr>
          <td class="align-center">Error : No data found !</td>
        </tr>
      </tbody> -->
      <input type="hidden" id="show-purchase-modal-check" value="1">
    <?php   
      // }

    }
    else
    {
?>
    <input type="hidden" id="show-purchase-modal-check" value="0">
    <script>
      alert("Please add Sales Price for <?php echo $resgetmonth['name']." ".$resgetyear['name']; ?> first");
    </script>
    
<?php
    }

  }
?>
          </table>


  