<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    if(isset($_POST['projectid']) && $_POST['projectid'] != "")
    {
      $projectid = $_POST['projectid'];

      $cograndtotal = 0;
?>
      
            
              <?php
                  $con = connectMySQL();
                  $sqlgetcloudoutward = "select co.id,b.year, co.sales_total, co.remark,co.is_cancelled,co.created_on, co.cancel_reason,co.bill_id,
                  (select full_name from portal.login_detail where id = co.created_by) as created_by_name,
                  (select name from month where value = b.month) as month_name
                   from cloud_outward as co INNER JOIN bill as b on co.bill_id = b.id where co.project_id = ".$projectid." order by b.month , b.year , co.id";
                  $rowgetcloudoutward = mysqli_query($con, $sqlgetcloudoutward);

                  if (mysqli_num_rows($rowgetcloudoutward) > 0)
                  {
              ?>
          <table class="table-element align-center" id="project-cloud-outward-table" cellpadding="0" cellspacing="0" style="font-size: .8em;">
            <thead>
              <tr>
                <th>Month</th>
                <th>Year</th>
                <th>CO Number</th>
                <th>Sales Total excl. GST (Rs.)</th>
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
                    while ($i <= ($resgetcloudoutward = mysqli_fetch_array($rowgetcloudoutward)))
                    {
                      $cograndtotal += (float)$resgetcloudoutward['sales_total'];
                ?>
                <tr> 
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetcloudoutward['month_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetcloudoutward['year']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo "SEPL/CO/".$resgetcloudoutward['id']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo numberFormat(round($resgetcloudoutward['sales_total'],2),2); ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetcloudoutward['remark']; ?>
                    </div>
                  </td>
                  <td>
                    <?php 
                      if($resgetcloudoutward['is_cancelled'] == "0")
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
                      <?php echo $resgetcloudoutward['created_by_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper" title="<?php echo date("h:i:s a",strtotime($resgetcloudoutward['created_on'])); ?>">
                      <?php echo date("d-m-Y",strtotime($resgetcloudoutward['created_on'])); ?>
                    </div>
                  </td>   
                  <td>
                    <div class="table-value-wrapper align-center">
                      <?php 
                        if($resgetcloudoutward['is_cancelled'] == "0")
                        {
                      ?>
                      <a class="show-cancel-cloud-outward-modal-btn" id="show-cancel-cloud-outward-modal-btn-<?php echo $i+1; ?>" data-cloud-outward-id="<?php echo $resgetcloudoutward['id']; ?>" href="javascript:void(0);" style="color: red;font-size: 1.5em;" ><i class="fa fa-times" aria-hidden="true"></i></a>
                      <?php
                        }

                        if($resgetcloudoutward['is_cancelled'] == "1")
                        {
                          if($resgetcloudoutward['cancel_reason'] == "")
                          {
                            echo "Bill Deleted";
                          }
                          else
                          {
                            echo $resgetcloudoutward['cancel_reason'];
                          }
                        }
                      ?>
                    </div>
                  </td>            
                  <td class="no-print">
                    <div class="table-value-wrapper align-center">
                      <a target="_blank" href="<?php echo getDomain()."/printcloudoutward".getPageExt()."?pid=".$projectid."&bid=".$resgetcloudoutward['bill_id']."&coid=".$resgetcloudoutward['id']; ?>" style="color: grey;font-size: 1.5em;" ><i class="fa fa-print" aria-hidden="true"></i></a>
                    </div>
                  </td>
                </tr>
                <?php
                      $i++;
                    }
                ?>
            </tbody>
          </table>
          <div><b>Grand Total :</b> <?php echo "Rs. ".numberFormat(round($cograndtotal,2),2); ?></div>

            <script type="text/javascript">
              initializeDataTable("project-cloud-outward-table");
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
