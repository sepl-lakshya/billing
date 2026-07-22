<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

?>
      
          <table class="table-element" id="user-section-table" cellpadding="0" cellspacing="0">
            <thead>
              <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Azure Object ID</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
                <?php
                  $con = connectMySQL();
                  $sqlgetuser = "select * from login_detail where is_active = 1 AND user_type = 2 order by id desc";
                  $rowgetuser = mysqli_query($con, $sqlgetuser);

                  if (mysqli_num_rows($rowgetuser) > 0)
                  {
                    $i = 0;
                    while ($i <= ($resgetuser = mysqli_fetch_array($rowgetuser)))
                    {
                ?>
                <tr>
                  <!-- <td><div class="table-value-wrapper"><?php echo $i+1; ?></div></td> -->
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetuser['full_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetuser['email']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetuser['azure_object_id']; ?>
                    </div>
                  </td>                  
                  <td>
                    <div class="table-value-wrapper align-center">
                      <!-- <a class="edit-user-btn" id="edit-user-btn-<?php echo $i+1; ?>" data-user-id="<?php echo $resgetuser['id']; ?>" href="javascript:void(0);"><i class="fas fa-edit"></i></a> -->
                      <a class="delete-user-btn" id="delete-user-btn-<?php echo $i+1; ?>" data-user-id="<?php echo $resgetuser['id']; ?>" data-azure-object-id="<?php echo $resgetuser['azure_object_id']; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
                
                initializeDataTable("user-section-table");

            </script>
          </table>
