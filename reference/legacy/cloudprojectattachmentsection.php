  <?php
    if (session_status() == PHP_SESSION_NONE) 
    {
      session_start();
    }
      require_once "include/config.php";
      $con = connectMySQL();
      
      if(isset($_POST['projectid']) && $_POST['projectid'] != "")
      {
        $pid = $_POST['projectid'];


  ?>
        
            <table class="table-element" cellpadding="0" cellspacing="0" style="font-size: .8em;">
              
                  <?php
                    $sqlgetattachments = "select * from project_attachment where project_id = ".$pid;
                    $rowgetattachments = mysqli_query($con, $sqlgetattachments);

                    if (mysqli_num_rows($rowgetattachments) > 0)
                    {

                ?>
              <thead>
                <tr>
                  <th>SrNo</th>
                  <th>Title</th>
                  <th>File</th>
                  <th class="no-print-action-btn">Delete</th>
                </tr>
              </thead>
              <tbody>
                <?php
                      $i = 0;
                      while ($i <= ($resgetattachments = mysqli_fetch_array($rowgetattachments)))
                      {
                        $filelink = getDomain()."/uploads/attachment/".$resgetattachments['file_name'];
                  ?>
                  <tr>
                    <td>
                      <div class="table-value-wrapper align-center">
                        <?php echo $i+1; ?>
                      </div>
                    </td>
                    <td>
                      <div class="table-value-wrapper align-center">
                        <?php echo $resgetattachments['title']; ?>
                      </div>
                    </td>
                    <td>
                      <div class="table-value-wrapper align-center">
                        <a href="<?php echo $filelink; ?>" download="<?php echo $resgetattachments['original_name']; ?>" style="color: blue;text-decoration: underline;cursor: pointer;" title="Click to Download "><?php echo $resgetattachments['original_name']; ?> <i class="fa fa-download" aria-hidden="true"></i></a>
                      </div>
                    </td>
                    <td>
                      <div class="table-value-wrapper align-center">
                        <a class="delete-attachment-file-btn" id="delete-attachment-file-btn-<?php echo $i+1; ?>" data-attachment-id="<?php echo $resgetattachments['id']; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
                      </div>
                    </td>
                  </tr>
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
                <?php
                    }

                    mysqli_close($con);
                  ?>
            </table>
  <?php 
    }
  ?>
