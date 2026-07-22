<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    $con = connectMySQL();
    if(isset($_POST['projectid']) && $_POST['projectid'] != "" && isset($_POST['billid']) && $_POST['billid'] != "")
    {
      $projectid = $_POST['projectid'];
      $billid = $_POST['billid'];


?>
      <?php
          $sqlgetpurchaseinvoice = "select invoice_id,
          (select amount from purchase_invoice where id = bim.invoice_id) as amount,
          (select number from purchase_invoice where id = bim.invoice_id) as number
          from bill_invoice_mapping as bim where bill_id = ".$billid;
          $rowgetpurchaseinvoice = mysqli_query($con, $sqlgetpurchaseinvoice);

          $pi = 0;
          if (mysqli_num_rows($rowgetpurchaseinvoice) > 0)
          {
      ?>
        
        <table id="add-purchase-bill-invoice-table" class="table-element align-center" cellpadding="0" cellspacing="0" style="font-size: .6em;margin-top: 10px;">
          <thead>
            <tr>
              <th>Invoice Number</th>
              <th>Invoice Amount</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
        <?php
            $rowcount = 1;
            while ($pi <= ($resgetpurchaseinvoice = mysqli_fetch_array($rowgetpurchaseinvoice)))
            {
        ?>  
          <tr class="add-purchase-bill-invoice-table-row" id="add-purchase-bill-invoice-table-row-<?php echo $pi; ?>" data-invoice-row-no="<?php echo $pi; ?>" style="" data-invoice-id="<?php echo $resgetpurchaseinvoice['invoice_id']; ?>" data-invoice-number="<?php echo $resgetpurchaseinvoice['number']; ?>" data-invoice-amount="<?php echo $resgetpurchaseinvoice['amount']; ?>">
            <td>
              <div class="table-value-wrapper"><?php echo $resgetpurchaseinvoice['number']; ?></div>
            </td>
            <td>
              <div class="table-value-wrapper"><?php echo numberFormat(round($resgetpurchaseinvoice['amount'],2),2); ?></div>
            </td>
            <td>
              <div class="table-value-wrapper">
                <?php
                  $sqlgetcloudinward = "select id from invoice_cloud_inward where project_id = ".$projectid." AND bill_id = ".$billid." AND invoice_id = ".$resgetpurchaseinvoice['invoice_id']." AND is_cancelled = 0";
                  $rowgetcloudinward = mysqli_query($con, $sqlgetcloudinward);

                  if(mysqli_num_rows($rowgetcloudinward) > 0)
                  {
                    echo "CI Created";
                  }
                  else
                  {
                ?>
                <a class="delete-purchase-bill-invoice-btn" id="delete-purchase-bill-invoice-btn-<?php echo $pi; ?>" data-invoice-id="<?php echo $resgetpurchaseinvoice['invoice_id']; ?>" data-bill-id="<?php echo $billid; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
                <?php
                  }
                ?>
              </div>
            </td>
          </tr>
        <?php
              $pi++;
            }
        ?>
          </tbody>
        </table>
        <?php
          }
        ?>            

        <input type="hidden" id="add-purchase-bill-invoice-table-row-count" name="add-purchase-bill-invoice-table-row-count" value="<?php echo $pi; ?>">
<?php 
  }
?>
