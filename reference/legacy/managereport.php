<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }

  include "include/config.php";  

  if(isLoggedIn())
  {
    $con = connectMySQL();
    if($_SESSION['user_type'] == "1")
    {
      $createdby = "";
    }
    else
    {
      $createdby = " AND created_by = ".$_SESSION['user_id'];
    }

?>


<!DOCTYPE html>
<html <?php echo getDefaultTheme(); ?> >
<head>
  <title>Reports</title>

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
        <p>Generate Reports</p>
      </div>

      <section class="content-section">
        <div class="section-heading">
          
        </div>
        <div class="section-content-wrapper">
          <div method="post" class="ce-form" id="ce-generate-report-form">

            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">Select Project :</div>
              <div class="ce-form-input">
                <select id="ce-generate-report-project-id" name="ce-generate-report-project-id">
                  <option value="0">Select Project</option>
                  <option value="All">All Projects</option>
                  <?php

                    $sqlgetproject = "select id,name
                    from project as p 
                    where (is_active = 1 AND product_category = 1 ".$createdby.") OR id IN (select project_id from project_user_mapping where user_id = ".$_SESSION['user_id'].") order by created_on desc";
                    $rowgetproject = mysqli_query($con, $sqlgetproject);

                    if (mysqli_num_rows($rowgetproject) > 0)
                    {
                      $phn = 0;
                      while ($phn <= ($resgetproject = mysqli_fetch_array($rowgetproject)))
                      {
                    ?>
                    <option value="<?php echo $resgetproject['id']; ?>" >
                      <?php echo $resgetproject['name']; ?>
                    </option>
                    <?php
                        $phn++;
                      }
                    }
                  ?>
                </select>
                </div>
              </div>

              <div class="ce-form-controls ce-form-control-col-3">
                <div class="ce-form-label"> </div>
                <div class="ce-form-input"> </div>
              </div>

              <div class="ce-form-controls ce-form-control-col-3">
                <div class="ce-form-label"> </div>
                <div class="ce-form-input"> </div>
              </div>

              <div class="ce-form-controls ce-form-control-col-6 toggle-report-btn">
                <div class="ce-form-input">
                    <button class="ce-generate-report-btn" value="1">Full Report</button>
                </div>
              </div>
              <div class="ce-form-controls ce-form-control-col-6 toggle-report-btn">
                <div class="ce-form-input">
                    <button class="ce-generate-report-btn" value="2">Bill Report</button>
                </div>
              </div>
              <div class="ce-form-controls ce-form-control-col-6">
                <div class="ce-form-input">
                    <button class="ce-generate-report-btn" value="3">CI Report</button>
                </div>
              </div>
              <div class="ce-form-controls ce-form-control-col-6">
                <div class="ce-form-input">
                    <button class="ce-generate-report-btn" value="4">CO Report</button>
                </div>
              </div>
              <div class="ce-form-controls ce-form-control-col-6">
                <div class="ce-form-input">
                    <button class="ce-generate-report-btn" value="5">DN Report</button>
                </div>
              </div>
              <div class="ce-form-controls ce-form-control-col-6">
                <div class="ce-form-input">
                    <button class="ce-generate-report-btn" value="6">Invoice Report</button>
                </div>
              </div>
          </div>
        </div>
      </section>

      <div class="content-section-divider"></div>

      <section class="content-section">
        <div class="section-heading">
          
        </div>
        <div class="section-content-wrapper">
          <div id="report-section">
                    
          </div>
          <div class="ce-spinner-overlay" id="report-section-loading" style="text-align:center;display:none;">
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

    $("#product-tab").addClass(" active");
    var menuid = "#master-menu";
    $(menuid+" .dropdown-content").addClass("show");
    $(menuid+" button .fa-caret-down").css("transform","rotate(180deg)"); 


  });

</script>

<!----------------------------- Modal 1 (Report Month) -------------------------------->
  <div id="generate-report-month-form-modal" class="modal" style="max-width: unset;">
    <div class="modal-heading-wrapper">
      
    </div>
    <div class="modal-content-wrapper" id="generate-report-month-form-cont">
        
    </div>
  </div>

<!----------------------------- Modal 2 (Report Date) -------------------------------->
  <div id="generate-report-date-form-modal" class="modal" style="max-width: unset;">
    <div class="modal-heading-wrapper">
      
    </div>
    <div class="modal-content-wrapper" id="generate-report-date-form-cont">
        
    </div>
  </div>

<script type="text/javascript" src="js/managereport.js?uid=<?php echo uniqid(); ?>" ></script>
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