<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    $con = connectMySQL();
    
    if(isset($_POST['projectid']) && $_POST['projectid'] != "" && isset($_POST['billid']) && $_POST['billid'] != "")
    {
      $pid = mysqli_real_escape_string($con, clean_input($_POST['projectid']));
      $bid = mysqli_real_escape_string($con, clean_input($_POST['billid']));

      $sqlgetbill = "select id,hash,status,month,year,progress,
                    (select name from month where value = b.month) as month_name, 
                    (select name from year where value = b.year) as year_name
                    from bill as b where project_id = ".$pid." AND id = ".$bid;
      $rowgetbill = mysqli_query($con, $sqlgetbill);

      if(mysqli_num_rows($rowgetbill) > 0)
      {
        $resgetbill = mysqli_fetch_array($rowgetbill);


         

?>
      <div class="modal-heading-wrapper">
        Select progress for <span style="font-size:1.1em; text-decoration: underline;"><?php echo $resgetbill['month_name']." ".$resgetbill['year_name']; ?></span>
      </div>
        <form method="post" id="ce-monthly-bill-progress-form">
          <input type="hidden" id="progress-project-id" name="progress-project-id" value="<?php echo $pid; ?>">      
          <input type="hidden" id="progress-bill-id" name="progress-bill-id" value="<?php echo $bid; ?>">      

          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-input">
                <select id="bill-progress" name="bill-progress">
                  <option value="0">Select Progress</option>
                  <?php

                    $sqlgetprogress = "select * from billing_progress where value > ".$resgetbill['progress'];
                    $rowgetprogress = mysqli_query($con, $sqlgetprogress);

                    if (mysqli_num_rows($rowgetprogress) > 0)
                    {
                      $i = 0;
                      while ($i <= ($resgetprogress = mysqli_fetch_array($rowgetprogress)))
                      {
                        if($resgetbill['progress'] == $resgetprogress['value'])
                        {
                          $progressselected = "selected";
                        }
                        else
                        {
                          $progressselected = "";
                        }
                  ?>
                    <option value="<?php echo $resgetprogress['value']; ?>" <?php echo $progressselected; ?> >
                      <?php echo $resgetprogress['name']; ?>
                    </option>
                  <?php
                        $i++;
                      }
                    }
                  ?>
                </select>
            </div>
          </div>
          <div class="ce-form-controls ce-form-control-col-2">
            <div class="ce-form-input align-right">
                <input type="submit" id="ce-monthly-bill-progress-form-submit" name="ce-monthly-bill-progress-form-submit" value="Update">
            </div>
          </div>
        </form>
    <?php   
      }

    }
    else
    {

  }
?>
          </table>


  