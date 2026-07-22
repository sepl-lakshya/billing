<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    // if(isset($_POST['projectid']) && $_POST['projectid'] != "")
    // {
    //   $pid = $_POST['projectid'];


?>
      
          <table class="table-element align-center" cellpadding="0" cellspacing="0" style="font-size: .8em;">
            
              <?php
                  $con = connectMySQL();
                  $sqlgetexpense = "select *,
                  (select name from expense_type where value = ex.expense_type_id) as expense_type_name
                   from expense as ex";
                  $rowgetexpense = mysqli_query($con, $sqlgetexpense);

                  if (mysqli_num_rows($rowgetexpense) > 0)
                  {
              ?>
            <thead>
              <tr>
                <th>Expense Type</th>
                <th>Expense Name</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
                    $i = 0;
                    while ($i <= ($resgetexpense = mysqli_fetch_array($rowgetexpense)))
                    {
                ?>
                <tr> 
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetexpense['expense_type_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetexpense['name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetexpense['amount']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetexpense['description']; ?>
                    </div>
                  </td>   
                  <td>
                    <div class="table-value-wrapper align-center">
                      <a class="delete-expense-btn" id="delete-expense-btn-<?php echo $i+1; ?>" data-expense-id="<?php echo $resgetexpense['id']; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
                    </div>
                  </td>            
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
                <!-- <tbody>
                  <tr>
                    <td class="align-center">No Data Found !</td>
                  </tr>
                </tbody> -->
              <?php
                  }
                  mysqli_close($con);

                ?>
          </table>
<?php 
  // }
?>
