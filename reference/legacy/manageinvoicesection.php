<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    // if(isset($_POST['projectid']) && $_POST['projectid'] != "")
    // {
?>
      
          <table class="table-element align-center" id="project-invoice-table" cellpadding="0" cellspacing="0" style="font-size: .8em;">
            
              <?php
                  $con = connectMySQL();
                  $sqlgetinvoice = "select *,
                  (select bill_id from bill_invoice_mapping where invoice_id = pi.id) as bill_id,
                  (select name from project where id = pi.project_id) as project_name,
                  (select name from gst_slab where percentage = pi.gst_slab) as gst_name
                   from purchase_invoice as pi where pi.is_deleted = 0";
                  $rowgetinvoice = mysqli_query($con, $sqlgetinvoice);

                  if (mysqli_num_rows($rowgetinvoice) > 0)
                  {
              ?>
            <thead>
              <tr>
                <th>Project</th>
                <th>Reference Number</th>
                <th>Amount excl. GST (Rs.)</th>
                <th>GST Percent</th>
                <th>Amount incl. GST (Rs.)</th>
                <th>Description</th>
                <th>Type</th>
                <th>Tagged</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
                    $i = 0;
                    while ($i <= ($resgetinvoice = mysqli_fetch_array($rowgetinvoice)))
                    {
                ?>
                <tr> 
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetinvoice['project_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetinvoice['number']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper invoice-amount-excl-gst">
                      <?php echo $resgetinvoice['amount']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetinvoice['gst_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php  

                        if($resgetinvoice['gst_slab'] != "0")
                        {
                          $amount = (float)$resgetinvoice['amount'];
                          $gstslab = (int)$resgetinvoice['gst_slab'];

                          $gstamount = ((float)$resgetinvoice['amount']/100)*$gstslab;

                          $totalamount = $amount + $gstamount; 
                        }
                        else
                        {
                          $totalamount = $resgetinvoice['amount'];
                        }

                        echo numberFormat(round($totalamount,2),2);

                      ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetinvoice['description']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php  
                        if($resgetinvoice['type']=="1"){echo "Recurring";}
                        if($resgetinvoice['type']=="2"){echo "Onetime";}

                      ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php  
                        if($resgetinvoice['bill_id'] == "" || $resgetinvoice['bill_id'] == NULL)
                        {
                          echo "General";
                        }
                        else
                        {
                          $sqlgetbill = "select year,
                          (select name from month where value = b.month) as month_name
                          from bill as b where id = ".$resgetinvoice['bill_id'];
                          $rowgetbill = mysqli_query($con, $sqlgetbill);
                          $resgetbill = mysqli_fetch_array($rowgetbill);

                          echo $resgetbill['month_name']." ".$resgetbill['year'];
                        }
                      ?>
                    </div>
                  </td>   
                  <td>
                    <div class="table-value-wrapper align-center">
                      <a class="delete-invoice-btn" id="delete-invoice-btn-<?php echo $i+1; ?>" data-invoice-id="<?php echo $resgetinvoice['id']; ?>" data-project-id="<?php echo $resgetinvoice['project_id']; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
                    </div>
                  </td>            
                <?php
                      $i++;
                    }
                ?>
            </tbody>
            <tfoot>
              <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
              </tr>
          </tfoot>
            <script type="text/javascript">
              initializeDataTable("project-invoice-table");
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
