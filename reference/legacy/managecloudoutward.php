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
  <title>Cloud Outwards</title>

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
        <p>Cloud Outwards</p>
      </div>

      <section class="content-section">
        <div class="section-heading">
          
        </div>
        <div class="section-content-wrapper">
          <table class="table-element align-center" cellpadding="0" cellspacing="0" style="font-size: .8em;" id="cloud-outward-table">
            
              <?php
                  $con = connectMySQL();

                  $cloudoutwardexist = false;

                  $sqlgetcloudoutward = "select co.id,b.year, co.sales_total, co.remark,co.is_cancelled,co.created_on, co.cancel_reason,co.bill_id, co.project_id,
                  (select full_name from portal.login_detail where id = co.created_by) as created_by_name,
                  (select name from month where value = b.month) as month_name,
                  (select name from project where id = co.project_id) as project_name
                   from cloud_outward as co INNER JOIN bill as b on co.bill_id = b.id order by co.id";
                  $rowgetcloudoutward = mysqli_query($con, $sqlgetcloudoutward);

                  if (mysqli_num_rows($rowgetcloudoutward) > 0)
                  {
                    $cloudoutwardexist = true;
              ?>
            <thead>
              <tr>
                <th>CO Number</th>
                <th>Month</th>
                <th>Year</th>
                <th>Project Name</th>
                <th>Sales Total (Rs.)</th>
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
                    while ($i <= ($resgetcloudoutward = mysqli_fetch_array($rowgetcloudoutward)))
                    {
                ?>
                <tr> 
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo "SEPL/CO/".$resgetcloudoutward['id']; ?>
                    </div>
                  </td>
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
                      <?php echo $resgetcloudoutward['project_name']; ?>
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
                        else
                        {
                          echo "-";
                        }
                      ?>
                    </div>
                  </td>  
                  <td>
                    <div class="table-value-wrapper align-center">
                      <a target="_blank" href="<?php echo getDomain()."/printcloudoutward".getPageExt()."?pid=".$resgetcloudoutward['project_id']."&bid=".$resgetcloudoutward['bill_id']."&coid=".$resgetcloudoutward['id']; ?>" style="color: grey;font-size: 1.5em;" ><i class="fa fa-print" aria-hidden="true"></i></a>
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
                    <td class="align-center">No Cloud Outward Found !</td>
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

    $("#cloud-outward-tab").addClass(" active");
    var menuid = "#cloud-main-menu";
    $(menuid+" .dropdown-content").addClass("show");
    $(menuid+" button .fa-caret-down").css("transform","rotate(180deg)"); 

  });

</script>

<script type="text/javascript" src="js/managecloudoutward.js?uid=<?php echo uniqid(); ?>" ></script>
<?php
  if($cloudoutwardexist)
  {
?>
  <script type="text/javascript">
    initializeDataTable("cloud-outward-table");
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