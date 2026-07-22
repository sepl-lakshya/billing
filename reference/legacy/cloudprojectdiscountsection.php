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
                  $sqlgetprojectdiscount = "select * 
                   from project_discount where project_id = ".$pid;
                  $rowgetprojectdiscount = mysqli_query($con, $sqlgetprojectdiscount);

                  if (mysqli_num_rows($rowgetprojectdiscount) > 0)
                  {
              ?>
            <thead>
              <tr>
                <th style="width:20%;">From</th>
                <th style="width:20%;">To</th>
                <th style="width:20%;">PAYG Discount(%)</th>
                <th style="width:20%;">RI Discount(%)</th>
                <th style="width:20%;">Credit Days</th>
              </tr>
            </thead>
            <tbody>
              <?php
                    $i = 0;
                    while ($i <= ($resgetprojectdiscount = mysqli_fetch_array($rowgetprojectdiscount)))
                    {
                ?>
                <tr>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo date("d M Y",strtotime($resgetprojectdiscount['from_date'])); ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php 

                        if($resgetprojectdiscount['to_date'] == "" || $resgetprojectdiscount['to_date'] == NULL)
                        {
                          echo "-";
                        }
                        else
                        {
                          echo date("d M Y",strtotime($resgetprojectdiscount['to_date'])); 
                        }
                      ?>
                    </div>
                  </td>  
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectdiscount['payg_discount']." %"; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectdiscount['ri_discount']." %"; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php
                        if((int)$resgetprojectdiscount['credit_days'] <= 1)
                        {
                          echo $resgetprojectdiscount['credit_days']. " day"; 
                        }
                        else
                        {
                          echo $resgetprojectdiscount['credit_days']." days"; 
                        }
                      ?>
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
                <tbody>
                  <tr>
                    <td class="align-center">No Data Found !</td>
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
