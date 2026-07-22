function getProjectProducts()
{
	var pid = $("#project-id").val();

	$("#project-product-list-section").html("");  
	$("#project-product-list-section").hide();  
	$("#project-product-list-section-loading").show();
	dataArray = {projectid : pid};
    $("#project-product-list-section").load("viewlicenceprojectitemsection.php",dataArray,function(){    	
	  	$("#project-product-list-section-loading").fadeOut(50,function(){
	   		$("#project-product-list-section").fadeIn(100);  
		});
	});	
}

function showModal(modalid)
{
	$("#"+modalid).modal({
	    escapeClose: false,
	    clickClose: false,
	});
}

function getMonthlyStatus()
{
	var pid = $("#project-id-common").val();

	$("#monthly-bill-status-section").html("");  
	$("#monthly-bill-status-section").hide();  
	$("#monthly-bill-status-section-loading").show();
	dataArray = {projectid : pid};
    $("#monthly-bill-status-section").load("licenceviewmonthlybillstatussection.php",dataArray,function(){    	
	  	$("#monthly-bill-status-section-loading").fadeOut(50,function(){
	   		$("#monthly-bill-status-section").fadeIn(100);  
		});
	});	
}

function getCustomerSales()
{
	var pid = $("#project-id-common").val();

	$("#customer-sales-list-section").html("");  
	$("#customer-sales-list-section").hide();  
	$("#customer-sales-list-section-loading").show();
	dataArray = {projectid : pid};
    $("#customer-sales-list-section").load("licencecustomersalessection.php",dataArray,function(){    	
	  	$("#customer-sales-list-section-loading").fadeOut(50,function(){
	   		$("#customer-sales-list-section").fadeIn(100);  
		});
	});	
}

function getVendorPurchase()
{
	var pid = $("#project-id-common").val();

	$("#vendor-purchase-list-section").html("");  
	$("#vendor-purchase-list-section").hide();  
	$("#vendor-purchase-list-section-loading").show();
	dataArray = {projectid : pid};
    $("#vendor-purchase-list-section").load("licencevendorpurchasesection.php",dataArray,function(){    	
	  	$("#vendor-purchase-list-section-loading").fadeOut(50,function(){
	   		$("#vendor-purchase-list-section").fadeIn(100);  
		});
	});	
}

function getSubPurchase()
{
	var pid = $("#project-id-common").val();

	$("#sub-purchase-list-section").html("");  
	$("#sub-purchase-list-section").hide();  
	$("#sub-purchase-list-section-loading").show();
	dataArray = {projectid : pid};
    $("#sub-purchase-list-section").load("licencesubpurchasesection.php",dataArray,function(){    	
	  	$("#sub-purchase-list-section-loading").fadeOut(50,function(){
	   		$("#sub-purchase-list-section").fadeIn(100);  
		});
	});	
}

function showMonthlyBillItemsModal(bid)
{
	var pid = $("#project-id-common").val();
	$("#monthly-bill-item-cont").html("");    
	dataArray = {billid : bid,projectid : pid};
    $("#monthly-bill-item-cont").load("licencemonthlybillitemsummarysection.php",dataArray,function(){    	
    	$("#monthly-bill-item-modal").modal({
		    // escapeClose: false,
		    // clickClose: false,
		});
	});	
}

