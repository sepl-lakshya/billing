<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

?>
      
          <table class="table-element" id="distributor-section-table" cellpadding="0" cellspacing="0">
            <thead>
              <tr>
                <th>Distributor Name</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
                <?php
                  $con = connectMySQL();
                  $sqlgetdistributor = "select * from distributor where is_active = 1 order by id desc";
                  $rowgetdistributor = mysqli_query($con, $sqlgetdistributor);

                  if (mysqli_num_rows($rowgetdistributor) > 0)
                  {
                    $i = 0;
                    while ($i <= ($resgetdistributor = mysqli_fetch_array($rowgetdistributor)))
                    {
                ?>
                <tr>
                  <!-- <td><div class="table-value-wrapper"><?php echo $i+1; ?></div></td> -->
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetdistributor['name']; ?>
                    </div>
                  </td>                  
                  <td>
                    <div class="table-value-wrapper align-center">
                      <!-- <a class="edit-distributor-btn" id="edit-distributor-btn-<?php echo $i+1; ?>" data-distributor-id="<?php echo $resgetdistributor['id']; ?>" href="javascript:void(0);"><i class="fas fa-edit"></i></a> -->
                      <a class="delete-distributor-btn" id="delete-distributor-btn-<?php echo $i+1; ?>" data-distributor-id="<?php echo $resgetdistributor['id']; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
                
                initializeDataTable("distributor-section-table");

            </script>
          </table>
