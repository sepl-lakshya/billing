<?php
  if (session_status() == PHP_SESSION_NONE) 
  {
    session_start();
  }
    require_once "include/config.php";

?>
      
          <table class="table-element" id="product-section-table" cellpadding="0" cellspacing="0">
            <thead>
              <tr>
                <th>Product Name</th>
                <th>OEM</th>
                <th>Category</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
                <?php
                  $con = connectMySQL();
                  $sqlgetproduct = "select *,
                  (select name from product_category where id = p.category) as category_name,
                  (CASE 
                      WHEN category = 1 THEN (select name from cloud_category where id = p.sub_category)
                      WHEN category = 2 THEN (select name from licence_category where id = p.sub_category)
                  END) as sub_category_name,
                  (select name from oem where id = p.oem) as oem_name
                  from product as p 
                  where is_active = 1 order by id desc";
                  $rowgetproduct = mysqli_query($con, $sqlgetproduct);

                  if (mysqli_num_rows($rowgetproduct) > 0)
                  {
                    $i = 0;
                    while ($i <= ($resgetproduct = mysqli_fetch_array($rowgetproduct)))
                    {
                ?>
                <tr>
                  <!-- <td><div class="table-value-wrapper"><?php echo $i+1; ?></div></td> -->
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetproduct['name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php echo $resgetproduct['oem_name']; ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper">
                      <?php 
                        $subcategoryname = "";
                        if(($resgetproduct['category'] == "2" || $resgetproduct['category'] == "1") && $resgetproduct['sub_category'] != "0")
                        {
                          $subcategoryname = " - ".$resgetproduct['sub_category_name'];
                        }
                        echo $resgetproduct['category_name'].$subcategoryname; 
                      ?>
                    </div>
                  </td>
                  <td>
                    <div class="table-value-wrapper align-center" >
                      <!-- <a class="edit-product-btn" id="edit-product-btn-<?php echo $i+1; ?>" data-product-id="<?php echo $resgetproduct['id']; ?>" href="javascript:void(0);"><i class="fas fa-edit"></i></a> -->
                      <a class="delete-product-btn" id="delete-product-btn-<?php echo $i+1; ?>" data-product-id="<?php echo $resgetproduct['id']; ?>" href="javascript:void(0);"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
                
                initializeDataTable("product-section-table");

            </script>
          </table>
