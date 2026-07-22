<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

    if(isset($_POST['projectitemid']) && $_POST['projectitemid'] != "")
    {
      $projectitemid = $_POST['projectitemid'];
      
      $con = connectMySQL();

      $sqlgetprojectitem = "select *,
      (select name from project_header where id = pi.header_id) as header_name,
      (select name from product where id = pi.product) as product_name
      from project_item as pi where id = ".$projectitemid;
      $rowgetprojectitem = mysqli_query($con, $sqlgetprojectitem);

      if (mysqli_num_rows($rowgetprojectitem) > 0)
      {
        $resgetprojectitem = mysqli_fetch_array($rowgetprojectitem);


?>
          <form method="post" class="ce-form" id="ce-deactivate-project-item-form" style="width:100%;">
              <input type="hidden" id="deactivate-project-item-id" name="deactivate-project-item-id" value="<?php echo $projectitemid; ?>">
              
              <div class="project-products-list-item-form">
                <div class="ce-form-controls ce-form-control-col-1">
                  <div class="ce-form-label">Header Name :</div>
                  <div class="ce-form-input">
                    <?php echo $resgetprojectitem['header_name'] ?>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-3">
                  <div class="ce-form-label">Product :</div>
                  <div class="ce-form-input">
                    <?php echo $resgetprojectitem['product_name']; ?>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-3">
                  <div class="ce-form-label">Model :</div>
                  <div class="ce-form-input">
                    <?php echo $resgetprojectitem['model']; ?>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-3">
                  <div class="ce-form-label">Deployment Date :</div>
                  <div class="ce-form-input">
                    <?php echo date("d-m-Y",strtotime($resgetprojectitem['deployment_start'])); ?>
                  </div>
                </div>
                <div class="ce-form-controls ce-form-control-col-4">
                  <div class="ce-form-label">Deployment End :</div>
                  <div class="ce-form-input">
                    <input type="date" id="deactivate-item-deployment-end" name="deactivate-item-deployment-end"  min="<?php echo $resgetprojectitem['deployment_start']; ?>" max="<?php echo date("Y-m-d"); ?>">
                  </div>
                </div>

                <div class="ce-form-controls ce-form-control-col-1">
                  <div class="ce-form-input align-right">
                      <input type="submit" id="ce-deactivate-project-item-form-submit" name="ce-deactivate-project-item-form-submit" value="Submit" >
                  </div>
                </div>
              </div>
            </form>

<?php
      } 
    }
?>