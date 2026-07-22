<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

    if(isset($_POST['projectid']) && $_POST['projectid'] != "")
    {
      $projectid = $_POST['projectid'];

      if(isset($_POST['headerid']) && $_POST['headerid'] != "")
      {
        $headerid = $_POST['headerid'];
        $hasheaderid = true;
      }
      else
      {
        $hasheaderid = false;
      }
      
      $con = connectMySQL();


?>
        <input type="hidden" id="add-project-item-table-row-count" name="add-project-item-table-row-count" value="0">
          <form method="post" class="ce-form" id="ce-add-project-product-form" style="width:100%;">
              <input type="hidden" id="project-id" name="project-id" value="<?php echo $projectid; ?>">
              <input type="hidden" id="product-cateogry" name="product-cateogry" value="1">
              
              <?php  
                if($hasheaderid)
                {
                    $sqlgetheadername = "select name from project_header where id = ".$headerid;
                    $rowgetheadername = mysqli_query($con, $sqlgetheadername);
                    $resgetheadername = mysqli_fetch_array($rowgetheadername);
              ?>
              <div class="project-products-list-item-form">
                <div class="ce-form-controls ce-form-control-col-1">
                  <div class="ce-form-label">Header Name :</div>
                  <div class="ce-form-input">
                    <?php echo $resgetheadername['name'] ?>
                    <input type="hidden" id="add-item-header-id" name="add-item-header-id" value="<?php echo $headerid; ?>">
                  </div>
                </div>
              </div>
              <?php
                }
                else
                {
              ?>

              <div class="project-products-list-item-form">
                <div class="ce-form-controls ce-form-control-col-1">
                  <div class="ce-form-label">Header Name :</div>
                  <div class="ce-form-input">
                    <input type="text" id="header-name" name="header-name" placeholder="Header Name">
                  </div>
                </div>
              </div>
              <?php 
                }
              ?>
          </form>

          <table id="add-cloud-project-item-table" class="table-element" cellpadding="0" cellspacing="0" style="font-size: .6em;margin-top: 10px;">
            <thead>
            </thead>
            <tbody>
              
            </tbody>
          </table>

          <div class="ce-form" style="width:100%;font-size: .8em !important;">
              <div class="project-products-list-item-form">
                <div class="ce-form-controls ce-form-control-col-4">
                  <div class="ce-form-label">Product Type :</div>
                  <div class="ce-form-input">
                    <select id="product-type" name="product-type" data-row-id="1">
                      <option value="0">Select Product Type</option>
                      <?php

                          $sqlgetcloudcategory = "select * from cloud_category order by name";
                            $rowgetcloudcategory = mysqli_query($con, $sqlgetcloudcategory);

                            if (mysqli_num_rows($rowgetcloudcategory) > 0)
                            {
                              $i = 0;
                              while ($i <= ($resgetcloudcategory = mysqli_fetch_array($rowgetcloudcategory)))
                              {
                          ?>
                            <option value="<?php echo $resgetcloudcategory['id']; ?>">
                              <?php echo $resgetcloudcategory['name']; ?>
                            </option>
                          <?php
                                $i++;
                              }
                            }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-4">
                  <div class="ce-form-label">Product :</div>
                  <div class="ce-form-input">
                    <select id="product" name="product" data-row-id="1">
                      <option value="0">Select Product</option>
                    </select>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-4">
                  <div class="ce-form-label">Distributor :</div>
                  <div class="ce-form-input">
                    <select id="distributor" name="distributor" data-row-id="1">
                      <option value="0">Select Distributor</option>
                      <?php

                          $sqlgetdistributor = "select * from distributor where is_active = 1";
                          $rowgetdistributor = mysqli_query($con, $sqlgetdistributor);

                          if (mysqli_num_rows($rowgetdistributor) > 0)
                          {
                            $i = 0;
                            while ($i <= ($resgetdistributor = mysqli_fetch_array($rowgetdistributor)))
                            {
                        ?>
                          <option value="<?php echo $resgetdistributor['id']; ?>">
                            <?php echo $resgetdistributor['name']; ?>
                          </option>
                        <?php
                              $i++;
                            }
                          }
                      ?>
                    </select>
                  </div>
                </div>
                <?php
                  $sqlgetproject = "select start_date from project where id = ".$projectid;
                  $rowgetproject = mysqli_query($con, $sqlgetproject);
                  $resgetproject = mysqli_fetch_array($rowgetproject);
                ?>
                <div class="ce-form-controls ce-form-control-col-4">
                  <div class="ce-form-label">Deployment Start :</div>
                  <div class="ce-form-input">
                    <input type="date" id="deployment-start" name="deployment-start" placeholder="Deployment Date" min="<?php echo $resgetproject['start_date']; ?>" max="<?php echo date("Y-m-d"); ?>">
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-4">
                  <div class="ce-form-label">Deployment End :</div>
                  <div class="ce-form-input">
                    <input type="date" id="deployment-end" name="deployment-end" placeholder="Deployment Date" min="<?php echo $resgetproject['start_date']; ?>" max="<?php echo date("Y-m-d"); ?>">
                  </div>
                </div>
                
                
                <div class="ce-form-controls ce-form-control-col-4">
                  <div class="ce-form-label">Unit of Measure :</div>
                  <div class="ce-form-input">
                    <select id="unit-measure" name="unit-measure" data-row-id="1">
                      <option value="0">Unit of Measure</option>
                      <?php

                          $sqlgetunitmeasure = "select * from unit_measure";
                          $rowgetunitmeasure = mysqli_query($con, $sqlgetunitmeasure);

                          if (mysqli_num_rows($rowgetunitmeasure) > 0)
                          {
                            $i = 0;
                            while ($i <= ($resgetunitmeasure = mysqli_fetch_array($rowgetunitmeasure)))
                            {
                        ?>
                          <option value="<?php echo $resgetunitmeasure['value']; ?>">
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
                <div class="ce-form-controls ce-form-control-col-4">
                  <div class="ce-form-label">Quoted Price(incl GST) :</div>
                  <div class="ce-form-input">
                    <input type="text" id="unit-price" name="unit-price" placeholder="Quoted Price">
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-4">
                  <div class="ce-form-label">Quantity :</div>
                  <div class="ce-form-input">
                    <input type="number" id="quantity" name="quantity" placeholder="Quantity">
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-4">
                  <div class="ce-form-label">Deployed Product :</div>
                  <div class="ce-form-input" id="deployed-product-input-field">
                    <select id="product-deployed" name="product-deployed" data-row-id="1">
                      <option value="0">Select Deployed Product</option>
                      <?php

                          $sqlgetproduct = "select * from product where category = 1 AND is_active = 1 order by name";
                            $rowgetproduct = mysqli_query($con, $sqlgetproduct);

                            if (mysqli_num_rows($rowgetproduct) > 0)
                            {
                              $i = 0;
                              while ($i <= ($resgetproduct = mysqli_fetch_array($rowgetproduct)))
                              {
                          ?>
                            <option value="<?php echo $resgetproduct['id']; ?>">
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
                <div class="ce-form-controls ce-form-control-col-4">
                  <div class="ce-form-label">Model :</div>
                  <div class="ce-form-input" id="product-model-input-field">
                    <input type="radio" id="product-model-ri" name="product-model-radio" value="RI">
                    <label for="product-model-ri" class="ce-radio-label">RI</label>
                    <input type="radio" id="product-model-payg" name="product-model-radio" value="PAYG">
                    <label for="product-model-payg" class="ce-radio-label">PAYG</label>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-4">
                  <div class="ce-form-label">Discovery Status:</div>
                  <div class="ce-form-input" id="deployed-product-input-field">
                    <select id="product-discovery" name="product-discovery" data-row-id="1">
                      <option value="0">Select Discovery Status</option>
                      <?php

                          $sqlgetproductdiscovery = "select * from project_item_discovery order by name";
                            $rowgetproductdiscovery = mysqli_query($con, $sqlgetproductdiscovery);

                            if (mysqli_num_rows($rowgetproductdiscovery) > 0)
                            {
                              $i = 0;
                              while ($i <= ($resgetproductdiscovery = mysqli_fetch_array($rowgetproductdiscovery)))
                              {
                          ?>
                            <option value="<?php echo $resgetproductdiscovery['value']; ?>">
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
                <div class="ce-form-controls ce-form-control-col-4">
                  <div class="ce-form-label">Purchase Header :</div>
                  <div class="ce-form-input" id="deployed-product-input-field">
                    <select id="product-purchase-header" name="product-purchase-header" data-row-id="1">
                      <option value="0">Select Purchase Header</option>
                      <?php

                          $sqlgetpurchaseheader = "select * from purchase_header where is_active = 1 AND (project_id = ".$projectid." OR project_id = 0) order by name";
                            $rowgetpurchaseheader = mysqli_query($con, $sqlgetpurchaseheader);

                            if (mysqli_num_rows($rowgetpurchaseheader) > 0)
                            {
                              $i = 0;
                              while ($i <= ($resgetpurchaseheader = mysqli_fetch_array($rowgetpurchaseheader)))
                              {
                          ?>
                            <option value="<?php echo $resgetpurchaseheader['id']; ?>">
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
                    <input type="text" id="product-resource-id" name="product-resource-id" placeholder="Resource ID" maxlength="1999">
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-1">
                  <div class="ce-form-label">Description (Max 999 characters) :</div>
                  <div class="ce-form-input">
                    <input type="text" id="product-desc" name="product-desc" placeholder="Description" maxlength="999">
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-1">
                  <div class="ce-form-input">
                    <input type="button" id="ce-add-project-item-btn" name="ce-add-project-item-btn" value="Add Product" >
                  </div>
                </div>


              </div>
          </div>


          <div class="ce-form" style="width:100%;">
            <div class="ce-form-controls ce-form-control-col-1" id="ce-add-project-product-form-submit-btn-wrapper" style="display:none;">
              <div class="ce-form-input align-right">
                  <input type="submit" id="ce-add-project-product-form-submit" name="ce-add-project-product-form-submit" value="Submit" form="ce-add-project-product-form">
              </div>
            </div>
          </div>

<?php 
    }
?>