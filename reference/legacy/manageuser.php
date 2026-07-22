<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }

  include "include/config.php";  

  if(isLoggedIn() && $_SESSION["user_type"] == "1")
  {

?>


<!DOCTYPE html>
<html <?php echo getDefaultTheme(); ?> >
<head>
  <title>Manage User</title>

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
        <p>Manage Users</p>
      </div>

      <section class="content-section">
        <div class="section-heading">
          Add New User
        </div>
        <div class="section-content-wrapper">
          <form method="post" class="ce-form" id="ce-add-user-form">

            <div class="ce-form-controls ce-form-control-col-2">
              <div class="ce-form-label">Full Name :</div>
              <div class="ce-form-input">
                  <input type="text" id="full-name" name="full-name">
              </div>
            </div>
            <div class="ce-form-controls ce-form-control-col-2">
              <div class="ce-form-label">Email :</div>
              <div class="ce-form-input">
                  <input type="text" id="user-email" name="user-email">
              </div>
            </div>
            <div class="ce-form-controls ce-form-control-col-2">
              <div class="ce-form-label">Azure Object ID :</div>
              <div class="ce-form-input">
                  <input type="text" id="azure-object-id" name="azure-object-id">
              </div>
            </div>
            <div class="ce-form-controls ce-form-control-col-2">
              <div class="ce-form-input">
                  <input type="submit" id="ce-add-user-form-submit" name="ce-add-user-form-submit" value="Add User">
              </div>
            </div>
          </form>
        </div>
      </section>


      <div class="content-section-divider"></div>


      <section class="content-section">
        <div class="section-heading">
          List of Allowed Users
        </div>
        <div class="section-content-wrapper">
          <div id="user-list-section">       
          </div>
          <div class="ce-spinner-overlay" id="user-list-section-loading" style="text-align:center;display:none;">
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

    $("#user-tab").addClass(" active");

  });

</script>

<script type="text/javascript" src="js/manageuser.js?uid=<?php echo uniqid(); ?>" ></script>
</body>
</html>
<?php
  
  }
  else
  { 
      header("location:".getDomain());
  }

  
?>