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
  <title>OEMs</title>

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
        <p>Manage OEMs</p>
      </div>

      <section class="content-section">
        <div class="section-heading">
          Add New OEMs
        </div>
        <div class="section-content-wrapper">
          <form method="post" class="ce-form" id="ce-add-oem-form">

            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">OEM Name :</div>
              <div class="ce-form-input">
                  <input type="text" id="oem-name" name="oem-name">
              </div>
            </div>

            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-input">
                  <input type="submit" id="ce-add-oem-form-submit" name="ce-add-oem-form-submit" value="Add OEM">
              </div>
            </div>
          </form>
        </div>
      </section>


      <div class="content-section-divider"></div>


      <section class="content-section">
        <div class="section-heading">
          List of OEMs
        </div>
        <div class="section-content-wrapper">
          <div id="oem-list-section">
                    
          </div>
          <div class="ce-spinner-overlay" id="oem-list-section-loading" style="text-align:center;display:none;">
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

    $("#oem-tab").addClass(" active");
    var menuid = "#master-menu";
    $(menuid+" .dropdown-content").addClass("show");
    $(menuid+" button .fa-caret-down").css("transform","rotate(180deg)"); 

  });

</script>

<script type="text/javascript" src="js/manageoem.js?uid=<?php echo uniqid(); ?>" ></script>
</body>
</html>
<?php
  
  }
  else
  { 
      header("location:".getDomain());
  }

  
?>