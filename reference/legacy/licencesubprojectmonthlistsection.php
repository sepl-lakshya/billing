              <option value="0">Select Month</option>
<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

    if(isset($_POST['year']) && $_POST['year'] != "" && isset($_POST['projectid']) && $_POST['projectid'] != "")
    {
      $yearval = $_POST['year'];
      $projectid = $_POST['projectid'];
      $projectitemid = $_POST['projectitemid'];
      
      $con = connectMySQL();

      $sqlgetproject = "select YEAR(start_date) as year, MONTH(start_date) as month from licence_project where id = ".$projectid;
      $rowgetproject = mysqli_query($con, $sqlgetproject);

      if (mysqli_num_rows($rowgetproject) > 0)
      {
        $resgetproject = mysqli_fetch_array($rowgetproject);
        
        if($resgetproject['year'] == $yearval)
        {
          $monthstart = $resgetproject['month'];
        }
        else
        {
          $monthstart = 1;
        }

        $sqlgetmonths = "select * from month where value >= ".$monthstart;
                    $rowgetmonths = mysqli_query($con, $sqlgetmonths);

        if (mysqli_num_rows($rowgetmonths) > 0)
        {
          $i = 0;
          while ($i <= ($resgetmonths = mysqli_fetch_array($rowgetmonths)))
          {
      ?>
        <option value="<?php echo $resgetmonths['value']; ?>">
          <?php echo $resgetmonths['name']; ?>
        </option>
      <?php
            $i++;
          }
        }
      }

        mysqli_close($con);

    }
?>
