  <?php
    if (session_status() == PHP_SESSION_NONE) 
    {
      session_start();
    }
      require_once "include/config.php";
      $con = connectMySQL();
      
      if(isset($_POST['projectid']) && $_POST['projectid'] != "")
      {
        $pid = $_POST['projectid'];

        $portalcolumngrandtotal = 0;
        $salescolumngrandtotal = 0;
        $purchasecolumngrandtotal = 0;
        $invoicecolumngrandtotal = 0;

  ?>
        
            <table class="table-element" cellpadding="0" cellspacing="0" style="font-size: .8em;">
              
                  <?php
                    $sqlgetbill = "select id,year,month,status,ri_discount,payg_discount,progress,
                    purchase_header_status,
                    (select percentage from billing_progress where value = b.progress) as progress_val,
                    (select name from billing_progress where value = b.progress) as progress_name,
                    (select name from month where value = b.month) as month_val,
                    (select SUM(portal_price) from bill_item where bill_id = b.id) as portal_total,
                    (select SUM(sales_price) from bill_item where bill_id = b.id) as sales_total,
                    (select SUM(amount) from bill_purchase_header where bill_id = b.id AND project_id = ".$pid.") as purchase_total,
                    (select SUM(amount) from purchase_invoice where id IN (select invoice_id from bill_invoice_mapping where bill_id = b.id AND project_id = ".$pid.")) as invoice_total,
                    (select COUNT(id) from purchase_invoice where id IN (select invoice_id from bill_invoice_mapping where bill_id = b.id AND project_id = ".$pid.")) as invoice_count,
                    (select SUM(amount) from purchase_invoice where id IN (select invoice_id from invoice_cloud_inward where bill_id = b.id AND project_id = ".$pid." AND is_cancelled = 0)) as invoice_ci_total
                    from bill as b where project_id = ".$pid." AND is_deleted = 0 order by year,month";
                    $rowgetbill = mysqli_query($con, $sqlgetbill);

                    if (mysqli_num_rows($rowgetbill) > 0)
                    {

                ?>
              <thead>
                <tr>
                  <th>Month</th>
                  <th>Year</th>
                  <th>Portal Total <br> excl. GST (Rs.)</th>
                  <th>Sales Total <br> excl. GST (Rs.)</th>
                  <th>Purchase Total <br> excl. GST (Rs.)</th>
                  <th>Invoices Total <br> excl. GST (Rs.)</th>
                  <th>RI Discount (%)</th>
                  <th>PAYG Discount (%)</th>
                  <th>Progress (%)</th>
                  <th>CI</th>
                  <th>CO</th>
                  <th class="no-print-action-btn">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                      $i = 0;
                      while ($i <= ($resgetbill = mysqli_fetch_array($rowgetbill)))
                      {
                        $sqlgetcloudinward = "select id from cloud_inward where project_id = ".$pid." AND bill_id = ".$resgetbill['id']." AND is_cancelled = 0";
                        $rowgetcloudinward = mysqli_query($con, $sqlgetcloudinward);

                        if(mysqli_num_rows($rowgetcloudinward) > 0)
                        {
                          $resgetcloudinward = mysqli_fetch_array($rowgetcloudinward);
                          $changeci = false;
                        }
                        else
                        {
                          $changeci = true;
                        }

                        $sqlgetcloudoutward = "select id from cloud_outward where project_id = ".$pid." AND bill_id = ".$resgetbill['id']." AND is_cancelled = 0";
                        $rowgetcloudoutward = mysqli_query($con, $sqlgetcloudoutward);

                        if(mysqli_num_rows($rowgetcloudoutward) > 0)
                        {
                          $resgetcloudoutward = mysqli_fetch_array($rowgetcloudoutward);
                          $changeco = false;
                        }
                        else
                        {
                          $changeco = true;
                        }


                        $discountdate = $resgetbill['year']."-".$resgetbill['month']."-15";

                        $sqlgetdiscount = "select ri_discount,payg_discount from project_discount where from_date < '".$discountdate."' AND to_date > '".$discountdate."' AND project_id = ".$pid;
                        $rowgetdiscount = mysqli_query($con, $sqlgetdiscount);
                        if(mysqli_num_rows($rowgetdiscount) > 0)
                        {
                          $resgetdiscount = mysqli_fetch_array($rowgetdiscount);
                          $ridiscountval =  $resgetdiscount['ri_discount'];
                          $paygdiscountval =  $resgetdiscount['payg_discount'];
                        }
                        else
                        {
                          $sqlgetlastdiscount = "select ri_discount,payg_discount from project_discount where project_id = ".$pid." AND to_date IS NULL";
                          $rowgetlastdiscount = mysqli_query($con, $sqlgetlastdiscount);
                          $resgetlastdiscount = mysqli_fetch_array($rowgetlastdiscount);
                          $ridiscountval =  $resgetlastdiscount['ri_discount'];
                          $paygdiscountval =  $resgetlastdiscount['payg_discount'];
                        }
                  ?>
                  <tr>
                      <?php   
                        $deviation = false;
                        $negativedeviation = false;

                        if($resgetbill['status'] >= 3 && $resgetbill['purchase_header_status'] == 1)
                        {  
                          $sqlgetpurchaseheader = "select purchase_header_id,amount 
                          from bill_purchase_header as bph where project_id = ".$pid." AND bill_id = ".$resgetbill['id'];
                          $rowgetpurchaseheader = mysqli_query($con, $sqlgetpurchaseheader);

                          if (mysqli_num_rows($rowgetpurchaseheader) > 0)
                          {
                            $ph = 0;
                            $negativedeviation = false;
                            $calculatedheaderamountgrandtotal = 0;
                            $actualheaderamountgrandtotal = 0;

                            while ($ph <= ($resgetpurchaseheader = mysqli_fetch_array($rowgetpurchaseheader)))
                            {
                              // Get RI Total
                              $sqlgetritotal = "select SUM(portal_price) 
                              from bill_item as bi INNER JOIN project_item as pi ON pi.id = bi.project_item_id where pi.model = 'RI' AND bi.purchase_header_id = ".$resgetpurchaseheader['purchase_header_id']." AND bi.bill_id = ".$resgetbill['id'];
                              $rowgetritotal = mysqli_query($con, $sqlgetritotal);
                              if (mysqli_num_rows($rowgetritotal) > 0)
                              {
                                $resgetritotal = mysqli_fetch_array($rowgetritotal);

                                if($resgetritotal['SUM(portal_price)'] == "")
                                { $ritotalval = 0; }
                                else
                                { 
                                  $risum = (float)$resgetritotal['SUM(portal_price)']; 
                                  $ritotalval = $risum-(($risum/100)*$ridiscountval);
                                }
                              }
                              else
                              {
                                $ritotalval = 0;
                              }


                              // Get PAYG Total
                              $sqlgetpaygtotal = "select SUM(portal_price) 
                              from bill_item as bi INNER JOIN project_item as pi ON pi.id = bi.project_item_id where pi.model = 'PAYG' AND bi.purchase_header_id = ".$resgetpurchaseheader['purchase_header_id']." AND bi.bill_id = ".$resgetbill['id'];
                              $rowgetpaygtotal = mysqli_query($con, $sqlgetpaygtotal);
                              if (mysqli_num_rows($rowgetpaygtotal) > 0)
                              {
                                $resgetpaygtotal = mysqli_fetch_array($rowgetpaygtotal);
                                if($resgetpaygtotal['SUM(portal_price)'] == "")
                                { $paygtotalval = 0; }
                                else
                                { 
                                  $paygsum = (float)$resgetpaygtotal['SUM(portal_price)']; 
                                  $paygtotalval = $paygsum - (($paygsum/100)*$paygdiscountval);
                                }
                              }
                              else
                              {
                                $paygtotalval = 0;
                              }

                              //Calculate Header Total
                              $purchaseheadersumtotal = round($paygtotalval,2) + round($ritotalval,2);
                              $calculatedheaderamount = round($purchaseheadersumtotal,2);
                              // echo "<br>".$calculatedheaderamount;
                              //Actual total
                              $actualtotal = round((float)$resgetpurchaseheader['amount'],2);
                              // echo "<br>".$actualtotal;

                              // Check for Deviation


                              if($calculatedheaderamount < $actualtotal)
                              {
                                $actualheaderamountgrandtotal += $actualtotal;
                                $calculatedheaderamountgrandtotal += $calculatedheaderamount;
                              }


                              $ph++;
                            }

                            $debitnoteamount = 0;

                            $sqlgetdebitnote = "select SUM(amount) 
                              from debit_note where project_id = ".$pid." AND invoice_id IN (select invoice_id from bill_invoice_mapping where project_id = ".$pid." AND bill_id = ".$resgetbill['id'].") ";
                            $rowgetdebitnote = mysqli_query($con, $sqlgetdebitnote);
                            if (mysqli_num_rows($rowgetdebitnote) > 0)
                            {
                              $resgetdebitnote = mysqli_fetch_array($rowgetdebitnote);

                              if($resgetdebitnote['SUM(amount)'] != NULL && $resgetdebitnote['SUM(amount)'] != "")
                              {
                                $debitnoteamount = $resgetdebitnote['SUM(amount)'];
                              }

                            }

                            // Check For Total Deviation
                            if($calculatedheaderamountgrandtotal == $actualheaderamountgrandtotal)
                            {
                              $headertotalvalue = 0;
                              $bordercolor = " #4FFFB0;";
                            }
                            elseif($calculatedheaderamountgrandtotal < $actualheaderamountgrandtotal)
                            {
                              $headertotalvalue = (($actualheaderamountgrandtotal - $calculatedheaderamountgrandtotal)/$actualheaderamountgrandtotal)*100;
                              $bordercolor = " #FA8072;";
                              $negativedeviation = true;
                            }
                            elseif($calculatedheaderamountgrandtotal > $actualheaderamountgrandtotal)
                            {
                              $headertotalvalue = (($calculatedheaderamountgrandtotal - $actualheaderamountgrandtotal)/$calculatedheaderamountgrandtotal)*100;
                              $bordercolor = " #4FFFB0;";
                            }
                            else
                            {
                              $headertotalvalue = 0;
                              $bordercolor = " #FA8072;";
                            }

                            
                            $headertotalvalue = round($headertotalvalue,2); 

                            // if($headertotalvalue <= 0.1)
                            // {
                            //   $negativedeviation = false;
                            //   $headertotalvalue = 0;                         
                            //   $bordercolor = " #4FFFB0;";
                            // }

                            if($negativedeviation)
                            {

                              $calculatedheaderamountgrandtotal += $debitnoteamount;
                              if($calculatedheaderamountgrandtotal >= $actualheaderamountgrandtotal)
                              {

                                $bordercolor = " #FFC72C;";
                                $headertotalvalue = (($calculatedheaderamountgrandtotal - $actualheaderamountgrandtotal)/$calculatedheaderamountgrandtotal)*100;


                                $cntotal = 0;
                                $dntotal = 0;
                                
                                $sqlgetdebitnote = "select amount,
                                (select SUM(amount) from credit_note where debit_note_id = dn.id) as cn_total
                                from debit_note as dn where bill_id = ".$resgetbill['id'];
                                $rowgetdebitnote = mysqli_query($con, $sqlgetdebitnote);
                                if (mysqli_num_rows($rowgetdebitnote) > 0)
                                {
                                  $hasdn = true;
                                  $dn = 0;
                                  while ($dn <= ($resgetdebitnote = mysqli_fetch_array($rowgetdebitnote)))
                                  {

                                    $cntotal += (float)$resgetdebitnote['cn_total'];
                                    $dntotal += (float)$resgetdebitnote['amount'];

                                    $dn++;
                                  }
                                }
                                else
                                {
                                  $hasdn = false;
                                }

                                if($hasdn)
                                {
                                  if($cntotal >= $dntotal)
                                  {
                                    $bordercolor = "#007FFF";
                                  }
                                }

                              }
                              else
                              {
                                $deviation = true;
                              }

                            }

                          }
                        }
                        else
                        {
                          $deviation = true;
                          $bordercolor = "grey;";
                        }


                      ?>
                    <td style="border-left: 5px solid <?php echo $bordercolor; ?>;">
                      <div class="table-value-wrapper align-center" >
                        <?php echo $resgetbill['month_val']; ?>
                      </div>
                    </td>
                    <td>
                      <div class="table-value-wrapper align-center">
                        <?php echo $resgetbill['year']; ?>
                      </div>
                    </td>
                    <td>
                      <div class="table-value-wrapper  align-right">
                          <a href="javascript:void(0);" class="show-cloud-portal-bill-section" data-bill-month="<?php echo $resgetbill['month']; ?>" data-bill-year="<?php echo $resgetbill['year']; ?>" style="text-decoration:underline;"><?php echo numberFormat(round($resgetbill['portal_total'],2),2); ?></a>
                          <?php 
                            if($changeci)
                            {
                        ?>
                          &nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" class="update-portal-bill-item-btn no-print" data-bill-id="<?php echo $resgetbill['id']; ?>" data-month="<?php echo $resgetbill['month']; ?>" data-year="<?php echo $resgetbill['year']; ?>" style="text-decoration:underline;color: #007FFF;">Refresh</a> 
                        <?php
                            }

                            $portalcolumngrandtotal += (float)$resgetbill['portal_total'];
                          ?>
                      </div>
                    </td> 
                    <td>
                      <div class="table-value-wrapper align-right">
                        <?php 
                          if($resgetbill['status'] >= 2)
                          {
                            $salesheadertotal = 0;
                            $sqlgetheader = "select amount from bill_header where bill_id = ".$resgetbill['id']." AND project_id = ".$pid;
                            $rowgetheader = mysqli_query($con, $sqlgetheader);
                            if (mysqli_num_rows($rowgetheader) > 0)
                            {
                              $h = 0;
                              while ($h <= ($resgetheader = mysqli_fetch_array($rowgetheader)))
                              {
                                $salesheadertotal = $salesheadertotal+(float)$resgetheader['amount'];
                                $h++;
                              }
                            }
                            
                        ?>
                          <a href="javascript:void(0);" class="show-cloud-sale-bill-section" data-bill-month="<?php echo $resgetbill['month']; ?>" data-bill-year="<?php echo $resgetbill['year']; ?>" style="text-decoration:underline;"><?php echo numberFormat(round($salesheadertotal,2),2); ?></a>
                        <?php
                            $salescolumngrandtotal += $salesheadertotal;

                          }
                          else
                          {
                            echo "Not Added";
                          }

                        ?>
                      </div>
                    </td>
                    <td>
                      <div class="table-value-wrapper align-right">
                        <?php 
                          if($resgetbill['status'] >= 3)
                          {

                        ?>
                          <a href="javascript:void(0);" class="show-cloud-purchase-bill-section" data-bill-month="<?php echo $resgetbill['month']; ?>" data-bill-year="<?php echo $resgetbill['year']; ?>" style="text-decoration:underline;"><?php echo numberFormat(round($resgetbill['purchase_total'],2),2); ?></a>
                        <?php
                            if($resgetbill['purchase_header_status'] == 1 )
                            {
                              if($changeci)
                              {
                        ?>
                          &nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" class="reset-purchase-price-btn no-print" data-bill-id="<?php echo $resgetbill['id']; ?>" style="text-decoration:underline;color: #007FFF;">Clear</a> 
                        <?php
                              }
                            }
                            $purchasecolumngrandtotal += (float)$resgetbill['purchase_total'];  
                          }
                          else
                          {
                            echo "Not Added";
                          } 
                        ?>
                      </div>
                    </td>
                    <td>
                      <div class="table-value-wrapper align-right">  
                      <?php 
                          if($resgetbill['status'] >= 3 && (int)$resgetbill['invoice_count'] > 0)
                          {
                            echo numberFormat(round($resgetbill['invoice_total'],2),2);
                            $difference = $resgetbill['purchase_total'] - $resgetbill['invoice_total'];
                            if($difference < 1 && $difference > -1)
                            {
                            ?>
                              <a href="javascript:void(0);" style="color:green;font-size: 1.5em;cursor: default;" title="Purchase Total OK"><i class="fa fa-check" aria-hidden="true"></i></a>
                            <?php
                            } 
                            $invoicecolumngrandtotal += (float)$resgetbill['invoice_total'];                              
                          }
                          else
                          {
                            echo "-";
                          } 
                        ?>                    
                          <!-- <a href="javascript:void(0);" class="show-cloud-purchase-bill-section" data-bill-month="<?php //echo $resgetbill['month']; ?>" data-bill-year="<?php //echo $resgetbill['year']; ?>" style="text-decoration:underline;"><?php //echo numberFormat(round($resgetbill['purchase_total'],2),2); ?></a> -->
                      </div>
                    </td>
                    <!-- <td>
                      <div class="table-value-wrapper align-right">
                        <?php 
                          // if($resgetbill['status'] >= 3)
                          // {
                          //   $portalvalue = (int)$resgetbill['portal_total'];
                          //   $salesvalue = (int)$resgetbill['sales_total'];
                          //   $purchasevalue = (int)$resgetbill['purchase_total'];
                          //   echo round(((($salesvalue-$purchasevalue)/$purchasevalue)*100),2); 
                          // }
                          // else
                          // {
                          //   echo "N/A";
                          // } 
                        ?>
                      </div>
                    </td>  -->   
                    <td>
                      <div class="table-value-wrapper  align-right">
                        <?php echo $ridiscountval." %"; ?>
                      </div>
                    </td>
                    <td>
                      <div class="table-value-wrapper  align-right">
                        <?php echo $paygdiscountval." %"; ?>
                      </div>
                    </td>      
                    <td>
                      <div class="table-value-wrapper align-right" title="<?php echo $resgetbill['progress_name'];  ?>">
                        <?php 
                          if($resgetbill['progress_val'] == "")
                          {
                            echo "N/A"; 
                          }
                          else
                          {
                            echo $resgetbill['progress_val']; 
                          }

                          if($resgetbill['progress'] != "4")
                          {
                        ?>
                        <a class="change-monthly-bill-progress-btn" id="change-monthly-bill-progress-btn-<?php echo $i+1; ?>" data-bill-id="<?php echo $resgetbill['id']; ?>" data-bill-id="<?php echo $resgetbill['id']; ?>" data-bill-id="<?php echo $resgetbill['id']; ?>" href="javascript:void(0);"><i class="fa fa-edit" aria-hidden="true"></i></a>
                        <?php
                          }
                        ?>
                      </div>
                    </td>        
                    <td>
                      <div class="table-action-btn-wrapper align-center">
                        <?php
                        if($resgetbill['status'] >= 3)
                        {
                          // if(!$changeci)
                          // {
                          if($resgetbill['invoice_ci_total'] == $resgetbill['invoice_total'])
                          {
                              if((int)$resgetbill['invoice_count'] > 0)
                              {
                          ?>
                            <a href="javascript:void(0);" style="color:green;font-size: 1.5em;cursor: default;" title=""><i class="fa fa-check" aria-hidden="true"></i></a>
                          <?php
                              }
                            }
                            else
                            { 

                              // if(!$deviation)
                              // {
                          ?>
                          <a href="javascript:void(0);" style="color:orangered;font-size: 1.5em;cursor: default;" title="Missing CI for Invoice"><i class="fa fa-exclamation-circle" aria-hidden="true"></i></a>

                          <!-- <a class="show-create-cloud-inward-form-modal-btn" id="show-create-cloud-inward-form-modal-btn-<?php //echo $i+1; ?>" data-bill-id="<?php //echo $resgetbill['id']; ?>" href="javascript:void(0);" title="Create Cloud Inward"><i class="fa fa-plus" aria-hidden="true"></i></a> -->
                            <?php
                                // }
                              }
                          }
                          ?>
                      </div>
                    </td>
                    <td>
                      <div class="table-action-btn-wrapper align-center">
                        <?php 
                        if($resgetbill['status'] >= 3)
                        {
                          if(!$changeco)
                          {
                        ?> 
                          <a href="javascript:void(0);" style="color:green;font-size: 1.5em;cursor: default;" title="<?php echo "SEPL/CO/".$resgetcloudoutward['id']; ?>"><i class="fa fa-check" aria-hidden="true"></i></a>
                        <?php
                          }
                          else
                          { 

                            if(!$deviation)
                            {
                        ?>
                          <a class="show-create-cloud-outward-form-modal-btn" id="show-create-cloud-outward-form-modal-btn-<?php echo $i+1; ?>" data-bill-id="<?php echo $resgetbill['id']; ?>" href="javascript:void(0);" title="Create Cloud Outward"><i class="fa fa-plus" aria-hidden="true"></i></a>
                        <?php
                            }
                          }
                        }
                        ?>
                      </div>
                    </td>
                    <td class="no-print-action-btn">
                      <div class="table-action-btn-wrapper align-center">
                        <a class="view-monthly-bill-items-btn" id="view-monthly-bill-items-btn-<?php  echo $i+1; ?>" data-bill-id="<?php echo $resgetbill['id']; ?>" href="javascript:void(0);" title="View Bill Summary" style="color: blue;"><i class="fa-solid fa-eye"></i></a>
                        <?php  
                          if($changeci && $changeco)
                          {
                        ?>
                        <a class="delete-monthly-bill-btn" id="delete-monthly-bill-btn-<?php echo $i+1; ?>" data-bill-id="<?php echo $resgetbill['id']; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
                        <?php
                          }
                        ?>
                      </div>
                    </td>
                  </tr>
                  <?php
                        $i++;
                      }
                  ?>
                  <tr class="align-right" style="font-weight: bold;">
                    <td colspan="2">Grand Total</td>
                    <td><?php echo numberFormat(round($portalcolumngrandtotal,2),2); ?></td>
                    <td><?php echo numberFormat(round($salescolumngrandtotal,2),2); ?></td>
                    <td><?php echo numberFormat(round($purchasecolumngrandtotal,2),2); ?></td>
                    <td><?php echo numberFormat(round($invoicecolumngrandtotal,2),2); ?></td>
                    <td colspan="7"></td>
                  </tr>
              </tbody>
                  <?php
                    }
                    else
                    {
                  ?>
                  <tbody>
                    <tr>
                      <td class="align-center">No Entries Found !</td>
                    </tr>
                  </tbody>
                <?php
                    }

                    mysqli_close($con);
                  ?>
            </table>
  <?php 
    }
  ?>
