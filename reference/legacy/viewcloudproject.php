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
        $createdby = " AND (created_by = ".$_SESSION['user_id']." OR id IN (select project_id from project_user_mapping where user_id = ".$_SESSION['user_id']."))";
      }

      $sqlgetproject = "select *,YEAR(start_date) as start_year, (select name from states where value = p.state) as state_name from project as p where id = ".$projectid." AND hash = '".$projecthash."' ".$createdby;

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
<div class="hidden-project-name" style="display:none;text-align: center;font-size: 2em;margin-left: 50px;">
  <p>Project : <?php echo $resgetproject['name']; ?> </p>
</div>
<div class="app-container">
  <div class="app-header">

    <!-- Header Include -->
    <?php include "include/header.php";  ?>

  <div class="app-content">
    
    <!-- Sidebar Include -->
    <?php include "include/sidebar.php"; ?>
    
    <div class="content-container" style="padding-top: 0;">
      <div class="content-container-header" style="margin-top:30px;">
        <p>Project : <?php echo $resgetproject['name']; ?> </p>
      </div>

      <!-- Project Detail Section -->
      <section class="content-section" id="show-project-detail-section">
        <div class="section-heading">
          Project Details : 
          <?php
            // if($resgetproject['created_by'] == $_SESSION['user_id'])
            // {
          ?>
          <a href="javascript:void(0);" id="edit-project-detail-btn" style="text-decoration:underline;margin-left: 25px;"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>Edit</a>
          <?php 
            // } 
          ?>
        </div>


        <!-- Show Project Detail Section -->
        <div class="section-content-wrapper" >
            
            <!-- Form Section -->
            <!-- <div class="ce-form-section-heading">Project Details</div> -->
          <div class="ce-form">  
            <div class="ce-form-controls ce-form-control-col-4">
              <div class="ce-form-label">City :</div>
              <div class="ce-form-input">
                  <?php echo $resgetproject['city']; ?>
              </div>
            </div>
            <div class="ce-form-controls ce-form-control-col-4">
              <div class="ce-form-label">State :</div>
              <div class="ce-form-input">
                <?php echo $resgetproject['state_name']; ?>
              </div>
            </div>

            <div class="ce-form-controls ce-form-control-col-4">
              <div class="ce-form-label">Tender Reference Number :</div>
              <div class="ce-form-input">
                  <?php echo $resgetproject['tender_ref_no']; ?>
              </div>
            </div>
            <div class="ce-form-controls ce-form-control-col-4">
              <div class="ce-form-label">Project Start Date :</div>
              <div class="ce-form-input">
                  <?php echo date("d-m-Y",strtotime($resgetproject['start_date'])); ?>
              </div>
            </div>
            <div class="ce-form-controls ce-form-control-col-1">
              <div class="ce-form-label">Descripton :</div>
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
              <div class="ce-form-controls ce-form-control-col-3">
                <div class="ce-form-label">City :</div>
                <div class="ce-form-input">
                   <input type="text" id="edit-project-city" name="edit-project-city" value="<?php echo $resgetproject['city']; ?>" >
                </div>
              </div>
              <div class="ce-form-controls ce-form-control-col-3">
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

              <div class="ce-form-controls ce-form-control-col-3">
                <div class="ce-form-label">Tender Reference Number :</div>
                <div class="ce-form-input">
                    <input type="text" id="edit-tender-ref-no" name="edit-tender-ref-no" value="<?php echo $resgetproject['tender_ref_no']; ?>" >
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
          Project Discount 
        </div>
        <div class="section-content-wrapper" >

        <?php
          $showpricesection = "";
          $sqlgetprojectdiscount = "select * 
           from project_discount where project_id = ".$projectid;
          $rowgetprojectdiscount = mysqli_query($con, $sqlgetprojectdiscount);

          if (mysqli_num_rows($rowgetprojectdiscount) > 0)
          {
        ?>
          <div class="ce-form-controls ce-form-control-col-4">
            <div class="ce-form-input">
                <input type="button" id="ce-change-project-discount-btn" name="ce-change-project-discount-btn" value="Change Discount" >
            </div>
          </div>
          <div id="project-discount-table-section">
            <table class="table-element align-center" cellpadding="0" cellspacing="0" style="font-size: .8em;">
              <thead>
                <tr>
                  <th style="width:20%;">From</th>
                  <th style="width:20%;">To</th>
                  <th style="width:20%;">PAYG Discount(%)</th>
                  <th style="width:20%;">RI Discount(%)</th>
                  <th style="width:20%;">Credit Days</th>
                </tr>
              </thead>
              <tbody>
              <?php
                    $d = 0;
                    while ($d <= ($resgetprojectdiscount = mysqli_fetch_array($rowgetprojectdiscount)))
                    {
                ?>
                <tr>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo date("d M Y",strtotime($resgetprojectdiscount['from_date'])); ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php 

                        if($resgetprojectdiscount['to_date'] == "" || $resgetprojectdiscount['to_date'] == NULL)
                        {
                          echo "-";
                        }
                        else
                        {
                          echo date("d M Y",strtotime($resgetprojectdiscount['to_date'])); 
                        }
                      ?>
                    </div>
                  </td>  
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectdiscount['payg_discount']." %"; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectdiscount['ri_discount']." %"; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php
                        if((int)$resgetprojectdiscount['credit_days'] <= 1)
                        {
                          echo $resgetprojectdiscount['credit_days']." day"; 
                        }
                        else
                        {
                          echo $resgetprojectdiscount['credit_days']." days"; 
                        }
                      ?>
                    </div>
                  </td>               
                <?php
                      $d++;
                    }
                ?>
              </tbody>
            </table>    
          </div>
          <div class="ce-spinner-overlay" id="project-discount-table-section-loading" style="text-align:center;display:none;">
              <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
          </div>
        <?php 
          }
          else
          {
            $showpricesection = "display:none;";
        ?>
          <div class="ce-form-controls ce-form-control-col-4">
            <div class="ce-form-input">
                <input type="button" id="ce-add-project-discount-btn" name="ce-add-project-discount-btn" value="Add Discount" >
            </div>
          </div>
        <?php
          }
        ?>
        </div>
      </section>

      <!-- Section Divider --><div class="content-section-divider"></div>
      <section class="content-section">
        <div class="section-heading">
          Add Sales Header  
        </div>
        <div class="section-content-wrapper">
          <form method="post" class="ce-form" id="ce-add-project-header-form" style="width:100%;">
              <input type="hidden" id="add-header-project-id" name="add-header-project-id" value="<?php echo $projectid; ?>">
              <div class="project-products-list-item-form">

                <div class="ce-form-controls ce-form-control-col-2">
                  <div class="ce-form-label">Header Name :</div>
                  <div class="ce-form-input">
                    <input type="text" id="add-header-name" name="add-header-name" placeholder="Header Name">
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-4">
                  <div class="ce-form-label">Quantity :</div>
                  <div class="ce-form-input">
                    <input type="number" id="add-header-quantity" name="add-header-quantity" placeholder="Quantity" min="1">
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-4">
                  <div class="ce-form-input align-right">
                      <input type="submit" id="ce-add-project-header-form-submit" name="ce-add-project-header-form-submit" value="Add Header" >
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-1">
                  <div class="ce-form-label">Description :</div>
                  <div class="ce-form-input">
                    <textarea id="add-header-description" name="add-header-description" style="height:40px;"></textarea>
                  </div>
                </div>



              </div>
          </form>
        </div>
      </section>

      <!-- Section Divider --><div class="content-section-divider"></div>
      <section class="content-section" id="project-items-main-wrapper">
        <div class="section-heading">
          Project Items &nbsp;&nbsp;<a href="javascript:void(0);" id="toggle-project-item-view-btn" style="font-weight: normal;font-size: .8em;text-decoration: underline;" data-status="0">Show Items</a>  <br> 
        </div>
        <div class="section-content-wrapper" id="project-items-section-main" style="display:none;">
          <div id="project-product-list-section">
                    
          </div>
          <div class="ce-spinner-overlay" id="project-product-list-section-loading" style="text-align:center;display:none;">
              <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
          </div>
        </div>
      </section>

    <div id="show-after-discout-section-wrapper" style="<?php echo $showpricesection; ?>">
      <!-- Section Divider --><div class="content-section-divider"></div>
      
      


      <section class="content-section">
        <div class="content-section-wrapper-col-3">
          <div class="section-heading">
            Portal Bill
          </div>
          <div class="section-content-wrapper">
            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-input">
                  <!-- <a class="button" id="generate-sales-bill-btn" href="javascript:void(0);" >Generate Sales Bill</a> -->
                  <a class="button show-modal-btn" data-modal-id="portal-price-month-modal" href="javascript:void(0);" >Add/Update</a>
              </div>
            </div>
          </div>
        </div>
        <div class="content-section-wrapper-col-3">
          <div class="section-heading">
            Sales Bill
          </div>
          <div class="section-content-wrapper">
            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-input">
                  <!-- <a class="button" id="generate-sales-bill-btn" href="javascript:void(0);" >Generate Sales Bill</a> -->
                  <a class="button show-modal-btn" data-modal-id="sales-price-month-modal" href="javascript:void(0);" >Add/Update</a>
              </div>
            </div>
          </div>
        </div>
        <div class="content-section-wrapper-col-3">
          <div class="section-heading">
          Purchase Bill
          </div>
          <div class="section-content-wrapper">
            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-input">
                  <!-- <a class="button" id="generate-sales-bill-btn" href="javascript:void(0);" >Generate Sales Bill</a> -->
                  <a class="button show-modal-btn" data-modal-id="purchase-price-month-modal" href="javascript:void(0);" >Add/Update</a>
              </div>
            </div>
          </div>
        </div>
      </section>



      <!-- Section Divider --><div class="content-section-divider"></div>

      <section class="content-section" id="monthly-bill-status-main-wrapper">
        <div class="section-heading">
          Monthly Status 
          <button class="print-btn" onclick="printDiv('monthly-bill-status-main-wrapper');">Print</button>
          </div>
          <div class="section-content-wrapper">
            <div id="monthly-bill-status-section">
                    
            </div>
            <div class="ce-spinner-overlay" id="monthly-bill-status-section-loading" style="text-align:center;display:none;">
                <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
            </div>
            <div class="no-print" style="margin-top:10px;">
              <div style="vertical-align: middle;height:15px;width: 20px;background:#4FFFB0;display: inline-block;"></div> : OK &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp; 

              <div style="vertical-align: middle;height:15px;width: 20px;background:#FA8072;display: inline-block;"></div> : Deviation &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;  
              <div style="vertical-align: middle;height:15px;width: 20px;background:#FFC72C;display: inline-block;"></div> : DN Generated &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp; 
              
              <div style="vertical-align: middle;height:15px;width: 20px;background:#007FFF;display: inline-block;"></div> : CN Received &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp; 

              <div style="vertical-align: middle;height:15px;width: 20px;background:grey;display: inline-block;"></div> : Pending 
            </div>
          </div>
      </section>

      <!-- Section Divider --><div class="content-section-divider"></div>

      <section class="content-section" id="project-cloud-inward-main-wrapper">
          <div class="section-heading">
            Cloud Inwards
            <button class="print-btn" onclick="printDiv('project-cloud-inward-main-wrapper');">Print</button>
          </div>
          <div class="section-content-wrapper">
            <div id="project-cloud-inward-section">
                
            </div>
            <div class="ce-spinner-overlay" id="project-cloud-inward-section-loading" style="text-align:center;display:none;">
                <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
            </div>
          </div>
      </section>

      <!-- Section Divider --><div class="content-section-divider"></div>

      <section class="content-section" id="project-cloud-outward-main-wrapper">
          <div class="section-heading">
            Cloud Outwards
            <button class="print-btn" onclick="printDiv('project-cloud-outward-main-wrapper');">Print</button>
          </div>
          <div class="section-content-wrapper">
            <div id="project-cloud-outward-section">
                
            </div>
            <div class="ce-spinner-overlay" id="project-cloud-outward-section-loading" style="text-align:center;display:none;">
                <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
            </div>
          </div>
      </section>


      <!-- Section Divider --><div class="content-section-divider"></div>

      <section class="content-section" id="purchase-invoice-main-wrapper">
          <div class="section-heading">
            Purchase Invoices 
            <button class="print-btn" onclick="printDiv('purchase-invoice-main-wrapper');">Print</button>
          </div>
          <div class="section-content-wrapper no-print">
            <div class="ce-form">
              <div class="ce-form-controls ce-form-control-col-1">
                <div class="ce-form-input">
                  <input type="button" class="show-add-invoice-form-modal-btn" value="Add New Invoice" data-load-select="1">
                </div>
              </div>          
            </div>
          </div>
          <div class="section-content-wrapper">
            <div id="purchase-invoice-section">
                
            </div>
            <div class="ce-spinner-overlay" id="purchase-invoice-section-loading" style="text-align:center;display:none;">
                <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
            </div>
          </div>
      </section>

      <!-- Section Divider --><div class="content-section-divider"></div>

      <section class="content-section" id="debit-note-section-main-wrapper">
          <div class="section-heading">
            Debit Notes
            <button class="print-btn" onclick="printDiv('debit-note-section-main-wrapper');">Print</button>
          </div>
          <div class="section-content-wrapper no-print">
            <div class="ce-form">
              <div class="ce-form-controls ce-form-control-col-1">
                <div class="ce-form-input">
                  <input type="button" class="show-create-debit-note-modal-btn" value="Create Debit Note" data-is-general-debit-note="1">
                </div>
              </div>          
            </div>
          </div>
          <div class="section-content-wrapper">
            <div id="debit-note-section">
                
            </div>
            <div class="ce-spinner-overlay" id="debit-note-section-loading" style="text-align:center;display:none;">
                <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
            </div>
          </div>
      </section>

      <!-- Section Divider --><div class="content-section-divider"></div>

      <section class="content-section" id="credit-note-section-main-wrapper">
          <div class="section-heading">
            Credit Notes
            <button class="print-btn" onclick="printDiv('credit-note-section-main-wrapper');">Print</button>
          </div>
          <div class="section-content-wrapper">
            <div id="credit-note-section">
                
            </div>
            <div class="ce-spinner-overlay" id="credit-note-section-loading" style="text-align:center;display:none;">
                <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
            </div>
          </div>
      </section>

      <!-- Section Divider --><div class="content-section-divider"></div>

       <section class="content-section" id="attachment-section-main-wrapper">
          <div class="section-heading">
            Attachments <span style="font-weight: normal;">( Extensions Allowed : .pdf, .xlsx, .xls, .doc, .docx, .png, .jpg, .jpeg ) Max : 2MB</span>
          </div>
          <div class="section-content-wrapper no-print">
            <form method="POST" class="ce-form" id="ce-file-attachment-form">
              <input type="hidden" name="attachment-project-id" id="attachment-project-id" value="<?php echo $projectid; ?>">
              
              <div class="ce-form-controls ce-form-control-col-3">
                <div class="ce-form-label">Title : </div>
                <div class="ce-form-input">
                   <input type="text" id="file-title" name="file-title" >
                </div>
              </div>

              <div class="ce-form-controls ce-form-control-col-3">
                <div class="ce-form-label">Select File : </div>
                <div class="ce-form-input">
                   <input type="file" id="attachment-file" name="attachment-file" >
                </div>
              </div>
              
              <div class="ce-form-controls ce-form-control-col-6">
                <div class="ce-form-input">
                    <input type="submit" id="ce-file-attachment-form-submit" name="ce-file-attachment-form-submit" value="Upload" >
                </div>
              </div>
            </form> 
          </div>

          <div class="section-content-wrapper">
            <div id="attachment-section">
                
            </div>
            <div class="ce-spinner-overlay" id="attachment-section-loading" style="text-align:center;display:none;">
                <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
            </div>
          </div>
      </section>

      <!-- Section Divider --><div class="content-section-divider"></div>
      



    </div>

      

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

    $("#cloud-project-tab").addClass(" active");
    var menuid = "#cloud-main-menu";
    $(menuid+" .dropdown-content").addClass("show");
    $(menuid+" button .fa-caret-down").css("transform","rotate(180deg)"); 

  });
