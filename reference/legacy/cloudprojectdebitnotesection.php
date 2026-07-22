<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    if(isset($_POST['projectid']) && $_POST['projectid'] != "")
    {
      $pid = $_POST['projectid'];
      $dnamountgrandtotal = 0;
      $dnduegrandtotal = 0;


?>
      
            
              <?php
                $con = connectMySQL();
                $sqlgetdebitnote = "select id,bill_id,invoice_id,amount,remark,
                  (select name from debit_note_type where value = dn.type) as type_name,
                  (select name from credit_note_type where value = dn.credit_type) as credit_type_name,
                  (select name from month where value = (select month from bill where id = dn.bill_id)) as month_name,
                  (select year from bill where id = dn.bill_id) as year_val,
                  (select SUM(amount) from credit_note where debit_note_id = dn.id) as cn_val,
                  (select number from purchase_invoice where id = dn.invoice_id) as invoice_number
                    from debit_note as dn where project_id = ".$pid;
                $rowgetdebitnote = mysqli_query($con, $sqlgetdebitnote);

                if(mysqli_num_rows($rowgetdebitnote) > 0)
                {
              ?>
          <table class="table-element align-center" id="project-debit-note-table" cellpadding="0" cellspacing="0" style="font-size: .8em;">
            <thead>
              <tr>
                <th>DN Number</th>
                <th>Relation</th>
                <th>Invoice Number</th>
                <th>DN Amount (Rs.)</th>
                <th>DN Type</th>
                <th>Tagged Bill</th>
                <th>Status</th>
                <th>Due Amount(Rs.)</th>
                <th class="no-print">Action</th>
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
                      <?php echo $resgetdebitnote['type_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php 
                        if($resgetdebitnote['invoice_number'] == "")
                        {
                          echo "N/A";
                        } 
                        else
                        {
                          echo $resgetdebitnote['invoice_number'];
                        }
                      ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo numberFormat(round($resgetdebitnote['amount'],2),2); ?>
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
                        if($resgetdebitnote['year_val'] == "" && $resgetdebitnote['month_name'] == "")
                        {
                          echo "N/A";
                        } 
                        else
                        {
                          echo $resgetdebitnote['month_name']." ".$resgetdebitnote['year_val'];
                        }
                      ?>
                    </div>
                  </td>
                  <td>
                      <?php  
                        if($resgetdebitnote['cn_val'] >= $resgetdebitnote['amount'])
                        {
                      ?>
                      <div class="table-value-wrapper" style="color:#17B169;font-weight: bold;">
                        Closed
                      </div>  
                      <?php
                        }
                        else
                        {
                      ?>
                      <div class="table-value-wrapper" style="color:#D2122E;font-weight: bold;">
                        Open
                      </div>
                      <?php
                        }
                      ?>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php  
                        if($resgetdebitnote['cn_val'] < $resgetdebitnote['amount'])
                        {
                          $dueamount = $resgetdebitnote['amount'] - $resgetdebitnote['cn_val']; 
                          echo numberFormat(round($dueamount,2),2);
                        }
                        else
                        {
                          $dueamount = 0;
                          echo $dueamount;
                        }
                      ?>
                    </div>
                  </td>   
                  <td class="no-print">
                    <div class="table-action-btn-wrapper" >
                      <a class="button show-create-credit-note-modal-btn" id="show-create-credit-note-modal-btn-<?php echo $i+1; ?>" data-debit-note-id="<?php echo $resgetdebitnote['id']; ?>" data-bill-id="<?php echo $resgetdebitnote['bill_id']; ?>" data-invoice-id="<?php echo $resgetdebitnote['invoice_id']; ?>" href="javascript:void(0);" title="Create Credit Note">Create CN</a> 
                      <?php  
                        $sqlgetcloudinward = "select id from invoice_cloud_inward where project_id = ".$pid." AND bill_id = ".$resgetdebitnote['bill_id']." AND invoice_id = ".$resgetdebitnote['invoice_id']." AND is_cancelled = 0";
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
                      <a target="_blank" href="<?php echo getDomain()."/printdebitnote?pid=".$pid."&bid=".$resgetdebitnote['bill_id']."&dnid=".$resgetdebitnote['id']; ?>" title="Print Debit Note" style="margin-left: 10px;"><i class="fa fa-print " aria-hidden="true"></i></a>
                    </div>
                  </td>            
                <?php
                      $dnamountgrandtotal += (float)$resgetdebitnote['amount'];
                      $dnduegrandtotal += (float)$dueamount;

                      $i++;
                    }
                ?>
            </tbody>
          </table>
          <div><b>Grand Total -  DN Amount :</b> <?php echo "Rs. ".numberFormat(round($dnamountgrandtotal,2),2); ?> <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Due Amount :</b> <?php echo "Rs. ".numberFormat(round($dnduegrandtotal,2),2); ?></div>

            <script type="text/javascript">
              initializeDataTable("project-debit-note-table");
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
<?php 
  }
?>
