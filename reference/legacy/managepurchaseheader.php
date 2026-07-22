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
  <title>Purchase Headers</title>

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
        <p>Manage Purchase Headers</p>
      </div>

      <section class="content-section">
        <div class="section-heading">
          Add New Purchase Header
        </div>
        <div class="section-content-wrapper">
          <form method="post" class="ce-form" id="ce-add-purchase-header-form">

            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">Purchase Header Name :</div>
              <div class="ce-form-input">
                  <input type="text" id="purchase-header-name" name="purchase-header-name">
              </div>
            </div>

            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">Assign to Project :</div>
              <div class="ce-form-input">
                <select id="purchase-header-project-id" name="purchase-header-project-id">
                  <option value="0">Generic (All)</option>
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
              <div class="ce-form-input">
                  <input type="submit" id="ce-add-purchase-header-form-submit" name="ce-add-purchase-header-form-submit" value="Add Purchase Header">
              </div>
            </div>
          </form>
        </div>
      </section>


      <div class="content-section-divider"></div>


      <section class="content-section">
        <div class="section-heading">
          List of Purchase Headers
        </div>
        <div class="section-content-wrapper">
          <div id="purchase-header-list-section">
                    
          </div>
          <div class="ce-spinner-overlay" id="purchase-header-list-section-loading" style="text-align:center;display:none;">
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

<script >

  $(document).ready(function(){

    $("#purchase-header-tab").addClass(" active");
    var menuid = "#master-menu";
    $(menuid+" .dropdown-content").addClass("show");
    $(menuid+" button .fa-caret-down").css("transform","rotate(180deg)"); 

  });

</script>

<script type="text/javascript" src="js/managepurchaseheader.js?uid=<?php echo uniqid(); ?>" ></script>
</body>
</html>
<?php
  
  }
  else
  { 
      header("location:".getDomain());
  }

  
?>