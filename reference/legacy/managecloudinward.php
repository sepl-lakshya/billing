<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }

  include "include/config.php";  

  if(isLoggedIn())
  {

?>


<!DOCTYPE html>
<html <?php echo getDefaultTheme(); ?> >
<head>
  <title>Cloud Inwards</title>

  <!-- Head Section Include -->
  <?php include "include/headsection.php"; ?>
  
</head>
<body>
<div class="app-container">
  <div class="app-header">

    <!-- Header Include -->
    <?php include "include/header.php";  ?>

  <div class="app-content">
    
    <!-- Sidebar Include -->
    <?php include "include/sidebar.php"; ?>
    
    <div class="content-container">
      <div class="content-container-header">
        <p>Cloud Inwards</p>
      </div>

      <section class="content-section">
        <div class="section-heading">
          
        </div>
        <div class="section-content-wrapper">
          <table class="table-element align-center" cellpadding="0" cellspacing="0" style="font-size: .8em;" id="cloud-inward-table">
              <?php
                $con = connectMySQL();

                $cloudinwardexist = false;

                $sqlgetcloudinward = "select ici.id,b.year, ici.portal_total, ici.purchase_total, ici.remark,ici.is_cancelled,ici.created_on, ici.cancel_reason, ici.bill_id,ici.project_id,
                (select full_name from portal.login_detail where id = ici.created_by) as created_by_name,
                (select name from month where value = b.month) as month_name,
                (select name from project where id = ici.project_id) as project_name,
                (select number from purchase_invoice where id = ici.invoice_id) as invoice_number,
                (select amount from purchase_invoice where id = ici.invoice_id) as invoice_amount
                 from invoice_cloud_inward as ici INNER JOIN bill as b on ici.bill_id = b.id order by ici.id";
                $rowgetcloudinward = mysqli_query($con, $sqlgetcloudinward);

                if (mysqli_num_rows($rowgetcloudinward) > 0)
                {
                  $cloudinwardexist = true;
              ?>
            <thead>
              <tr>
                <th>CI Number</th>
                <th>Month</th>
                <th>Year</th>
                <th>Project Name</th>
                <th>Invoice Number</th>
                <th>Invoice Amount (Rs.)</th>
                <th>Portal Total (Rs.)</th>
                <th>Purchase Total (Rs.)</th>
                <th>Remark</th>
                <th>Status</th>
                <th>Verified By</th>
                <th>Created On</th>
                <th>Cancel Reason</th>
                <th>Print</th>
              </tr>
            </thead>
            <tbody>
                <?php
                    $i = 0;
                    while ($i <= ($resgetcloudinward = mysqli_fetch_array($rowgetcloudinward)))
                    {
                ?>
                <tr> 
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo "SEPL/CI/".$resgetcloudinward['id']; ?>
                    </div>
                  </td>
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
                      <?php echo $resgetcloudinward['project_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetcloudinward['invoice_number']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetcloudinward['invoice_amount']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo numberFormat(round($resgetcloudinward['portal_total'],2),2); ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo numberFormat(round($resgetcloudinward['purchase_total'],2),2); ?>
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
                      <?php echo $statustext; ?>
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
                        else
                        {
                          echo "-";
                        }
                      ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper align-center">
                      <a target="_blank" href="<?php echo getDomain()."/printcloudinward".getPageExt()."?pid=".$resgetcloudinward['project_id']."&bid=".$resgetcloudinward['bill_id']."&ciid=".$resgetcloudinward['id']; ?>" style="color: grey;font-size: 1.5em;" ><i class="fa fa-print" aria-hidden="true"></i></a>
                    </div>
                  </td>            
                <?php
                      $i++;
                    }
                ?>
              </tr>
            </tbody>
                <?php
                  }
                  else
                  {
              ?>
                <tbody>
                  <tr>
                    <td class="align-center">No Cloud Inward Found !</td>
                  </tr>
                </tbody>
              <?php
                  }
                  mysqli_close($con);

                ?>
          </table>
        </div>
      </section>


      <div class="content-section-divider"></div>


      <!-- <section class="content-section">
        <div class="section-heading">
          List of Distributors
        </div>
        <div class="section-content-wrapper">
          <div id="distributor-list-section">
                    
          </div>
          <div class="ce-spinner-overlay" id="distributor-list-section-loading" style="text-align:center;display:none;">
              <img src="<?php echo getImageDomain();?>/images/gif/spinner.gif" height="50" width="50">
          </div>
        </div>
      </section> -->
      
    </div>


</div>

</div>
</div>

<!-- Footer Include -->
<?php include "include/footer.php"; ?>

<!-- Bottom JS -->
<?php include "include/bottomjs.php"; ?>

<script >

  $(document).ready(function(){

    $("#cloud-inward-tab").addClass(" active");
    var menuid = "#cloud-main-menu";
    $(menuid+" .dropdown-content").addClass("show");
    $(menuid+" button .fa-caret-down").css("transform","rotate(180deg)"); 

  });

</script>

<script type="text/javascript" src="js/managecloudinward.js?uid=<?php echo uniqid(); ?>" ></script>
<?php
  if($cloudinwardexist)
  {
?>
  <script type="text/javascript">
    initializeDataTable("cloud-inward-table");
  </script>
<?php
  }
?>
</body>
</html>
<?php
  }
  else
  { 
      header("location:".getDomain());
  }

  
?>