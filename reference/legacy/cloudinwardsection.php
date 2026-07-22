<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    if(isset($_POST['projectid']) && $_POST['projectid'] != "")
    {
      $projectid = $_POST['projectid'];

      $cigrandtotal = 0;
?>
      
            
              <?php
                  $con = connectMySQL();
                  $sqlgetcloudinward = "select ici.id,b.year, ici.portal_total, ici.purchase_total, ici.remark,ici.is_cancelled,ici.created_on, ici.cancel_reason,ici.bill_id,
                  (select full_name from portal.login_detail where id = ici.created_by) as created_by_name,
                  (select name from month where value = b.month) as month_name,
                  (select number from purchase_invoice where id = ici.invoice_id) as invoice_number,
                  (select amount from purchase_invoice where id = ici.invoice_id) as invoice_amount
                   from invoice_cloud_inward as ici INNER JOIN bill as b on ici.bill_id = b.id where ici.project_id = ".$projectid." order by b.month , b.year , ici.id";
                  $rowgetcloudinward = mysqli_query($con, $sqlgetcloudinward);

                  if (mysqli_num_rows($rowgetcloudinward) > 0)
                  {
              ?>
          <table class="table-element align-center" id="project-cloud-inward-table" cellpadding="0" cellspacing="0" style="font-size: .8em;">
            <thead>
              <tr>
                <th>Month</th>
                <th>Year</th>
                <th>CI Number</th>
                <th>Invoice Number</th>
                <th>Invoice Total excl. GST (Rs.)</th>
                <th>Remark</th>
                <th>Status</th>
                <th>Verified By</th>
                <th>Created On</th>
                <th>Cancel/Reason</th>
                <th class="no-print">Print</th>
              </tr>
            </thead>
            <tbody>
              <?php
                    $i = 0;
                    while ($i <= ($resgetcloudinward = mysqli_fetch_array($rowgetcloudinward)))
                    {
                      $cigrandtotal += (float)$resgetcloudinward['invoice_amount'];
                ?>
                <tr> 
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetcloudinward['month_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetcloudinward['year']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo "SEPL/CI/".$resgetcloudinward['id']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetcloudinward['invoice_number']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo numberFormat(round($resgetcloudinward['invoice_amount'],2),2); ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetcloudinward['remark']; ?>
                    </div>
                  </td>
                  <td>
                    <?php 
                      if($resgetcloudinward['is_cancelled'] == "0")
                      {
                        $statustext = "Active";
                        $statuscolor = "";
                      } 
                      else
                      {
                        $statustext = "Cancelled";
                        $statuscolor = "color:red;";
                      }
                    ?>
                    <div class="table-value-wrapper" style="<?php echo $statuscolor; ?>">
                      <?php echo $statustext ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetcloudinward['created_by_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper" title="<?php echo date("h:i:s a",strtotime($resgetcloudinward['created_on'])); ?>">
                      <?php echo date("d-m-Y",strtotime($resgetcloudinward['created_on'])); ?>
                    </div>
                  </td>   
                  <td>
                    <div class="table-value-wrapper align-center">
                      <?php 
                        if($resgetcloudinward['is_cancelled'] == "0")
                        {
                      ?>
                      <a class="show-cancel-invoice-cloud-inward-modal-btn" id="show-cancel-invoice-cloud-inward-modal-btn-<?php echo $i+1; ?>" data-cloud-inward-id="<?php echo $resgetcloudinward['id']; ?>" href="javascript:void(0);" style="color: red;font-size: 1.5em;" ><i class="fa fa-times" aria-hidden="true"></i></a>
                      <?php
                        }

                        if($resgetcloudinward['is_cancelled'] == "1")
                        {
                          if($resgetcloudinward['cancel_reason'] == "")
                          {
                            echo "Bill Deleted";
                          }
                          else
                          {
                            echo $resgetcloudinward['cancel_reason'];
                          }
                        }
                      ?>
                    </div>
                  </td> 
                  <td class="no-print">
                    <div class="table-value-wrapper align-center">
                      <a target="_blank" href="<?php echo getDomain()."/printcloudinward".getPageExt()."?pid=".$projectid."&bid=".$resgetcloudinward['bill_id']."&ciid=".$resgetcloudinward['id']; ?>" style="color: grey;font-size: 1.5em;" ><i class="fa fa-print" aria-hidden="true"></i></a>
                    </div>
                  </td>            
                </tr>
                <?php
                      $i++;
                    }
                ?>
            </tbody>
          </table>
          <div><b>Grand Total :</b> <?php echo "Rs. ".numberFormat(round($cigrandtotal,2),2); ?></div>
            <script type="text/javascript">
              initializeDataTable("project-cloud-inward-table");
            </script>
                <?php
                  }
                  else
                  {
              ?>

              <?php
                  }
                  mysqli_close($con);

                ?>

<?php 
  }
?>
