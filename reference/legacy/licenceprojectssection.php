<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

    if(isset($_POST))
    {
      if($_SESSION['user_type'] == "1")
      {
        $createdby = "";
      }
      else
      {
        $createdby = " AND created_by = ".$_SESSION['user_id'];
      }
?>
      
          <table class="table-element" id="project-section-table" cellpadding="0" cellspacing="0">
            
                <?php
                  $con = connectMySQL();
                  $sqlgetproject = "select *,
                  (select name from states where value = p.state) as state_name, 
                  (select full_name from login_detail where id = p.assigned_to) as assigned_user_name  
                  from licence_project as p 
                  where (is_active = 1 AND product_category = 2 ".$createdby.") OR id IN (select project_id from licence_project_user_mapping where user_id = ".$_SESSION['user_id'].") order by created_on desc";
                  $rowgetproject = mysqli_query($con, $sqlgetproject);

                  if (mysqli_num_rows($rowgetproject) > 0)
                  {
            ?>
                <thead> 
                  <tr>
                    <th>Project Name</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Tender Number</th>
                    <th>Start Date</th>
                    <th>Contract Period</th>
                    <th>Action</th>
                    <?php
                      if($_SESSION['user_type'] == "1")
                      {
                    ?>
                    <th>Assign / <br> Assigned To</th>
                    <?php
                      }
                    ?>
                  </tr>
                </thead>
                <tbody>
            <?php
                    $i = 0;
                    while ($i <= ($resgetproject = mysqli_fetch_array($rowgetproject)))
                    {
                      $projectlink = getDomain()."/viewlicenceproject".getPageExt()."?pid=".$resgetproject['id']."&ph=".$resgetproject['hash'];
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
                      <?php 
                          $contractperiod = "";
                          if($resgetproject['contract_year'] != "0")
                          {
                            if($resgetproject['contract_year'] == "1")
                            {
                              $contractperiod .= $resgetproject['contract_year']." Year ";
                            }
                            else
                            {
                              $contractperiod .= $resgetproject['contract_year']." Years ";
                            }
                          }

                          if($resgetproject['contract_month'] != "0")
                          {
                            if($resgetproject['contract_month'] == "1")
                            {
                              $contractperiod .= "<br> ".$resgetproject['contract_month']." Month ";
                            }
                            else
                            {
                              $contractperiod .= "<br> ".$resgetproject['contract_month']." Months ";
                            }
                          }

                        echo $contractperiod;
                      ?>
                    </div>
                  </td>                    
                  <td>
                    <div class="table-value-wrapper align-center">
                      <!-- <a class="edit-project-btn" id="edit-project-btn-<?php echo $i+1; ?>" data-project-id="<?php echo $resgetproject['id']; ?>" href="javascript:void(0);"><i class="fas fa-edit"></i></a> -->
                <?php
                  if($resgetproject['created_by'] == $_SESSION['user_id'])
                  {
                ?>
                    <a class="delete-project-btn" id="delete-project-btn-<?php echo $i+1; ?>" data-project-id="<?php echo $resgetproject['id']; ?>" data-project-hash="<?php echo $resgetproject['hash']; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
                <?php
                  }
                ?>
                    </div>
                  </td>
              <?php
                if($_SESSION['user_type'] == "1")
                {
              ?>
                  <td>
                  <?php
                    if($resgetproject['created_by'] == $_SESSION['user_id'])
                    {
                  ?>
                    <div class="table-value-wrapper align-center">
                      <a class="button manage-assignment-btn" id="manage-assignment-btn-<?php echo $i+1; ?>" data-project-id="<?php echo $resgetproject['id']; ?>" href="javascript:void(0);">Manage</a>
                    </div>
                  <?php
                    }
                  ?>
                  </td>
              <?php
                }
              ?>
                </tr>
                <?php
                      $i++;
                    }
              ?>
                </tbody>
                <script type="text/javascript">
                    initializeDataTable("project-section-table");
                </script>
              <?php
                  }
                  else
                  {
              ?>
                <tbody>
                  <tr>
                    <td class="align-center"> No Projects Found !</td>
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
