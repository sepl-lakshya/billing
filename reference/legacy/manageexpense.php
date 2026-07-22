<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }

  include "include/config.php";  

  if(isLoggedIn())
  {
    $con = connectMySQL();
?>


<!DOCTYPE html>
<html <?php echo getDefaultTheme(); ?> >
<head>
  <title>Expenses</title>

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
        <p>Manage Expenses</p>
      </div>

      <section class="content-section">
        <div class="section-heading">
            Add New Expense
          </div>
          <div class="section-content-wrapper">
            <form method="post" class="ce-form" id="ce-add-expense-form" style="width:100%;">

                <div class="ce-form-controls ce-form-control-col-3">
                  <div class="ce-form-label">Expense Type :</div>
                  <div class="ce-form-input">
                    <select id="add-expense-type" name="add-expense-type">
                      <option value="0">Select Expense</option>
                    <?php

                      $sqlgetexpensetype = "select * from expense_type order by order_val,name";
                      $rowgetexpensetype = mysqli_query($con, $sqlgetexpensetype);

                      if (mysqli_num_rows($rowgetexpensetype) > 0)
                      {
                        $exp = 0;
                        while ($exp <= ($resgetexpensetype = mysqli_fetch_array($rowgetexpensetype)))
                        {

                    ?>
                        <option value="<?php echo $resgetexpensetype['value']; ?>" ><?php echo $resgetexpensetype['name']; ?></option>
                    <?php
                          $exp++;
                        }
                      }
                    ?>
                    </select>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-3">
                  <div class="ce-form-label">Name :</div>
                  <div class="ce-form-input">
                    <input type="text" id="add-expense-name" name="add-expense-name" placeholder="Name">
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-3">
                  <div class="ce-form-label">Amount :</div>
                  <div class="ce-form-input">
                    <input type="text" id="add-expense-amount" name="add-expense-amount" placeholder="Amount">
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-1">
                  <div class="ce-form-label">Description :</div>
                  <div class="ce-form-input">
                    <input type="text" id="add-expense-desc" name="add-expense-desc" placeholder="Description">
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-1">
                  <div class="ce-form-input ">
                      <input type="submit" id="ce-add-expense-form-submit" name="ce-add-expense-form-submit" value="Add Expense" >
                  </div>
                </div>
          </form>
          </div>
          <div class="section-content-wrapper">
            <div id="project-expense-section">
                
            </div>
            <div class="ce-spinner-overlay" id="project-expense-section-loading" style="text-align:center;display:none;">
                <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
            </div>
          </div>
      </section>
      <div class="content-section-divider"></div>


      <section class="content-section">
        <div class="section-heading">
          List of Expenses
        </div>
        <div class="section-content-wrapper">
          <div id="expense-list-section">
                    
          </div>
          <div class="ce-spinner-overlay" id="expense-list-section-loading" style="text-align:center;display:none;">
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

    $("#expense-tab").addClass(" active");

  });

</script>

<script type="text/javascript" src="js/manageexpense.js?uid=<?php echo uniqid(); ?>" ></script>
</body>
</html>
<?php
  
  }
  else
  { 
      header("location:".getDomain());
  }

  
?>