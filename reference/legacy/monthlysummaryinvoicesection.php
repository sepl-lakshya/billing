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
          (select number from purchase_invoice where id = bim.invoice_id) as number,
          (select invoice_date from purchase_invoice where id = bim.invoice_id) as invoice_date
          from bill_invoice_mapping as bim where bill_id = ".$billid;
          $rowgetpurchaseinvoice = mysqli_query($con, $sqlgetpurchaseinvoice);

          $pi = 0;
          if (mysqli_num_rows($rowgetpurchaseinvoice) > 0)
          {
      ?>
        
        <table id="add-purchase-bill-invoice-table" class="table-element align-center" cellpadding="0" cellspacing="0" style="font-size: .8em;margin-top: 10px;">
          <thead>
            <tr>
              <th >Invoice Number</th>
              <th >Invoice Amount (Rs.)</th>
              <th >Invoice Date</th>
              <th >Action</th>
            </tr>
          </thead>
          <tbody>
        <?php
            $rowcount = 1;
            while ($pi <= ($resgetpurchaseinvoice = mysqli_fetch_array($rowgetpurchaseinvoice)))
            {
        ?>  
          <tr class="add-purchase-bill-invoice-table-row" id="add-purchase-bill-invoice-table-row-<?php echo $pi; ?>" data-invoice-row-no="<?php echo $pi; ?>" style="background: #C4BEBE;" data-invoice-id="<?php echo $resgetpurchaseinvoice['invoice_id']; ?>" data-invoice-number="<?php echo $resgetpurchaseinvoice['number']; ?>" data-invoice-amount="<?php echo $resgetpurchaseinvoice['amount']; ?>">
            <td>
              <div class="table-value-wrapper"><?php echo $resgetpurchaseinvoice['number']; ?></div>
            </td>
            <td>
              <div class="table-value-wrapper"><?php echo "Rs.".numberFormat(round($resgetpurchaseinvoice['amount'],2),2); ?></div>
            </td>
            <td>
              <div class="table-value-wrapper"><?php echo date("d-m-Y",strtotime($resgetpurchaseinvoice['invoice_date'])); ?></div>
            </td>
            <td>
              <div class="table-value-wrapper">
            <?php
              $sqlgetcloudinward = "select id from invoice_cloud_inward where project_id = ".$projectid." AND bill_id = ".$billid." AND invoice_id = ".$resgetpurchaseinvoice['invoice_id']." AND is_cancelled = 0";
                $rowgetcloudinward = mysqli_query($con, $sqlgetcloudinward);

                if(mysqli_num_rows($rowgetcloudinward) > 0)
                {
                  $resgetcloudinward = mysqli_fetch_array($rowgetcloudinward);
                  echo "<b>CI Created : SEPL/CI/".$resgetcloudinward['id']."</b>";
                }
                else
                {
            ?>
                  <a class="button show-create-debit-note-modal-btn" id="show-create-debit-note-modal-btn-<?php echo $pi; ?>" data-invoice-id="<?php echo $resgetpurchaseinvoice['invoice_id']; ?>" data-bill-id="<?php echo $billid; ?>" data-is-general-debit-note="0" href="javascript:void(0);">Create DN</a>

                  <a class="button show-create-invoice-cloud-inward-modal-btn" id="show-create-debit-note-modal-btn-<?php echo $pi; ?>" data-invoice-id="<?php echo $resgetpurchaseinvoice['invoice_id']; ?>" data-bill-id="<?php echo $billid; ?>" data-is-general-debit-note="0" href="javascript:void(0);">Create CI</a>
              <?php
                }
              ?>
              </div>
            </td>
            <!-- <td>
              <div class="table-value-wrapper">
                <a class="delete-purchase-bill-invoice-btn" id="delete-purchase-bill-invoice-btn-<?php echo $pi; ?>" data-invoice-id="<?php echo $resgetpurchaseinvoice['invoice_id']; ?>" data-bill-id="<?php echo $billid; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
              </div>
            </td> -->
          </tr>


          <?php
            if($resgetpurchaseinvoice['invoice_id'] != "")
            {  
              $sqlgetdebitnote = "select *, 
              (select name from credit_note_type where value = dn.credit_type) as debit_note_type
              from debit_note as dn where project_id = ".$projectid." AND invoice_id = ".$resgetpurchaseinvoice['invoice_id'];
              $rowgetdebitnote = mysqli_query($con, $sqlgetdebitnote);

              if(mysqli_num_rows($rowgetdebitnote) > 0)
              {
                $dn = 0;
                while ($dn <= ($resgetdebitnote = mysqli_fetch_array($rowgetdebitnote)))
                {
            ?>
            <tr >
              <td colspan="4" style="border: none;padding: 0;">
              <table class="table-element" cellspacing="0" style="width: 95% !important;margin-left: 4.5%;">
                <tr>
                  <td style="width: 25%;">
                    <div class="table-value-wrapper">
                      <b>DN Number</b> : <?php echo "SEPL/DN/".$resgetdebitnote['id']; ?> 
                    </div>
                  </td>
                  <td style="width: 25%;">
                    <div class="table-value-wrapper">
                      <b>DN Amount</b> : Rs.<?php echo $resgetdebitnote['amount']; ?> 
                    </div>
                  </td>
                  <td style="width: 25%;">
                    <div class="table-value-wrapper">
                      <b>DN Type</b> : <?php echo $resgetdebitnote['debit_note_type']; ?> 
                    </div>
                  </td>
                  <td style="width: 25%;">
                    <div class="table-value-wrapper">
                      <b>Created On</b> : <?php echo date("d m Y",strtotime($resgetdebitnote['created_on'])); ?> 
                    </div>
                  </td>
                </tr>
              </table>
              </td>
            </tr>
            <?php
                  $dn++;
                }
              }
            }
            ?>


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
