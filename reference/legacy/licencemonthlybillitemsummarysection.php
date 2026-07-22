<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    $con = connectMySQL();
    

    if(isset($_POST['billid']) && $_POST['billid'] != "" && isset($_POST['projectid']) && $_POST['projectid'] != "")
    {
      $billid = mysqli_real_escape_string($con, clean_input($_POST['billid']));
      $projectid = mysqli_real_escape_string($con, clean_input($_POST['projectid']));

      // $sqlgetproject = "select ri_discount,payg_discount from project where id = ".$projectid;
      // $rowgetproject = mysqli_query($con, $sqlgetproject);
      // $resgetproject = mysqli_fetch_array($rowgetproject);

      $sqlgetbill = "select * from bill where project_id = ".$projectid." AND id = ".$billid;
      $rowgetbill = mysqli_query($con, $sqlgetbill);

      if(mysqli_num_rows($rowgetbill) > 0)
      {
        $resgetbill = mysqli_fetch_array($rowgetbill);

        // if($resgetbill['status'] == 1)
        // {

          $daysinmonth = cal_days_in_month(CAL_GREGORIAN, $resgetbill['month'], $resgetbill['year']);

          $monthstartdate = $resgetbill['year']."-".$resgetbill['month']."-1";
          $monthenddate = $resgetbill['year']."-".$resgetbill['month']."-".$daysinmonth;

          $monthstartdate = date("Y-m-d",strtotime($monthstartdate));
          $monthenddate = date("Y-m-d",strtotime($monthenddate));

          $sqlgetmonth = "select name from month where value = ".$resgetbill['month'];
  	      $rowgetmonth = mysqli_query($con, $sqlgetmonth);
  	      $resgetmonth = mysqli_fetch_array($rowgetmonth);
          
  	      $sqlgetyear = "select name from year where value = ".$resgetbill['year'];
  	      $rowgetyear = mysqli_query($con, $sqlgetyear);
  	      $resgetyear = mysqli_fetch_array($rowgetyear);

?>
      <div class="modal-heading-wrapper">
        Prices of Items for <span style="font-size:1.1em; text-decoration: underline;"><?php echo $resgetmonth['name']." ".$resgetyear['name']; ?></span>
      </div>      
          <table class="table-element" id="purchase-price-table" cellpadding="0" cellspacing="0" style="font-size:.8em;">
            
                <?php
                  
              $sqlgetbillitem = "select * from bill_item where bill_id = ".$resgetbill['id']." AND project_id = ".$projectid;
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
                <th>Deployed On</th>
                <th>Status / Deactivation Date</th>
                <th>Portal Price (Rs.)</th>
                <!-- <th>Sales Price (Rs.)</th> -->
                <th>Purchase Price (Rs.)</th>
                <th>Discount (%)</th>
              </tr>
            </thead>
            <tbody>
          <?php

                $i = 0;
                $rowcount = 1;
                $portaltotal = 0;
                $salestotal = 0;
                $purchasetotal = 0;
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
                   from licence_project_item as pi where id = ".$resgetbillitem['project_item_id']." AND project_id = ".$projectid;


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
                    <div class="table-value-wrapper align-right">
                      <?php
                        echo (float)$resgetbillitem['portal_price'];

                        $portaltotal = $portaltotal + (float)$resgetbillitem['portal_price'];
                      ?>
                    </div>
                  </td>
                 <!--  <td>
                    <div class="table-value-wrapper align-right">
                      <?php
                        if($resgetbill['status'] >= 2)
                        {
                          echo (float)$resgetbillitem['sales_price'];
                          $salestotal = $salestotal + (float)$resgetbillitem['sales_price'];
                        }
                        else
                        {
                          echo "Not Added";
                        }
                      
                      ?>
                    </div>
                  </td> -->
                  <td>
                    <div class="table-value-wrapper align-right">
                      <?php
                        if($resgetbill['status'] >= 3)
                        {
                          echo $resgetbillitem['purchase_price'];
                          $purchasetotal = $purchasetotal + (float)$resgetbillitem['purchase_price'];
                        }
                        else
                        {
                          echo "Not Added";
                        }
                      ?>
                    </div>
                  </td>
                  <td>
                    <?php

                      if($resgetbill['status'] >= 3)
                      {                      
                        $portalvalue = (float)$resgetbillitem['portal_price'];
                        $salesvalue = (float)$resgetbillitem['sales_price'];
                        $purchasevalue = (float)$resgetbillitem['purchase_price'];


                        if($portalvalue != 0)
                        {

                          $discount = (float)$resgetbill['ri_discount'];

                          $totalvalue = (($portalvalue - $purchasevalue)/$portalvalue)*100;

                          $discount = round($discount,2);
                          $totalvalue = round($totalvalue,2);


                          if($totalvalue == $discount)
                          {
                            $background = "background : #4FFFB0;";
                          }
                          elseif($totalvalue < $discount)
                          {
                            $background = "background : #FA8072;";
                          }
                          elseif($totalvalue > $discount)
                          {
                            $background = "background : #318CE7;";
                          }
                          else
                          {
                            $background = "background : #FA8072;";
                          }
                        }
                        else
                        {
                          $totalvalue = 0;
                        }
                      }
                      else
                      {
                        $totalvalue = "N/A";
                      }

                      ?>
                    <div class="table-value-wrapper align-right" style="font-weight: 500;<?php echo $background; ?>">
                      <?php echo  $totalvalue; ?>
                    </div>
                  </td>
                </tr>
                <?php
                        $rowcount++;
                      }
                      $i++;
                    }
                ?>

              <tr>
                <td colspan="6">
                  <div class="table-value-wrapper align-right">
                      Total
                  </div>
                </td>
                <td>
                  <div class="table-value-wrapper align-right">
                      <?php echo  $portaltotal; ?>
                  </div>
                </td>
                <!-- <td>
                  <div class="table-value-wrapper align-right">
                      <?php echo  $salestotal; ?>
                  </div>
                </td> -->
                <td>
                  <div class="table-value-wrapper align-right">
                      <?php echo  $purchasetotal; ?>
                  </div>
                </td>
                <td></td>
              </tr>
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


  