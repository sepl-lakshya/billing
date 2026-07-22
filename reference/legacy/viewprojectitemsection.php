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
      <!--<button class="print-btn" onclick="printDiv('project-items-main-wrapper');">Print</button>-->
          <table class="table-element" id="project-items-table-main" cellpadding="0" cellspacing="0" style="font-size: .6em;">
          <?php
            $con = connectMySQL();
          
            $sqlgetprojectheader = "select id,hash,name,quantity,description from project_header where project_id = ".$pid." AND is_deleted = 0" ;
            $rowgetprojectheader = mysqli_query($con, $sqlgetprojectheader);

            if (mysqli_num_rows($rowgetprojectheader) > 0)
            {
          ?>
            
            <thead>
              <tr>
                <th>SNo</th>
                <th>Product</th>
                <th>Type</th>
                <th>Distributor</th>
                <th>Quoted Price</th>
                <th>Unit Measure</th>
                <th>Quantity</th>
                <th>Model</th>
                <!-- <th>Deployed Product</th> -->
                <th>Deployed On</th>
                <th>Status / <br>Deactivation <br> Date</th>
                <th>Discovery Status</th>
                <th>Purchase Header</th>
                <th>Description</th>
                <th>Resource ID</th>
                <th class="no-print">Action</th>
              </tr>
            </thead>
            <tbody>

            <?php 
                $h = 0;
                while ($h <= ($resgetprojectheader = mysqli_fetch_array($rowgetprojectheader)))
                {
              ?>
              <tr class="cloud-header-row">
                <td colspan="14">
                  <div class="table-value-wrapper">
                    <?php echo $resgetprojectheader['name']; ?>
                    <?php
                      if($resgetprojectheader['description'] != "")
                      {
                    ?>
                    <i class="fa fa-info-circle" aria-hidden="true" title="<?php echo $resgetprojectheader['description']; ?>" style="margin-left: 20px;color: black;font-size: 1.4em;vertical-align: middle;" ></i>
                    <?php 
                      }
                    ?>
                    <span style="float:right;">Quantity : <?php echo $resgetprojectheader['quantity']; ?></span>
                  </div> 
                </td>
                <td style="color:#002D62;" class="no-print">
                  <div class="table-action-btn-wrapper align-center">

                    <a class="show-add-header-item-modal-btn" data-header-id="<?php echo $resgetprojectheader['id']; ?>" id="show-add-header-item-modal-btn-<?php echo $h+1; ?>" href="javascript:void(0);" title="Add Product" ><i class="fa fa-plus" aria-hidden="true"></i></a>

                    <a class="show-edit-project-header-modal-btn" id="show-edit-project-header-modal-btn-<?php echo $h+1; ?>" data-project-header-id="<?php echo $resgetprojectheader['id']; ?>" href="javascript:void(0);" title="Edit Header"><i class="fas fa-edit"></i></a>

                    <a class="delete-project-header-btn" id="delete-project-header-btn-<?php echo $h+1; ?>" data-project-header-id="<?php echo $resgetprojectheader['id']; ?>" href="javascript:void(0);" title="Delete Header"><i class="fa fa-trash" aria-hidden="true"></i></a>
                  </div> 
                </td>
              </tr>

              <?php

                  $sqlgetprojectitem = "select pi.id,pi.hash,pi.resource_id,  
                  (select name from cloud_category where value = (select sub_category from product where id = pi.product)) as category_name, 
                  (select name from product where id = pi.product) as product_name, 
                  (select name from distributor where id = pi.distributor) as distributor_name, 
                  deployment_start,deployment_end,model,unit_price,quantity,status,description, 
                  (select name from product where id = pi.deployed_product) as deployed_product_name, 
                  (select name from unit_measure where value = pi.unit_measure) as unit_measure_name, 
                  (select name from project_item_discovery where value = pi.discovery_status) as discovery_status_name, 
                  (select name from purchase_header where id = pi.purchase_header_id) as purchase_header_name, 
                  case when pi.status = 1 then 'fa-toggle-on' else 'fa-toggle-off' end as product_status 
                  from project_item as pi INNER JOIN product pr ON pr.id = pi.product where pi.project_id = ".$pid." AND pi.header_id = ".$resgetprojectheader['id']." AND pi.is_deleted = 0 order BY pr.sub_category";
                  $rowgetprojectitem = mysqli_query($con, $sqlgetprojectitem);

                  if (mysqli_num_rows($rowgetprojectitem) > 0)
                  {
             
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
                      <?php echo $resgetprojectitem['category_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['distributor_name']; ?>
                    </div>
                  </td> 
                  <td>
                    <div class="table-value-wrapper align-right">
                      <?php echo $resgetprojectitem['unit_price']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['unit_measure_name']; ?>
                    </div>
                  </td> 
                  <td>
                    <div class="table-value-wrapper align-right">
                      <?php echo $resgetprojectitem['quantity']; ?>
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
                  <!-- <td>
                    <div class="table-value-wrapper">
                      <?php //echo $resgetprojectitem['product_status']; ?>
                      <i class="fa <?php echo $resgetprojectitem['product_status']; ?>" aria-hidden="true"></i>
                    </div>
                  </td>  --> 
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo date("d-m-Y",strtotime($resgetprojectitem['deployment_start'])); ?>
                    </div>
                  </td>
                  <td>
                    <?php 
                      if($resgetprojectitem['status'] == 0 && $resgetprojectitem['deployment_end'] != NULL)
                      {
                        $itemstatus = date("d-m-Y",strtotime($resgetprojectitem['deployment_end']));
                        $itemstatuscolor = "background:#F08080;";
                      }
                      else
                      {
                        $itemstatus = "Active";
                        $itemstatuscolor = "background:#90EE90;";
                      }
                    ?>
                    <div class="table-value-wrapper align-center" style="<?php echo $itemstatuscolor; ?>">
                      <?php echo $itemstatus; ?>
                    </div>
                  </td> 
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['discovery_status_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['purchase_header_name']; ?>
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
                  <td class="no-print">
                    <div class="table-action-btn-wrapper align-center" >

                      <?php
                        if($resgetprojectitem['status'] == 1)
                        {
                      ?>

                      <a class="show-disable-project-item-modal-btn" data-project-item-id="<?php echo $resgetprojectitem['id']; ?>" id="show-disable-project-item-modal-btn-<?php echo $i; ?>" href="javascript:void(0);" style="color: red;" title="Deactivate Product" ><i class="fa fa-ban" aria-hidden="true"></i></a>



                      <?php
                        }
                      ?>

                      <a class="show-edit-project-item-modal-btn" id="show-edit-project-item-modal-btn-<?php echo $i+1; ?>" data-project-item-id="<?php echo $resgetprojectitem['id']; ?>" href="javascript:void(0);" title="Edit Product"><i class="fas fa-edit"></i></a>

                      <a class="delete-project-item-btn" id="delete-project-item-btn-<?php echo $i+1; ?>" data-project-item-id="<?php echo $resgetprojectitem['id']; ?>" href="javascript:void(0);" title="Delete Product"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
                  //else
                  //{
              ?>
                <!-- <tbody>
                  <tr>
                    <td class="align-center">No Products Found !</td>
                  </tr>
                </tbody -->
              <?php
                  //}
                   $h++;
                }
              }

                  mysqli_close($con);
                ?>
          </table>
<?php 
  }
?>
