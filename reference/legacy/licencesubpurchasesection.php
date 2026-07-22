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
      
          <table class="table-element align-center" cellpadding="0" cellspacing="0" style="font-size: .8em;">
            
                <?php
                  $con = connectMySQL();
                  $sqlgetsubpurchase = "select distinct(project_item_id) as item_id from licence_sub_purchase where project_id = ".$pid;
                  $rowgetsubpurchase = mysqli_query($con, $sqlgetsubpurchase);

                  if (mysqli_num_rows($rowgetsubpurchase) > 0)
                  {
              ?>
            <!-- <thead>
              <tr>
                <th>SNo</th>
                <th>Product</th>
                <th>Category</th>
              </tr>
            </thead> -->
            <tbody>
              <?php
                    $i = 0;
                    while ($i <= ($resgetsubpurchase = mysqli_fetch_array($rowgetsubpurchase)))
                    {
                      $sqlgetprojectitem = "select id,licence_category,
                      (select name from licence_category where value = pi.licence_category) as licence_category_name,
                      (select name from product where id = pi.product) as product_name,
                      status, (sale_quantity - purchase_quantity) as remaining_quantity  
                       from licence_project_item as pi where project_id = ".$pid." AND id = ".$resgetsubpurchase['item_id'];
                      $rowgetprojectitem = mysqli_query($con, $sqlgetprojectitem);

                      $resgetprojectitem = mysqli_fetch_array($rowgetprojectitem)
                ?>
                <tr>
                  <td><div class="table-value-wrapper"><?php echo $i+1; ?>&nbsp;&nbsp;</div></td>
                  <td>
                    <div class="table-value-wrapper" style="font-weight:600;border: 1px solid;font-size: 1.1em;">
                      <?php echo $resgetprojectitem['product_name']; ?>
                    </div>
                  </td>
                 <!--  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectitem['licence_category_name']; ?>
                    </div>
                  </td> -->  
                                 
                </tr>
                  <?php
                    $sqlgetsubpurchasemonth = "select id,quantity, 
                    (select name from month where value = lsp.month) as month_name,
                    (select name from year where value = lsp.year) as year_name 
                    from licence_sub_purchase as lsp where project_id = ".$pid." AND project_item_id = ".$resgetsubpurchase['item_id'];
                      $rowgetsubpurchasemonth = mysqli_query($con, $sqlgetsubpurchasemonth);

                      if (mysqli_num_rows($rowgetsubpurchasemonth) > 0)
                      {
                  ?>
                <tr>
                  <td colspan="3">
                  <table class="table-element" style="font-size: 1em;">
                    <tbody>
                    <?php
                        $j = 0;
                        while ($j <= ($resgetsubpurchasemonth = mysqli_fetch_array($rowgetsubpurchasemonth)))
                        {
                      ?>
                        <tr>
                          <td style="border: none;width: 190px;"></td>
                          <td>
                            <div class="table-value-wrapper">
                              <?php echo $resgetsubpurchasemonth['month_name']; ?>
                            </div>
                          </td>
                          <td>
                            <div class="table-value-wrapper">
                              <?php echo $resgetsubpurchasemonth['year_name']; ?>
                            </div>
                          </td>
                          <td>
                            <div class="table-value-wrapper">
                              <?php echo $resgetsubpurchasemonth['quantity']; ?>
                            </div>
                          </td>
                          <td style="width:50px;">
                            <div class="table-value-wrapper">
                              <a class="delete-licence-sub-purchase-btn" id="delete-licence-sub-purchase-btn-<?php echo $i+1; ?>" data-sub-purchase-id="<?php echo $resgetsubpurchasemonth['id']; ?>" data-project-item-id="<?php echo $resgetsubpurchase['item_id']; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
                            </div>
                          </td>
                        </tr>
                      <?php    
                          $j++;
                        }
                  ?>
                    </tbody>                    
                  </table>
                  </td>
                </tr>
                <?php
                      }
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
                    <td class="align-center">No Sub Purchases Found !</td>
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
