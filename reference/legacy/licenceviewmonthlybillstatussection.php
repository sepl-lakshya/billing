<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    $con = connectMySQL();
    
    if(isset($_POST['projectid']) && $_POST['projectid'] != "")
    {
      $pid = $_POST['projectid'];

?>
      
          <table class="table-element" cellpadding="0" cellspacing="0" style="font-size: .8em;">
            
                <?php
                  $sqlgetbill = "select id,year,status,ri_discount,payg_discount,
                  (select name from month where value = b.month) as month_val,
                  (select SUM(portal_price) from bill_item where bill_id = b.id) as portal_total,
                  (select SUM(sales_price) from bill_item where bill_id = b.id) as sales_total,
                  (select SUM(purchase_price) from bill_item where bill_id = b.id) as purchase_total
                  from bill as b where project_id = ".$pid." order by month , year";
                  $rowgetbill = mysqli_query($con, $sqlgetbill);

                  if (mysqli_num_rows($rowgetbill) > 0)
                  {

              ?>
            <thead>
              <tr>
                <th>Month</th>
                <th>Year</th>
                <th>Portal Total</th>
                <th>Sales Total</th>
                <th>Purchase Total</th>
                <th>Discount (%)</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
                    $i = 0;
                    while ($i <= ($resgetbill = mysqli_fetch_array($rowgetbill)))
                    {
                ?>
                <tr>
                  <td>
                    <div class="table-value-wrapper align-center">
                      <?php echo $resgetbill['month_val']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper align-center">
                      <?php echo $resgetbill['year']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper  align-right">
                      <?php echo round($resgetbill['portal_total'],2); ?>
                    </div>
                  </td> 
                  <td>
                    <div class="table-value-wrapper align-right">
                      <?php 
                        if($resgetbill['status'] >= 2)
                        {
                          echo round($resgetbill['sales_total'],2); 
                        }
                        else
                        {
                          echo "Not Added";
                        }

                      ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper align-right">
                      <?php 
                        if($resgetbill['status'] >= 3)
                        {
                          echo round($resgetbill['purchase_total'],2); 
                        }
                        else
                        {
                          echo "Not Added";
                        } 
                      ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper  align-right">
                      <?php echo $resgetbill['ri_discount']; ?>
                    </div>
                  </td>                  
                  <td>
                    <div class="table-value-wrapper align-center">
                      <a class="button view-monthly-bill-items-btn" id="view-monthly-bill-items-btn-<?php  echo $i+1; ?>" data-bill-id="<?php echo $resgetbill['id']; ?>" href="javascript:void(0);">View</a>
                      <a class="delete-monthly-bill-btn" id="delete-monthly-bill-btn-<?php echo $i+1; ?>" data-bill-id="<?php echo $resgetbill['id']; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
                    <td class="align-center">No Entries Found !</td>
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
