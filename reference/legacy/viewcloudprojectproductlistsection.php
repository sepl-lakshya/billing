
<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

    if(isset($_POST['catid']) && $_POST['catid'] != "")
    {
      $catid = $_POST['catid'];
      

                  $con = connectMySQL();
                  $sqlgetproduct = "select * from product where category = 1 AND sub_category = ".$catid." AND is_active = 1";
                  $rowgetproduct = mysqli_query($con, $sqlgetproduct);

                  if (mysqli_num_rows($rowgetproduct) > 0)
                  {
                ?>  
                    <option value="0">Select Product</option>
                <?php
                    $i = 0;
                    while ($i <= ($resgetproduct = mysqli_fetch_array($rowgetproduct)))
                    {
                ?>
                  <option value="<?php echo $resgetproduct['id']; ?>">
                    <?php echo $resgetproduct['name']; ?>
                  </option>
                <?php
                      $i++;
                    }
                  }
                  else
                  {
                ?>
                  <option value="0" style="font-style: italic;">-No Product Found-</option>
                <?php
                  }

                  mysqli_close($con);

    }
?>
