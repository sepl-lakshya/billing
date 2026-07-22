<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }

  include "include/config.php";  

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
        <p>Manage Projects</p>
      </div>

      <section class="content-section">
        <div class="section-heading">
          Create New Project
        </div>
        <div class="section-content-wrapper">
          <form method="post" class="ce-form" id="ce-create-project-form">
            
            <!-- Form Section -->
            <!-- <div class="ce-form-section-heading">Project Details</div> -->
            
            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">Project Name :</div>
              <div class="ce-form-input">
                  <input type="text" id="project-name" name="project-name">
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
                  $con = connectMySQL();
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

            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">Tender Reference Number :</div>
              <div class="ce-form-input">
                  <input type="text" id="tender-ref-no" name="tender-ref-no">
              </div>
            </div>
            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">Project Start Date :</div>
              <div class="ce-form-input">
                  <input type="date" id="project-start-date" name="project-start-date" max="<?php echo date("Y-m-d"); ?>">
              </div>
            </div>
            <!-- <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">RI Discount (%) :</div>
              <div class="ce-form-input">
                  <input type="text" id="ri-discount" name="ri-discount">
              </div>
            </div>
            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">PAYG Discount (%) :</div>
              <div class="ce-form-input">
                  <input type="text" id="payg-discount" name="payg-discount">
              </div>
            </div> -->
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
          List of Projects
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

<script>

  $(document).ready(function(){

    $("#project-tab").addClass(" active");

  });

</script>

<script type="text/javascript" src="js/manageproject.js?uid=<?php echo uniqid(); ?>" ></script>


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