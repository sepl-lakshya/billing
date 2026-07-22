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
  <title>Products</title>

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
        <p>Manage Products</p>
      </div>

      <section class="content-section">
        <div class="section-heading">
          Add New Product
        </div>
        <div class="section-content-wrapper">
          <form method="post" class="ce-form" id="ce-add-product-form">

            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">OEM :</div>
              <div class="ce-form-input">
                  <select id="product-oem" name="product-oem">
                    <option value="0">Select OEM</option>
                <?php
                  $con = connectMySQL();
                  $sqlgetoem = "select * from oem";
                  $rowgetoem = mysqli_query($con, $sqlgetoem);

                  if (mysqli_num_rows($rowgetoem) > 0)
                  {
                    $o = 0;
                    while ($o <= ($resgetoem = mysqli_fetch_array($rowgetoem)))
                    {
                ?>
                    <option value="<?php echo $resgetoem['id']; ?>"><?php echo $resgetoem['name']; ?></option>
                <?php
                    $o++;
                  }
                }
                ?>
                  </select>
                </div>
              </div>
            <div class="ce-form-controls ce-form-control-col-3">
              <div class="ce-form-label">Product Category :</div>
              <div class="ce-form-input">
                  <select id="product-category" name="product-category">
                    <option value="0">Select Product Category</option>
                <?php
                  $con = connectMySQL();
                  $sqlgetcategory = "select * from product_category where is_active = 1";
                  $rowgetcategory = mysqli_query($con, $sqlgetcategory);

                  if (mysqli_num_rows($rowgetcategory) > 0)
                  {
                    $i = 0;
                    while ($i <= ($resgetcategory = mysqli_fetch_array($rowgetcategory)))
                    {
                ?>
                    <option value="<?php echo $resgetcategory['value']; ?>"><?php echo $resgetcategory['name']; ?></option>
                <?php
                    $i++;
                  }
                }
                ?>
                  </select>
                </div>
              </div>
              <div class="ce-form-controls ce-form-control-col-3 product-sub-category-wrapper" id="sub-category-wrapper-blank">

              </div>
              
              <div class="ce-form-controls ce-form-control-col-3 product-sub-category-wrapper" id="licence-category-wrapper" style="display:none;">
              <div class="ce-form-label">Licence Category :</div>
              <div class="ce-form-input">
                  <select id="licence-category" name="licence-category">
                    <option value="0">Select Licence Category</option>
                <?php
                  $con = connectMySQL();
                  $sqlgetlicencecategory = "select * from licence_category";
                  $rowgetlicencecategory = mysqli_query($con, $sqlgetlicencecategory);

                  if (mysqli_num_rows($rowgetlicencecategory) > 0)
                  {
                    $l = 0;
                    while ($l <= ($resgetlicencecategory = mysqli_fetch_array($rowgetlicencecategory)))
                    {
                ?>
                    <option value="<?php echo $resgetlicencecategory['value']; ?>"><?php echo $resgetlicencecategory['name']; ?></option>
                <?php
                      $l++;
                    }
                  }
                ?>
                  </select>
                </div>
              </div>

              <div class="ce-form-controls ce-form-control-col-3 product-sub-category-wrapper" id="cloud-category-wrapper" style="display:none;">
              <div class="ce-form-label">Product Type :</div>
              <div class="ce-form-input">
                  <select id="cloud-category" name="cloud-category">
                    <option value="0">Select Product Type</option>
                <?php
                  $con = connectMySQL();
                  $sqlgetcloudcategory = "select * from cloud_category";
                  $rowgetcloudcategory = mysqli_query($con, $sqlgetcloudcategory);

                  if (mysqli_num_rows($rowgetcloudcategory) > 0)
                  {
                    $c = 0;
                    while ($c <= ($resgetcloudcategory = mysqli_fetch_array($rowgetcloudcategory)))
                    {
                ?>
                    <option value="<?php echo $resgetcloudcategory['value']; ?>"><?php echo $resgetcloudcategory['name']; ?></option>
                <?php
                      $c++;
                    }
                  }
                ?>
                  </select>
                </div>
              </div>

              <div class="ce-form-controls ce-form-control-col-2">
                <div class="ce-form-label">Product Name :</div>
                <div class="ce-form-input">
                    <input type="text" id="product-name" name="product-name">
                </div>
              </div>

              <div class="ce-form-controls ce-form-control-col-3">
                <div class="ce-form-input">
                    <input type="submit" id="ce-add-product-form-submit" name="ce-add-product-form-submit" value="Add Product">
                </div>
              </div>
          </form>
        </div>
      </section>

      <div class="content-section-divider"></div>

      <section class="content-section">
        <div class="section-heading">
          List of Products
        </div>
        <div class="section-content-wrapper">
          <div id="product-list-section">
                    
          </div>
          <div class="ce-spinner-overlay" id="product-list-section-loading" style="text-align:center;display:none;">
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

<script type="text/javascript" src="js/manageproducts.js?uid=<?php echo uniqid(); ?>" ></script>
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