</script>

<!-- Modals Section -->

<!----------------------------- Modal 1 (Select Month for Portal Price) -------------------------------->
    <div id="portal-price-month-modal" class="modal" style="max-width: unset;">
      <div class="timeline-wrapper">
        <div class="timeline-common timeline-circle-fill"></div>
        <div class="timeline-common timeline-bar-empty"></div>
        <div class="timeline-common timeline-circle-empty"></div>
      </div>
      <div class="modal-content-wrapper">
        <div class="modal-heading-wrapper">
          Select Month & Year of Portal Bill
        </div> 
        <form method="post" id="ce-generate-bill-month-form">
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-input">
                <select id="bill-year" name="bill-year">
                  <option value="0">Select Year</option>
                  <?php

                      $yearval = (int)$resgetproject['start_year'];

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
                ?>
                </select>
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-input">
                <select id="bill-month" name="bill-month">
                  <option value="0">Select Month</option>
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
    </div>

<!----------------------------- Modal 2 (Enter Portal Price) -------------------------------->

    <div id="portal-price-modal"  class="modal" style="max-width: unset;">

      <div class="timeline-wrapper no-print">
        <div class="timeline-common timeline-circle-fill"></div>
        <div class="timeline-common timeline-bar-fill"></div>
        <div class="timeline-common timeline-circle-fill"></div>
      </div>
      <div class="modal-content-wrapper" id="portal-bill-items-cont" >
        
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

    <div id="sales-price-month-modal" class="modal" style="max-width: unset;">
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
                <select id="sales-bill-year" name="sales-bill-year">
                  <option value="0">Select Year</option>
                  <?php

                      $yearval = (int)$resgetproject['start_year'];

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
                ?>
                </select>
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-input">
                <select id="sales-bill-month" name="sales-bill-month">
                  <option value="0">Select Month</option>
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

    <div id="sales-price-modal" class="modal" style="max-width: unset;">
      <div class="timeline-wrapper no-print">
        <div class="timeline-common timeline-circle-fill"></div>
        <div class="timeline-common timeline-bar-fill"></div>
        <div class="timeline-common timeline-circle-fill"></div>
      </div>
      <div class="modal-content-wrapper" id="sales-bill-items-cont" >
        
      </div>
    </div>

