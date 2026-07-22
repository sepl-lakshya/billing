<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    // if(isset($_POST['projectid']) && $_POST['projectid'] != "")
    // {

?>
      
          <table class="table-element align-center" cellpadding="0" cellspacing="0" style="font-size: .8em;">
            
              <?php
                  $con = connectMySQL();
                  $sqlgetcreditnote = "select *,
                  (select number from purchase_invoice where id = cn.invoice_id) as invoice_number,
                  (select name from project where id = cn.project_id) as project_name
                   from credit_note as cn";
                  $rowgetcreditnote = mysqli_query($con, $sqlgetcreditnote);

                  if (mysqli_num_rows($rowgetcreditnote) > 0)
                  {
              ?>
            <thead>
              <tr>
                <th>CN Number</th>
                <th>Project</th>
                <th>Reference Number</th>
                <th>Invoice Number</th>
                <th>Credit Amount (Rs.)</th>
                <th>DN Number</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
                    $i = 0;
                    while ($i <= ($resgetcreditnote = mysqli_fetch_array($rowgetcreditnote)))
                    {
                ?>
                <tr> 
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo "SEPL/CN/".$resgetcreditnote['id']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetcreditnote['project_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php  
                        if($resgetcreditnote['invoice_number'] == "")
                        {
                          echo "N/A";
                        }
                        else
                        {
                          echo $resgetcreditnote['invoice_number'];
                        }
                      ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetcreditnote['reference_no']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo round($resgetcreditnote['amount'],2); ?>
                    </div>
                  </td>   
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo "SEPL/DN/".$resgetcreditnote['debit_note_id']; ?>
                    </div>
                  </td>
                  <td>
                    <?php  
                      // $sqlgetcloudinward = "select id from cloud_inward where project_id = ".$pid." AND bill_id = ".$resgetcreditnote['bill_id']." AND is_cancelled = 0";
                      // $rowgetcloudinward = mysqli_query($con, $sqlgetcloudinward);

                      // if(mysqli_num_rows($rowgetcloudinward) > 0)
                      // {
                      //   $resgetcloudinward = mysqli_fetch_array($rowgetcloudinward);
                      //   $changeci = false;
                    ?>
                      <!-- <div class="table-value-wrapper" title="<?php //echo "SEPL/CI/".$resgetcloudinward['id']; ?>">
                        CI Generated
                      </div> -->
                    <?php
                      // }
                      // else
                      // {
                    ?>
                    <div class="table-value-wrapper align-center">
                      <a class="delete-credit-note-btn" id="delete-credit-note-btn-<?php echo $i+1; ?>" data-credit-note-id="<?php echo $resgetcreditnote['id']; ?>" data-bill-id="<?php echo $resgetcreditnote['bill_id']; ?>" data-invoice-id="<?php echo $resgetcreditnote['invoice_id']; ?>" data-project-id="<?php echo $resgetcreditnote['project_id']; ?>" href="javascript:void(0);" title="Delete Credit Note"><i class="fa fa-trash " aria-hidden="true"></i></a>
                    </div>
                    <?php
                      // }
                    ?>
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
