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
                  $sqlgetprojectexpense = "select id,amount,
                  (select name from expense_type where value = pe.expense_type_id) as expense_name
                   from project_expense as pe where project_id = ".$pid;
                  $rowgetprojectexpense = mysqli_query($con, $sqlgetprojectexpense);

                  if (mysqli_num_rows($rowgetprojectexpense) > 0)
                  {
              ?>
            <thead>
              <tr>
                <th style="width:33.33%;">Expense</th>
                <th style="width:33.33%;">Amount</th>
                <th style="width:33.33%;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
                    $i = 0;
                    while ($i <= ($resgetprojectexpense = mysqli_fetch_array($rowgetprojectexpense)))
                    {
                ?>
                <tr> 
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectexpense['expense_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectexpense['amount']; ?>
                    </div>
                  </td>   
                  <td>
                    <div class="table-value-wrapper align-center">
                      <a class="delete-project-expense-btn" id="delete-project-expense-btn-<?php echo $i+1; ?>" data-expense-id="<?php echo $resgetprojectexpense['id']; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
  }
?>
