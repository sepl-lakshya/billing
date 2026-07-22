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

      $daysinmonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

      $monthstartdate = $year."-".$month."-1";
      $monthenddate = $year."-".$month."-".$daysinmonth;

      $monthstartdate = date("Y-m-d",strtotime($monthstartdate));
      $monthenddate = date("Y-m-d",strtotime($monthenddate));

      $sqlgetmonth = "select name from month where value = ".$month;
      $rowgetmonth = mysqli_query($con, $sqlgetmonth);
      $resgetmonth = mysqli_fetch_array($rowgetmonth);

      $sqlgetyear = "select name from year where value = ".$year;
      $rowgetyear = mysqli_query($con, $sqlgetyear);
      $resgetyear = mysqli_fetch_array($rowgetyear);

      $discountdate = $year."-".$month."-15";

      $sqlgetdiscount = "select ri_discount,payg_discount from project_discount where from_date < '".$monthenddate."' AND to_date > '".$monthstartdate."' AND project_id = ".$pid;
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

      $editprices = true;

      $sqlgetbill = "select id,hash,status,ri_discount,payg_discount from bill where project_id = ".$pid." AND month = ".$month." AND year = ".$year." AND is_deleted = 0";
      $rowgetbill = mysqli_query($con, $sqlgetbill);

      // If Portal Price Exists 
      if(mysqli_num_rows($rowgetbill) > 0)
      {
        $resgetbill = mysqli_fetch_array($rowgetbill);

        $sqlgetcloudinward = "select id from cloud_inward where project_id = ".$pid." AND bill_id = ".$resgetbill['id']." AND is_cancelled = 0";
        $rowgetcloudinward = mysqli_query($con, $sqlgetcloudinward);

        if(mysqli_num_rows($rowgetcloudinward) > 0)
        {
          $editprices = false;
        }
?>
      <button class="print-btn" onclick="printDiv('portal-price-modal');">Print</button>
      <div class="modal-heading-wrapper">
        Portal Price of Items for <span style="font-size:1.1em; text-decoration: underline;"><?php echo $resgetmonth['name']." ".$resgetyear['name']; ?></span>
      </div>
          <input type="hidden" id="portal-price-month" name="portal-price-month" value="<?php echo $month; ?>">      
          <input type="hidden" id="portal-price-year" name="portal-price-year" value="<?php echo $year; ?>">  
          <div class="ce-form-controls ce-form-control-col-2" readonly >
            <div class="ce-form-label">RI Discount : <?php echo $ridiscountval; ?> %</div>
            <div class="ce-form-input">
                <input type="hidden" id="portal-bill-ri-discount" name="portal-bill-ri-discount" value="<?php echo $ridiscountval; ?>">
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-label">PAYG Discount : <?php echo $paygdiscountval; ?> %</div>
            <div class="ce-form-input">
                <input type="hidden" id="portal-bill-payg-discount" name="portal-bill-payg-discount" value="<?php echo $paygdiscountval; ?>" >
            </div>
          </div>    
          <table class="table-element" id="portal-price-table" cellpadding="0" cellspacing="0" style="font-size:.8em;">
            
                <?php
              $sqlgetbillitem = "select * from bill_item as bi INNER JOIN product pr ON pr.id = (select product from project_item where id = bi.project_item_id) where bill_id = ".$resgetbill['id']." AND project_id = ".$pid." order by pr.sub_category";
              $rowgetbillitem = mysqli_query($con, $sqlgetbillitem);

              if (mysqli_num_rows($rowgetbillitem) > 0)
              {          
          ?>
            <thead>
              <tr class="align-center">
                <th>SNo</th>
                <th>Product</th>
                <th>Type</th>
                <th>Model</th>
                <th>Header Name</th>
                <th>Description</th>
                <th>Resource ID</th>
                <th>Deployed On</th>
                <th>Status/<br>Deactivation<br> Date</th>
                <th>Quantity</th>
                <th>Portal Price excl. GST</th>
              </tr>
            </thead>
            <tbody>
          <?php
                $i = 0;
                $rowcount = 1;
                while ($i <= ($resgetbillitem = mysqli_fetch_array($rowgetbillitem)))
                {
                  $sqlgetprojectitem = "select pi.id,pi.resource_id,
                  (select name from cloud_category where value = (select sub_category from product where id = pi.product)) as category_name,
                  (select name from product where id = pi.product) as product_name,
                  (select name from distributor where id = pi.distributor) as distributor_name,
                  deployment_start,model,unit_price,quantity,deployment_end,status,description,
                  (select name from product where id = pi.deployed_product) as deployed_product_name,
                  (select name from unit_measure where value = pi.unit_measure) as unit_measure_name,
                  (select name from project_header where id = pi.header_id) as header_name,
                  case when pi.status = 1 then 'fa-toggle-on' else 'fa-toggle-off' end as product_status
                   from project_item as pi where pi.id = ".$resgetbillitem['project_item_id']." AND pi.project_id = ".$pid;
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
                <tr class="portal-price-row" id="portal-price-row-<?php echo $rowcount; ?>" data-row-no="<?php echo $rowcount; ?>" data-project-item-id="<?php echo $resgetprojectitem['id']; ?>" >
                  <td><div class="table-value-wrapper"><?php echo $rowcount; ?>&nbsp;&nbsp;</div></td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['product_name']; ?>
                    </div>
                  </td>
                  <td>
                    <?php 

                    $sqlgetcategorytotal = "select SUM(portal_price) from bill_item as bi INNER JOIN project_item as pi ON bi.project_item_id = pi.id INNER JOIN product as pr ON pi.product = pr.id where bi.bill_id = ".$resgetbill['id']." AND pr.sub_category = ".$resgetbillitem['sub_category'];
                    $rowgetcategorytotal = mysqli_query($con, $sqlgetcategorytotal);
                    $resgetcategorytotal = mysqli_fetch_array($rowgetcategorytotal);
                    
                    

                    ?>
                    <div class="table-value-wrapper" title="<?php echo $resgetprojectitem['category_name']." Total : ".$resgetcategorytotal['SUM(portal_price)']; ?>" style="cursor: pointer;">
                      <?php
                        echo $resgetprojectitem['category_name']; 
                      ?>
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
                      <?php
                        if($resgetprojectitem['header_name'] == "")
                        {
                          echo "N/A";
                        }
                        else
                        {
                         echo $resgetprojectitem['header_name']; 
                        }
                      ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['description']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper" style="font-size: 1em;font-weight: normal;word-break: break-all;">
                        <a class="copy-hash-code-btn" data-hash-value="<?php echo $resgetprojectitem['resource_id']; ?>" id="copy-hash-code-btn-<?php echo $i; ?>" href="javascript:void(0);" title="Click to Copy" style="position: relative;" >
                          <?php  
                            if($resgetprojectitem['resource_id'] != "")
                            {
                              echo $resgetprojectitem['resource_id']; 
                          ?>
                            <i class="fa-solid fa-copy" style="vertical-align:middle;"></i>
                          <?php
                            }
                          ?>
                          <div class="text-copied-msg-box">
                            <div class="text-copied-msg-wrapper">
                              Copied!
                            </div>  
                          </div>
                        </a>
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
                    <div class="table-value-wrapper" style="<?php echo $itemstatuscolor; ?>">
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
                      <?php echo $resgetprojectitem['quantity']; ?>
                    </div>
                  </td>                                     
                  <td>
                  <?php 
                    if($editprices)
                    {
                  ?>
                    <input type="text" id="portal-price-value-<?php echo $rowcount; ?>" name="portal-price-value-<?php echo $rowcount; ?>" value="<?php echo $resgetbillitem['portal_price']; ?>">
                  <?php
                    }
                    else
                    {
                      echo numberFormat(round($resgetbillitem['portal_price'],2),2); 
                    }
                  ?>
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
                    $showsubmitbtn = true;
                }
                else
                {
                    $showsubmitbtn = false;
              ?>
                <tbody>
                  <tr>
                    <td class="align-center">No Products Found !</td>
                  </tr>
                </tbody>
              <?php
                  }

                ?>
          </table>
<?php 

      }
      // If Portal Price Not Exist
      else
      {

?>
      <div class="modal-heading-wrapper">
        Enter Portal Price of Items & Discount for <span style="font-size:1.1em; text-decoration: underline;"><?php echo $resgetmonth['name']." ".$resgetyear['name']; ?></span>
      </div>
          <input type="hidden" id="portal-price-month" name="portal-price-month" value="<?php echo $month; ?>">      
          <input type="hidden" id="portal-price-year" name="portal-price-year" value="<?php echo $year; ?>">      
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-label">PAYG Discount : </div>
            <div class="ce-form-input">
                <?php echo $paygdiscountval; ?>%
                <input type="hidden" id="portal-bill-payg-discount" name="portal-bill-payg-discount" value="<?php echo $paygdiscountval; ?>">
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-label">RI Discount : </div>
            <div class="ce-form-input">
                <?php echo $ridiscountval; ?>%
                <input type="hidden" id="portal-bill-ri-discount" name="portal-bill-ri-discount" value="<?php echo $ridiscountval; ?>">
            </div>
          </div>
          <table class="table-element" id="portal-price-table" cellpadding="0" cellspacing="0" style="font-size:.8em;">
            
                <?php

                  $sqlgetprojectitem = "select pi.id,pr.sub_category,resource_id,
                  (select name from cloud_category where value = (select sub_category from product where id = pi.product)) as category_name,
                  (select name from product where id = pi.product) as product_name,
                  (select name from distributor where id = pi.distributor) as distributor_name,
                  deployment_start,model,unit_price,quantity,deployment_end,status,description,
                  (select name from product where id = pi.deployed_product) as deployed_product_name,
                  (select name from unit_measure where value = pi.unit_measure) as unit_measure_name,
                  (select name from project_header where id = pi.header_id) as header_name,
                  case when pi.status = 1 then 'fa-toggle-on' else 'fa-toggle-off' end as product_status
                  from project_item as pi INNER JOIN product pr ON pr.id = pi.product where pi.project_id = ".$pid." AND pi.is_deleted = 0 order BY pr.sub_category, pi.id asc";
                  $rowgetprojectitem = mysqli_query($con, $sqlgetprojectitem);

                  if (mysqli_num_rows($rowgetprojectitem) > 0)
                  {
              ?>
            <thead>
              <tr>
                <th>SNo</th>
                <th>Product</th>
                <th>Type</th>
                <th>Model</th>
                <th>Header Name</th>
                <th>Description</th>
                <th>Resource ID</th>
                <th>Deployed On</th>
                <th>Status/Deactivation Date</th>
                <th>Portal Price</th>
              </tr>
            </thead>
            <tbody>
              <?php
                    $i = 0;
                    $rowcount = 1;
                    while ($i <= ($resgetprojectitem = mysqli_fetch_array($rowgetprojectitem)))
                    {
                      $deployenddate = strtotime($resgetprojectitem['deployment_end']);
                      $deploystartdate = strtotime($resgetprojectitem['deployment_start']);

                      if($resgetprojectitem['status'] == 0)
                      {
                        if(($deployenddate >= strtotime($monthstartdate)) && ($deployenddate <= strtotime($monthenddate)) || (
                          ($deployenddate >= strtotime($monthstartdate)) && ($deploystartdate <= strtotime($monthenddate))))
                        {
                          $showrow = true;
                        }
                        else
                        {
                          $showrow = false;
                        }
                      }
                      else
                      {
                        if($deploystartdate <= strtotime($monthenddate))
                        {  
                          $showrow = true;
                        }
                        else
                        {
                          $showrow = false;
                        }
                      }


                      if($showrow)
                      {

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
                <tr class="portal-price-row" id="portal-price-row-<?php echo $rowcount; ?>" data-row-no="<?php echo $rowcount; ?>" data-project-item-id="<?php echo $resgetprojectitem['id']; ?>" >
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
                      <?php
                        if($resgetprojectitem['header_name'] == "")
                        {
                          echo "N/A";
                        }
                        else
                        {
                         echo $resgetprojectitem['header_name']; 
                        }
                      ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['description']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper" style="font-size: 1em;font-weight: normal;word-break: break-all;">
                        <a class="copy-hash-code-btn" data-hash-value="<?php echo $resgetprojectitem['resource_id']; ?>" id="copy-hash-code-btn-<?php echo $i; ?>" href="javascript:void(0);" title="Click to Copy" style="position: relative;" >
                          <?php  
                            if($resgetprojectitem['resource_id'] != "")
                            {
                              echo $resgetprojectitem['resource_id']; 
                          ?>
                            <i class="fa-solid fa-copy" style="vertical-align:middle;"></i>
                          <?php
                            }
                          ?>
                          <div class="text-copied-msg-box">
                            <div class="text-copied-msg-wrapper">
                              Copied!
                            </div>  
                          </div>
                        </a>
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
                    <div class="table-value-wrapper" style="<?php echo $itemstatuscolor; ?>">
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
                    <input type="text" id="portal-price-value-<?php echo $rowcount; ?>" name="portal-price-value-<?php echo $rowcount; ?>" value="">
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
                    $showsubmitbtn = true;
                  }
                  else
                  {
                    $showsubmitbtn = false;
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
          </table>
<?php 
      }
      if($showsubmitbtn)
      {
        if($editprices)
        {
  ?>
    <div class="ce-form-controls ce-form-control-col-2 no-print">
      <div class="ce-form-input ">
          <input type="button" id="ce-portal-price-back-btn" name="ce-portal-price-back-btn" value="< Back">
      </div>
    </div>
    <div class="ce-form-controls ce-form-control-col-2 no-print">
      <div class="ce-form-input align-right">
          <input type="button" id="ce-submit-portal-price-btn" name="ce-submit-portal-price-btn" value="Save & Exit >">
      </div>
    </div>
  <?php
        }
      }
    }
?>
