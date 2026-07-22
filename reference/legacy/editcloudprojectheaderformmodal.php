<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

    if(isset($_POST['projectid']) && $_POST['projectid'] != "")
    {
      $projectid = $_POST['projectid'];
      $headerid = $_POST['headerid'];
            
      $con = connectMySQL();
      $sqlgetprojectheader = "select name,quantity,description from project_header where id = ".$headerid." AND project_id = ".$projectid;
      $rowgetprojectheader = mysqli_query($con, $sqlgetprojectheader);

      if (mysqli_num_rows($rowgetprojectheader) > 0)
      {
        $resgetprojectheader = mysqli_fetch_array($rowgetprojectheader);
?>
          <form method="post" class="ce-form" id="ce-edit-project-header-form" style="width:100%;">
            <input type="hidden" id="edit-header-project-id" name="edit-header-project-id" value="<?php echo $projectid; ?>">
            <input type="hidden" id="edit-header-id" name="edit-header-id" value="<?php echo $headerid; ?>">
            <div class="project-products-list-item-form">
            <div class="ce-form-controls ce-form-control-col-2">
              <div class="ce-form-label">Header Name :</div>
              <div class="ce-form-input">
                <input type="text" id="edit-header-name" name="edit-header-name" value="<?php echo $resgetprojectheader['name']; ?>">
              </div>
            </div>
            <div class="ce-form-controls ce-form-control-col-2">
              <div class="ce-form-label">Quantity:</div>
              <div class="ce-form-input">
                <input type="number" id="edit-header-quantity" name="edit-header-quantity" value="<?php echo $resgetprojectheader['quantity']; ?>" min="1" >
              </div>
            </div>
            <div class="ce-form-controls ce-form-control-col-2">
              <div class="ce-form-label">Description :</div>
              <div class="ce-form-input">
                <textarea id="edit-header-description" name="edit-header-description" ><?php echo $resgetprojectheader['description']; ?></textarea>
              </div>
            </div>


            <div class="ce-form-controls ce-form-control-col-2" >
              <div class="ce-form-input align-right">
                  <input type="submit" id="ce-edit-project-header-form-submit" name="ce-edit-project-header-form-submit" value="Submit">
              </div>
            </div>  
            </div>
          </form>
<?php 
      }
      else
      {
        echo "Error : Header not found!";
      }
    }
    else
    {
      echo "Error : Missing Parameters!";
    }
?>