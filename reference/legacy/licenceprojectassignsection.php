<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";
    if(isset($_POST['projectid']) && $_POST['projectid'] != "")
    {
      $pid = $_POST['projectid'];

      $con = connectMySQL();
?>
          <form method="post" class="ce-form" id="ce-assign-project-form">
            <input type="hidden" id="assign-project-id" name="assign-project-id" value="<?php echo $pid; ?>">
            <div class="ce-form-controls ce-form-control-col-2" id="contract-period-wrapper">
              <div class="ce-form-label">Select User :</div>
              <div class="ce-form-input">
                <select id="assign-user-id" name="assign-user-id">
                  <option value="0">Select User</option>
                  <?php

                      $sqlgetusers = "select * from login_detail where user_type = 2 AND id NOT IN (select user_id from licence_project_user_mapping where project_id = ".$pid.")";
                      $rowgetusers = mysqli_query($con, $sqlgetusers);

                      if (mysqli_num_rows($rowgetusers) > 0)
                      {
                        $i = 0;
                        while ($i <= ($resgetusers = mysqli_fetch_array($rowgetusers)))
                        {
                    ?>
                      <option value="<?php echo $resgetusers['id']; ?>">
                        <?php echo $resgetusers['full_name']." (".$resgetusers['email'].")"; ?>
                      </option>
                    <?php
                          $i++;
                        }
                      }
                  ?>
                </select>
              </div>
            </div>
            <div class="ce-form-controls ce-form-control-col-2">
              <div class="ce-form-input align-right">
                  <input type="submit" id="ce-assign-project-form-submit" name="ce-assign-project-form-submit" value="Assign" >
              </div>
            </div>
          </form>

          <table class="table-element align-center" cellpadding="0" cellspacing="0" style="font-size: .8em;">
            
              <?php
                  $sqlgetprojectuser = "select user_id,created_on,
                  (select full_name from login_detail where id = lpum.user_id) as user_full_name,
                  (select email from login_detail where id = lpum.user_id) as user_email
                  from licence_project_user_mapping as lpum where project_id = ".$pid." AND assigned_by = ".$_SESSION['user_id'];
                  $rowgetprojectuser = mysqli_query($con, $sqlgetprojectuser);

                  if (mysqli_num_rows($rowgetprojectuser) > 0)
                  {
              ?>
            <thead>
              <tr>
                <th>User</th>
                <th>Assigned On</th>
                <th>Remove</th>
              </tr>
            </thead>
            <tbody>
              <?php
                    $i = 0;
                    while ($i <= ($resgetprojectuser = mysqli_fetch_array($rowgetprojectuser)))
                    {
                ?>
                <tr>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetprojectuser['user_full_name']." (".$resgetprojectuser['user_email'].")"; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo date("d M Y",strtotime($resgetprojectuser['created_on'])); ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <a class="button unassign-project-btn" id="unassign-project-btn-<?php echo $i+1; ?>" data-project-id="<?php echo $pid; ?>" data-user-id="<?php echo $resgetprojectuser['user_id']; ?>" href="javascript:void(0);">Remove</a>
                    </div>
                  </td>  
                <?php
                      $i++;
                    }
                ?>
            </tbody>
                <?php
                  }
                  else
                  {
              ?>
                <tbody>
                  <tr>
                    <td class="align-center">No Data Found !</td>
                  </tr>
                </tbody>
              <?php
                  }
                  mysqli_close($con);

                ?>
          </table>

<?php 
  }
?>