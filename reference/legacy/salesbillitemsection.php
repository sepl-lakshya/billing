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

      $sqlgetbill = "select id,hash,status,ri_discount,payg_discount from bill where project_id = ".$pid." AND month = ".$month." AND year = ".$year." AND is_deleted = 0";
      $rowgetbill = mysqli_query($con, $sqlgetbill);

      if(mysqli_num_rows($rowgetbill) > 0)
      {
        $resgetbill = mysqli_fetch_array($rowgetbill);

        $sqlgetcloudoutward = "select id from cloud_outward where project_id = ".$pid." AND bill_id = ".$resgetbill['id']." AND is_cancelled = 0";
        $rowgetcloudoutward = mysqli_query($con, $sqlgetcloudoutward);

        if(mysqli_num_rows($rowgetcloudoutward) > 0)
        {
          $resgetcloudoutward = mysqli_fetch_array($rowgetcloudoutward);
          $editprices = false;
        }
        else
        {
          $editprices = true;
        }

        // if($resgetbill['status'] == 1)
        // {

          $daysinmonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

          $monthstartdate = $year."-".$month."-1";
          $monthenddate = $year."-".$month."-".$daysinmonth;

          $monthstartdate = date("Y-m-d",strtotime($monthstartdate));
          $monthenddate = date("Y-m-d",strtotime($monthenddate));

          $ridiscount = (float)$resgetbill['ri_discount'];
          $paygdiscount = (float)$resgetbill['payg_discount'];

          

?>
      <button class="print-btn" onclick="printDiv('sales-price-modal');">Print</button>
      <div class="modal-heading-wrapper">
        Sales Price of Items for <span style="font-size:1.1em; text-decoration: underline;"><?php echo $resgetmonth['name']." ".$resgetyear['name']; ?></span>
      </div>
          <input type="hidden" id="sales-price-month" name="sales-price-month" value="<?php echo $month; ?>">      
          <input type="hidden" id="sales-price-year" name="sales-price-year" value="<?php echo $year; ?>">      
          <input type="hidden" id="sales-bill-id" name="sales-bill-id" value="<?php echo $resgetbill['id']; ?>">      
          <table class="table-element" id="sales-price-table" cellpadding="0" cellspacing="0" style="font-size:.8em;">
            
        <?php


          $sqlgetprojectheader = "select header_id,amount, 
          (select name from project_header where id = bh.header_id) as header_name
          from bill_header as bh where project_id = ".$pid." AND bill_id = ".$resgetbill['id'];
          $rowgetprojectheader = mysqli_query($con, $sqlgetprojectheader);

          if (mysqli_num_rows($rowgetprojectheader) > 0)
          {
        ?>
          <thead>
            <tr class="align-center">
              <th>SNo</th>
              <th>Product</th>
              <th>Type</th>
              <th>Model</th>
              <th>Description</th>
              <!-- <th>Deployed Product</th> -->
              <th>Deployed On</th>
              <th>Status/Deactivation Date</th>
              <th>Portal Price</th>
              <th>Estimated Purchase Price</th>
              <th>Sales Price excl. GST</th>
            </tr>
          </thead>
          <tbody>

        <?php

            $h = 0;
            $rowcount = 1;
            while ($h <= ($resgetprojectheader = mysqli_fetch_array($rowgetprojectheader)))
            {
              
        ?>
            <tr class="cloud-header-row" data-header-row-no="<?php echo $h; ?>" data-project-header-id="<?php echo $resgetprojectheader['header_id'] ?>">
                <td colspan="9"><div class="table-value-wrapper" style="word-wrap: break-word;"><?php echo $resgetprojectheader['header_name']; ?></div> </td>
                <td>
                  <?php
                    if((int)$resgetbill['status'] <= 1)
                    {
                      $headeramount = "";
                    }
                    else
                    {
                      if($resgetprojectheader['amount'] != NULL)
                      {
                        $headeramount = $resgetprojectheader['amount'];
                      }
                      else
                      {
                        $headeramount = "";
                      }
                    }

                    if($editprices)
                    {
                  ?>
                   <input type="text" id="sales-price-header-value-<?php echo $h; ?>" name="sales-price-header-value-<?php echo $h; ?>" value="<?php echo $headeramount; ?>" style="text-align: right;">
                    <?php
                    }
                    else
                    {
                  ?>
                  <div class="table-value-wrapper align-right">
                      <?php echo numberFormat(round($headeramount,2),2); ?>
                  </div>
                  <?php
                    }
                    ?> 
                </td>
              </tr>

        <?php

              $sqlgetbillitem = "select * from bill_item as bi INNER JOIN product pr ON pr.id = (select product from project_item where id = bi.project_item_id) INNER JOIN project_item pi ON pi.id = bi.project_item_id where bill_id = ".$resgetbill['id']." AND  bi.project_id = ".$pid." AND pi.header_id = ".$resgetprojectheader['header_id']." order by pr.sub_category";

              $rowgetbillitem = mysqli_query($con, $sqlgetbillitem);

              if (mysqli_num_rows($rowgetbillitem) > 0)
              {
                ?>
            
          <?php

                $i = 0;
                while ($i <= ($resgetbillitem = mysqli_fetch_array($rowgetbillitem)))
                {

                  $sqlgetprojectitem = "select id,
                  (select name from cloud_category where value = (select sub_category from product where id = pi.product)) as category_name,
                  (select name from product where id = pi.product) as product_name,
                  (select name from distributor where id = pi.distributor) as distributor_name,
                  deployment_start,model,unit_price,quantity,deployment_end,status,description,
                  (select name from product where id = pi.deployed_product) as deployed_product_name,
                  (select name from unit_measure where value = pi.unit_measure) as unit_measure_name,
                  case when pi.status = 1 then 'fa-toggle-on' else 'fa-toggle-off' end as product_status
                   from project_item as pi where id = ".$resgetbillitem['project_item_id']." AND project_id = ".$pid;


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
                <tr class="sales-price-row" id="sales-price-row-<?php echo $rowcount; ?>" data-row-no="<?php echo $rowcount; ?>" data-project-item-id="<?php echo $resgetprojectitem['id']; ?>" >
                  <td><div class="table-value-wrapper"><?php echo $rowcount; ?>&nbsp;&nbsp;</div></td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['product_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['category_name']; ?>
                    </div>
                  </td>
                  <td>
                    <?php
                      if($resgetprojectitem['model'] == "")
                      {
                        $itemmodel = "N/A";
                      }
                      else
                      {
                        $itemmodel = $resgetprojectitem['model']; 
                        $itemmodelcolor = "";
                        
                        if($resgetprojectitem['model'] == "RI")
                        {
                          $itemmodelcolor = "background:#FEBE10";
                        }

                        if($resgetprojectitem['model'] == "PAYG")
                        {
                          $itemmodelcolor = "background:#D8BFD8";
                        }

                      }
                    ?>
                    <div class="table-value-wrapper align-center" style="<?php echo $itemmodelcolor; ?>">
                      <?php echo $itemmodel; ?>
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
                    <?php 
                      if($itemstatus == "Active")
                      {
                        $itemstatuscolor = "background:#90EE90;";
                      }
                      else
                      {
                        $itemstatuscolor = "background:#F08080;";
                      }
                    ?>
                    <div class="table-value-wrapper align-center" style="<?php echo $itemstatuscolor; ?>">
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
                        echo numberFormat($resgetbillitem['portal_price'],2);
                      ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper align-right">
                      <?php

                        $discountpercent = 0;
                        if($resgetprojectitem['model'] == "RI")
                        {
                          $discountpercent = $ridiscount;
                        }

                        if($resgetprojectitem['model'] == "PAYG")
                        {
                          $discountpercent = $paygdiscount;
                        }

                        $portalprice = (float)$resgetbillitem['portal_price'];
                        $discountvalue = ($portalprice/100)*$discountpercent;

                        $estimatedvalue = ($portalprice - $discountvalue);
                        echo numberFormat(round($estimatedvalue,2),2);
                      ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper align-right">
                    <?php 
                      if($resgetbillitem['sales_price'] == null || $resgetbillitem['sales_price'] == "")
                      {
                        $salespricevalue = $resgetbillitem['portal_price'];
                      }
                      else
                      {
                        $salespricevalue = $resgetbillitem['sales_price'];
                      }

                      // echo numberFormat(round($salespricevalue,2),2);
                    ?>
                    </div>

                    <input type="hidden" id="sales-price-value-<?php echo $rowcount; ?>" name="sales-price-value-<?php echo $rowcount; ?>" value="<?php echo $salespricevalue; ?>" >
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
                  // else
                  // {
              ?>
                <!-- <tbody>
                  <tr>
                    <td class="align-center">No Products Found !</td>
                  </tr>
                </tbody> -->
              <?php
                  // }
                $h++;
              }
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
      <input type="hidden" id="show-sales-modal-check" value="1">
    <?php   
      // }

    }
    else
    {
  ?>
    <input type="hidden" id="show-sales-modal-check" value="0">
    <script>
      alert("Please add Portal Price for <?php echo $resgetmonth['name']." ".$resgetyear['name']; ?> first");
    </script>
    
<?php    
    }

  }
?>
    </table>
    <?php
      if($editprices)
      {
    ?>
      <div class="modal-change-btn-wrapper">        
        <div class="ce-form-controls ce-form-control-col-2 no-print">
          <div class="ce-form-input ">
              <input type="button" id="ce-sales-price-back-btn" name="ce-sales-price-back-btn" value="< Back">
          </div>
        </div>
        <div class="ce-form-controls ce-form-control-col-2 no-print">
          <div class="ce-form-input align-right">
              <input type="button" id="ce-submit-sales-price-btn" name="ce-submit-sales-price-btn" value="Save & Exit">
          </div>
        </div>
      </div>
    <?php 
      }
    ?>


  