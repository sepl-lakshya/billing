<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

    if(isset($_POST['projectid']) && $_POST['projectid'] != "" && isset($_POST['projectitemid']) && $_POST['projectitemid'] != "")
    {
      $projectid = $_POST['projectid'];
      $projectitemid = $_POST['projectitemid'];
      
      $con = connectMySQL();

      $sqlgetprojectitem = "select *,
      (select name from project_header where id = pi.header_id) as header_name,
      (select name from product where id = pi.product) as product_name
      from project_item as pi where id = ".$projectitemid." AND project_id = ".$projectid;
      $rowgetprojectitem = mysqli_query($con, $sqlgetprojectitem);

      if (mysqli_num_rows($rowgetprojectitem) > 0)
      {
        $resgetprojectitem = mysqli_fetch_array($rowgetprojectitem);


?>
          <form method="post" class="ce-form" id="ce-edit-project-item-form" style="width:100%;">
              <input type="hidden" id="edit-item-project-id" name="edit-item-project-id" value="<?php echo $projectid; ?>">
              <input type="hidden" id="edit-project-item-id" name="edit-project-item-id" value="<?php echo $projectitemid; ?>">
              
              <div class="project-products-list-item-form">
                <div class="ce-form-controls ce-form-control-col-1">
                  <div class="ce-form-label">Header Name :</div>
                  <div class="ce-form-input">
                    <?php echo $resgetprojectitem['header_name'] ?>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-3">
                  <div class="ce-form-label">Product :</div>
                  <div class="ce-form-input">
                    <?php echo $resgetprojectitem['product_name']; ?>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-3">
                  <div class="ce-form-label">Model :</div>
                  <div class="ce-form-input">
                    <?php echo $resgetprojectitem['model']; ?>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-3">
                  <div class="ce-form-label">Deployment Date :</div>
                  <div class="ce-form-input">
                    <?php echo date("d-m-Y",strtotime($resgetprojectitem['deployment_start'])); ?>
                  </div>
                </div>

                <div class="ce-form-controls ce-form-control-col-3">
                  <div class="ce-form-label">Unit of Measure :</div>
                  <div class="ce-form-input">
                    <select id="edit-unit-measure" name="edit-unit-measure" data-row-id="1">
                      <option value="0">Unit of Measure</option>
                      <?php

                          $sqlgetunitmeasure = "select * from unit_measure";
                          $rowgetunitmeasure = mysqli_query($con, $sqlgetunitmeasure);

                          if (mysqli_num_rows($rowgetunitmeasure) > 0)
                          {
                            $i = 0;
                            while ($i <= ($resgetunitmeasure = mysqli_fetch_array($rowgetunitmeasure)))
                            {
                              if($resgetunitmeasure['value'] == $resgetprojectitem['unit_measure'])
                              {
                                $unitmeasureselected = "selected";
                              }
                              else
                              {
                                $unitmeasureselected = "";
                              }
                        ?>
                          <option value="<?php echo $resgetunitmeasure['value']; ?>" <?php echo $unitmeasureselected; ?> >
                            <?php echo $resgetunitmeasure['name']; ?>
                          </option>
                        <?php
                              $i++;
                            }
                          }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-3">
                  <div class="ce-form-label">Quoted Price(incl GST) :</div>
                  <div class="ce-form-input">
                    <input type="text" id="edit-unit-price" name="edit-unit-price" placeholder="Quoted Price" value="<?php echo $resgetprojectitem['unit_price']; ?>">
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-3">
                  <div class="ce-form-label">Quantity :</div>
                  <div class="ce-form-input">
                    <input type="number" id="edit-quantity" name="edit-quantity" placeholder="Quantity" value="<?php echo $resgetprojectitem['quantity']; ?>">
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-3">
                  <div class="ce-form-label">Deployed Product :</div>
                  <div class="ce-form-input" id="deployed-product-input-field">
                    <select id="edit-product-deployed" name="edit-product-deployed" data-row-id="1">
                      <option value="0">Select Deployed Product</option>
                      <?php

                          $sqlgetproduct = "select * from product where category = 1 AND is_active = 1 order by name";
                            $rowgetproduct = mysqli_query($con, $sqlgetproduct);

                            if (mysqli_num_rows($rowgetproduct) > 0)
                            {
                              $i = 0;
                              while ($i <= ($resgetproduct = mysqli_fetch_array($rowgetproduct)))
                              {
                                if($resgetproduct['id'] == $resgetprojectitem['product'])
                                {
                                  $productselected = "selected";
                                }
                                else
                                {
                                  $productselected = "";
                                }
                          ?>
                            <option value="<?php echo $resgetproduct['id']; ?>" <?php echo $productselected; ?> >
                              <?php echo $resgetproduct['name']; ?>
                            </option>
                          <?php
                                $i++;
                              }
                            }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-3">
                  <div class="ce-form-label">Discovery Status:</div>
                  <div class="ce-form-input" id="deployed-product-input-field">
                    <select id="edit-product-discovery" name="edit-product-discovery" data-row-id="1">
                      <option value="0">Select Discovery Status</option>
                      <?php

                          $sqlgetproductdiscovery = "select * from project_item_discovery order by name";
                            $rowgetproductdiscovery = mysqli_query($con, $sqlgetproductdiscovery);

                            if (mysqli_num_rows($rowgetproductdiscovery) > 0)
                            {
                              $i = 0;
                              while ($i <= ($resgetproductdiscovery = mysqli_fetch_array($rowgetproductdiscovery)))
                              {
                                if($resgetproductdiscovery['value'] == $resgetprojectitem['discovery_status'])
                                {
                                  $productdiscoveryselected = "selected";
                                }
                                else
                                {
                                  $productdiscoveryselected = "";
                                }
                          ?>
                            <option value="<?php echo $resgetproductdiscovery['value']; ?>" <?php echo $productdiscoveryselected; ?> >
                              <?php echo $resgetproductdiscovery['name']; ?>
                            </option>
                          <?php
                                $i++;
                              }
                            }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-3">
                  <div class="ce-form-label">Purchase Header :</div>
                  <div class="ce-form-input" id="deployed-product-input-field">
                    <select id="edit-product-purchase-header" name="edit-product-purchase-header" data-row-id="1">
                      <option value="0">Select Purchase Header</option>
                      <?php

                          $sqlgetpurchaseheader = "select * from purchase_header where is_active = 1 AND (project_id = ".$projectid." OR project_id = 0)  order by name";
                            $rowgetpurchaseheader = mysqli_query($con, $sqlgetpurchaseheader);

                            if (mysqli_num_rows($rowgetpurchaseheader) > 0)
                            {
                              $i = 0;
                              while ($i <= ($resgetpurchaseheader = mysqli_fetch_array($rowgetpurchaseheader)))
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
                                $i++;
                              }
                            }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-1">
                  <div class="ce-form-label">Resource ID (Max 1999 characters) :</div>
                  <div class="ce-form-input">
                    <input type="text" id="edit-product-resource-id" name="edit-product-resource-id" placeholder="Resource ID" maxlength="999" value="<?php echo $resgetprojectitem['resource_id']; ?>">
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-1">
                  <div class="ce-form-label">Description (Max 999 characters) :</div>
                  <div class="ce-form-input">
                    <input type="text" id="edit-product-desc" name="edit-product-desc" placeholder="Description" maxlength="999" value="<?php echo $resgetprojectitem['description']; ?>">
                  </div>
                </div>
                

                <div class="ce-form-controls ce-form-control-col-1">
                  <div class="ce-form-input align-right">
                      <input type="submit" id="ce-edit-project-item-form-submit" name="ce-edit-project-item-form-submit" value="Submit" >
                  </div>
                </div>
              </div>
            </form>

<?php
      } 
    }
?>