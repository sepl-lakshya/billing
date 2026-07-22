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
                <th>OEM Name</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
                <?php
                  $con = connectMySQL();
                  $sqlgetoem = "select * from oem where is_active = 1 order by id desc";
                  $rowgetoem = mysqli_query($con, $sqlgetoem);

                  if (mysqli_num_rows($rowgetoem) > 0)
                  {
                    $i = 0;
                    while ($i <= ($resgetoem = mysqli_fetch_array($rowgetoem)))
                    {
                ?>
                <tr>
                  <!-- <td><div class="table-value-wrapper"><?php echo $i+1; ?></div></td> -->
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetoem['name']; ?>
                    </div>
                  </td>                  
                  <td>
                    <div class="table-value-wrapper align-center">
                      <!-- <a class="edit-oem-btn" id="edit-oem-btn-<?php echo $i+1; ?>" data-oem-id="<?php echo $resgetoem['id']; ?>" href="javascript:void(0);"><i class="fas fa-edit"></i></a> -->
                      <a class="delete-oem-btn" id="delete-oem-btn-<?php echo $i+1; ?>" data-oem-id="<?php echo $resgetoem['id']; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