<!----------------------------- Modal 5 (Select Month for Purchase price) -------------------------------->
    <div id="purchase-price-month-modal" class="modal" style="max-width: unset;">
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
                <select id="purchase-bill-year" name="purchase-bill-year">
                  <option value="0">Select Year</option>
                  <?php

                      $yearval = (int)$resgetproject['start_year'];

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
                ?>
                </select>
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-input">
                <select id="purchase-bill-month" name="purchase-bill-month">
                  <option value="0">Select Month</option>
                </select>
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-1">
            <div class="ce-form-input align-right">
                <input type="submit" id="ce-purchase-price-month-form-submit" name="ce-purchase-price-month-form-submit" value="Next >">
            </div>
          </div>
        </form>
      </div> 
      <!-- <div class="modal-change-btn-wrapper"> 
        <a class="button show-modal-btn show-modal-next-btn" data-modal-id="portal-price-modal" href="javascript:void(0);" >Next ></a>
      </div> -->
    </div>

<!----------------------------- Modal 6 (Purchase Price) -------------------------------->

    <div id="purchase-price-modal" class="modal" style="max-width: unset;">
      <div class="timeline-wrapper no-print">
        <div class="timeline-common timeline-circle-fill"></div>
        <div class="timeline-common timeline-bar-fill"></div>
        <div class="timeline-common timeline-circle-fill"></div>
      </div>
      <div class="modal-content-wrapper" id="purchase-bill-items-cont" >
        
      </div>
    </div>


