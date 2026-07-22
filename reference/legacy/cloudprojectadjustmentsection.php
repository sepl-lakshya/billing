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
                  $sqlgetprojectadjustment = "select id,amount,reference_no,
                  (select name from adjustment_type where value = pa.adjustment_type_id) as adjustment_name
                   from project_adjustment as pa where project_id = ".$pid;
                  $rowgetprojectadjustment = mysqli_query($con, $sqlgetprojectadjustment);

                  if (mysqli_num_rows($rowgetprojectadjustment) > 0)
                  {
              ?>
            <thead>
              <tr>
                <th>Adjustment</th>
                <th>Amount</th>
                <th>Reference Number</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
                    $i = 0;
                    while ($i <= ($resgetprojectadjustment = mysqli_fetch_array($rowgetprojectadjustment)))
                    {
                ?>
                <tr> 
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectadjustment['adjustment_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectadjustment['amount']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectadjustment['reference_no']; ?>
                    </div>
                  </td>   
                  <td>
                    <div class="table-value-wrapper align-center">
                      <a class="delete-project-adjustment-btn" id="delete-project-adjustment-btn-<?php echo $i+1; ?>" data-adjustment-id="<?php echo $resgetprojectadjustment['id']; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
