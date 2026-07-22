<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    // if(isset($_POST['projectid']) && $_POST['projectid'] != "")
    // {

?>
      
          <table class="table-element align-center" id="debit-note-table" cellpadding="0" cellspacing="0" style="font-size: .8em;">
            
              <?php
                  $con = connectMySQL();
                  $sqlgetdebitnote = "select *,
                  (select name from debit_note_type where value = dn.type) as type_name,
                  (select name from credit_note_type where value = dn.credit_type) as credit_type_name,
                  (select name from project where id = dn.project_id) as project_name
                   from debit_note as dn";
                  $rowgetdebitnote = mysqli_query($con, $sqlgetdebitnote);

                  if (mysqli_num_rows($rowgetdebitnote) > 0)
                  {
              ?>
            <thead>
              <tr>
                <th>DN Number</th>
                <th>Project</th>
                <th>Relation</th>
                <th>Invoice Number</th>
                <th>DN Amount excl. GST (Rs.)</th>
                <th>DN Type</th>
                <th>Tagged Bill</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
                    $i = 0;
                    while ($i <= ($resgetdebitnote = mysqli_fetch_array($rowgetdebitnote)))
                    {
                ?>
                <tr> 
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo "SEPL/DN/".$resgetdebitnote['id']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetdebitnote['project_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetdebitnote['type_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php  
                        if($resgetdebitnote['invoice_id'] == "0")
                        {
                          echo "N/A";
                        }
                        else
                        {
                          $sqlgetinvoice = "select number from purchase_invoice where id = ".$resgetdebitnote['invoice_id'];
                          $rowgetinvoice = mysqli_query($con, $sqlgetinvoice);

                          if (mysqli_num_rows($rowgetinvoice) > 0)
                          {
                            $resgetinvoice = mysqli_fetch_array($rowgetinvoice);
                            echo $resgetinvoice['number'];

                          }
                        }
                      ?>
                    </div>
                  </td>   
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo round($resgetdebitnote['amount'],2); ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetdebitnote['credit_type_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php  
                        if($resgetdebitnote['bill_id'] == "0")
                        {
                          echo "N/A";
                        }
                        else
                        {
                          $sqlgetbill = "select year,
                          (select name from month where value = b.month) as month_name
                          from bill as b where id = ".$resgetdebitnote['bill_id'];
                          $rowgetbill = mysqli_query($con, $sqlgetbill);

                          if (mysqli_num_rows($rowgetbill) > 0)
                          {
                            $resgetbill = mysqli_fetch_array($rowgetbill);
                            echo $resgetbill['month_name']." ".$resgetbill['year'];

                          }
                        }
                      ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper align-center">
                        <a class="button show-create-credit-note-modal-btn" id="show-create-credit-note-modal-btn-<?php echo $i+1; ?>" data-debit-note-id="<?php echo $resgetdebitnote['id']; ?>" data-bill-id="<?php echo $resgetdebitnote['bill_id']; ?>" data-project-id="<?php echo $resgetdebitnote['project_id']; ?>" data-invoice-id="<?php echo $resgetdebitnote['invoice_id']; ?>" href="javascript:void(0);" title="Create Credit Note">Create CN</a>
                      <?php  
                        $sqlgetcloudinward = "select id from cloud_inward where project_id = ".$resgetdebitnote['project_id']." AND bill_id = ".$resgetdebitnote['bill_id']." AND is_cancelled = 0";
                        $rowgetcloudinward = mysqli_query($con, $sqlgetcloudinward);

                        if(mysqli_num_rows($rowgetcloudinward) > 0)
                        {
                          $resgetcloudinward = mysqli_fetch_array($rowgetcloudinward);
                          $changeci = false;
                      ?>
                      <?php
                        }
                        else
                        {
                      ?>
                        <a class="delete-debit-note-btn" id="delete-debit-note-btn-<?php echo $i+1; ?>" data-debit-note-id="<?php echo $resgetdebitnote['id']; ?>" data-bill-id="<?php echo $resgetdebitnote['bill_id']; ?>" data-invoice-id="<?php echo $resgetdebitnote['invoice_id']; ?>" href="javascript:void(0);" title="Delete Debit Note" style="margin-left: 10px;"><i class="fa fa-trash " aria-hidden="true"></i></a>
                      <?php
                        }
                      ?>
                      </div>
                  </td>            
                <?php
                      $i++;
                    }
                ?>
            </tbody>
            <script type="text/javascript">
              initializeDataTable("debit-note-table");
            </script>
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
