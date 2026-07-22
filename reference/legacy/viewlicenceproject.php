<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }

  include "include/config.php";  
  $con = connectMySQL();

  if(isLoggedIn())
  {
    if(isset($_GET['pid']) && $_GET['pid'] != "" && isset($_GET['ph']) && $_GET['ph'] != "")
    {
      $projectid = mysqli_real_escape_string($con, clean_input($_GET['pid']));
      $projecthash = mysqli_real_escape_string($con, clean_input($_GET['ph']));

      if($_SESSION['user_type'] == "1")
      {
        $createdby = "";
      }
      else
      {
        $createdby = " AND (created_by = ".$_SESSION['user_id']." OR id IN (select project_id from licence_project_user_mapping where user_id = ".$_SESSION['user_id']."))";
      }

      $sqlgetproject = "select *,(select name from states where value = p.state) as state_name from licence_project as p where id = ".$projectid." AND hash = '".$projecthash."' ".$createdby;

      $rowgetproject = mysqli_query($con, $sqlgetproject);

      if (mysqli_num_rows($rowgetproject) > 0)
      {  
        $resgetproject = mysqli_fetch_array($rowgetproject);

?>


<!DOCTYPE html>
<html <?php echo getDefaultTheme(); ?> >
<head>
  <title>Projects</title>

  <!-- Head Section Include -->
  <?php include "include/headsection.php"; ?>
  
</head>
<body>
<input type="hidden" id="project-id-common" name="project-id-common" value="<?php echo $projectid; ?>">
<div class="app-container">
  <div class="app-header">

    <!-- Header Include -->
    <?php include "include/header.php";  ?>

  <div class="app-content">
    
    <!-- Sidebar Include -->
    <?php include "include/sidebar.php"; ?>
    
    <div class="content-container">
      <div class="content-container-header">
        <p>Project : <?php echo $resgetproject['name']; ?> </p>
      </div>

      <!-- Show Project Detail Form Section -->
      <section class="content-section" id="show-project-detail-section">
        <div class="section-heading">
          Project Details
          <?php
            if($resgetproject['created_by'] == $_SESSION['user_id'])
            {
          ?>
          <a href="javascript:void(0);" id="edit-project-detail-btn" style="text-decoration:underline;margin-left: 25px;"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>Edit</a>
          <?php } ?>
        </div>


        <!-- Show Project Detail Section -->
        <div class="section-content-wrapper">
            
            <!-- Form Section -->
            <!-- <div class="ce-form-section-heading">Project Details</div> -->
          <div class="ce-form">  
            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">City :</div>
              <div class="ce-form-input">
                  <?php echo $resgetproject['city']; ?>
              </div>
            </div>
            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">State :</div>
              <div class="ce-form-input">
                <?php echo $resgetproject['state_name']; ?>
              </div>
            </div>

            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">Tender Reference Number :</div>
              <div class="ce-form-input">
                  <?php echo $resgetproject['tender_ref_no']; ?>
              </div>
            </div>
            <!-- <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">Licence Category :</div>
              <div class="ce-form-input">
                  <?php //echo $resgetproject['licence_category_name']; ?>
              </div>
            </div> -->
            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">Project Start Date :</div>
              <div class="ce-form-input">
                  <?php echo date("d-m-Y",strtotime($resgetproject['start_date'])); ?>
              </div>
            </div>
            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">Contract Period :</div>
              <div class="ce-form-input">
                  <?php 
                    $contractperiod = "";
                    if($resgetproject['contract_year'] != "0")
                    {
                      if($resgetproject['contract_year'] == "1")
                      {
                        $contractperiod .= $resgetproject['contract_year']." Year ";
                      }
                      else
                      {
                        $contractperiod .= $resgetproject['contract_year']." Years ";
                      }
                    }

                    if($resgetproject['contract_month'] != "0")
                    {
                      if($resgetproject['contract_month'] == "1")
                      {
                        $contractperiod .= " &nbsp;&nbsp;&nbsp;&nbsp;".$resgetproject['contract_month']." Month ";
                      }
                      else
                      {
                        $contractperiod .= " &nbsp;&nbsp;&nbsp;&nbsp;".$resgetproject['contract_month']." Months ";
                      }
                    }

                  echo $contractperiod;
                ?>
              </div>
            </div>
            <div class="ce-form-controls ce-form-control-col-1">
              <div class="ce-form-label">Description :</div>
              <div class="ce-form-input">
                  <?php echo $resgetproject['description']; ?>
              </div>
            </div>
          </div>         
          
        </div>
      </section>

      <!-- Edit Project Detail Form Section -->
      <section class="content-section" id="edit-project-detail-section" style="display:none;">
        <div class="section-heading">
          Edit Project Details :
        </div>

        <!-- Show Project Detail Section -->
        <div class="section-content-wrapper">
            
          <form method="POST" class="ce-form" id="ce-edit-project-form">
            <input type="hidden" name="edit-project-id" id="edit-project-id" value="<?php echo $projectid; ?>">
            <input type="hidden" name="edit-project-hash" id="edit-project-hash" value="<?php echo $projecthash; ?>">
              <div class="ce-form-controls ce-form-control-col-4">
                <div class="ce-form-label">City :</div>
                <div class="ce-form-input">
                   <input type="text" id="edit-project-city" name="edit-project-city" value="<?php echo $resgetproject['city']; ?>" >
                </div>
              </div>
              <div class="ce-form-controls ce-form-control-col-4">
                <div class="ce-form-label">State :</div>
                <div class="ce-form-input">
                  <select id="edit-project-state" name="edit-project-state">
                    <option value="0">Select State</option>
                  <?php

                    $sqlgetstate = "select * from states";
                    $rowgetstate = mysqli_query($con, $sqlgetstate);

                    if (mysqli_num_rows($rowgetstate) > 0)
                    {
                      $i = 0;
                      while ($i <= ($resgetstate = mysqli_fetch_array($rowgetstate)))
                      {
                        $stateselected = "";
                        if($resgetstate['value'] == $resgetproject['state'])
                        {
                          $stateselected = "selected";
                        }
                  ?>
                      <option value="<?php echo $resgetstate['value']; ?>" <?php echo $stateselected; ?> ><?php echo $resgetstate['name']; ?></option>
                  <?php
                        $i++;
                      }
                    }
                  ?>
                  </select>
                </div>
              </div>

              <div class="ce-form-controls ce-form-control-col-4">
                <div class="ce-form-label">Tender Reference Number :</div>
                <div class="ce-form-input">
                    <input type="text" id="edit-tender-ref-no" name="edit-tender-ref-no" value="<?php echo $resgetproject['tender_ref_no']; ?>" >
                </div>
              </div>
              <div class="ce-form-controls ce-form-control-col-4">
                <div class="ce-form-label">Contract Period :</div>
                <div class="ce-form-input">
                  <select id="edit-contract-year" name="edit-contract-year" data-row-id="1" style="width:49%;">
                    <option value="0">Select Year</option>
                    <?php

                        $sqlgetcontractyear = "select * from contract_year";
                        $rowgetcontractyear = mysqli_query($con, $sqlgetcontractyear);

                        if (mysqli_num_rows($rowgetcontractyear) > 0)
                        {
                          $i = 0;
                          while ($i <= ($resgetcontractyear = mysqli_fetch_array($rowgetcontractyear)))
                          {
                            $contractyearselected = "";
                            if($resgetcontractyear['value'] == $resgetproject['contract_year'])
                            {
                              $contractyearselected = "selected";
                            }
                      ?>
                        <option value="<?php echo $resgetcontractyear['value']; ?>" <?php echo $contractyearselected; ?> >
                          <?php echo $resgetcontractyear['name']; ?>
                        </option>
                      <?php
                            $i++;
                          }
                        }
                    ?>
                  </select>
                  <select id="edit-contract-month" name="edit-contract-month" data-row-id="1" style="width:49%;">
                    <option value="0">Select Month</option>
                    <?php

                        $sqlgetcontractmonth = "select * from contract_month";
                        $rowgetcontractmonth = mysqli_query($con, $sqlgetcontractmonth);

                        if (mysqli_num_rows($rowgetcontractmonth) > 0)
                        {
                          $i = 0;
                          while ($i <= ($resgetcontractmonth = mysqli_fetch_array($rowgetcontractmonth)))
                          {
                            $contractmonthselected = "";
                            if($resgetcontractmonth['value'] == $resgetproject['contract_month'])
                            {
                              $contractmonthselected = "selected";
                            }
                      ?>
                        <option value="<?php echo $resgetcontractmonth['value']; ?>" <?php echo $contractmonthselected; ?> >
                          <?php echo $resgetcontractmonth['name']; ?>
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
                <div class="ce-form-label">Descripton :</div>
                <div class="ce-form-input">
                    <textarea id="edit-project-description" name="edit-project-description"><?php echo $resgetproject['description']; ?></textarea>
                </div>
              </div>
              <div class="ce-form-controls ce-form-control-col-6">
                <div class="ce-form-input">
                    <input type="submit" id="ce-edit-project-form-submit" name="ce-edit-project-form-submit" value="Update" >
                </div>
              </div>
              <div class="ce-form-controls ce-form-control-col-6">
                <div class="ce-form-input">
                    <input type="button" id="ce-edit-project-form-cancel" name="ce-edit-project-form-cancel" value="Cancel" >
                </div>
              </div>
          </form>         
          
        </div>
      </section>

      <!-- <div class="timeline-wrapper">
        <div class="timeline-common timeline-circle-fill"></div>
        <div class="timeline-common timeline-bar-fill"></div>
        <div class="timeline-common timeline-circle-empty"></div>
        <div class="timeline-common timeline-bar-empty"></div>
        <div class="timeline-common timeline-circle-empty"></div>
        <div class="timeline-common timeline-bar-empty"></div>
        <div class="timeline-common timeline-circle-empty"></div>
        <div class="timeline-common timeline-bar-empty"></div>
        <div class="timeline-common timeline-circle-empty"></div>
        <div class="timeline-common timeline-bar-empty"></div>
      </div> -->


      <!-- Section Divider --><div class="content-section-divider"></div>

      <section class="content-section">
        <div class="section-heading">
          Add Product
        </div>
        <div class="section-content-wrapper">
          <form method="post" class="ce-form" id="ce-add-project-product-form" style="width:100%;">
              <input type="hidden" id="project-id" name="project-id" value="<?php echo $projectid; ?>">
              <div class="project-products-list-item-form">
                <div class="ce-form-controls ce-form-control-col-5">
                  <div class="ce-form-label">Category :</div>
                  <div class="ce-form-input">
                    <select id="licence-category" name="licence-category" data-row-id="1">
                      <option value="0">Select Category</option>
                      <?php
                        $sqlgetlicencecategory = "select * from licence_category";
                        $rowgetlicencecategory = mysqli_query($con, $sqlgetlicencecategory);

                        if (mysqli_num_rows($rowgetlicencecategory) > 0)
                        {
                          $i = 0;
                          while ($i <= ($resgetlicencecategory = mysqli_fetch_array($rowgetlicencecategory)))
                          {
                      ?>
                        <option value="<?php echo $resgetlicencecategory['value']; ?>">
                          <?php echo $resgetlicencecategory['name']; ?>
                        </option>
                      <?php
                            $i++;
                          }
                        }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-5">
                  <div class="ce-form-label">Product :</div>
                  <div class="ce-form-input">
                    <select id="product" name="product" data-row-id="1">
                      <option value="0">Select Product</option>
                    </select>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-1" >
                  <div class="ce-form-label">Description  :</div>
                  <div class="ce-form-input">
                    <input type="text" id="product-desc" name="product-desc" placeholder="Description" maxlength="512">
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-1">
                  <div class="ce-form-input">
                      <input type="submit" id="ce-add-project-product-form-submit" name="ce-add-project-product-form-submit" value="Add Product" >
                  </div>
                </div>


              </div>
          </form>
        </div>
      </section>

      <!-- Section Divider -->
      <div class="content-section-divider"></div>

      <section class="content-section">
        <div class="section-heading">
          Products 
        </div>
        <div class="section-content-wrapper">
          <div id="project-product-list-section">
                    
          </div>
          <div class="ce-spinner-overlay" id="project-product-list-section-loading" style="text-align:center;display:none;">
              <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
          </div>
        </div>
      </section>

      <!-- Section Divider -->
      <div class="content-section-divider"></div>

      <section class="content-section">
        <div class="section-heading">
          Customer Sales
        </div>
        <div class="section-content-wrapper">
          <div id="customer-sales-list-section">
                    
          </div>
          <div class="ce-spinner-overlay" id="customer-sales-list-section-loading" style="text-align:center;display:none;">
              <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
          </div>
        </div>
      </section>

       <!-- Section Divider -->
      <div class="content-section-divider"></div>

      <section class="content-section">
        <div class="section-heading">
          Vendor Purchase
        </div>
        <div class="section-content-wrapper">
          <div id="vendor-purchase-list-section">
                    
          </div>
          <div class="ce-spinner-overlay" id="vendor-purchase-list-section-loading" style="text-align:center;display:none;">
              <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
          </div>
        </div>
      </section>

       <!-- Section Divider -->
      <div class="content-section-divider"></div>

      <section class="content-section">
        <div class="section-heading">
          Sub-Purchase
        </div>
        <div class="section-content-wrapper">
          <div id="sub-purchase-list-section">
                    
          </div>
          <div class="ce-spinner-overlay" id="sub-purchase-list-section-loading" style="text-align:center;display:none;">
              <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
          </div>
        </div>
      </section>

      <!-- Section Divider -->
      <div class="content-section-divider"></div>

    </div>

  </div>

</div>
</div>

<!-- Footer Include -->
<?php include "include/footer.php"; ?>

<!-- Bottom JS -->
<?php include "include/bottomjs.php"; ?>

<script>

$(document).ready(function(){

    $("#licence-project-tab").addClass(" active");
    var menuid = "#project-menu";
    $(menuid+" .dropdown-content").addClass("show");
    $(menuid+" button .fa-caret-down").css("transform","rotate(180deg)"); 

  });

</script>

<!-- Modals Section -->

<!----------------------------- Modal 1 (Select Month for Portal Price) -------------------------------->
    <div id="create-sub-purchase-modal" class="modal" style="width: 900px !important;max-width: unset;">
      <div class="timeline-wrapper">
        <div class="timeline-common timeline-circle-fill"></div>
        <div class="timeline-common timeline-bar-empty"></div>
        <div class="timeline-common timeline-circle-empty"></div>
      </div>
      <div class="modal-content-wrapper">
        <div class="modal-heading-wrapper">
          Select Month & Year of Purchase
        </div> 
        <div id="create-sub-purchase-form-cont">
          
        </div>
      </div> 
    </div>

<!----------------------------- Modal 2 (Enter Portal Price) -------------------------------->

    <div id="create-project-sale-modal"  class="modal" style="width: 900px !important;max-width: unset;">

      <div class="timeline-wrapper">
        <div class="timeline-common timeline-circle-fill"></div>
        <div class="timeline-common timeline-bar-fill"></div>
        <div class="timeline-common timeline-circle-fill"></div>
      </div>
      <div class="modal-content-wrapper" id="sales-bill-items-cont" >
        <div class="modal-heading-wrapper">
          Create Sales
        </div>
        <div id="create-sale-form-cont" >
          
        </div>
      </div>



      <div class="modal-change-btn-wrapper">
        <!-- <div class="ce-form-controls ce-form-control-col-2">
          <div class="ce-form-input">
              <a class="button show-modal-btn show-modal-back-btn" data-modal-id="portal-price-month-modal" href="javascript:void(0);" >< Back</a>
          </div>
        </div> -->
        
      </div>
    </div>


<!----------------------------- Modal 3 (Select Sales Price Month) -------------------------------->

    <div id="sales-price-month-modal" class="modal" style="width: 900px !important;max-width: unset;">
      <div class="timeline-wrapper">
        <div class="timeline-common timeline-circle-fill"></div>
        <div class="timeline-common timeline-bar-empty"></div>
        <div class="timeline-common timeline-circle-empty"></div>
      </div>
      <div class="modal-content-wrapper">
        <div class="modal-heading-wrapper">
          Select Month & Year of Sales Bill
        </div> 
        <form method="post" id="ce-sales-price-month-form">
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-input">
                <select id="sales-bill-month" name="sales-bill-month">
                  <option value="0">Select Month</option>
                  <?php

                    $sqlgetmonths = "select * from month";
                    $rowgetmonths = mysqli_query($con, $sqlgetmonths);

                    if (mysqli_num_rows($rowgetmonths) > 0)
                    {
                      $i = 0;
                      while ($i <= ($resgetmonths = mysqli_fetch_array($rowgetmonths)))
                      {
                  ?>
                    <option value="<?php echo $resgetmonths['value']; ?>">
                      <?php echo $resgetmonths['name']; ?>
                    </option>
                  <?php
                        $i++;
                      }
                    }
                  ?>
                </select>
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-input">
                <select id="sales-bill-year" name="sales-bill-year">
                  <option value="0">Select Year</option>
                  <?php

                    $sqlgetyear = "select MIN(YEAR(deployment_start)) as year from licence_project_item where project_id = ".$projectid;
                    $rowgetyear = mysqli_query($con, $sqlgetyear);

                    if (mysqli_num_rows($rowgetyear) > 0)
                    {
                      $resgetyear = mysqli_fetch_array($rowgetyear);
                      $yearval = (int)$resgetyear['year'];

                      $i = 0;
                      while ($i <= 10)
                      {

                  ?>
                    <option value="<?php echo $yearval; ?>">
                      <?php echo $yearval; ?>
                    </option>
                  <?php
                        $i++;
                        $yearval++;
                      }
                    }
                ?>
                </select>
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-1">
            <div class="ce-form-input align-right">
                <input type="submit" id="ce-generate-bill-month-form-submit" name="ce-generate-bill-month-form-submit" value="Next >">
            </div>
          </div>
        </form>
      </div> 
      <!-- <div class="modal-change-btn-wrapper"> 
        <a class="button show-modal-btn show-modal-next-btn" data-modal-id="portal-price-modal" href="javascript:void(0);" >Next ></a>
      </div> -->
    </div>

<!----------------------------- Modal 4 (Sales Price) -------------------------------->

    <div id="create-purchase-modal" class="modal" style="width: 900px !important;max-width: unset;">
      <div class="timeline-wrapper">
        <div class="timeline-common timeline-circle-fill"></div>
        <div class="timeline-common timeline-bar-fill"></div>
        <div class="timeline-common timeline-circle-fill"></div>
      </div>
      <div class="modal-content-wrapper" >
        <div class="modal-heading-wrapper">
          Create Sales
        </div>
        <div id="create-purchase-form-cont">
          
        </div>
      </div>
    </div>

<!----------------------------- Modal 5 (Select Month for Purchase price) -------------------------------->
    <div id="purchase-price-month-modal" class="modal" style="width: 900px !important;max-width: unset;">
      <div class="timeline-wrapper">
        <div class="timeline-common timeline-circle-fill"></div>
        <div class="timeline-common timeline-bar-empty"></div>
        <div class="timeline-common timeline-circle-empty"></div>
      </div>
      <div class="modal-content-wrapper">
        <div class="modal-heading-wrapper">
          Select Month & Year of Purchase Bill
        </div> 
        <form method="post" id="ce-purchase-price-month-form">
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-input">
                <select id="purchase-bill-month" name="purchase-bill-month">
                  <option value="0">Select Month</option>
                  <?php

                    $sqlgetmonths = "select * from month";
                    $rowgetmonths = mysqli_query($con, $sqlgetmonths);

                    if (mysqli_num_rows($rowgetmonths) > 0)
                    {
                      $i = 0;
                      while ($i <= ($resgetmonths = mysqli_fetch_array($rowgetmonths)))
                      {
                  ?>
                    <option value="<?php echo $resgetmonths['value']; ?>">
                      <?php echo $resgetmonths['name']; ?>
                    </option>
                  <?php
                        $i++;
                      }
                    }
                  ?>
                </select>
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-input">
                <select id="purchase-bill-year" name="purchase-bill-year">
                  <option value="0">Select Year</option>
                  <?php

                    $sqlgetyear = "select MIN(YEAR(deployment_start)) as year from licence_project_item where project_id = ".$projectid;
                    $rowgetyear = mysqli_query($con, $sqlgetyear);

                    if (mysqli_num_rows($rowgetyear) > 0)
                    {
                      $resgetyear = mysqli_fetch_array($rowgetyear);
                      $yearval = (int)$resgetyear['year'];

                      $i = 0;
                      while ($i <= 10)
                      {

                  ?>
                    <option value="<?php echo $yearval; ?>">
                      <?php echo $yearval; ?>
                    </option>
                  <?php
                        $i++;
                        $yearval++;
                      }
                    }
                ?>
                </select>
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-1">
            <div class="ce-form-input align-right">
                <input type="submit" id="ce-purchase-price-month-form-submit" name="ce-purchase-price-month-form-submit" value="Next >" >
            </div>
          </div>
        </form>
      </div> 
      <!-- <div class="modal-change-btn-wrapper"> 
        <a class="button show-modal-btn show-modal-next-btn" data-modal-id="portal-price-modal" href="javascript:void(0);" >Next ></a>
      </div> -->
    </div>

<!----------------------------- Modal 6 (Purchase Price) -------------------------------->

    <div id="purchase-price-modal" class="modal" style="width: 900px !important;max-width: unset;">
      <div class="timeline-wrapper">
        <div class="timeline-common timeline-circle-fill"></div>
        <div class="timeline-common timeline-bar-fill"></div>
        <div class="timeline-common timeline-circle-fill"></div>
      </div>
      <div class="modal-content-wrapper" id="purchase-bill-items-cont" >
        
      </div>
      <div class="modal-change-btn-wrapper">
        <div class="ce-form-controls ce-form-control-col-2">
          <div class="ce-form-input">
              <a class="button show-modal-btn show-modal-back-btn" data-modal-id="purchase-price-month-modal" href="javascript:void(0);" >< Back</a>
          </div>
        </div>
        
        <div class="ce-form-controls ce-form-control-col-2">
          <div class="ce-form-input align-right">
              <input type="button" id="ce-submit-purchase-price-btn" name="ce-submit-purchase-price-btn" value="Save & Next >">
          </div>
        </div>
      </div>
    </div>


<!----------------------------- Modal 6 (Monthly Bill Item) -------------------------------->

    <div id="monthly-bill-item-modal" class="modal" style="width: 900px !important;max-width: unset;">
      <div class="modal-content-wrapper" id="monthly-bill-item-cont" >
        
      </div>
    </div>



<script type="text/javascript" src="js/viewlicenceproject.js?uid=<?php echo uniqid(); ?>" defer ></script>


<script type="text/javascript">
  
</script>
</body>
</html>
<?php
    
      }
      else
      { 
          header("location:".getDomain()."/licenceprojects".getPageExt());
      }
    }
    else
    { 
        header("location:".getDomain()."/licenceprojects".getPageExt());
    }
  }
  else
  { 
      header("location:".getDomain());
  }

  mysqli_close($con);

?>