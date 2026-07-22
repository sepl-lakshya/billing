<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

    if(isset($_POST))
    {

?>
      
          <table class="table-element" id="project-section-table" cellpadding="0" cellspacing="0">
            <thead>
              <tr>
                <th>Project Name</th>
                <th>City</th>
                <th>State</th>
                <th>Tender Number</th>
                <th>Start Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
                <?php
                  $con = connectMySQL();
                  $sqlgetproject = "select *,(select name from states where value = p.state) as state_name from project as p where is_active = 1 order by created_on desc";
                  $rowgetproject = mysqli_query($con, $sqlgetproject);

                  if (mysqli_num_rows($rowgetproject) > 0)
                  {
                    $i = 0;
                    while ($i <= ($resgetproject = mysqli_fetch_array($rowgetproject)))
                    {
                      $projectlink = getDomain()."/viewproject".getPageExt()."?pid=".$resgetproject['id']."&ph=".$resgetproject['hash'];
                ?>
                <tr>
                  <!-- <td><div class="table-value-wrapper"><?php echo $i+1; ?></div></td> -->
                  <td>
                    <div class="table-value-wrapper">
                      <a href="<?php echo $projectlink; ?>" style="text-decoration: underline;" class="project-links">
                        <i class="fa fa-external-link" aria-hidden="true"></i><?php echo $resgetproject['name']; ?> 
                      </a>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetproject['city']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetproject['state_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetproject['tender_ref_no']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo date("d-m-Y",strtotime($resgetproject['start_date'])); ?>
                    </div>
                  </td>                      
                  <td>
                    <div class="table-value-wrapper align-center">
                      <!-- <a class="edit-project-btn" id="edit-project-btn-<?php echo $i+1; ?>" data-project-id="<?php echo $resgetproject['id']; ?>" href="javascript:void(0);"><i class="fas fa-edit"></i></a> -->
                      <a class="delete-project-btn" id="delete-project-btn-<?php echo $i+1; ?>" data-project-id="<?php echo $resgetproject['id']; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
                
                initializeDataTable("project-section-table");

            </script>
          </table>
<?php
  }
?>
