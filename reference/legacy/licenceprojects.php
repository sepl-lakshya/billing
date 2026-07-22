<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }

  include "include/config.php";  
  $con = connectMySQL();

  if(isLoggedIn())
  {

?>


<!DOCTYPE html>
<html <?php echo getDefaultTheme(); ?> >
<head>
  <title>Projects</title>

  <!-- Head Section Include -->
  <?php include "include/headsection.php"; ?>
  
</head>
<body>
<div class="app-container">
  <div class="app-header">

    <!-- Header Include -->
    <?php include "include/header.php";  ?>

  <div class="app-content">
    
    <!-- Sidebar Include -->
    <?php include "include/sidebar.php"; ?>
    
    <div class="content-container">
      <div class="content-container-header">
        <p>Manage Licence Projects</p>
      </div>

      <section class="content-section">
        <div class="section-heading">
          Create New Project
        </div>
        <div class="section-content-wrapper">
          <form method="post" class="ce-form" id="ce-create-project-form">
            <input type="hidden" id="product-category" name="product-category" value="2">
            <!-- Form Section -->
            <!-- <div class="ce-form-section-heading">Project Details</div> -->
            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">Project Name : <br><span style="font-size: .8em;color: orangered;">(Note: This cannot be changed later)</span></div>
              <div class="ce-form-input">
                  <input type="text" id="project-name" name="project-name">
              </div>
            </div>
            
            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">Tender Reference Number :</div>
              <div class="ce-form-input">
                  <input type="text" id="tender-ref-no" name="tender-ref-no">
              </div>
            </div>
            <div class="ce-form-controls ce-form-control-col-3" id="contract-period-wrapper">
              <div class="ce-form-label">Contract Period :</div>
              <div class="ce-form-input">
                <select id="contract-year" name="contract-year" data-row-id="1" style="width:49%;">
                  <option value="0">Select Year</option>
                  <?php

                      $sqlgetcontractyear = "select * from contract_year";
                      $rowgetcontractyear = mysqli_query($con, $sqlgetcontractyear);

                      if (mysqli_num_rows($rowgetcontractyear) > 0)
                      {
                        $i = 0;
                        while ($i <= ($resgetcontractyear = mysqli_fetch_array($rowgetcontractyear)))
                        {
                    ?>
                      <option value="<?php echo $resgetcontractyear['value']; ?>">
                        <?php echo $resgetcontractyear['name']; ?>
                      </option>
                    <?php
                          $i++;
                        }
                      }
                  ?>
                </select>
                <select id="contract-month" name="contract-month" data-row-id="1" style="width:49%;">
                  <option value="0">Select Month</option>
                  <?php

                      $sqlgetcontractmonth = "select * from contract_month";
                      $rowgetcontractmonth = mysqli_query($con, $sqlgetcontractmonth);

                      if (mysqli_num_rows($rowgetcontractmonth) > 0)
                      {
                        $i = 0;
                        while ($i <= ($resgetcontractmonth = mysqli_fetch_array($rowgetcontractmonth)))
                        {
                    ?>
                      <option value="<?php echo $resgetcontractmonth['value']; ?>">
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

            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">Project Start Date : <br><span style="font-size: .8em;color: orangered;">(Note: This cannot be changed later)</span></div>
              <div class="ce-form-input">
                  <input type="date" id="project-start-date" name="project-start-date" max="<?php echo date("Y-m-d"); ?>">
              </div>
            </div>
            
            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">City :</div>
              <div class="ce-form-input">
                  <input type="text" id="project-city" name="project-city">
              </div>
            </div>

            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">State :</div>
              <div class="ce-form-input">
                <select id="project-state" name="project-state">
                  <option value="0">Select State</option>
                <?php
                  $sqlgetstate = "select * from states";
                  $rowgetstate = mysqli_query($con, $sqlgetstate);

                  if (mysqli_num_rows($rowgetstate) > 0)
                  {
                    $i = 0;
                    while ($i <= ($resgetstate = mysqli_fetch_array($rowgetstate)))
                    {
                ?>
                    <option value="<?php echo $resgetstate['value']; ?>"><?php echo $resgetstate['name']; ?></option>
                <?php
                      $i++;
                    }
                  }
                ?>
                </select>
              </div>
            </div>

            
            <div class="ce-form-controls ce-form-control-col-1">
              <div class="ce-form-label">Description (Max 1999 characters):</div>
              <div class="ce-form-input">
                  <textarea id="project-description" name="project-description" maxlength="1999"></textarea>
              </div>
            </div>
            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-input">
                  <input type="submit" id="ce-create-project-form-submit" name="ce-create-project-form-submit" value="Create Project" >
              </div>
            </div>
          </form>
        </div>
      </section>

      <!-- Section Divider --><div class="content-section-divider"></div>

      <section class="content-section">
        <div class="section-heading">
          List of Licence Projects
        </div>
        <div class="section-content-wrapper">
          <div id="project-list-section">
                    
          </div>
          <div class="ce-spinner-overlay" id="project-list-section-loading" style="text-align:center;display:none;">
              <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
          </div>
        </div>
      </section>

      
    </div>

</div>

</div>
</div>



<!-- Footer Include -->
<?php include "include/footer.php"; ?>

<!-- Bottom JS -->
<?php include "include/bottomjs.php"; ?>


<!----------------------------- Modal 1 (Assign Project) -------------------------------->

    <div id="assign-project-modal" class="modal" style="width: 900px !important;max-width: unset;">
      <div class="modal-content-wrapper" id="assign-project-cont" >
          
      </div>
    </div>
    
<script>

  $(document).ready(function(){

    $("#licence-project-tab").addClass(" active");
    var menuid = "#licence-main-menu";
    $(menuid+" .dropdown-content").addClass("show");
    $(menuid+" button .fa-caret-down").css("transform","rotate(180deg)"); 

  });

</script>

<script type="text/javascript" src="js/licenceproject.js?uid=<?php echo uniqid(); ?>" ></script>


</body>
</html>
<?php
  
  }
  else
  { 
      header("location:".getDomain());
  }

  mysqli_close($con);

?>