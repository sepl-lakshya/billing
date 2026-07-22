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
  <title>Invoices</title>

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
        <p>Manage Invoices</p>
      </div>

      <section class="content-section">
        <div class="section-heading">
        </div>
        <div class="section-content-wrapper">
          <div class="ce-form">
            <div class="ce-form-controls ce-form-control-col-1">
              <div class="ce-form-input">
                <input type="button" id="show-add-invoice-form-modal-btn" value="Add New Invoice">
              </div>
            </div>
          </div>
        </div>
      </section>


      <div class="content-section-divider"></div>


      <section class="content-section">
        <div class="section-heading">
          List of Invoices
        </div>
        <div class="section-content-wrapper">
          <div id="purchase-invoice-section">
                    
          </div>
          <div class="ce-spinner-overlay" id="purchase-invoice-section-loading" style="text-align:center;display:none;">
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

    $("#invoice-tab").addClass(" active");
    // var menuid = "#master-menu";
    // $(menuid+" .dropdown-content").addClass("show");
    // $(menuid+" button .fa-caret-down").css("transform","rotate(180deg)"); 

  });

</script>

<!-------------------- Modal 1 (Add Invoice Model) --------------------------->

    <div id="add-invoice-form-modal" class="modal" style="width: 900px !important;max-width: unset;">
      <div class="modal-heading-wrapper">
        
      </div>
      <div class="modal-content-wrapper" id="add-invoice-form-cont">
          
      </div>
    </div>

<script type="text/javascript" src="js/manageinvoice.js?uid=<?php echo uniqid(); ?>" ></script>
</body>
</html>
<?php
  
  }
  else
  { 
      header("location:".getDomain());
  }

  
?>