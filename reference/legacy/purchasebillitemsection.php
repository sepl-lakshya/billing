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


      $sqlgetbill = "select id,hash,status,ri_discount,payg_discount from bill where project_id = ".$pid." AND month = ".$month." AND year = ".$year." AND status >= 2 AND is_deleted = 0";
      $rowgetbill = mysqli_query($con, $sqlgetbill);

      if(mysqli_num_rows($rowgetbill) > 0)
      {
        $resgetbill = mysqli_fetch_array($rowgetbill);

        $sqlgetcloudinward = "select id from cloud_inward where project_id = ".$pid." AND bill_id = ".$resgetbill['id']." AND is_cancelled = 0";
        $rowgetcloudinward = mysqli_query($con, $sqlgetcloudinward);

        if(mysqli_num_rows($rowgetcloudinward) > 0)
        {
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
          
          $ridiscountval = (float)$resgetbill['ri_discount'];
          $paygdiscountval = (float)$resgetbill['payg_discount'];
         

?>   
        <button class="print-btn" onclick="printDiv('purchase-price-modal');">Print</button>
        <div class="modal-heading-wrapper">

        Purchase Invoice Details for <span style="font-size:1.1em; text-decoration: underline;"><?php echo $resgetmonth['name']." ".$resgetyear['name']; ?></span>
        </div>
          <input type="hidden" id="purchase-price-month" name="purchase-price-month" value="<?php echo $month; ?>">      
          <input type="hidden" id="purchase-price-year" name="purchase-price-year" value="<?php echo $year; ?>">      
          <input type="hidden" id="purchase-bill-id" name="purchase-bill-id" value="<?php echo $resgetbill['id']; ?>">  

        <?php
          if($editprices)
          {
        ?>    
        <div class="section-content-wrapper no-print">
            
          <form class="ce-form" id="ce-add-purchase-bill-invoice-form" style="width:100%;">
            <input type="hidden" id="add-purchase-bill-invoice-project-id" name="add-purchase-bill-invoice-project-id" value="<?php echo $pid ?>">
            <input type="hidden" id="add-purchase-bill-invoice-bill-id" name="add-purchase-bill-invoice-bill-id" value="<?php echo $resgetbill['id']; ?>">
              <div class="project-products-list-item-form">
                <div class="ce-form-controls ce-form-control-col-2">
                  <div class="ce-form-label">Invoice : <a href="javascript:void(0);" class="show-add-invoice-form-modal-btn" style="font-size:.8em; font-weight: normal;color: blue;margin-left: 20px;text-decoration: underline;" data-load-select="1" >Add New Invoice</a></div>
                  <div class="ce-form-input">
                    <select id="add-purchase-bill-invoice-id" name="add-purchase-bill-invoice-id">
                    </select>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-4">
                  <div class="ce-form-input align-right">
                      <input type="submit" id="ce-add-purchase-bill-invoice-form-submit" name="ce-add-purchase-bill-invoice-form-submit" value="Add Invoice" >
                  </div>
                </div>
              </div>
          </form>   
                   
        </div>
        <script type="text/javascript">
          getUnassignedPurchaseInvoice();
        </script>

        <?php 
          } 
        ?>
          
        <div class="section-content-wrapper">
          <div id="purchase-bill-invoice-table-section">
              
          </div>
          <div class="ce-spinner-overlay" id="purchase-bill-invoice-table-section-loading" style="text-align:center;display:none;">
              <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
          </div>
        </div>
        <script type="text/javascript">
          getPurchaseBillInvoices(<?php echo $resgetbill['id']; ?>);
        </script>

        <div class="modal-heading-wrapper">
        Purchase Price of Items for <span style="font-size:1.1em; text-decoration: underline;"><?php echo $resgetmonth['name']." ".$resgetyear['name']; ?></span>
        </div>
        <div class="align-right" style="margin-bottom: 10px;">
          RI Discount : <?php echo $ridiscountval; ?> % &nbsp;&nbsp; | &nbsp;&nbsp; PAYG Discount : <?php echo $paygdiscountval; ?> %
        </div>

        <table class="table-element" id="purchase-price-table" cellpadding="0" cellspacing="0" style="font-size:.8em;">
          
        <?php


          $sqlgetpurchaseheader = "select purchase_header_id,amount, 
          (select name from purchase_header where id = bph.purchase_header_id) as purchase_header_name
          from bill_purchase_header as bph where project_id = ".$pid." AND bill_id = ".$resgetbill['id'];
          $rowgetpurchaseheader = mysqli_query($con, $sqlgetpurchaseheader);

          if (mysqli_num_rows($rowgetpurchaseheader) > 0)
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
                <th>Status/<br>Deactivation Date</th>
                <th>Portal Price</th>
                <th>Purchase Price excl. GST</th>
              </tr>
            </thead>
            <tbody>
        <?php

            $ph = 0;
            $rowcount = 1;
            $calculatedheaderamountgrandtotal = 0;
            $actualheaderamountgrandtotal = 0;
            while ($ph <= ($resgetpurchaseheader = mysqli_fetch_array($rowgetpurchaseheader)))
            {
              
        ?>
            <tr class="cloud-purchase-header-row" data-header-row-no="<?php echo $ph; ?>" data-purchase-header-id="<?php echo $resgetpurchaseheader['purchase_header_id'] ?>">
                <td colspan="8"><div class="table-value-wrapper" style="word-wrap: break-word;"><?php echo $resgetpurchaseheader['purchase_header_name']; ?></div> </td>
                <td>
                  <?php
                    
                    // Get RI Total
                      $sqlgetritotal = "select SUM(portal_price) 
                      from bill_item as bi INNER JOIN project_item as pi ON pi.id = bi.project_item_id where pi.model = 'RI' AND bi.purchase_header_id = ".$resgetpurchaseheader['purchase_header_id']." AND bi.bill_id = ".$resgetbill['id'];
                      $rowgetritotal = mysqli_query($con, $sqlgetritotal);
                      $resgetritotal = mysqli_fetch_array($rowgetritotal);

                      if($resgetritotal['SUM(portal_price)'] == "")
                      { $ritotalval = 0; }
                      else
                      { 
                        $risum = (float)$resgetritotal['SUM(portal_price)']; 
                        $ritotalval = $risum-(($risum/100)*$ridiscountval);
                      }

                      
                      // Get PAYG Total
                      $sqlgetpaygtotal = "select SUM(portal_price) 
                      from bill_item as bi INNER JOIN project_item as pi ON pi.id = bi.project_item_id where pi.model = 'PAYG' AND bi.purchase_header_id = ".$resgetpurchaseheader['purchase_header_id']." AND bi.bill_id = ".$resgetbill['id'];
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

                      $purchaseheadertotal = $paygtotalval + $ritotalval;



                    if((int)$resgetbill['status'] <= 2 || $resgetpurchaseheader['amount'] == NULL)
                    {
                      $headeramount = $purchaseheadertotal;
                    }
                    else
                    {
                      $headeramount = $resgetpurchaseheader['amount'];
                      $calculatedheaderamountgrandtotal += $purchaseheadertotal; 
                      $actualheaderamountgrandtotal += round($resgetpurchaseheader['amount'],2); 
                    }

                  
                    if($editprices)
                    {
                  ?>
                  <input type="text" id="purchase-header-value-<?php echo $ph; ?>" name="purchase-header-value-<?php echo $ph; ?>" value="<?php echo round($headeramount,2); ?>" style="text-align: right;"> 
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


              $sqlgetbillitem = "select * from bill_item as bi INNER JOIN product pr ON pr.id = (select product from project_item where id = bi.project_item_id) where bill_id = ".$resgetbill['id']." AND project_id = ".$pid." AND purchase_header_id = ".$resgetpurchaseheader['purchase_header_id']." order by pr.sub_category";
              $rowgetbillitem = mysqli_query($con, $sqlgetbillitem);

              if (mysqli_num_rows($rowgetbillitem) > 0)
              {

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
                    <div class="table-value-wrapper">
                      <?php
                        echo numberFormat(round($resgetbillitem['portal_price'],2),2);
                      ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper align-right"style="padding-right: 10px;">
                    <?php 
                      // if($resgetbillitem['purchase_price'] == null || $resgetbillitem['purchase_price'] == "")
                      // {
                      //   $purchasepricevalue = "";
                      // }
                      // else
                      // {
                      //   $purchasepricevalue = $resgetbillitem['purchase_price'];
                      // }

                      $portalvalue = (float)$resgetbillitem['portal_price'];

                      $discount = 0;

                      if($resgetprojectitem['model'] == "PAYG")
                      {
                        $discount = $paygdiscountval;
                      }

                      if($resgetprojectitem['model'] == "RI")
                      {
                        $discount = $ridiscountval;
                      }

                      $purchasepricevalue = $portalvalue - (($portalvalue/100)*$discount);
                      echo $purchasepricevalue = round($purchasepricevalue,2);
                      
                    ?>

                      <input type="hidden" id="purchase-price-value-<?php echo $rowcount; ?>" name="purchase-price-value-<?php echo $rowcount; ?>" value="<?php echo $purchasepricevalue; ?>">
                    </div>
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
                $ph++;
              }

      ?>
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
        <?php
          if($editprices)
          {
        ?>
        <script type="text/javascript">

        </script>
        <div class="modal-change-btn-wrapper">
        <!-- <div class="ce-form-controls ce-form-control-col-2">
          <div class="ce-form-input">
              <a class="button show-modal-btn show-modal-back-btn" data-modal-id="purchase-price-month-modal" href="javascript:void(0);" >< Back</a>
          </div>
        </div> -->
        
        <div class="ce-form-controls ce-form-control-col-1 no-print" >
          <div class="ce-form-input align-right" id="ce-submit-purchase-price-btn-wrapper">
            <input type="button" id="ce-submit-purchase-price-btn" name="ce-submit-purchase-price-btn" value="Save & Next >">
          </div>
        </div>
      </div>
      <?php
        }
      ?>


  