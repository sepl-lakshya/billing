
<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }

  include "include/config.php";  
  
  if(isLoggedIn())
  {
    
    // $sqlcheckuser = "select is_valid from login_detail where login_id = ".$_SESSION['login_id'];
    //   $rowcheckuser = mysqli_query($con,$sqlcheckuser);
    //   if(mysqli_num_rows($rowcheckuser) > 0)
    //   {
    //     $rescheckuser = mysqli_fetch_array($rowcheckuser);
    //     if($rescheckuser["is_valid"] == 1)
    //     {

?>


<!DOCTYPE html>
<html <?php echo getDefaultTheme(); ?> >
<head>
  <title>Home</title>

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
        <p>Home</p>
      </div>

      <section class="content-section">
        <div class="section-heading">
          
        </div>
        <div class="section-content-wrapper">
          
        </div>
      </section>

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

    $("#home-tab").addClass(" active");

  });

</script>

</body>
</html>
<?php
      //   }
      //   else
      //   {
      //       header("location:".getDomain());

      //   }
      // }
      // else
      // {
      //     header("location:".getDomain());

      // }
  }
  else
  { 
      header("location:".getDomain());
      // header("location:http://localhost/portal");
  }

?>