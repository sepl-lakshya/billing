<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    if(isset($_POST['projectid']) && $_POST['projectid'] != "")
    {
      $pid = $_POST['projectid'];


?>
      
          <table class="table-element" cellpadding="0" cellspacing="0" style="font-size: .8em;">
            
                <?php
                  $con = connectMySQL();
                  $sqlgetprojectitem = "select id,licence_category,
                  (select name from licence_category where value = pi.licence_category) as licence_category_name,
                  (select name from product where id = pi.product) as product_name,
                  description,status 
                   from licence_project_item as pi where project_id = ".$pid." AND is_deleted = 0";
                  $rowgetprojectitem = mysqli_query($con, $sqlgetprojectitem);

                  if (mysqli_num_rows($rowgetprojectitem) > 0)
                  {
              ?>
            <thead>
              <tr>
                <th>SNo</th>
                <th>Product</th>
                <th>Category</th>
                <th>Description</th>  
                <th>Create/Edit</th>
                <th>Delete</th>
              </tr>
            </thead>
            <tbody>
              <?php
                    $i = 0;
                    while ($i <= ($resgetprojectitem = mysqli_fetch_array($rowgetprojectitem)))
                    {
                ?>
                <tr>
                  <td><div class="table-value-wrapper"><?php echo $i+1; ?>&nbsp;&nbsp;</div></td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['product_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['licence_category_name']; ?>
                    </div>
                  </td>                  
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['description']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper align-center">
                      
                      <button type="button" class="create-project-sales-btn" data-project-item-id="<?php echo $resgetprojectitem['id']; ?>" data-modal-id="sales-price-modal" id="create-project-sales-btn-<?php echo $i; ?>" >Sales</button>

                      <?php
                        if((int)$resgetprojectitem['status'] >= 1)
                        {
                      ?>
                        <button type="button" class="create-project-purchase-btn" data-project-item-id="<?php echo $resgetprojectitem['id']; ?>" id="create-project-purchase-btn-<?php echo $i; ?>" >Purchase</button>
                      <?php    
                        }
                      ?>
                    
                    </div>
                  </td>                 
                  <td>
                    <div class="table-value-wrapper align-center">

                      <?php
                        if($resgetprojectitem['status'] == 1)
                        {
                      ?>
                      <?php
                        }
                      ?>

                      <a class="delete-project-item-btn" id="delete-project-item-btn-<?php echo $i+1; ?>" data-project-item-id="<?php echo $resgetprojectitem['id']; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
                    </div>
                  </td>
                </tr>
                <?php
                      $i++;
                    }
                ?>
            </tbody>
                <?php
                  }
                  else
                  {
              ?>
                <tbody>
                  <tr>
                    <td class="align-center">No Products Found !</td>
                  </tr>
                </tbody>
              <?php
                  }

                  mysqli_close($con);

                ?>
          </table>
<?php 
  }
?>