<!----------------------------- Modal 7 (Monthlly Bill Item) -------------------------------->

    <div id="monthly-bill-item-modal" class="modal" style="max-width: unset;">
      <div class="modal-content-wrapper" id="monthly-bill-item-cont" >
        
      </div>
    </div>
<!----------------------------- Modal 8 (Monthly Bill Progress) -------------------------------->

    <div id="monthly-bill-progress-modal" class="modal" style="max-width: unset;">
      <div class="modal-content-wrapper" id="monthly-bill-progress-cont" >
        
      </div>
    </div>

<!----------------------------- Modal 9 (Update Project Discount) -------------------------------->

    <div id="update-project-discount-modal" class="modal" style="max-width: unset;">
      <div class="modal-content-wrapper" id="update-project-discount-cont" >
        
      </div>
    </div>

<!----------------------------- Modal 10 (Add Project Discount) -------------------------------->

    <div id="add-project-discount-modal" class="modal" style="max-width: unset;">
      <div class="modal-content-wrapper" id="add-project-discount-cont" >
        <form method="post" id="ce-add-cloud-project-discount-form">
          <input type="hidden" id="discount-project-id" name="discount-project-id" value="<?php echo $projectid; ?>">          
          <div class="ce-form-controls ce-form-control-col-3">
            <div class="ce-form-label">RI Discount (%) :</div>
            <div class="ce-form-input">
              <input type="text" id="add-ri-discount" name="add-ri-discount" placeholder="RI Discount" value="0">
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-3">
            <div class="ce-form-label">PAYG Discount (%) :</div>
            <div class="ce-form-input">
              <input type="text" id="add-payg-discount" name="add-payg-discount" placeholder="PAYG Discount" value="0">
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-3">
            <div class="ce-form-label">Credit Days :</div>
            <div class="ce-form-input">
              <input type="number" id="add-credit-days" name="add-credit-days" placeholder="Credit Days" value="0" min="0">
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-1">
            <div class="ce-form-input">
                <input type="submit" id="ce-add-cloud-project-discount-form-submit" name="ce-add-cloud-project-discount-form-submit" value="Save" >
            </div>
          </div>
        </form>
      </div>
    </div>

