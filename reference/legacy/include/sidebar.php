<div class="app-sidebar">

      <a id="home-tab" href="<?php echo getDomain(); ?>" class="app-sidebar-link">
       <i class="fa-solid fa-house"></i> &nbsp;&nbsp;Home
      </a>
       <div class="app-sidebar-link" id="master-menu"> 
            <button class="dropbtn" id="app-master-record"> <i class="fa-solid fa-database"></i>&nbsp;&nbsp;  <i class="fa fa-caret-down" aria-hidden="true" style="float:right;"></i> Masters</button>
            <div id="master-menu-dropdown" class="dropdown-content">
                  <a id="product-tab" href="<?php echo getDomain()."/manageproducts".getPageExt(); ?>" class="app-sidebar-link">
                   <i class="fa fa-cubes" aria-hidden="true"></i> &nbsp;&nbsp;Products
                  </a>
                  <a id="distributor-tab" href="<?php echo getDomain()."/managedistributors".getPageExt(); ?>" class="app-sidebar-link">
                   <i class="fa fa-users" aria-hidden="true"></i> &nbsp;&nbsp;Distributors
                  </a>
                  <a id="oem-tab" href="<?php echo getDomain()."/manageoem".getPageExt(); ?>" class="app-sidebar-link">
                   <i class="fa fa-industry" aria-hidden="true"></i> &nbsp;&nbsp;OEM
                  </a>
                  <a id="purchase-header-tab" href="<?php echo getDomain()."/managepurchaseheader".getPageExt(); ?>" class="app-sidebar-link">
                   <i class="fas fa-heading"></i> &nbsp;&nbsp;Purchase Header
                  </a>
            </div>
      </div>
      <!-- <div class="app-sidebar-link" id="licence-main-menu"> 
            <button class="dropbtn" id="app-master-record"> <i class='fas fa-project-diagram'></i>&nbsp;&nbsp;  <i class="fa fa-caret-down" aria-hidden="true" style="float:right;"></i> Licence</button>
            <div id="master-menu-dropdown" class="dropdown-content">
                  <a id="licence-project-tab" href="<?php echo getDomain()."/licenceprojects".getPageExt(); ?>" class="app-sidebar-link">
                   <i class="fa fa-id-card" aria-hidden="true"></i> &nbsp;&nbsp;Licence Projects
                  </a>
            </div>
      </div> -->

      <div class="app-sidebar-link" id="cloud-main-menu"> 
            <button class="dropbtn" id="app-master-record"> <i class='fas fa-project-diagram'></i>&nbsp;&nbsp;  <i class="fa fa-caret-down" aria-hidden="true" style="float:right;"></i> Cloud</button>
            <div id="master-menu-dropdown" class="dropdown-content">
                  <a id="cloud-project-tab" href="<?php echo getDomain()."/cloudprojects".getPageExt(); ?>" class="app-sidebar-link">
                   <i class="fa fa-cloud" aria-hidden="true"></i> &nbsp;&nbsp;Cloud Projects
                  </a>
                  <a id="cloud-inward-tab" href="<?php echo getDomain()."/managecloudinward".getPageExt(); ?>" class="app-sidebar-link">
                   <i class="fa fa-cloud-download" aria-hidden="true"></i> &nbsp;&nbsp;Cloud Inward
                  </a>

                  <a id="cloud-outward-tab" href="<?php echo getDomain()."/managecloudoutward".getPageExt(); ?>" class="app-sidebar-link">
                   <i class="fa fa-cloud-upload" aria-hidden="true"></i> &nbsp;&nbsp;Cloud Outward
                  </a>
            </div>
      </div>
      
      <a id="debit-note-tab" href="<?php echo getDomain()."/managedebitnote".getPageExt(); ?>" class="app-sidebar-link">
       <i class="fa-solid fa-d"></i> &nbsp;&nbsp;Debit Notes
      </a>

      <a id="credit-note-tab" href="<?php echo getDomain()."/managecreditnote".getPageExt(); ?>" class="app-sidebar-link">
       <i class="fa-solid fa-c"></i> &nbsp;&nbsp;Credit Notes
      </a>
      
      <a id="invoice-tab" href="<?php echo getDomain()."/manageinvoice".getPageExt(); ?>" class="app-sidebar-link">
       <i class="fas fa-file-invoice"></i> &nbsp;&nbsp;Invoices
      </a>
        
      <?php
            if($_SESSION['user_type'] == 1)
            {
      ?>
      <a id="expense-tab" href="<?php echo getDomain()."/manageexpense".getPageExt(); ?>" class="app-sidebar-link">
       <i class="fa fa-money" aria-hidden="true"></i> &nbsp;&nbsp;Expenses
      </a>
      <?php } ?>
      
      <a id="project-tab" href="<?php echo getDomain()."/logout.php"; ?>" class="app-sidebar-link">
       <i class='fa fa-sign-out'></i> &nbsp;&nbsp;Logout
      </a>
</div>

<script type="text/javascript">
       
       $(document).ready(function(){


          $(".app-sidebar-link").click(function(){
            menuid = "#"+this.id;
            // $(".dropdown-content").removeClass("show");
            $(".dropbtn .fa-caret-down").css("transform","rotate(0deg)");

            if(!$(menuid+" .dropdown-content").hasClass("show"))
            {  
              $(menuid+" .dropdown-content").addClass("show");
              $(menuid+" button .fa-caret-down").css("transform","rotate(180deg)");
            }
            else
            {
              $(menuid+" .dropdown-content").removeClass("show");
              $(menuid+" button .fa-caret-down").css("transform","rotate(0deg)");

            } 
          });


         });
    </script>
