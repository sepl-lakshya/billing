              <option value="0">Select Invoice</option>
<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

    if(isset($_POST['projectid']) && $_POST['projectid'] != "")
    {
      $projectid = $_POST['projectid'];     
      $con = connectMySQL();

      $sqlgetinvoice = "select id,number,amount from purchase_invoice as pi where pi.is_deleted = 0 AND pi.project_id = ".$projectid." AND id NOT IN (select invoice_id from bill_invoice_mapping where project_id = ".$projectid.")";
      $rowgetinvoice = mysqli_query($con, $sqlgetinvoice);

      if(mysqli_num_rows($rowgetinvoice) > 0)
      {
        $pri = 0;
        while ($pri <= ($resgetinvoice = mysqli_fetch_array($rowgetinvoice)))
        {
          $invoicelabel = $resgetinvoice['number']." (Rs. ".numberFormat(round($resgetinvoice['amount'],2),2).")";
    ?>
        <option label="<?php echo $invoicelabel; ?>" value="<?php echo $resgetinvoice['id']; ?>" data-ref-no="<?php echo $resgetinvoice['number']; ?>" data-amount="<?php echo $resgetinvoice['amount']; ?>"><?php echo $invoicelabel; ?></option>
    <?php      
          $pri++;
        }
      }                 
      mysqli_close($con);
    }
?>