<!----------------------------- Modal 10 (Add Project Item) -------------------------------->

    <div id="add-project-item-form-modal" class="modal" style="max-width: unset;">
      <div class="modal-heading-wrapper">
        Add Project Item
      </div>
      <div class="modal-content-wrapper" id="add-project-item-form-cont">
      </div>
    </div>

<!------------------------ Modal 11 (Deactivate Item Date Model) ---------------------------->

    <div id="deactivate-project-item-form-modal" class="modal" style="max-width: unset;">
      <div class="modal-heading-wrapper">
        Deactivate Projecct Item
      </div>
      <div class="modal-content-wrapper" id="deactivate-project-item-form-cont">
          
      </div>
    </div>

<!----------------------------- Modal 12 (Edit Project Header Model) -------------------------------->

    <div id="edit-project-header-form-modal" class="modal" style="max-width: unset;">
      <div class="modal-heading-wrapper">
        Edit Projecct Header
      </div>
      <div class="modal-content-wrapper" id="edit-project-header-form-cont">
          
      </div>
    </div>

<!----------------------------- Modal 13 (Edit Project Item Model) -------------------------------->

    <div id="edit-project-item-form-modal" class="modal" style="max-width: unset;">
      <div class="modal-heading-wrapper">
        Edit Product
      </div>
      <div class="modal-content-wrapper" id="edit-project-item-form-cont">
          
      </div>
    </div>

