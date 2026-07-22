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
  <title>Credit Notes</title>

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
        <p>Manage Credit Notes</p>
      </div>

      <div class="content-section-divider"></div>


      <section class="content-section">
        <div class="section-heading">
          List of Credit Notes
        </div>
        <div class="section-content-wrapper">
          <div id="credit-note-section">
                               
          </div>
          <div class="ce-spinner-overlay" id="credit-note-section-loading" style="text-align:center;display:none;">
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

    $("#credit-note-tab").addClass(" active");
    // var menuid = "#cloud-main-menu";
    // $(menuid+" .dropdown-content").addClass("show");
    // $(menuid+" button .fa-caret-down").css("transform","rotate(180deg)"); 

  });

</script>


<script type="text/javascript" src="js/managecreditnote.js?uid=<?php echo uniqid(); ?>" ></script>
</body>
</html>
<?php
  
  }
  else
  { 
      header("location:".getDomain());
  }

  
?>