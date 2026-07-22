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


      $sqlgetbill = "select * from bill where project_id = ".$pid." AND month = ".$month." AND year = ".$year." AND status >= 2 AND is_deleted = 0";
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

        if($resgetbill['purchase_header_status'] == "0" && $editprices)
        {

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
      <div class="modal-heading-wrapper">
        Enter Purchase Price of Items for <span style="font-size:1.1em; text-decoration: underline;"><?php echo $resgetmonth['name']." ".$resgetyear['name']; ?></span>
      </div>
      <div class="align-right" style="margin-bottom: 10px;">
        RI Discount : <?php echo $ridiscountval; ?> % &nbsp;&nbsp; | &nbsp;&nbsp; PAYG Discount : <?php echo $paygdiscountval; ?> %
      </div>   
          <input type="hidden" id="change-header-purchase-price-month" name="change-header-purchase-price-month" value="<?php echo $month; ?>">      
          <input type="hidden" id="change-header-purchase-price-year" name="change-header-purchase-price-year" value="<?php echo $year; ?>">      
          <input type="hidden" id="change-header-purchase-bill-id" name="change-header-purchase-bill-id" value="<?php echo $resgetbill['id']; ?>">      
          <table class="table-element" id="purchase-header-table" cellpadding="0" cellspacing="0" style="font-size:.8em;">
          
            <thead>
              <tr>
                <th>SNo</th>
                <th>Product</th>
                <th>Type</th>
                <th>Model</th>
                <th>Description</th>
                <!-- <th>Deployed Product</th> -->
                <th>Deployed On</th>
                <th>Status/Deactivation Date</th>
                <th>Purchase Header</th>
              </tr>
            </thead>
            <tbody>
          <?php


              $sqlgetbillitem = "select * from bill_item as bi INNER JOIN product pr ON pr.id = (select product from project_item where id = bi.project_item_id) where bill_id = ".$resgetbill['id']." AND project_id = ".$pid." order by pr.sub_category";
              $rowgetbillitem = mysqli_query($con, $sqlgetbillitem);

              if (mysqli_num_rows($rowgetbillitem) > 0)
              {

                $i = 0;
                $rowcount = 1;
                while ($i <= ($resgetbillitem = mysqli_fetch_array($rowgetbillitem)))
                {

                  $sqlgetprojectitem = "select id,purchase_header_id,
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
                <tr class="change-purchase-header-row" id="change-purchase-header-row-<?php echo $rowcount; ?>" data-row-no="<?php echo $rowcount; ?>" data-project-item-id="<?php echo $resgetprojectitem['id']; ?>" >
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
                      <div class="ce-form-input">
                      <select id="change-purchase-header-id-<?php echo $rowcount; ?>" name="change-purchase-header-id-<?php echo $rowcount; ?>">
                        <option value="0">Select Purchase Header</option>
                        <?php

                            $sqlgetpurchaseheader = "select * from purchase_header where is_active = 1 AND (project_id = ".$pid." OR project_id = 0) order by name";
                              $rowgetpurchaseheader = mysqli_query($con, $sqlgetpurchaseheader);

                              if (mysqli_num_rows($rowgetpurchaseheader) > 0)
                              {
                                $phn = 0;
                                while ($phn <= ($resgetpurchaseheader = mysqli_fetch_array($rowgetpurchaseheader)))
                                {
                                  if($resgetpurchaseheader['id'] == $resgetprojectitem['purchase_header_id'])
                                  {
                                    $purchaseheaderselected = "selected";
                                  }
                                  else
                                  {
                                    $purchaseheaderselected = "";
                                  }
                            ?>
                              <option value="<?php echo $resgetpurchaseheader['id']; ?>" <?php echo $purchaseheaderselected; ?> >
                                <?php echo $resgetpurchaseheader['name']; ?>
                              </option>
                            <?php
                                  $phn++;
                                }
                              }
                        ?>
                      </select>
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

    <script>
      var dataArray3 = {month :<?php echo $month; ?>,year :<?php echo $year; ?>,pid :<?php echo $pid; ?>};

      $("#purchase-bill-items-cont").load("purchasebillitemsection.php",dataArray3,function(){      
        if($("#show-purchase-modal-check").val() == "1")
        { 
            $("#purchase-price-modal").modal({
              escapeClose: false,
              clickClose: false,
          });
        }
      });
    </script>

    <?php
      }
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
          <div class="modal-change-btn-wrapper">
          <!-- <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-input">
                <a class="button show-modal-btn show-modal-back-btn" data-modal-id="purchase-price-month-modal" href="javascript:void(0);" >< Back</a>
            </div>
          </div> -->
          
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-input ">
                <input type="button" id="ce-purchase-price-back-btn" name="ce-purchase-price-back-btn" value="< Back">
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-input align-right">
                <input type="button" id="ce-submit-purchase-header-change-btn" name="ce-submit-purchase-header-change-btn" value="Save & Next >">
            </div>
          </div>
        </div>


  