<!------------------- Modal 14 (Change Purchase Header Form Modal) --------------------------->

    <div id="change-purchase-header-form-modal" class="modal" style="max-width: unset;">
      <div class="timeline-wrapper">
        <div class="timeline-common timeline-circle-fill"></div>
        <div class="timeline-common timeline-bar-fill"></div>
        <div class="timeline-common timeline-circle-fill"></div>
      </div>
      <div class="modal-content-wrapper" id="change-purchase-header-form-cont" >
        
      </div>
    </div>

<!-------------------- Modal 15 (Create Cloud Inward Form Model) --------------------------->

    <div id="create-cloud-inward-form-modal" class="modal" style="max-width: unset;">
      <div class="modal-heading-wrapper">
        
      </div>
      <div class="modal-content-wrapper" id="create-cloud-inward-form-cont">
          
      </div>
    </div>

<!-------------------- Modal 15 (Cancel Cloud Inward Form Model) --------------------------->

    <div id="cancel-cloud-inward-form-modal" class="modal" style="max-width: unset;">
      <div class="modal-heading-wrapper">
        
      </div>
      <div class="modal-content-wrapper" id="cancel-cloud-inward-form-cont">
          
      </div>
    </div>

<!-------------------- Modal 15 (Create Cloud Outward Form Model) --------------------------->

    <div id="create-cloud-outward-form-modal" class="modal" style="max-width: unset;">
      <div class="modal-heading-wrapper">
        
      </div>
      <div class="modal-content-wrapper" id="create-cloud-outward-form-cont">
          
      </div>
    </div>