$(document).ready(function(){

	getProjectProducts();	
	// getMonthlyStatus();
	getCustomerSales();
	getVendorPurchase();
	getSubPurchase();


	//Project Detail Edit BUtton
	$(document).on("click","#edit-project-detail-btn",function(e){
		e.preventDefault();
		$("#show-project-detail-section").fadeOut(100,function(){
			$("#edit-project-detail-section").fadeIn(100);
		});
	});


	//Edit Project Detail Cancel BUtton
	$(document).on("click","#ce-edit-project-form-cancel",function(e){
		e.preventDefault();
		$("#edit-project-detail-section").fadeOut(100,function(){
			$("#show-project-detail-section").fadeIn(100);
		});
	});

	//Edit Project Submit
	$("form#ce-edit-project-form").on("submit",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Updating...";
		var btndefault = "Update";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("edit-project-city") == "")
		{
			alert("Please enter project city");
		}
		else if(formData.get("edit-project-state") == "0")
		{
			alert("Please select project state");
		}
		else if(formData.get("edit-tender-ref-no") == "")
		{
			alert("Please enter tender number");
		}
		else if(formData.get("edit-contract-year") == "0" && formData.get("edit-contract-month") == "0")
		{
			alert("Please select contract period");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/update_licence_project.php",
	            data: formData,
	            dataType: 'JSON',
	            contentType: false,
	            processData: false,
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	window.location.reload();
	              	$(submitbtnid).removeAttr('disabled');
	            	$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            	$(formid).trigger("reset");
	            }
	            else
	            {
	              	$(submitbtnid).removeAttr('disabled');
	              	alert(response.msg);
	           		$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            }               
	        })
	        //Error
	        .fail(function(response) 
	        {         
	            alert("Server Error : Invalid response from Server.");
	            $(submitbtnid).removeAttr('disabled');
	           	$(submitbtnid).val(btndefault);
	            $("#"+spinnerid).remove();
	        })
	        //Always
	        .always(function(response) 
	        {         
	        });
		}

	});


	// Add Field on Licence Category Change
	$(document).on("change","#licence-category",function(){
		
		var cid = $(this).val();
		dataArray = {catid:cid};

		$("#product").load("viewlicenceprojectproductlistsection.php",dataArray,function(){	
		});
		

	});


	//Add Project Product Submit
	$("form#ce-add-project-product-form").on("submit",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Adding...";
		var btndefault = "Add Product";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("licence-category") == "0")
		{
			alert("Please select licence category");
		}
		else if(formData.get("product") == "0")
		{
			alert("Please select product");
		}
		// else if(formData.get("distributor") == "0")
		// {
		// 	alert("Please select distributor");
		// }
		// else if(formData.get("unit-price") == "")
		// {
		// 	alert("Please enter unit price");
		// }
		// else if(!formData.get("unit-price").toString().match(/^\-?\d+((\.)\d+)?$/))
		// {
		// 	alert("Please enter a valid unit price");
		// }
		// else if(formData.get("quantity") == "")
		// {
		// 	alert("Please enter product quantity");
		// }
		// else if(formData.get("deployment-start") == "")
		// {
		// 	alert("Please select licence deployment date");
		// }
		// else if(formData.get("subscription-term") == "0")
		// {
		// 	alert("Please select subscription term");
		// }
		// else if(formData.get("billing-term") == "0")
		// {
		// 	alert("Please select billing term");
		// }
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/add_licence_project_product.php",
	            data: formData,
	            dataType: 'JSON',
	            contentType: false,
	            processData: false,
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getProjectProducts();	
	              	$(submitbtnid).removeAttr('disabled');
	            	$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            	$(formid).trigger("reset");
	            }
	            else
	            {
	              	$(submitbtnid).removeAttr('disabled');
	              	alert(response.msg);
	           		$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            }               
	        })
	        //Error
	        .fail(function(response) 
	        {         
	            alert("Server Error : Invalid response from Server.");
	            $(submitbtnid).removeAttr('disabled');
	           	$(submitbtnid).val(btndefault);
	            $("#"+spinnerid).remove();
	        })
	        //Always
	        .always(function(response) 
	        {         
	        });
		}

	});


	// Delete Project Item Button
	$(document).on("click",".delete-project-item-btn",function(){

		if(confirm("Are you sure you want to delete this item ?"))
		{
			var eleid = $(this).attr("id");
			var piid = $(this).attr("data-project-item-id");
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {productitemid : piid};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deleting...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_licence_project_item.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getProjectProducts();
	            }
	            else
	            {
	              	alert(response.msg);
	              	$(eleid).removeAttr('disabled');
	              	// $(eleid).html("Delete");
					$("#"+spinnerid).remove();
	            }               
	        })
	        //Error
	        .fail(function(response) 
	        {         
	            alert("Server Error : Invalid response from Server.");
	            $(eleid).removeAttr('disabled');
	            // $(eleid).html("Delete");
				$("#"+spinnerid).remove();
	        });

		}

	});


	$(document).on("click",".create-project-sales-btn",function(){

		
		var piid = $(this).attr("data-project-item-id");
		$("#create-sale-project-item-id").val(piid);
		$("#create-project-sale-modal").modal({
		    escapeClose: false,
		    clickClose: false,
		});
	});
	
	//Create Project sales
	$(document).on("click",".create-project-sales-btn",function(){

		
		var projectitemid = $(this).attr("data-project-item-id");
		var projectid = $("#project-id-common").val();

		dataArray = {pid : projectid,piid : projectitemid};
	    $("#create-sale-form-cont").load("createprojectsalesection.php",dataArray,function(){    	
			$("#create-sale-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			});
		});	

	});

	//Create Sales Submit
	$(document).on("submit","form#ce-create-sales-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Creating...";
		var btndefault = "Create";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("sale-unit-price") == "")
		{
			alert("Please enter unit price");
		}
		else if(!formData.get("sale-unit-price").toString().match(/^\-?\d+((\.)\d+)?$/))
		{
			alert("Please enter a valid unit price");
		}
		else if(formData.get("sale-quantity") == "")
		{
			alert("Please enter product quantity");
		}
		else if(formData.get("deployment-start") == "")
		{
			alert("Please select licence deployment date");
		}
		else if(formData.get("sale-subscription-term") == "0")
		{
			alert("Please select subscription term");
		}
		else if(formData.get("sale-billing-term") == "0")
		{
			alert("Please select billing term");
		}
		else if(formData.get("contract-year") == "0" && formData.get("contract-month") == "0")
		{
			alert("Please select item contract period");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/create_licence_project_sales.php",
	            data: formData,
	            dataType: 'JSON',
	            contentType: false,
	            processData: false,
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getProjectProducts();
	            	getCustomerSales();
	              	$(submitbtnid).removeAttr('disabled');
	            	$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            	$(formid).trigger("reset");
	            	$.modal.close();
	            }
	            else
	            {
	              	$(submitbtnid).removeAttr('disabled');
	              	alert(response.msg);
	           		$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            }               
	        })
	        //Error
	        .fail(function(response) 
	        {         
	            alert("Server Error : Invalid response from Server.");
	            $(submitbtnid).removeAttr('disabled');
	           	$(submitbtnid).val(btndefault);
	            $("#"+spinnerid).remove();
	        })
	        //Always
	        .always(function(response) 
	        {         
	        });
		}

	});



	$(document).on("click",".create-project-purchase-btn",function(){

		
		var projectitemid = $(this).attr("data-project-item-id");
		var projectid = $("#project-id-common").val();

		dataArray = {pid : projectid,piid : projectitemid};
	    $("#create-purchase-form-cont").load("createprojectpurchasesection.php",dataArray,function(){    	
			$("#create-purchase-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			});
		});	

	});

	//Create Purchase Submit
	$(document).on("submit","form#ce-create-purchase-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Creating...";
		var btndefault = "Create";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("purchase-subscription-term") == "0")
		{
			alert("Please select subscription term");
		}
		else if(formData.get("purchase-billing-term") == "0")
		{
			alert("Please select billing term");
		}
		else if(formData.get("purchase-distributor") == "0")
		{
			alert("Please select distributor");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/create_licence_project_purchase.php",
	            data: formData,
	            dataType: 'JSON',
	            contentType: false,
	            processData: false,
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getProjectProducts();
	            	getVendorPurchase();
	              	$(submitbtnid).removeAttr('disabled');
	            	$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            	$(formid).trigger("reset");
	            	$.modal.close();
	            }
	            else
	            {
	              	$(submitbtnid).removeAttr('disabled');
	              	alert(response.msg);
	           		$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            }               
	        })
	        //Error
	        .fail(function(response) 
	        {         
	            alert("Server Error : Invalid response from Server.");
	            $(submitbtnid).removeAttr('disabled');
	           	$(submitbtnid).val(btndefault);
	            $("#"+spinnerid).remove();
	        })
	        //Always
	        .always(function(response) 
	        {         
	        });
		}

	});

	$(document).on("click",".create-project-sub-purchase-btn",function(){

		
		var projectitemid = $(this).attr("data-project-item-id");
		var projectid = $("#project-id-common").val();

		dataArray = {pid : projectid,piid : projectitemid};
	    $("#create-sub-purchase-form-cont").load("createprojectsubpurchasesection.php",dataArray,function(){    	
			$("#create-sub-purchase-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			});
		});	

	});

	// Get Sub Purchase Form Month
	$(document).on("change","#sub-purchase-year",function(){
		
		var yearval = $(this).val();
		var piid = $(this).attr("data-project-item-id");
		var pid = $(this).attr("data-project-id");
		dataArray = {year:yearval,projectid:pid,projectitemid:piid};

		$("#sub-purchase-month").load("licencesubprojectmonthlistsection.php",dataArray,function(){	
		});

	});


	//Create Sub Purchase Submit
	$(document).on("submit","form#ce-create-sub-purchase-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Submitting...";
		var btndefault = "Submit";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("sub-purchase-year") == "0")
		{
			alert("Please select year");
		}
		else if(formData.get("sub-purchase-month") == "0")
		{
			alert("Please select month");
		}
		else if(formData.get("sub-purchase-quantity") == "")
		{
			alert("Please enter quantity");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/create_licence_project_sub_purchase.php",
	            data: formData,
	            dataType: 'JSON',
	            contentType: false,
	            processData: false,
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getVendorPurchase();
	            	getSubPurchase();
	            	
	              	$(submitbtnid).removeAttr('disabled');
	            	$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            	$(formid).trigger("reset");
	            	$.modal.close();
	            }
	            else
	            {
	              	$(submitbtnid).removeAttr('disabled');
	              	alert(response.msg);
	           		$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            }               
	        })
	        //Error
	        .fail(function(response) 
	        {         
	            alert("Server Error : Invalid response from Server.");
	            $(submitbtnid).removeAttr('disabled');
	           	$(submitbtnid).val(btndefault);
	            $("#"+spinnerid).remove();
	        })
	        //Always
	        .always(function(response) 
	        {         
	        });
		}

	});


	$(document).on('click',".show-modal-btn",function(){
		var modalid = "#"+$(this).attr("data-modal-id");
		$(modalid).modal({
		    escapeClose: false,
		    clickClose: false,
		});
	});


	// Disable Project Item Button
	$(document).on("click",".disable-project-item-btn",function(){

		if(confirm("Deactivate this item? You will not be able to undo this action."))
		{
			var eleid = $(this).attr("id");
			var piid = $(this).attr("data-project-item-id");
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {productitemid : piid};

			$(eleid).attr('disabled','disabled');
          	$(eleid).html("Deactivating...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/disable_project_item.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getProjectProducts();
	            }
	            else
	            {
	              	alert(response.msg);
	              	$(eleid).removeAttr('disabled');
	              	$(eleid).html("Deactivate");
					$("#"+spinnerid).remove();
	            }               
	        })
	        //Error
	        .fail(function(response) 
	        {         
	            alert("Server Error : Invalid response from Server.");
	            $(eleid).removeAttr('disabled');
	            $(eleid).html("Deactivate");
				$("#"+spinnerid).remove();
	        });

		}

	});



	// Delete Monthly Bill Button
	$(document).on("click",".delete-licence-sub-purchase-btn",function(){

		if(confirm("Are you sure you want to delete this sub-purchase ?"))
		{
			var eleid = $(this).attr("id");
			var spid = $(this).attr("data-sub-purchase-id");
			var piid = $(this).attr("data-project-item-id");
			var pid = $("#project-id-common").val();
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {subpurchaseid : spid,projectid : pid,projectitemid : piid};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deactivating...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_licence_sub_purchase.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getVendorPurchase();
	            	getSubPurchase();
	            }
	            else
	            {
	              	alert(response.msg);
	              	$(eleid).removeAttr('disabled');
	              	// $(eleid).html("Deactivate");
					$("#"+spinnerid).remove();
	            }               
	        })
	        //Error
	        .fail(function(response) 
	        {         
	            alert("Server Error : Invalid response from Server.");
	            $(eleid).removeAttr('disabled');
	            // $(eleid).html("Deactivate");
				$("#"+spinnerid).remove();
	        });

		}

	});





});