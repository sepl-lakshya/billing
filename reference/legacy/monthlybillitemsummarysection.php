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

          $discountdate = $resgetbill['year']."-".$resgetbill['month']."-15";

          $sqlgetdiscount = "select ri_discount,payg_discount from project_discount where from_date < '".$discountdate."' AND to_date > '".$discountdate."' AND project_id = ".$projectid;
          $rowgetdiscount = mysqli_query($con, $sqlgetdiscount);
          if(mysqli_num_rows($rowgetdiscount) > 0)
          {
            $resgetdiscount = mysqli_fetch_array($rowgetdiscount);
            $ridiscountval =  $resgetdiscount['ri_discount'];
            $paygdiscountval =  $resgetdiscount['payg_discount'];
          }
          else
          {
            $sqlgetlastdiscount = "select ri_discount,payg_discount from project_discount where project_id = ".$projectid." AND to_date IS NULL";
            $rowgetlastdiscount = mysqli_query($con, $sqlgetlastdiscount);
            $resgetlastdiscount = mysqli_fetch_array($rowgetlastdiscount);
            $ridiscountval =  $resgetlastdiscount['ri_discount'];
            $paygdiscountval =  $resgetlastdiscount['payg_discount'];
          }
?>
      <button class="print-btn" onclick="printDiv('monthly-bill-item-modal');">Print</button>
      <div class="modal-heading-wrapper">

        Purchase Invoice Details for <span style="font-size:1.1em; text-decoration: underline;"><?php echo $resgetmonth['name']." ".$resgetyear['name']; ?></span>
        </div>
      <div class="section-content-wrapper">
        <div id="monthly-summary-invoice-table-section">
            
        </div>
        <div class="ce-spinner-overlay" id="monthly-summary-invoice-table-section-loading" style="text-align:center;display:none;">
            <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
        </div>
      </div>
      <script type="text/javascript">
        getMonthlySummaryInvoices(<?php echo $resgetbill['id']; ?>);
      </script>
      <div class="modal-heading-wrapper" style="font-size:1.2em;">
        Summary of Items for <span style="font-size:1.1em; text-decoration: underline;"><?php echo $resgetmonth['name']." ".$resgetyear['name']; ?></span>
      </div>   
      <div class="align-right" style="margin-bottom: 10px;">
        RI Discount : <?php echo $ridiscountval; ?> % &nbsp;&nbsp; | &nbsp;&nbsp; PAYG Discount : <?php echo $paygdiscountval; ?> %
      </div>   

        
        <?php
          $calculatedpaygheaderamountgrandtotal = 0;
          $calculatedriheaderamountgrandtotal = 0;
          $actualheaderamountgrandtotal = 0;

          $sqlgetpurchaseheader = "select distinct(bi.purchase_header_id),
          (select amount from bill_purchase_header where bill_id = ".$resgetbill['id']." AND project_id = ".$projectid." AND purchase_header_id = bi.purchase_header_id) as amount, 
          (select name from purchase_header where id = bi.purchase_header_id) as purchase_header_name
          from bill_item as bi INNER JOIN project_item as pi ON bi.project_item_id = pi.id where bi.project_id = ".$projectid." AND bi.bill_id = ".$resgetbill['id']." AND pi.model = 'PAYG'";
          $rowgetpurchaseheader = mysqli_query($con, $sqlgetpurchaseheader);

          if (mysqli_num_rows($rowgetpurchaseheader) > 0)
          {
        ?> 
            <!-- Section Divider --><div class="content-section-divider"></div>

          <div style="padding:10px;font-weight: bold;font-size: 1em;text-align: center;"> PAYG Items </div>
          <table class="table-element" id="purchase-price-table" cellpadding="0" cellspacing="0" style="font-size:.8em;">
      
            <thead>
              <tr class="align-center">
                <th>SNo</th>
                <th>Product</th>
                <th>Type</th>
                <th>Model</th>
                <th>Description</th>
                <th>Deployed On</th>
                <th>Status /<br> Deactivation<br> Date</th>
                <th>Portal Price (Rs.)</th>
                <th>Calculated <br> Purchase <br>Price (Rs.)</th>
                <!-- <th>Sales Price (Rs.)</th> -->
                <!-- <th>Purchase Price (Rs.)</th> -->
                <th>Actual <br> Purchase <br> Price (Rs.)</th>
                <th>Deviation (%)</th>
              </tr>
            </thead>
            <tbody>

              <?php

            $ph = 0;
            while ($ph <= ($resgetpurchaseheader = mysqli_fetch_array($rowgetpurchaseheader)))
            {
              
        ?>
            <tr class="cloud-purchase-header-row" data-header-row-no="<?php echo $ph; ?>" data-purchase-header-id="<?php echo $resgetpurchaseheader['purchase_header_id'] ?>">
                <td colspan="7"><div class="table-value-wrapper" style="word-wrap: break-word;"><?php echo $resgetpurchaseheader['purchase_header_name']; ?></div> </td>
                <td>
                  <div class="table-value-wrapper align-right">
                  <?php
                      
                      // Get PAYG Total
                      $sqlgetpaygtotal = "select SUM(portal_price) 
                      from bill_item as bi INNER JOIN project_item as pi ON pi.id = bi.project_item_id where pi.model = 'PAYG' AND bi.purchase_header_id = ".$resgetpurchaseheader['purchase_header_id']." AND bi.bill_id = ".$billid;
                      $rowgetpaygtotal = mysqli_query($con, $sqlgetpaygtotal);
                      $resgetpaygtotal = mysqli_fetch_array($rowgetpaygtotal);

                      if($resgetpaygtotal['SUM(portal_price)'] == "")
                      { 
                        $paygsum = 0; 
                      }
                      else
                      { 
                        $paygsum = (float)$resgetpaygtotal['SUM(portal_price)']; 
                      }
                      
                      //Calculate Header Total
                      $calculatedpheaderamount = round($paygsum,2);


                      echo numberFormat($calculatedpheaderamount,2);
                  ?>
                  </div>
                </td>
                <td>
                  <div class="table-value-wrapper align-right">
                  <?php
                                            
                      // Get PAYG Total
                      $sqlgetpaygtotal = "select SUM(portal_price) 
                      from bill_item as bi INNER JOIN project_item as pi ON pi.id = bi.project_item_id where pi.model = 'PAYG' AND bi.purchase_header_id = ".$resgetpurchaseheader['purchase_header_id']." AND bi.bill_id = ".$billid;
                      $rowgetpaygtotal = mysqli_query($con, $sqlgetpaygtotal);
                      $resgetpaygtotal = mysqli_fetch_array($rowgetpaygtotal);

                      if($resgetpaygtotal['SUM(portal_price)'] == "")
                      { $paygtotalval = 0; }
                      else
                      { 
                        $paygsum = (float)$resgetpaygtotal['SUM(portal_price)']; 
                        $paygtotalval = $paygsum - (($paygsum/100)*$paygdiscountval);
                      }
                      
                      //Calculate Header Total

                      $calculatedheaderamount = round($paygtotalval,2);
                      
                      
                      echo numberFormat($calculatedheaderamount,2);
                  ?>
                  </div>
                </td>
                <td>
                  <div class="table-value-wrapper align-right">
                      <?php 
                        if($resgetbill['status'] >= 3)
                        {
                          $actualtotal = round((float)$resgetpurchaseheader['amount'],2); 

                          if($calculatedheaderamount < $actualtotal)
                          {
                            $calculatedpaygheaderamountgrandtotal += $calculatedheaderamount;
                            $actualheaderamountgrandtotal += $actualtotal;
                          }
                          echo numberFormat($actualtotal,2);
                        }
                        else
                        {
                          echo "N/A";
                        }
                      ?>
                  </div>
                </td>
                <td>
                  <?php

                    if($resgetbill['status'] >= 3)
                    {
                      if($calculatedheaderamount == $actualtotal)
                      {
                        $headertotalvalue = 0;
                        $background = "background : #4FFFB0;";
                      }
                      elseif($calculatedheaderamount < $actualtotal)
                      {
                        $headertotalvalue = (($actualtotal - $calculatedheaderamount)/$actualtotal)*100;
                        $background = "background : #FA8072;";
                      }
                      elseif($calculatedheaderamount > $actualtotal)
                      {
                        $headertotalvalue = (($calculatedheaderamount - $actualtotal)/$calculatedheaderamount)*100;
                        $background = "background : #318CE7;";
                      }
                      else
                      {
                        $headertotalvalue = 0;
                        $background = "background : #FA8072;";
                      }

                      $headertotalvalue = round($headertotalvalue,2); 

                      if($headertotalvalue <= 0.1)
                      {
                         $headertotalvalue = 0;                         
                        $background = "background : #4FFFB0";
                      }

                      $headertotalvalue = $headertotalvalue." %";
                    }
                    else
                    {
                      $headertotalvalue = "N/A";
                    }


                  ?>
                  <div class="table-value-wrapper align-right" style="<?php echo $background; ?>">
                      <?php 
                        echo $headertotalvalue;
                      ?>
                  </div>
                </td>
              </tr>

          <?php
                  
              $sqlgetbillitem = "select *, 
              (select model from project_item where id = bi.project_item_id) as item_model 
              from bill_item as bi INNER JOIN product pr ON pr.id = (select product from project_item where id = bi.project_item_id) where bi.bill_id = ".$resgetbill['id']." AND bi.project_id = ".$projectid." AND bi.purchase_header_id = ".$resgetpurchaseheader['purchase_header_id']." order by pr.sub_category";
              $rowgetbillitem = mysqli_query($con, $sqlgetbillitem);

              if (mysqli_num_rows($rowgetbillitem) > 0)
              {

                $i = 0;
                $rowcount = 1;
                $portaltotal = 0;
                $salestotal = 0;
                $purchasetotal = 0;
                while ($i <= ($resgetbillitem = mysqli_fetch_array($rowgetbillitem)))
                {

                  if($resgetbillitem['item_model'] == "PAYG")
                  {
                    $sqlgetprojectitem = "select id,
                    (select name from cloud_category where value = (select sub_category from product where id = pi.product)) as category_name,
                    (select name from product where id = pi.product) as product_name,
                    (select name from distributor where id = pi.distributor) as distributor_name,
                    deployment_start,model,unit_price,quantity,deployment_end,status,description,
                    (select name from product where id = pi.deployed_product) as deployed_product_name,
                    (select name from unit_measure where value = pi.unit_measure) as unit_measure_name,
                    case when pi.status = 1 then 'fa-toggle-on' else 'fa-toggle-off' end as product_status
                     from project_item as pi where id = ".$resgetbillitem['project_item_id']." AND project_id = ".$projectid;


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
                        <?php echo $resgetprojectitem['category_name']; ?>
                      </div>
                    </td>
                    <td>
                      <div class="table-value-wrapper">
                        <?php
                          if($resgetprojectitem['model'] == "")
                          {
                            echo "N/A";
                          }
                          else
                          {
                           echo $resgetprojectitem['model']; 
                          }
                        ?>
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
                          echo numberFormat((float)$resgetbillitem['portal_price'],2);
                        ?>
                      </div>
                    </td>
                    <td>
                      <div class="table-value-wrapper align-right">
                        <?php
                          // echo (float)$resgetbillitem['portal_price'];

                          // $portaltotal = $portaltotal + (float)$resgetbillitem['portal_price'];


                          $calculatedportalvalue = (float)$resgetbillitem['portal_price'];

                          $calculateddiscount = 0;

                          if($resgetprojectitem['model'] == "PAYG")
                          {
                            $calculateddiscount = $paygdiscountval;
                          }

                          if($resgetprojectitem['model'] == "RI")
                          {
                            $calculateddiscount = $ridiscountval;
                          }

                          $calculatedpurchasepricevalue = $calculatedportalvalue - (($calculatedportalvalue/100)*$calculateddiscount);

                          $calculatedpurchasepricevalue = round($calculatedpurchasepricevalue,2);
                          echo numberFormat($calculatedpurchasepricevalue,2);
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
                            if(!($calculatedpurchasepricevalue == 0 || $calculatedheaderamount == 0 || $actualtotal == 0))
                            {
                              // echo $resgetbillitem['purchase_price'];
                              $purchasetotal = $purchasetotal + (float)$resgetbillitem['purchase_price'];
                              $subratiopercent = ($calculatedpurchasepricevalue/$calculatedheaderamount)*100;

                              $subratioamount = ($actualtotal/100)*$subratiopercent;

                              echo numberFormat(round($subratioamount,2),2);
                            }
                            else
                            {
                              echo "0";
                            }
                          }
                          else
                          {
                            echo "N/A";
                          }
                        ?>
                      </div>
                    </td>
                    <td colspan="2">
                      <?php

                        if($resgetbill['status'] >= 3)
                        {                      
                          $portalvalue = (float)$resgetbillitem['portal_price'];
                          $salesvalue = (float)$resgetbillitem['sales_price'];
                          $purchasevalue = (float)$resgetbillitem['purchase_price'];


                          if(!($portalvalue == 0 && $purchasevalue == 0))
                          {
                            $discount = 0;

                            if($resgetprojectitem['model'] == "PAYG")
                            {
                              $discount = (float)$paygdiscountval;
                            }

                            if($resgetprojectitem['model'] == "RI")
                            {
                              $discount = (float)$ridiscountval;
                            }

                            $totalpurchasevalue = $portalvalue - (($portalvalue/100)*$discount);


                            if($portalvalue == $totalpurchasevalue)
                            {
                              $totalvalue = 0;
                            }
                            elseif($portalvalue < $totalpurchasevalue)
                            {
                              $totalvalue = (($totalpurchasevalue - $portalvalue)/$totalpurchasevalue)*100;
                            }
                            elseif($portalvalue > $totalpurchasevalue)
                            {
                              $totalvalue = (($portalvalue - $totalpurchasevalue)/$portalvalue)*100;
                            }
                            else
                            {
                              $totalvalue = 0;
                            }


                            $totalpurchasevalue = round($totalpurchasevalue,2);
                            $totalvalue = round($totalvalue,2);
                            $discount = round($discount,2);


                            if($totalpurchasevalue == $purchasevalue)
                            {
                              $background = "background : #4FFFB0;";
                              $discountval = $discount;
                            }
                            elseif($totalpurchasevalue < $purchasevalue)
                            {
                              $background = "background : #FA8072;";
                              $discountval = $totalvalue;
                            }
                            elseif($totalpurchasevalue > $purchasevalue)
                            {
                              $background = "background : #318CE7;";
                              $discountval = $totalvalue;
                            }
                            else
                            {
                              $background = "background : #4FFFB0;";
                              $discountval = $totalvalue;
                            }


                            $discountval = $discountval." %";
                          }
                          else
                          {
                            $totalvalue = "0 %";
                          }
                        }
                        else
                        {
                          $totalvalue = "N/A";
                          $discountval = "N/A";
                        }

                        ?>
                      <div class="table-value-wrapper align-right" style="font-weight: 500;<?php //echo $background; ?>">
                        <?php //echo  $discountval; ?>
                      </div>
                    </td>
                  </tr>
                  <?php
                          $rowcount++;
                        }
                          
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
                $ph++;
              }
            ?>
              <!-- <tr>
                <td colspan="7">
                  <div class="table-value-wrapper align-right">
                      Total
                  </div>
                </td>
                <td>
                  <div class="table-value-wrapper align-right">
                      <?php //echo  $portaltotal; ?>
                  </div>
                </td>
                <td>
                  <div class="table-value-wrapper align-right">
                      <?php //echo  $salestotal; ?>
                  </div>
                </td>
                <td>
                  <div class="table-value-wrapper align-right">
                      <?php //echo  $purchasetotal; ?>
                  </div>
                </td>
                <td></td>
              </tr> -->
          </table>

            <?php


            }

                  
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

        
        <?php


          $sqlgetpurchaseheader = "select distinct(bi.purchase_header_id),
          (select amount from bill_purchase_header where bill_id = ".$resgetbill['id']." AND project_id = ".$projectid." AND purchase_header_id = bi.purchase_header_id) as amount, 
          (select name from purchase_header where id = bi.purchase_header_id) as purchase_header_name
          from bill_item as bi INNER JOIN project_item as pi ON bi.project_item_id = pi.id where bi.project_id = ".$projectid." AND bi.bill_id = ".$resgetbill['id']." AND pi.model = 'RI'";
          $rowgetpurchaseheader = mysqli_query($con, $sqlgetpurchaseheader);

          if (mysqli_num_rows($rowgetpurchaseheader) > 0)
          {
        ?> 

            <!-- Section Divider --><div class="content-section-divider"></div>

          <div style="padding:10px;font-weight: bold;font-size: 1em;text-align: center;"> RI Items </div>

          <!-- RI Amount -->

           <table class="table-element" id="purchase-price-table" cellpadding="0" cellspacing="0" style="font-size:.8em;">
          

            <thead>
              <tr class="align-center">
                <th>SNo</th>
                <th>Product</th>
                <th>Type</th>
                <th>Model</th>
                <th>Description</th>
                <th>Deployed On</th>
                <th>Status /<br> Deactivation<br> Date</th>
                <th>Portal Price (Rs.)</th>
                <th>Calculated <br> Purchase <br>Price (Rs.)</th>
                <!-- <th>Sales Price (Rs.)</th> -->
                <!-- <th>Purchase Price (Rs.)</th> -->
                <th>Actual <br> Purchase <br> Price (Rs.)</th>
                <th>Deviation (%)</th>
              </tr>
            </thead>
            <tbody>

              <?php

            $ph = 0;
            while ($ph <= ($resgetpurchaseheader = mysqli_fetch_array($rowgetpurchaseheader)))
            {
              
        ?>
            <tr class="cloud-purchase-header-row" data-header-row-no="<?php echo $ph; ?>" data-purchase-header-id="<?php echo $resgetpurchaseheader['purchase_header_id'] ?>">
                <td colspan="7"><div class="table-value-wrapper" style="word-wrap: break-word;"><?php echo $resgetpurchaseheader['purchase_header_name']; ?></div> </td>
                <td>
                  <div class="table-value-wrapper align-right">
                  <?php
                      // Get RI Total
                      $sqlgetritotal = "select SUM(portal_price) 
                      from bill_item as bi INNER JOIN project_item as pi ON pi.id = bi.project_item_id where pi.model = 'RI' AND bi.purchase_header_id = ".$resgetpurchaseheader['purchase_header_id']." AND bi.bill_id = ".$billid;
                      $rowgetritotal = mysqli_query($con, $sqlgetritotal);
                      $resgetritotal = mysqli_fetch_array($rowgetritotal);

                      if($resgetritotal['SUM(portal_price)'] == "")
                      { $risum = 0; }
                      else
                      { 
                        $risum = (float)$resgetritotal['SUM(portal_price)']; 
                        // $ritotalval = $risum-(($risum/100)*$ridiscountval);

                      }
                    
                                            
                      //Calculate Portal Header Total
                      $calculatedpheaderamount = round($risum,2);
                      echo numberFormat($calculatedpheaderamount,2);
                  ?>
                  </div>
                </td>
                <td>
                  <div class="table-value-wrapper align-right">
                  <?php
                      // Get RI Total
                      $sqlgetritotal = "select SUM(portal_price) 
                      from bill_item as bi INNER JOIN project_item as pi ON pi.id = bi.project_item_id where pi.model = 'RI' AND bi.purchase_header_id = ".$resgetpurchaseheader['purchase_header_id']." AND bi.bill_id = ".$billid;
                      $rowgetritotal = mysqli_query($con, $sqlgetritotal);
                      $resgetritotal = mysqli_fetch_array($rowgetritotal);

                      if($resgetritotal['SUM(portal_price)'] == "")
                      { $ritotalval = 0; }
                      else
                      { 
                        $risum = (float)$resgetritotal['SUM(portal_price)']; 
                        $ritotalval = $risum-(($risum/100)*$ridiscountval);

                      }

                      
                      //Calculate Header Total
                      $calculatedheaderamount = round($ritotalval,2);
                      echo numberFormat($calculatedheaderamount,2);
                  ?>
                  </div>
                </td>
                <td>
                  <div class="table-value-wrapper align-right">
                      <?php 
                        if($resgetbill['status'] >= 3)
                        {
                          $actualtotal = round((float)$resgetpurchaseheader['amount'],2);
                          
                          if($calculatedheaderamount < $actualtotal)
                          {
                            $calculatedriheaderamountgrandtotal += $calculatedheaderamount;
                            $actualheaderamountgrandtotal += $actualtotal;
                          }
                          echo numberFormat($actualtotal,2); 
                        }
                        else
                        {
                          echo "N/A";
                        }
                      ?>
                  </div>
                </td>
                <td>
                  <?php

                   if($resgetbill['status'] >= 3)
                    {

                      if($calculatedheaderamount == $actualtotal)
                      {
                        $headertotalvalue = 0;
                        $background = "background : #4FFFB0;";
                      }
                      elseif($calculatedheaderamount < $actualtotal)
                      {
                        $headertotalvalue = (($actualtotal - $calculatedheaderamount)/$actualtotal)*100;
                        $background = "background : #FA8072;";
                      }
                      elseif($calculatedheaderamount > $actualtotal)
                      {
                        $headertotalvalue = (($calculatedheaderamount - $actualtotal)/$calculatedheaderamount)*100;
                        $background = "background : #318CE7;";
                      }
                      else
                      {
                        $headertotalvalue = 0;
                        $background = "background : #FA8072;";
                      }

                      $headertotalvalue = round($headertotalvalue,2); 

                      if($headertotalvalue <= 0.1)
                      {
                         $headertotalvalue = 0;                         
                        $background = "background : #4FFFB0";
                      }

                      $headertotalvalue = $headertotalvalue." %";
                    }
                    else
                    {
                      $headertotalvalue = "N/A";
                    }



                ?>
                  <div class="table-value-wrapper align-right" style="<?php echo $background; ?>">
                      <?php 
                        echo $headertotalvalue;
                      ?>
                  </div>
                </td>
              </tr>

          <?php
                  
              $sqlgetbillitem = "select *, 
              (select model from project_item where id = bi.project_item_id) as item_model 
              from bill_item as bi INNER JOIN product pr ON pr.id = (select product from project_item where id = bi.project_item_id) where bi.bill_id = ".$resgetbill['id']." AND bi.project_id = ".$projectid." AND bi.purchase_header_id = ".$resgetpurchaseheader['purchase_header_id']." order by pr.sub_category";
              $rowgetbillitem = mysqli_query($con, $sqlgetbillitem);

              if (mysqli_num_rows($rowgetbillitem) > 0)
              {

                $i = 0;
                $rowcount = 1;
                $portaltotal = 0;
                $salestotal = 0;
                $purchasetotal = 0;
                while ($i <= ($resgetbillitem = mysqli_fetch_array($rowgetbillitem)))
                {

                  if($resgetbillitem['item_model'] == "RI")
                  {
                    $sqlgetprojectitem = "select id,
                    (select name from cloud_category where value = (select sub_category from product where id = pi.product)) as category_name,
                    (select name from product where id = pi.product) as product_name,
                    (select name from distributor where id = pi.distributor) as distributor_name,
                    deployment_start,model,unit_price,quantity,deployment_end,status,description,
                    (select name from product where id = pi.deployed_product) as deployed_product_name,
                    (select name from unit_measure where value = pi.unit_measure) as unit_measure_name,
                    case when pi.status = 1 then 'fa-toggle-on' else 'fa-toggle-off' end as product_status
                     from project_item as pi where id = ".$resgetbillitem['project_item_id']." AND project_id = ".$projectid;


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
                        <?php echo $resgetprojectitem['category_name']; ?>
                      </div>
                    </td>
                    <td>
                      <div class="table-value-wrapper">
                        <?php
                          if($resgetprojectitem['model'] == "")
                          {
                            echo "N/A";
                          }
                          else
                          {
                           echo $resgetprojectitem['model']; 
                          }
                        ?>
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
                          echo numberFormat((float)$resgetbillitem['portal_price'],2);
                        ?>
                      </div>
                    </td>
                    <td>
                      <div class="table-value-wrapper align-right">
                        <?php
                          // echo (float)$resgetbillitem['portal_price'];

                          // $portaltotal = $portaltotal + (float)$resgetbillitem['portal_price'];


                          $calculatedportalvalue = (float)$resgetbillitem['portal_price'];

                          $calculateddiscount = 0;

                          if($resgetprojectitem['model'] == "PAYG")
                          {
                            $calculateddiscount = $paygdiscountval;
                          }

                          if($resgetprojectitem['model'] == "RI")
                          {
                            $calculateddiscount = $ridiscountval;
                          }

                          $calculatedpurchasepricevalue = $calculatedportalvalue - (($calculatedportalvalue/100)*$calculateddiscount);

                          $calculatedpurchasepricevalue = round($calculatedpurchasepricevalue,2);
                          echo numberFormat($calculatedpurchasepricevalue,2);
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
                            if(!($actualtotal == 0 && $calculatedpurchasepricevalue == 0 && $calculatedheaderamount == 0))
                            {
                              // echo $resgetbillitem['purchase_price'];
                              $purchasetotal = $purchasetotal + (float)$resgetbillitem['purchase_price'];

                              $subratiopercent = ($calculatedpurchasepricevalue/$calculatedheaderamount)*100;

                              $subratioamount = ($actualtotal/100)*$subratiopercent;

                              echo numberFormat(round($subratioamount,2),2);
                            }
                            else
                            {
                              echo "0";
                            }
                          }
                          else
                          {
                            echo "N/A";
                          }
                        ?>
                      </div>
                    </td>
                    <td colspan="2">
                      <?php

                        if($resgetbill['status'] >= 3)
                        {                      
                          $portalvalue = (float)$resgetbillitem['portal_price'];
                          $salesvalue = (float)$resgetbillitem['sales_price'];
                          $purchasevalue = (float)$resgetbillitem['purchase_price'];


                          if(!($portalvalue == 0 && $purchasevalue == 0))
                          {
                            $discount = 0;

                            if($resgetprojectitem['model'] == "PAYG")
                            {
                              $discount = (float)$paygdiscountval;
                            }

                            if($resgetprojectitem['model'] == "RI")
                            {
                              $discount = (float)$ridiscountval;
                            }

                            $totalpurchasevalue = $portalvalue - (($portalvalue/100)*$discount);


                            if($portalvalue == $totalpurchasevalue)
                            {
                              $totalvalue = 0;
                            }
                            elseif($portalvalue < $totalpurchasevalue)
                            {
                              $totalvalue = (($totalpurchasevalue - $portalvalue)/$totalpurchasevalue)*100;
                            }
                            elseif($portalvalue > $totalpurchasevalue)
                            {
                              $totalvalue = (($portalvalue - $totalpurchasevalue)/$portalvalue)*100;
                            }
                            else
                            {
                              $totalvalue = 0;
                            }


                            $totalpurchasevalue = round($totalpurchasevalue,2);
                            $totalvalue = round($totalvalue,2);
                            $discount = round($discount,2);


                            if($totalpurchasevalue == $purchasevalue)
                            {
                              $background = "background : #4FFFB0;";
                              $discountval = $discount;
                            }
                            elseif($totalpurchasevalue < $purchasevalue)
                            {
                              $background = "background : #FA8072;";
                              $discountval = $totalvalue;
                            }
                            elseif($totalpurchasevalue > $purchasevalue)
                            {
                              $background = "background : #318CE7;";
                              $discountval = $totalvalue;
                            }
                            else
                            {
                              $background = "background : #4FFFB0;";
                              $discountval = $totalvalue;
                            }


                            $discountval = $discountval." %";
                          }
                          else
                          {
                            $totalvalue = "0 %";
                          }
                        }
                        else
                        {
                          $totalvalue = "N/A";
                          $discountval = "N/A";
                        }

                        ?>
                      <div class="table-value-wrapper align-right" style="font-weight: 500;<?php //echo $background; ?>">
                        <?php //echo  $discountval; ?>
                      </div>
                    </td>
                  </tr>
                  <?php
                          $rowcount++;
                        }
                          
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
                $ph++;
              }
            ?>
              <!-- <tr>
                <td colspan="7">
                  <div class="table-value-wrapper align-right">
                      Total
                  </div>
                </td>
                <td>
                  <div class="table-value-wrapper align-right">
                      <?php //echo  $portaltotal; ?>
                  </div>
                </td>
                <td>
                  <div class="table-value-wrapper align-right">
                      <?php //echo  $salestotal; ?>
                  </div>
                </td>
                <td>
                  <div class="table-value-wrapper align-right">
                      <?php //echo  $purchasetotal; ?>
                  </div>
                </td>
                <td></td>
              </tr> -->
          </table>

            <?php


            }

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


      $debitnoteamount = 0;

      $sqlgetdebitnote = "select SUM(amount) 
        from debit_note where project_id = ".$projectid." AND invoice_id IN (select invoice_id from bill_invoice_mapping where project_id = ".$projectid." AND bill_id = ".$billid.") ";
      $rowgetdebitnote = mysqli_query($con, $sqlgetdebitnote);
      if (mysqli_num_rows($rowgetdebitnote) > 0)
      {
        $resgetdebitnote = mysqli_fetch_array($rowgetdebitnote);

        if($resgetdebitnote['SUM(amount)'] != NULL && $resgetdebitnote['SUM(amount)'] != "")
        {
          $debitnoteamount = (float)$resgetdebitnote['SUM(amount)'];
        }

      }

      $calculatedheaderamountgrandtotal = $calculatedpaygheaderamountgrandtotal + $calculatedriheaderamountgrandtotal;

      $calculatedheaderamountgrandtotal += $debitnoteamount;

      $amountdifferencenegative = 0;
      if($calculatedheaderamountgrandtotal == $actualheaderamountgrandtotal)
      {
       $amountdifference = 0;
      }
      elseif($calculatedheaderamountgrandtotal >= $actualheaderamountgrandtotal)
      {
       $amountdifference = 0;
      }
      elseif($calculatedheaderamountgrandtotal < $actualheaderamountgrandtotal)
      {
       $amountdifference = $actualheaderamountgrandtotal - $calculatedheaderamountgrandtotal;
       $amountdifferencenegative = 1;
      }
      else
      {
        $amountdifference = 0;
      }
  ?>
      <input type="hidden" id="calculated-actual-difference" name="calculated-actual-difference" value="<?php echo $amountdifference; ?>" data-has-negative-amount="<?php echo $amountdifferencenegative; ?>">
  <?php
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
                  mysqli_close($con);

?>


          <!-- RI Amount -->




            
          <div style="margin-top:10px;">
            <div style="vertical-align: middle;height:15px;width: 20px;background:#FA8072;display: inline-block;"></div> : Negative Deviation &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;  
            <div style="vertical-align: middle;height:15px;width: 20px;background:#4FFFB0;display: inline-block;"></div> : Equal &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp; 
            <div style="vertical-align: middle;height:15px;width: 20px;background:#318CE7;display: inline-block;"></div> : Positive Deviation 
          </div>

          <div style="margin-top:10px;">
            <span style="color:orangered;font-size: 2em;vertical-align: middle;">*</span>
            NOTE : Values within +/- 0.1% are considered for rounding off the calculation and will be marked in green.
          </div>
          
      


  