<!-------------------- Modal 15 (Cancel Cloud Outward Form Model) --------------------------->

    <div id="cancel-cloud-outward-form-modal" class="modal" style="max-width: unset;">
      <div class="modal-heading-wrapper">
        
      </div>
      <div class="modal-content-wrapper" id="cancel-cloud-outward-form-cont">
          
      </div>
    </div>

<!-------------------- Modal 16 (Add Project Invoice Model) --------------------------->

    <div id="add-invoice-form-modal" class="modal" style="max-width: unset;">
      <div class="modal-heading-wrapper">
        
      </div>
      <div class="modal-content-wrapper" id="add-invoice-form-cont">
          
      </div>
    </div>

<!-------------------- Modal 17 (Create Debit Note Form Model) --------------------------->

    <div id="create-debit-note-form-modal" class="modal" style="max-width: unset;">
      <div class="modal-heading-wrapper">
        
      </div>
      <div class="modal-content-wrapper" id="create-debit-note-form-cont">
          
      </div>
    </div>

<!-------------------- Modal 18 (Create Credit Note Form Model) --------------------------->

    <div id="create-credit-note-form-modal" class="modal" style="max-width: unset;">
      <div class="modal-heading-wrapper">
        
      </div>
      <div class="modal-content-wrapper" id="create-credit-note-form-cont">
          
      </div>
    </div>

<!-------------------- Modal 19 (Create Cloud Inward Form Model) --------------------------->

    <div id="create-invoice-cloud-inward-form-modal" class="modal" style="max-width: unset;">
      <div class="modal-heading-wrapper">
        
      </div>
      <div class="modal-content-wrapper" id="create-invoice-cloud-inward-form-cont">
          
      </div>
    </div>

<!-------------------- Modal 20 (Create Invoice Cloud Inward Form Model) --------------------------->

    <div id="cancel-invoice-cloud-inward-form-modal" class="modal" style="max-width: unset;">
      <div class="modal-heading-wrapper">
        
      </div>
      <div class="modal-content-wrapper" id="cancel-invoice-cloud-inward-form-cont">
          
      </div>
    </div>



<script type="text/javascript" src="js/viewcloudproject.js?uid=<?php echo uniqid(); ?>" defer ></script>


<script type="text/javascript">
  
</script>
</body>
</html>
<?php
    
      }
      else
      { 
          header("location:".getDomain()."/cloudprojects".getPageExt());
      }
    }
    else
    { 
        header("location:".getDomain()."/cloudprojects".getPageExt());
    }
  }
  else
  { 
      header("location:".getDomain());
  }

  mysqli_close($con);

?>