<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

?>
      
          <table class="table-element" id="purchase-header-section-table" cellpadding="0" cellspacing="0">
            <thead>
              <tr>
                <th>Purchase Header Name</th>
                <th>Project Name</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
                <?php
                  $con = connectMySQL();

                  if($_SESSION['user_type'] == "1")
                  {
                    $createdby = "";
                  }
                  else
                  {
                    $createdby = " where created_by = ".$_SESSION['user_id'];
                  }

                  $sqlgetpurchaseheader = "select *, 
                  (select name from project where id = ph.project_id) as project_name 
                  from purchase_header as ph where is_active = 1 AND (ph.project_id IN (select id from project ".$createdby.") OR ph.project_id IN (select project_id from project_user_mapping where user_id = ".$_SESSION['user_id'].") OR ph.project_id = 0)  order by id desc";
                  $rowgetpurchaseheader = mysqli_query($con, $sqlgetpurchaseheader);

                  if (mysqli_num_rows($rowgetpurchaseheader) > 0)
                  {
                    $i = 0;
                    while ($i <= ($resgetpurchaseheader = mysqli_fetch_array($rowgetpurchaseheader)))
                    {

                ?>
                <tr>
                  <!-- <td><div class="table-value-wrapper"><?php echo $i+1; ?></div></td> -->
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetpurchaseheader['name']; ?>
                    </div>
                  </td>  
                  <td>
                    <div class="table-value-wrapper">
                      <?php
                        if($resgetpurchaseheader['project_id'] == "0")
                        {
                          echo "Generic";
                        } 
                        else
                        {
                          echo $resgetpurchaseheader['project_name']; 
                        }
                      ?>
                    </div>
                  </td>                  
                  <td>
                    <div class="table-value-wrapper align-center">
                      <!-- <a class="edit-oem-btn" id="edit-oem-btn-<?php echo $i+1; ?>" data-oem-id="<?php echo $resgetpurchaseheader['id']; ?>" href="javascript:void(0);"><i class="fas fa-edit"></i></a> -->
                      <a class="delete-purchase-header-btn" id="delete-purchase-header-btn-<?php echo $i+1; ?>" data-purchase-header-id="<?php echo $resgetpurchaseheader['id']; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
                    </div>
                  </td>
                </tr>
                <?php
                      $i++;
                    }
                  }

                  mysqli_close($con);
                ?>
            </tbody>
            <script type="text/javascript">
                
                initializeDataTable("purchase-header-section-table");

            </script>
          </table>
