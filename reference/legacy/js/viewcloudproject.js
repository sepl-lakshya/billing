
function initializeDataTable(tableid)
{
	let table = new DataTable('#'+tableid, {
    	order: false,
    	autoWidth: false

	});
}

function getProjectProducts()
{
	var pid = $("#project-id-common").val();

	$("#project-product-list-section").html("");  
	$("#project-product-list-section").hide();  
	$("#project-product-list-section-loading").show();
	dataArray = {projectid : pid};
    $("#project-product-list-section").load("viewprojectitemsection.php",dataArray,function(){    	
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
    $("#monthly-bill-status-section").load("viewmonthlybillstatussection.php",dataArray,function(){    	
	  	$("#monthly-bill-status-section-loading").fadeOut(50,function(){
	   		$("#monthly-bill-status-section").fadeIn(100);  
		});
	});	
}

function showMonthlyBillItemsModal(bid)
{
	var pid = $("#project-id-common").val();
	$("#monthly-bill-item-cont").html("");    
	dataArray = {billid : bid,projectid : pid};
	$.modal.close();
    $("#monthly-bill-item-cont").load("monthlybillitemsummarysection.php",dataArray,function(){    	
    	$("#monthly-bill-item-modal").modal({
		    // escapeClose: false,
		    // clickClose: false,
		});
	});	
}

function showMonthlyBillProgressModal(bid)
{
	var pid = $("#project-id-common").val();
	// $("#monthly-bill-progress-cont").html("");    
	dataArray = {billid : bid,projectid : pid};
    $("#monthly-bill-progress-cont").load("monthlybillprogresssection.php",dataArray,function(){    	
    	$("#monthly-bill-progress-modal").modal({
		    // escapeClose: false,
		    // clickClose: false,
		});
	});	
}

function getProjectDiscount()
{
	var pid = $("#project-id-common").val();  
	dataArray = {projectid : pid};
    $("#project-discount-table-section").html("");  
	$("#project-discount-table-section").hide();  
	$("#project-discount-table-section-loading").show();
	dataArray = {projectid : pid};
    $("#project-discount-table-section").load("cloudprojectdiscountsection.php",dataArray,function(){    	
	  	$("#project-discount-table-section-loading").fadeOut(50,function(){
	   		$("#project-discount-table-section").fadeIn(100);  
		});
	});		
}

// function getProjectExpense()
// {
// 	var pid = $("#project-id-common").val();  
// 	dataArray = {projectid : pid};
//     $("#project-expense-section").html("");  
// 	$("#project-expense-section").hide();  
// 	$("#project-expense-section-loading").show();
// 	dataArray = {projectid : pid};
//     $("#project-expense-section").load("cloudprojectexpensesection.php",dataArray,function(){    	
// 	  	$("#project-expense-section-loading").fadeOut(50,function(){
// 	   		$("#project-expense-section").fadeIn(100);  
// 		});
// 	});		
// }

function getProjectAdjustment()
{
	var pid = $("#project-id-common").val();  
	dataArray = {projectid : pid};
    $("#project-adjustment-section").html("");  
	$("#project-adjustment-section").hide();  
	$("#project-adjustment-section-loading").show();
	dataArray = {projectid : pid};
    $("#project-adjustment-section").load("cloudprojectadjustmentsection.php",dataArray,function(){    	
	  	$("#project-adjustment-section-loading").fadeOut(50,function(){
	   		$("#project-adjustment-section").fadeIn(100);  
		});
	});		
}

function getProjectCloudInward()
{
	var pid = $("#project-id-common").val();  
	dataArray = {projectid : pid};
    $("#project-cloud-inward-section").html("");  
	$("#project-cloud-inward-section").hide();  
	$("#project-cloud-inward-section-loading").show();
	dataArray = {projectid : pid};
    $("#project-cloud-inward-section").load("cloudinwardsection.php",dataArray,function(){    	
	  	$("#project-cloud-inward-section-loading").fadeOut(50,function(){
	   		$("#project-cloud-inward-section").fadeIn(100);  
		});
	});		
}

function getProjectCloudOutward()
{
	var pid = $("#project-id-common").val();  
	dataArray = {projectid : pid};
    $("#project-cloud-outward-section").html("");  
	$("#project-cloud-outward-section").hide();  
	$("#project-cloud-outward-section-loading").show();
	dataArray = {projectid : pid};
    $("#project-cloud-outward-section").load("cloudoutwardsection.php",dataArray,function(){    	
	  	$("#project-cloud-outward-section-loading").fadeOut(50,function(){
	   		$("#project-cloud-outward-section").fadeIn(100);  
		});
	});		
}

function getProjectInvoices()
{
	var pid = $("#project-id-common").val();  
	dataArray = {projectid : pid};
	$("#purchase-invoice-section").html("");  
	$("#purchase-invoice-section").hide();  
	$("#purchase-invoice-section-loading").show();
    $("#purchase-invoice-section").load("cloudprojectinvoicesection.php",dataArray,function(){    	
	  	$("#purchase-invoice-section-loading").fadeOut(50,function(){
	   		$("#purchase-invoice-section").fadeIn(100);  
		});
	});	
}

function getProjectDebitNote()
{
	var pid = $("#project-id-common").val();  
	dataArray = {projectid : pid};
	$("#debit-note-section").html("");  
	$("#debit-note-section").hide();  
	$("#debit-note-section-loading").show();
    $("#debit-note-section").load("cloudprojectdebitnotesection.php",dataArray,function(){    	
	  	$("#debit-note-section-loading").fadeOut(50,function(){
	   		$("#debit-note-section").fadeIn(100);  
		});
	});	
}



function getProjectCreditNote()
{
	var pid = $("#project-id-common").val();  
	dataArray = {projectid : pid};
	$("#credit-note-section").html("");  
	$("#credit-note-section").hide();  
	$("#credit-note-section-loading").show();
    $("#credit-note-section").load("cloudprojectcreditnotesection.php",dataArray,function(){    	
	  	$("#credit-note-section-loading").fadeOut(50,function(){
	   		$("#credit-note-section").fadeIn(100);  
		});
	});	
}

function getProjectAttachment()
{
	var pid = $("#project-id-common").val();  
	dataArray = {projectid : pid};
	$("#attachment-section").html("");  
	$("#attachment-section").hide();  
	$("#attachment-section-loading").show();
    $("#attachment-section").load("cloudprojectattachmentsection.php",dataArray,function(){    	
	  	$("#attachment-section-loading").fadeOut(50,function(){
	   		$("#attachment-section").fadeIn(100);  
		});
	});	
}
function getPurchaseBillInvoices(bid)
{
	var pid = $("#project-id-common").val();  
	dataArray = {projectid : pid,billid : bid};
	$("#purchase-bill-invoice-table-section").html("");  
	$("#purchase-bill-invoice-table-section").hide();  
	$("#purchase-bill-invoice-table-section-loading").show();
    $("#purchase-bill-invoice-table-section").load("purchasebillinvoicesection.php",dataArray,function(){    	
	  	$("#purchase-bill-invoice-table-section-loading").fadeOut(50,function(){
	   		$("#purchase-bill-invoice-table-section").fadeIn(100);  
		});
	});	
}

function getMonthlySummaryInvoices(bid)
{
	var pid = $("#project-id-common").val();  
	dataArray = {projectid : pid,billid : bid};
	$("#monthly-summary-invoice-table-section").html("");  
	$("#monthly-summary-invoice-table-section").hide();  
	$("#monthly-summary-invoice-table-section-loading").show();
    $("#monthly-summary-invoice-table-section").load("monthlysummaryinvoicesection.php",dataArray,function(){    	
	  	$("#monthly-summary-invoice-table-section-loading").fadeOut(50,function(){
	   		$("#monthly-summary-invoice-table-section").fadeIn(100);  
		});
	});	
}


function getUnassignedPurchaseInvoice()
{
	var pid = $("#project-id-common").val();  
	dataArray = {projectid:pid};

	$("#add-purchase-bill-invoice-id").attr("disabled","disabled");
	$("#add-purchase-bill-invoice-id").load("purchasebillinvoiceselectoption.php",dataArray,function(){	
		$("#add-purchase-bill-invoice-id").removeAttr("disabled");
        // $('#add-purchase-bill-invoice-id').select2({
        //     placeholder: "Select Invoice",
        // });

	});
}

function getUnassignedProjectPurchaseInvoice(pid)
{
	dataArray = {projectid:pid};

	$("#create-debit-note-invoice-id").attr("disabled","disabled");
	$("#create-debit-note-invoice-id").load("purchasebillinvoiceselectoption.php",dataArray,function(){	
		$("#create-debit-note-invoice-id").removeAttr("disabled");
        $('#create-debit-note-invoice-id').select2({
            placeholder: "Select Invoice",
        });

	});
}

function getProjectBillMonthList(eleid,yearval)
{
	var pid = $("#project-id-common").val();  
	dataArray = {year:yearval,projectid:pid};

	$("#"+eleid).load("cloudprojectmonthlistsection.php",dataArray,function(){	
	});
}

function toggleProductTableHeader(tableid)
{
	var tbody = $("#"+tableid+" tbody").html();
	var thead = "<tr><th>Product</th><th>Type</th><th>Distributor</th><th>Quoted Price</th><th>Unit Measure</th><th>Quantity</th><th>Model</th><th>Deployed Product</th><th>Deployed On</th><th>Status / <br>Deactivation <br> Date</th><th>Discovery Status</th><th>Purchase Header</th><th>Description</th><th>Resource ID</th><th></th></tr>";
	tbody = tbody.trim();
	if(tbody == "")
	{
		$("#"+tableid+" thead").html("");
		$("#ce-add-project-product-form-submit-btn-wrapper").hide();
	}
	else
	{
		$("#"+tableid+" thead").html(thead);
		$("#ce-add-project-product-form-submit-btn-wrapper").show();

	}
}

function togglePurchaseInvoiceTableHeader(tableid)
{
	var tbody = $("#"+tableid+" tbody").html();
	var thead = "<tr><th>Invoice Number</th><th>Invoice Amount</th><th>Action</th></tr>";
	tbody = tbody.trim();
	if(tbody == "")
	{
		$("#"+tableid+" thead").html("");
		// $("#ce-submit-purchase-price-btn-wrapper").hide();
	}
	else
	{
		$("#"+tableid+" thead").html(thead);
		// $("#ce-submit-purchase-price-btn-wrapper").show();
	}
}


$(document).ready(function(){

	getProjectProducts();	
	getMonthlyStatus();
	// getProjectExpense();
	getProjectCloudInward();
	getProjectCloudOutward();
	getProjectAdjustment();
	getProjectInvoices();
	getProjectDebitNote();
	getProjectCreditNote();
	getProjectAttachment();
	// getProjectDiscount();

	//Project Detail Edit BUtton
	$(document).on("click","#edit-project-detail-btn",function(){
		$("#show-project-detail-section").fadeOut(100,function(){
			$("#edit-project-detail-section").fadeIn(100);
		});
	});


	//Edit Project Detail Cancel BUtton
	$(document).on("click","#ce-edit-project-form-cancel",function(){
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
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/update_cloud_project.php",
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


	//Show/Hide Project Item Toggle
	$(document).on("click","#toggle-project-item-view-btn",function(){
		var status = $(this).attr("data-status");

		if(status == 0)
		{
			$("#project-items-section-main").show();
			$("#toggle-project-item-view-btn").attr("data-status",1);
			$("#toggle-project-item-view-btn").html("Hide Items");
		}

		if(status == 1)
		{
			$("#project-items-section-main").hide();
			$("#toggle-project-item-view-btn").attr("data-status",0);
			$("#toggle-project-item-view-btn").html("Show Items");
		}


	});

	//Show Add Project Item Modal
	$(document).on("click","#show-add-project-item-modal-btn",function(e){
		e.preventDefault();

		var pid = $("#project-id-common").val();
		var dataArray = {projectid:pid};

      	$("#add-project-item-form-cont").load("addcloudprojectitemformmodal.php",dataArray,function(){    	
		  	$("#add-project-item-form-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			});
		});
	});

	//Show Add Header Item Modal
	$(document).on("click",".show-add-header-item-modal-btn",function(e){
		e.preventDefault();

		var hid = $(this).attr("data-header-id");
		var pid = $("#project-id-common").val();
		var dataArray = {projectid:pid,headerid:hid};

      	$("#add-project-item-form-cont").load("addcloudprojectitemformmodal.php",dataArray,function(){    	
		  	$("#add-project-item-form-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			});
		});
	});

	// Add Field on Cloud Category Change
	$(document).on("change","#product-type",function(){
		
		var cid = $(this).val();
		dataArray = {catid:cid};

		if(cid == "0")
		{
			$("#product").html("<option value='0'>Select Product</option>");
		}
		else
		{
			$("#product").load("viewcloudprojectproductlistsection.php",dataArray,function(){});

		}

		

	});




	//Add Project Item to Table
	$(document).on("click","#ce-add-project-item-btn",function(e){
		e.preventDefault();

		var btnid = "ce-add-project-item-btn";
		var spinnerid = btnid+"-spinner";
		var btnprocessing = "Adding...";
		var btndefault = "Add Product";
		btnid = "#"+btnid;

		var producttype = $("#product-type").val();
		var producttypevalue = $("#product-type").find(":selected").text();
		var product = $("#product").val();
		var productvalue = $("#product").find(":selected").text();
		var distributor = $("#distributor").val();
		var distributorvalue = $("#distributor").find(":selected").text();
		var deploymentstart = $("#deployment-start").val();
		var deploymentend = $("#deployment-end").val();
		var unitmeasure = $("#unit-measure").val();
		var unitmeasurevalue = $("#unit-measure").find(":selected").text();
		var unitprice = $("#unit-price").val();
		var quantity = $("#quantity").val();
		var productdeployed = $("#product-deployed").val();
		var productdeployedvalue = $("#product-deployed").find(":selected").text();
		var productdiscovery = $("#product-discovery").val();
		var productdiscoveryvalue = $("#product-discovery").find(":selected").text();
		var productpurchaseheader = $("#product-purchase-header").val();
		var productpurchaseheadervalue = $("#product-purchase-header").find(":selected").text();
		var productdesc = $("#product-desc").val();
		var resourceid = $("#product-resource-id").val();
		var model = $("input:radio[name='product-model-radio']:checked").val();

		if(producttype == "0")
		{
			alert("Please select product type");
		}
		else if(product == "0")
		{
			alert("Please select product");
		}
		else if(distributor == "0")
		{
			alert("Please select distributor");
		}
		else if(deploymentstart == "")
		{
			alert("Please select product deployment date");
		}
		else if(unitmeasure == "0")
		{
			alert("Please select unit of measure");
		}
		else if(unitprice == "")
		{
			alert("Please enter unit price");
		}
		else if(!unitprice.toString().match(/^\-?\d+((\.)\d+)?$/))
		{
			alert("Please enter a valid unit price");
		}
		else if(quantity == "")
		{
			alert("Please enter product quantity");
		}
		else if(productdeployed == "0")
		{
			alert("Please select deployed product");
		}
		else if($("input:radio[name='product-model-radio']:checked").length == 0)
		{
			alert("Please select product model");
		}
		else if(productdiscovery == "0")
		{
			alert("Please select product discovery status");
		}
		else if(resourceid == "")
		{
			alert("Please enter resource ID");
		}
		// else if(productpurchaseheader == "0")
		// {
		// 	alert("Please select purchase header");
		// }
		else
		{
			$(btnid).attr('disabled','disabled');
          	$(btnid).val(btnprocessing);  	
			$(btnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
        
			var rowcount = $("#add-project-item-table-row-count").val();
			rowcount = parseInt(rowcount);
			rowcount++;

			if(deploymentend == "")
			{
				var deploymentendvalue = "Active";
			}
			else
			{
				var deploymentendvalue = deploymentend;
			}

			if(productpurchaseheader == "0")
			{
				productpurchaseheadervalue = "Not Selected";
			}

        	var rowval = "";
			rowval += "<tr id='add-project-item-row-"+rowcount+"' data-row-no='"+rowcount+"' style='display:none;'>";
			rowval += "<td><input type='hidden' id='product-"+rowcount+"' name='product-"+rowcount+"' value='"+product+"'><div class='table-value-wrapper' id='product-value-"+rowcount+"'>"+productvalue+"</div></td>";
			rowval += "<td><input type='hidden' id='product-type-"+rowcount+"' name='product-type-"+rowcount+"' value='"+producttype+"'><div class='table-value-wrapper' id='product-type-value-"+rowcount+"'>"+producttypevalue+"</div></td>";
			rowval += "<td><input type='hidden' id='distributor-"+rowcount+"' name='distributor-"+rowcount+"' value='"+distributor+"'><div class='table-value-wrapper' id='distributor-value-"+rowcount+"'>"+distributorvalue+"</div></td>";
			rowval += "<td><input type='hidden' id='unit-price-"+rowcount+"' name='unit-price-"+rowcount+"' value='"+unitprice+"'><div class='table-value-wrapper' id='unit-price-value-"+rowcount+"'>"+unitprice+"</div></td>";
			rowval += "<td><input type='hidden' id='unit-measure-"+rowcount+"' name='unit-measure-"+rowcount+"' value='"+unitmeasure+"'><div class='table-value-wrapper' id='unit-measure-value-"+rowcount+"'>"+unitmeasurevalue+"</div></td>";
			rowval += "<td><input type='hidden' id='quantity-"+rowcount+"' name='quantity-"+rowcount+"' value='"+quantity+"'><div class='table-value-wrapper' id='quantity-value-"+rowcount+"'>"+quantity+"</div></td>";
			rowval += "<td><input type='hidden' id='product-model-radio-"+rowcount+"' name='product-model-radio-"+rowcount+"' value='"+model+"'><div class='table-value-wrapper' id='product-model-radio-value-"+rowcount+"'>"+model+"</div></td>";
			rowval += "<td><input type='hidden' id='product-deployed-"+rowcount+"' name='product-deployed-"+rowcount+"' value='"+productdeployed+"'><div class='table-value-wrapper' id='product-deployed-value-"+rowcount+"'>"+productdeployedvalue+"</div></td>";
			rowval += "<td><input type='hidden' id='deployment-start-"+rowcount+"' name='deployment-start-"+rowcount+"' value='"+deploymentstart+"'><div class='table-value-wrapper' id='deployment-start-value-"+rowcount+"'>"+deploymentstart+"</div></td>";
			rowval += "<td><input type='hidden' id='deployment-end-"+rowcount+"' name='deployment-end-"+rowcount+"'  value='"+deploymentend+"'><div class='table-value-wrapper' id='deployment-end-value-"+rowcount+"'>"+deploymentendvalue+"</div></td>";
			rowval += "<td><input type='hidden' id='product-discovery-"+rowcount+"' name='product-discovery-"+rowcount+"'  value='"+productdiscovery+"'><div class='table-value-wrapper' id='product-discovery-value-"+rowcount+"'>"+productdiscoveryvalue+"</div></td>";
			rowval += "<td><input type='hidden' id='product-purchase-header-"+rowcount+"' name='product-purchase-header-"+rowcount+"'  value='"+productpurchaseheader+"'><div class='table-value-wrapper' id='product-purchase-header-value-"+rowcount+"'>"+productpurchaseheadervalue+"</div></td>";
			rowval += "<td><div class='table-value-wrapper' id='product-desc-"+rowcount+"'>"+productdesc+"</div></td>";
			rowval += "<td><div class='table-value-wrapper' id='product-resource-id-"+rowcount+"'>"+resourceid+"</div></td>";
			rowval += "<td><div class='table-value-wrapper'><a class='delete-project-item-row-btn' id='delete-project-item-row-btn-"+rowcount+"' data-row-no='"+rowcount+"' href='javascript:void(0);'><i class='fa fa-trash' aria-hidden='true'></i></a></div></td>";
			rowval += "</tr>";


			var rowid = "#add-project-item-row-"+rowcount;
			$("#add-cloud-project-item-table tbody").append(rowval);

			$(rowid).fadeIn(1000);

			$("#add-project-item-table-row-count").val(rowcount);
			
			toggleProductTableHeader("add-cloud-project-item-table");

			$('#add-project-item-form-modal').parent().scrollTop($('#add-project-item-form-modal')[0].scrollHeight);

			// $('#add-project-item-form-modal').parent().animate({ scrollTop: $('#add-project-item-form-modal').prop("scrollHeight")}, 1000);

			$(btnid).removeAttr('disabled');
        	$(btnid).val(btndefault);
        	$("#"+spinnerid).remove();
        	// $(formid).trigger("reset");

		}

	});

	// Remove Add Project Item Row
	$(document).on("click",".delete-project-item-row-btn",function(){

		var rowno = $(this).attr("data-row-no");
		$("#add-project-item-row-"+rowno).fadeOut(500,function(){
			$("#add-project-item-row-"+rowno).remove();
			toggleProductTableHeader("add-cloud-project-item-table");
		});

	});


	//Add Project Header Submit
	$("form#ce-add-project-header-form").on("submit",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Adding...";
		var btndefault = "Add Header";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("add-header-name") == "")
		{
			alert("Please enter header name");
		}
		else if(formData.get("add-header-quantity") == "")
		{
			alert("Please enter header quantity");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/add_cloud_project_header.php",
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
	              	$(submitbtnid).removeAttr('disabled');
	            	$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            	$(formid).trigger("reset");
	            	getProjectProducts();
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


	//Add Project Product Submit
	$(document).on("submit","form#ce-add-project-product-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Submitting...";
		var btndefault = "Submit";
		formid = "#"+formid;

		if($('#add-item-header-id').length == 0)
		{
			var hasheaderid = 0;
		}
		else
		{
			var hasheaderid = 1;
		}


		var formData = new FormData(this);

		if(formData.get("project-id") == "")
		{
			alert("Error : Missing Parameters");
		}
		else if(hasheaderid = 0 && formData.get("header-name") == "")
		{
			alert("Please enter header name.");
		}
		else
		{
			var productcount = 0;

			//Input Array
			var producttypearr = [];
			var productarr = [];
			var distributorarr = [];
			var deploymentstartarr = [];
			var deploymentendarr = [];
			var unitmeasurearr = [];
			var unitpricearr = [];
			var quantityarr = [];
			var productdeployedarr = [];
			var productdiscoveryarr = [];
			var purchaseheaderarr = [];
			var productdescarr = [];
			var resourceidarr = [];
			var modelarr = [];
			
			

			$('#add-cloud-project-item-table > tbody  > tr').each(function(index) { 

				var rowid = $(this).attr("id");
				var rowno = $(this).attr("data-row-no");
				
				var producttype = $("#product-type-"+rowno).val();
				var product = $("#product-"+rowno).val();
				var distributor = $("#distributor-"+rowno).val();
				var deploymentstart = $("#deployment-start-"+rowno).val();
				var deploymentend = $("#deployment-end-"+rowno).val();
				var unitmeasure = $("#unit-measure-"+rowno).val();
				var unitprice = $("#unit-price-"+rowno).val();
				var quantity = $("#quantity-"+rowno).val();
				var productdeployed = $("#product-deployed-"+rowno).val();
				var productdiscovery = $("#product-discovery-"+rowno).val();
				var purchaseheader = $("#product-purchase-header-"+rowno).val();
				var productdesc = $("#product-desc-"+rowno).html();
				var resourceid = $("#product-resource-id-"+rowno).html();
				var model = $("#product-model-radio-"+rowno).val();


				//Check for blank row
				if(!(producttype == "0" || product == "0" || distributor == "0" || deploymentstart == "" || unitmeasure == "" || unitprice == "" || quantity == "" || productdeployed == "0" || productdiscovery == "0" || model == "" || resourceid == ""))
				{			
					productcount++;
					producttypearr.push(producttype.replaceAll(",", " "));
					productarr.push(product.replaceAll(",", " "));
					distributorarr.push(distributor.replaceAll(",", " "));
					deploymentstartarr.push(deploymentstart.replaceAll(",", " "));
					deploymentendarr.push(deploymentend.replaceAll(",", " "));
					unitmeasurearr.push(unitmeasure.replaceAll(",", " "));
					unitpricearr.push(unitprice.replaceAll(",", " "));
					quantityarr.push(quantity.replaceAll(",", " "));
					productdeployedarr.push(productdeployed.replaceAll(",", " "));
					productdiscoveryarr.push(productdiscovery.replaceAll(",", " "));
					purchaseheaderarr.push(purchaseheader.replaceAll(",", " "));
					productdescarr.push(productdesc.replaceAll(",", " "));
					resourceidarr.push(resourceid.replaceAll(",", " "));
					modelarr.push(model.replaceAll(",", " "));					
				}
			});

			if(productcount > 0)
			{

				//Product Arrays to string
				var producttypearrval = producttypearr.toString();
				var productarrval = productarr.toString();
				var distributorarrval = distributorarr.toString();
				var deploymentstartarrval = deploymentstartarr.toString();
				var deploymentendarrval = deploymentendarr.toString();
				var unitmeasurearrval = unitmeasurearr.toString();
				var unitpricearrval = unitpricearr.toString();
				var quantityarrval = quantityarr.toString();
				var productdeployedarrval = productdeployedarr.toString();
				var productdiscoveryarrval = productdiscoveryarr.toString();
				var purchaseheaderarrval = purchaseheaderarr.toString();
				var productdescarrval = productdescarr.toString();
				var resourceidarrval = resourceidarr.toString();
				var modelarrval = modelarr.toString();
								
				//Append Array Strings
				formData.append('producttypearrpost', producttypearrval);
				formData.append('productarrpost', productarrval);
				formData.append('distributorarrpost', distributorarrval);
				formData.append('deploymentstartarrpost', deploymentstartarrval);
				formData.append('deploymentendarrpost', deploymentendarrval);
				formData.append('unitmeasurearrpost', unitmeasurearrval);
				formData.append('unitpricearrpost', unitpricearrval);
				formData.append('quantityarrpost', quantityarrval);
				formData.append('productdeployedarrpost', productdeployedarrval);
				formData.append('productdiscoveryarrpost', productdiscoveryarrval);
				formData.append('purchaseheaderarrpost', purchaseheaderarrval);
				formData.append('productdescarrpost', productdescarrval);
				formData.append('resourceidarrpost', resourceidarrval);
				formData.append('modelarrpost', modelarrval);

				$(submitbtnid).attr('disabled','disabled');
	          	$(submitbtnid).val(btnprocessing);  	
				$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
	          	
	          	$.ajax(
		        {
		        	type: "POST",
		            url: "php_action/add_cloud_project_product.php",
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
		              	$(submitbtnid).removeAttr('disabled');
		            	$(submitbtnid).val(btndefault);
		            	$("#"+spinnerid).remove();
		            	$(formid).trigger("reset");
		            	$.modal.close();
		            	getProjectProducts();
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
	    	else
	    	{
	    		alert("Please add a product");
	    	}
		}

	});

	//Show Edit Header Modal
	$(document).on("click",".show-edit-project-header-modal-btn",function(e){
		e.preventDefault();

		var hid = $(this).attr("data-project-header-id");
		var pid = $("#project-id-common").val();
		var dataArray = {projectid:pid,headerid:hid};

      	$("#edit-project-header-form-cont").load("editcloudprojectheaderformmodal.php",dataArray,function(){    	
		  	$("#edit-project-header-form-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			});
		});
	});


	//Edit Project Header Submit
	$(document).on("submit","form#ce-edit-project-header-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Submitting...";
		var btndefault = "Submit";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("edit-header-name") == "")
		{
			alert("Please enter header name");
		}
		else if(formData.get("edit-header-quantity") == "")
		{
			alert("Please enter header quantity");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/edit_cloud_project_header.php",
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
	              	$(submitbtnid).removeAttr('disabled');
	            	$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            	$(formid).trigger("reset");
	            	$.modal.close();
	            	getProjectProducts();
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

	// Delete Project Header Button
	$(document).on("click",".delete-project-header-btn",function(){

		if(confirm("Are you sure you want to delete this header and its products?"))
		{
			var eleid = $(this).attr("id");
			var hid = $(this).attr("data-project-header-id");
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {headerid : hid};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deleting...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_cloud_project_header.php",
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

	//Show Edit Project Item Modal
	$(document).on("click",".show-edit-project-item-modal-btn",function(e){
		e.preventDefault();

		var piid = $(this).attr("data-project-item-id");
		var pid = $("#project-id-common").val();
		var dataArray = {projectid:pid,projectitemid:piid};

      	$("#edit-project-item-form-cont").load("editcloudprojectitemformmodal.php",dataArray,function(){    	
		  	$("#edit-project-item-form-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			});
		});
	});

	//Edit Project Item Submit
	$(document).on("submit","form#ce-edit-project-item-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Submitting...";
		var btndefault = "Submit";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("edit-unit-measure") == "0")
		{
			alert("Please select unit of measure");
		}
		else if(formData.get("edit-unit-price") == "")
		{
			alert("Please enter quoted price");
		}
		else if(formData.get("edit-quantity") == "")
		{
			alert("Please enter quantity");
		}
		else if(formData.get("edit-product-deployed") == "0")
		{
			alert("Please select deployed product");
		}
		else if(formData.get("edit-product-discovery") == "0")
		{
			alert("Please select discovery status");
		}
		else if(formData.get("edit-product-resource-id") == "")
		{
			alert("Please enter resource ID");
		}
		// else if(formData.get("edit-product-purchase-header") == "0")
		// {
		// 	alert("Please select purchase header");
		// }
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/edit_cloud_project_item.php",
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
	              	$(submitbtnid).removeAttr('disabled');
	            	$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            	$(formid).trigger("reset");
	            	$.modal.close();
	            	getProjectProducts();
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
	            url: "php_action/delete_project_item.php",
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

	// $("#generate-sales-bill-btn").on('click',function(){

	// 	$('#portal-price-month-modal').modal({
	// 	    escapeClose: false,
	// 	    clickClose: false,
	// 	});

	// });

	$(".show-modal-btn").on('click',function(){
		var modalid = "#"+$(this).attr("data-modal-id");
		$(modalid).modal({
		    escapeClose: false,
		    clickClose: false,
		});
	});


	//Show Edit Header Modal
	$(document).on("click",".show-disable-project-item-modal-btn",function(e){
		e.preventDefault();

		var piid = $(this).attr("data-project-item-id");
		var dataArray = {projectitemid:piid};

      	$("#deactivate-project-item-form-cont").load("deactivatecloudprojectitemmodal.php",dataArray,function(){    	
		  	$("#deactivate-project-item-form-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			});
		});
	});

	//Deactivate Project Item Submit
	$(document).on("submit","form#ce-deactivate-project-item-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Submitting...";
		var btndefault = "Submit";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("deactivate-item-deployment-end") == "")
		{
			alert("Please select deployment end date");
		}
		else
		{
			if(confirm("Deactivate this item? You will not be able to undo this action."))
			{
				$(submitbtnid).attr('disabled','disabled');
	          	$(submitbtnid).val(btnprocessing);  	
				$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
	         

	          	$.ajax(
		        {
		        	type: "POST",
		            url: "php_action/disable_project_item.php",
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
		              	$(submitbtnid).removeAttr('disabled');
		            	$(submitbtnid).val(btndefault);
		            	$("#"+spinnerid).remove();
		            	$(formid).trigger("reset");
		            	$.modal.close();
		            	getProjectProducts();
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
		}

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

	// Get Portal Price Month
	$(document).on("change","#bill-year",function(){
		
		var year = $(this).val();
		getProjectBillMonthList("bill-month",year);

	});

	//Generate Bill Month Submit
	$("form#ce-generate-bill-month-form").on("submit",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Next";
		var btndefault = "Next";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("bill-year") == "0")
		{
			alert("Please select bill year");
		}
		else if(formData.get("bill-month") == "0")
		{
			alert("Please select bill month");
		}
		else
		{
			var billmonth = formData.get("bill-month");
			var billyear = formData.get("bill-year");
			var projectid = $("#project-id-common").val();
			var dataArray = {month : billmonth,year : billyear,pid : projectid};

          	$("#portal-bill-items-cont").load("portalbillitemsection.php",dataArray,function(){    	
			  	$("#portal-price-modal").modal({
				    escapeClose: false,
				    clickClose: false,
				});
			});
		}
	});


	//Show Cloud Portal Bill Section
	$(document).on("click",".show-cloud-portal-bill-section",function(e){
		e.preventDefault();

		var billmonth = $(this).attr("data-bill-month");
		var billyear = $(this).attr("data-bill-year");
		var projectid = $("#project-id-common").val();
		var dataArray = {month : billmonth,year : billyear,pid : projectid};

      	$("#portal-bill-items-cont").load("portalbillitemsection.php",dataArray,function(){    	
		  	$("#portal-price-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			});
		});
	});


	// Portal Bill Back Button
	$(document).on("click","#ce-portal-price-back-btn",function(){
		$("#portal-price-month-modal").modal({
		    escapeClose: false,
		    clickClose: false,
		});
	});

	// Submit Portal Price Modal Next Btn
	$(document).on("click","#ce-submit-portal-price-btn",function(){

		var eleid = $(this).attr("id");
		var spinnerid = eleid+"-spinner";
		eleid = "#"+eleid;			
		var projectItemErrorCheck = 1;

		var pricemonth = $("#portal-price-month").val();
		var priceyear = $("#portal-price-year").val();
		var portalridiscount = $("#portal-bill-ri-discount").val();
		var portalpaygdiscount = $("#portal-bill-payg-discount").val();
		var projectid = $("#project-id-common").val();


		if(portalridiscount == "")
		{
			alert("Please enter RI discount");
		}
		else if(!portalridiscount.toString().match(/^\-?\d+((\.)\d+)?$/))
		{
			alert("Please enter a valid RI discount");
		}
		else if(portalpaygdiscount == "")
		{
			alert("Please enter PAYG discount");
		}
		else if(!portalpaygdiscount.toString().match(/^\-?\d+((\.)\d+)?$/))
		{
			alert("Please enter a valid PAYG discount");
		}
		else
		{
			$(eleid).attr('disabled','disabled');
	  		$(eleid).val("Saving...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	




			//Input Array
			var projectitemidarr = [];
			var portalpricearr = [];




			$('#portal-price-table > tbody  > tr').each(function(index) { 
			   
			   		projectItemErrorCheck = 1;

					var rowid = $(this).attr("id");
					var rowno = $(this).attr("data-row-no");
					var projectitemid = $(this).attr("data-project-item-id");
					
					//Input Values
					var portalprice = $("#portal-price-value-"+rowno).val();
					

					if(portalprice == "")
					{
						alert("Please enter portal price for all items.");
						$("#portal-price-value-"+rowno).focus();
					}
					else if(!portalprice.toString().match(/^\-?\d+((\.)\d+)?$/))
					{
						alert("Please enter a valid portal price");
						$("#portal-price-value-"+rowno).focus();
					}
					else
					{
						projectItemErrorCheck = 0;
					}

					if(projectItemErrorCheck == 1)
					{
						$(eleid).removeAttr('disabled');
		              	$(eleid).val("Save & Next >");
						$("#"+spinnerid).remove();

						projectitemidarr = [];
						portalpricearr = [];
						return false;
					}
					else
					{
						projectitemidarr.push(projectitemid);
						portalpricearr.push(portalprice);
					}

			});

			if(projectItemErrorCheck == 0)
			{

				var projectitemidarrval = projectitemidarr.toString();
				var portalpricearrval = portalpricearr.toString();

				var dataArray = 
				{
					projectidpost : projectid,
					pricemonthpost : pricemonth,
					priceyearpost : priceyear,
					projectitemidarrvalpost : projectitemidarrval,
					portalpricearrvalpost : portalpricearrval,
					portalpaygdiscountpost : portalpaygdiscount,
					portalridiscountpost : portalridiscount
				};



				$.ajax(
		        {
		        	type: "POST",
		            url: "php_action/add_portal_price.php",
		            data: dataArray,
		            dataType: 'JSON',
		        })
		          //Success
		        .done(function(response) 
		        {
		            if(response.status == 1)
		            {  
		              	alert(response.msg);
		            	$(eleid).removeAttr('disabled');
		              	$(eleid).val("Save & Exit >");
						$("#"+spinnerid).remove();
						$.modal.close();
						getMonthlyStatus();

		            }
		            else
		            {
		              	alert(response.msg);
		              	$(eleid).removeAttr('disabled');
		              	$(eleid).val("Save & Exit >");
						$("#"+spinnerid).remove();
		            }               
		        })
		        //Error
		        .fail(function(response) 
		        {         
		            alert("Server Error : Invalid response from Server.");
		            $(eleid).removeAttr('disabled');
		            $(eleid).val("Save & Exit >");
					$("#"+spinnerid).remove();
		        });
			}
		}

	});

	// Get Sales Price Month
	$(document).on("change","#sales-bill-year",function(){
		
		var year = $(this).val();
		getProjectBillMonthList("sales-bill-month",year);

	});

	//Add Sales Price Month Submit
	$("form#ce-sales-price-month-form").on("submit",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Next";
		var btndefault = "Next";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("sales-bill-year") == "0")
		{
			alert("Please select bill year");
		}
		else if(formData.get("sales-bill-month") == "0")
		{
			alert("Please select bill month");
		}
		else
		{
			var billmonth = formData.get("sales-bill-month");
			var billyear = formData.get("sales-bill-year");
			var projectid = $("#project-id-common").val();
			var dataArray = {month : billmonth,year : billyear,pid : projectid};

          	$("#sales-bill-items-cont").load("salesbillitemsection.php",dataArray,function(){    	
			  	if($("#show-sales-modal-check").val() == "1")
				{	
				  	$("#sales-price-modal").modal({
					    escapeClose: false,
					    clickClose: false,
					});
				}

			});
		}

	});

	//Show Cloud Sales Bill Section
	$(document).on("click",".show-cloud-sale-bill-section",function(e){
		e.preventDefault();

		var billmonth = $(this).attr("data-bill-month");
		var billyear = $(this).attr("data-bill-year");
		var projectid = $("#project-id-common").val();
		var dataArray = {month : billmonth,year : billyear,pid : projectid};

      	$("#sales-bill-items-cont").load("salesbillitemsection.php",dataArray,function(){    	
		  	if($("#show-sales-modal-check").val() == "1")
			{	
			  	$("#sales-price-modal").modal({
				    escapeClose: false,
				    clickClose: false,
				});
			}

		});
	});

	//Show Cloud Portal Bill
	$(document).on("click",".show-cloud-sale-bill-section",function(e){
		e.preventDefault();

		var billmonth = $(this).attr("data-bill-month");
		var billyear = $(this).attr("data-bill-year");
		var projectid = $("#project-id-common").val();
		var dataArray = {month : billmonth,year : billyear,pid : projectid};

      	$("#sales-bill-items-cont").load("salesbillitemsection.php",dataArray,function(){    	
		  	if($("#show-sales-modal-check").val() == "1")
			{	
			  	$("#sales-price-modal").modal({
				    escapeClose: false,
				    clickClose: false,
				});
			}

		});
	});

	// Sales Bill Back Button
	$(document).on("click","#ce-sales-price-back-btn",function(){
		$("#sales-price-month-modal").modal({
		    escapeClose: false,
		    clickClose: false,
		});
	});

	//------------ Submit Sales Price Modal Next Btn
	$(document).on("click","#ce-submit-sales-price-btn",function(){

		var eleid = $(this).attr("id");
		var spinnerid = eleid+"-spinner";
		eleid = "#"+eleid;			

		$(eleid).attr('disabled','disabled');
  		$(eleid).html("Saving...");  	
		$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	


		var pricemonth = $("#sales-price-month").val();
		var priceyear = $("#sales-price-year").val();
		var projectid = $("#project-id-common").val();
		var billid = $("#sales-bill-id").val();


		//Input Array
		var projectitemidarr = [];
		var salespricearr = [];

		var projectheaderidarr = [];
		var headerpricearr = [];

		var projectHeaderErrorCheck = 1;

		$('#sales-price-table > tbody  > tr.cloud-header-row').each(function(index) { 
		   
		   		projectHeaderErrorCheck = 1;

				var rowid = $(this).attr("id");
				var rowno = $(this).attr("data-header-row-no");
				var projectheaderid = $(this).attr("data-project-header-id");
				
				//Input Values
				var headerprice = $("#sales-price-header-value-"+rowno).val();
				

				if(headerprice == "")
				{
					alert("Please enter price for all headers.");
					$("#sales-price-header-value-"+rowno).focus();
					$(eleid).removeAttr('disabled');
	              	$(eleid).val("Save & Exit");
					$("#"+spinnerid).remove();
				}
				else if(!headerprice.toString().match(/^\-?\d+((\.)\d+)?$/))
				{
					alert("Please enter a valid header price");
					$("#sales-price-header-value-"+rowno).focus();
					$(eleid).removeAttr('disabled');
	              	$(eleid).val("Save & Exit");
					$("#"+spinnerid).remove();
				}
				else
				{
					projectHeaderErrorCheck = 0;
				}

				if(projectHeaderErrorCheck == 1)
				{
					projectheaderidarr = [];
					headerpricearr = [];
					return false;
				}
				else
				{
					projectheaderidarr.push(projectheaderid);
					headerpricearr.push(headerprice);
				}

		});




		var projectItemErrorCheck = 1;

		$('#sales-price-table > tbody  > tr.sales-price-row').each(function(index) { 
		   
		   		projectItemErrorCheck = 1;

				var rowid = $(this).attr("id");
				var rowno = $(this).attr("data-row-no");
				var projectitemid = $(this).attr("data-project-item-id");
				
				//Input Values
				var salesprice = $("#sales-price-value-"+rowno).val();
				

				if(salesprice == "")
				{
					alert("Please enter sales price for all items.");
					$("#sales-price-value-"+rowno).focus();
					$(eleid).removeAttr('disabled');
	              	$(eleid).val("Save & Exit");
					$("#"+spinnerid).remove();
				}
				// else if(!salesprice.toString().match(/^\-?\d+((\.)\d+)?$/))
				// {
				// 	alert("Please enter a valid sales price");
				// 	$("#sales-price-value-"+rowno).focus();
				// 	$(eleid).removeAttr('disabled');
	   //            	$(eleid).val("Save & Exit");
				// 	$("#"+spinnerid).remove();
				// }
				else
				{
					projectItemErrorCheck = 0;
				}

				if(projectItemErrorCheck == 1)
				{
					projectitemidarr = [];
					salespricearr = [];
					return false;
				}
				else
				{
					projectitemidarr.push(projectitemid);
					salespricearr.push(salesprice);
				}

		});

		if(projectItemErrorCheck == 0 && projectHeaderErrorCheck == 0)
		{
			var projectitemidarrval = projectitemidarr.toString();
			var salespricearrval = salespricearr.toString();

			var projectheaderidarrval = projectheaderidarr.toString();
			var headerpricearrval = headerpricearr.toString();

			var dataArray = 
			{
				projectidpost : projectid,
				pricemonthpost : pricemonth,
				priceyearpost : priceyear,
				billidpost : billid,
				projectitemidarrvalpost : projectitemidarrval,
				salespricearrvalpost : salespricearrval,
				projectheaderidarrvalpost : projectheaderidarrval,
				headerpricearrvalpost : headerpricearrval
			};



			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/add_sales_price.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	$.modal.close();
	              	alert(response.msg);
	              	getMonthlyStatus();
	              	$(eleid).removeAttr('disabled');
	              	$(eleid).val("Save & Exit");
					$("#"+spinnerid).remove();
	            }
	            else
	            {
	              	alert(response.msg);
	              	$(eleid).removeAttr('disabled');
	              	$(eleid).val("Save & Exit");
					$("#"+spinnerid).remove();
	            }               
	        })
	        //Error
	        .fail(function(response) 
	        {         
	            alert("Server Error : Invalid response from Server.");
	            $(eleid).removeAttr('disabled');
	            $(eleid).val("Save & Exit");
				$("#"+spinnerid).remove();
	        });
		}

	});

	// Get Sales Price Month
	$(document).on("change","#purchase-bill-year",function(){
		
		var year = $(this).val();
		getProjectBillMonthList("purchase-bill-month",year);
	});

	//Add Purchase Price Month Submit
	$("form#ce-purchase-price-month-form").on("submit",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Next";
		var btndefault = "Next";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("purchase-bill-year") == "0")
		{
			alert("Please select bill year");
		}
		else if(formData.get("purchase-bill-month") == "0")
		{
			alert("Please select bill month");
		}
		else
		{
			var billmonth = formData.get("purchase-bill-month");
			var billyear = formData.get("purchase-bill-year");
			var projectid = $("#project-id-common").val();
			var dataArray = {month : billmonth,year : billyear,pid : projectid};

          	$("#change-purchase-header-form-modal").load("purchasebillitemheadersection.php",dataArray,function(){    	
			  	if($("#show-purchase-modal-check").val() == "1")
				{	
				  	$("#change-purchase-header-form-modal").modal({
					    escapeClose: false,
					    clickClose: false,
					    closeExisting: false
					});
				}

			});
		}

	});

	//Show Cloud Purchase Bill Section
	$(document).on("click",".show-cloud-purchase-bill-section",function(e){
		e.preventDefault();

		var billmonth = $(this).attr("data-bill-month");
		var billyear = $(this).attr("data-bill-year");
		var projectid = $("#project-id-common").val();
		var dataArray = {month : billmonth,year : billyear,pid : projectid};

      	$("#purchase-bill-items-cont").load("purchasebillitemsection.php",dataArray,function(){    	
		  	if($("#show-purchase-modal-check").val() == "1")
			{	
			  	$("#purchase-price-modal").modal({
				    escapeClose: false,
				    clickClose: false,
				});
			}

		});
	});

	// Portal Bill Back Button
	$(document).on("click","#ce-purchase-price-back-btn",function(){
		$("#purchase-price-month-modal").modal({
		    escapeClose: false,
		    clickClose: false,
		});
	});

	//------------ Submit Change Purchase Header Form Next Btn
	$(document).on("click","#ce-submit-purchase-header-change-btn",function(){

		var eleid = $(this).attr("id");
		var spinnerid = eleid+"-spinner";
		eleid = "#"+eleid;			

		$(eleid).attr('disabled','disabled');
  		$(eleid).html("Saving...");  	
		$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	


		var pricemonth = $("#change-header-purchase-price-month").val();
		var priceyear = $("#change-header-purchase-price-year").val();
		var projectid = $("#project-id-common").val();
		var billid = $("#change-header-purchase-bill-id").val();


		//Input Array
		var projectitemidarr = [];
		var purchaseheaderidarr = [];

		var purchaseHeaderErrorCheck = 1;

		$('#purchase-header-table > tbody  > tr.change-purchase-header-row').each(function(index) { 
		   
		   		purchaseHeaderErrorCheck = 1;

				var rowid = $(this).attr("id");
				var rowno = $(this).attr("data-row-no");
				var purchaseitemid = $(this).attr("data-project-item-id");
				
				//Input Values
				var headerid = $("#change-purchase-header-id-"+rowno).val();
				
				
				if(headerid == "0")
				{
					alert("Please select purchase header for all items.");
					$("#change-purchase-header-id-"+rowno).focus();
					$("#change-purchase-header-id-"+rowno).css("background","#E9967A");
					$(eleid).removeAttr('disabled');
	              	$(eleid).val("Save & Next");
					$("#"+spinnerid).remove();
				}
				// else if(!headerprice.toString().match(/^\-?\d+((\.)\d+)?$/))
				// {
				// 	alert("Please enter a valid header price");
				// 	$("#purchase-header-value-"+rowno).focus();
				// 	$(eleid).removeAttr('disabled');
	   			//	$(eleid).val("Save & Next");
				// 	$("#"+spinnerid).remove();
				// }
				else
				{
					purchaseHeaderErrorCheck = 0;
				}

				if(purchaseHeaderErrorCheck == 1)
				{
					projectitemidarr = [];
					purchaseheaderidarr = [];
					return false;
				}
				else
				{
					projectitemidarr.push(purchaseitemid);
					purchaseheaderidarr.push(headerid);
				}

		});


		if(purchaseHeaderErrorCheck == 0)
		{
			var projectitemidarrval = projectitemidarr.toString();
			var purchaseheaderidarrval = purchaseheaderidarr.toString();

			var dataArray = 
			{
				projectidpost : projectid,
				pricemonthpost : pricemonth,
				priceyearpost : priceyear,
				billidpost : billid,
				projectitemidarrvalpost : projectitemidarrval,
				purchaseheaderidarrvalpost : purchaseheaderidarrval
			};

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/change_purchase_item_header.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	// $.modal.close();
	              	// alert(response.msg);
	              	var dataArray2 = {month : pricemonth,year : priceyear,pid : projectid};

		          	$("#purchase-bill-items-cont").load("purchasebillitemsection.php",dataArray2,function(){    	
					  	if($("#show-purchase-modal-check").val() == "1")
						{	
						  	$("#purchase-price-modal").modal({
							    escapeClose: false,
							    clickClose: false,
							});
						}

					});
	              	$(eleid).removeAttr('disabled');
	              	$(eleid).val("Save & Exit");
					$("#"+spinnerid).remove();
	            }
	            else
	            {
	              	alert(response.msg);
	              	$(eleid).removeAttr('disabled');
	              	$(eleid).val("Save & Exit");
					$("#"+spinnerid).remove();
	            }               
	        })
	        //Error
	        .fail(function(response) 
	        {         
	            alert("Server Error : Invalid response from Server.");
	            $(eleid).removeAttr('disabled');
	            $(eleid).val("Save & Exit");
				$("#"+spinnerid).remove();
	        });
		}
	});

	//Add Purchase Bill Invoice Submit
	$(document).on("submit","form#ce-add-purchase-bill-invoice-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Adding...";
		var btndefault = "Add Invoice";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("add-purchase-bill-invoice-bill-id") == "")
		{
			alert("Error : Missing Parameters");
		}
		else if(formData.get("add-purchase-bill-invoice-id") == "0" || formData.get("add-purchase-bill-invoice-id") == "")
		{
			alert("Please select invoice");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/add_purchase_bill_invoice.php",
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
	              	$(submitbtnid).removeAttr('disabled');
	            	$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            	$(formid).trigger("reset");
	            	getUnassignedPurchaseInvoice();
					var billid = formData.get("add-purchase-bill-invoice-bill-id");
	            	getPurchaseBillInvoices(billid);
	            	getProjectInvoices();
	            	getMonthlyStatus();
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

	// Delete Purchase Bill Invoice Button
	$(document).on("click",".delete-purchase-bill-invoice-btn",function(){

		if(confirm("All debit & credit notes related to this invoice will also be deleted. Are you sure you want to remove this invoice ?"))
		{
			var eleid = $(this).attr("id");
			var bid = $(this).attr("data-bill-id");
			var inid = $(this).attr("data-invoice-id");
			var pid = $("#project-id-common").val();
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {billid : bid,invoiceid : inid,projectid : pid};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deactivating...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_purchase_bill_invoice.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getUnassignedPurchaseInvoice();
	            	getPurchaseBillInvoices(bid);
	            	getProjectInvoices();
	            	getProjectDebitNote();
	            	getProjectCreditNote();
	            	getMonthlyStatus();
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

	//------------ Submit Purchase Price Modal Next Btn
	$(document).on("click","#ce-submit-purchase-price-btn",function(){

		var eleid = $(this).attr("id");
		var spinnerid = eleid+"-spinner";
		eleid = "#"+eleid;			

		$(eleid).attr('disabled','disabled');
  		$(eleid).html("Saving...");  	
		$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	


		var pricemonth = $("#purchase-price-month").val();
		var priceyear = $("#purchase-price-year").val();
		var projectid = $("#project-id-common").val();
		var billid = $("#purchase-bill-id").val();


		//Input Array
		var projectitemidarr = [];
		var purchasepricearr = [];

		var purchaseheaderidarr = [];
		var headerpricearr = [];

		var purchaseInvoiceCount = $("#add-purchase-bill-invoice-table-row-count").val();
		purchaseInvoiceCount = parseInt(purchaseInvoiceCount);
		
		if(purchaseInvoiceCount > 0)
		{ 

			// Purchase Header Values
			var purchaseHeaderErrorCheck = 1;

			$('#purchase-price-table > tbody  > tr.cloud-purchase-header-row').each(function(index) { 
			   
		   		purchaseHeaderErrorCheck = 1;

				var rowid = $(this).attr("id");
				var rowno = $(this).attr("data-header-row-no");
				var purchaseheaderid = $(this).attr("data-purchase-header-id");
				
				//Input Values
				var headerprice = $("#purchase-header-value-"+rowno).val();
				

				if(headerprice == "")
				{
					alert("Please enter price for all headers.");
					$("#purchase-header-value-"+rowno).focus();
					$(eleid).removeAttr('disabled');
	              	$(eleid).val("Save & Exit");
					$("#"+spinnerid).remove();
				}
				else if(!headerprice.toString().match(/^\-?\d+((\.)\d+)?$/))
				{
					alert("Please enter a valid header price");
					$("#purchase-header-value-"+rowno).focus();
					$(eleid).removeAttr('disabled');
	              	$(eleid).val("Save & Exit");
					$("#"+spinnerid).remove();
				}
				else
				{
					purchaseHeaderErrorCheck = 0;
				}

				if(purchaseHeaderErrorCheck == 1)
				{
					purchaseheaderidarr = [];
					headerpricearr = [];
					return false;
				}
				else
				{
					purchaseheaderidarr.push(purchaseheaderid);
					headerpricearr.push(headerprice);
				}

			});



			var projectItemErrorCheck = 1;

			$('#purchase-price-table > tbody  > tr.purchase-price-row').each(function(index) { 
			   
			   		projectItemErrorCheck = 1;

					var rowid = $(this).attr("id");
					var rowno = $(this).attr("data-row-no");
					var projectitemid = $(this).attr("data-project-item-id");
					
					//Input Values
					var purchaseprice = $("#purchase-price-value-"+rowno).val();
					
					if(purchaseprice == "")
					{
						alert("Please enter purchase price for all items.");
						$("#purchase-price-value-"+rowno).focus();
						$(eleid).removeAttr('disabled');
		              	$(eleid).val("Save & Exit");
						$("#"+spinnerid).remove();
					}
					// else if(!purchaseprice.match(/^\-?\d+((\.)\d+)?$/))
					// {
					// 	alert("Please enter a valid purchase price");
					// 	$("#purchase-price-value-"+rowno).focus();
					// 	$(eleid).removeAttr('disabled');
		   // 				$(eleid).val("Save & Exit");
					// 	$("#"+spinnerid).remove();
					// }
					else
					{
						projectItemErrorCheck = 0;
					}

					if(projectItemErrorCheck == 1)
					{
						projectitemidarr = [];
						purchasepricearr = [];
						return false;
					}
					else
					{
						projectitemidarr.push(projectitemid);
						purchasepricearr.push(purchaseprice);
					}

			});

			if(projectItemErrorCheck == 0 && purchaseHeaderErrorCheck == 0)
			{
				var projectitemidarrval = projectitemidarr.toString();
				var purchasepricearrval = purchasepricearr.toString();

				var purchaseheaderidarrval = purchaseheaderidarr.toString();
				var headerpricearrval = headerpricearr.toString();

				var dataArray = 
				{
					projectidpost : projectid,
					pricemonthpost : pricemonth,
					priceyearpost : priceyear,
					billidpost : billid,
					projectitemidarrvalpost : projectitemidarrval,
					purchasepricearrvalpost : purchasepricearrval,
					purchaseheaderidarrvalpost : purchaseheaderidarrval,
					headerpricearrvalpost : headerpricearrval
				};

				$.ajax(
		        {
		        	type: "POST",
		            url: "php_action/add_purchase_price.php",
		            data: dataArray,
		            dataType: 'JSON',
		        })
		          //Success
		        .done(function(response) 
		        {
		            if(response.status == 1)
		            {  
		            	// $.modal.close();
		              	// alert(response.msg);
		              	getMonthlyStatus();
		              	showMonthlyBillItemsModal(response.billid);
		              	// alert(response.billid);
		              	$(eleid).removeAttr('disabled');
		              	$(eleid).val("Save & Exit");
						$("#"+spinnerid).remove();
		            }
		            else
		            {
		              	alert(response.msg);
		              	$(eleid).removeAttr('disabled');
		              	$(eleid).val("Save & Exit");
						$("#"+spinnerid).remove();
		            }               
		        })
		        //Error
		        .fail(function(response) 
		        {         
		            alert("Server Error : Invalid response from Server.");
		            $(eleid).removeAttr('disabled');
		            $(eleid).val("Save & Exit");
					$("#"+spinnerid).remove();
		        });
			}
		}
		else
		{
			alert("Please add invoices for this purchase bill");
			$(eleid).removeAttr('disabled');
            $(eleid).val("Save & Exit");
			$("#"+spinnerid).remove();
		}

	});

	// Reset Purchase Price Button
	$(document).on("click",".reset-purchase-price-btn",function(){

		if(confirm("Are you sure you want to delete this months purchase data ?"))
		{
			var eleid = $(this).attr("id");
			var bid = $(this).attr("data-bill-id");
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {billid : bid};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deactivating...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/reset_purchase_price.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getMonthlyStatus();
	            	getProjectInvoices();
	            	getProjectDebitNote();
	            	getProjectCreditNote();
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


	// Delete Monthly Bill Button
	$(document).on("click",".delete-monthly-bill-btn",function(){

		if(confirm("Are you sure you want to delete this months data ?"))
		{
			var eleid = $(this).attr("id");
			var bid = $(this).attr("data-bill-id");
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {billid : bid};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deactivating...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_monthly_bill.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getMonthlyStatus();
	            	getProjectCloudInward();
	            	getProjectCloudOutward();
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


	// View Monthly Bill Button
	$(document).on("click",".view-monthly-bill-items-btn",function(){

		
		var bid = $(this).attr("data-bill-id");
		showMonthlyBillItemsModal(bid);
	});


	// View Monthly Bill Button
	$(document).on("click",".change-monthly-bill-progress-btn",function(){
		var bid = $(this).attr("data-bill-id");
		showMonthlyBillProgressModal(bid);

	});


	//Add Project Product Submit
	$(document).on("submit","form#ce-monthly-bill-progress-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Updating...";
		var btndefault = "Update";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("bill-progress") == "0")
		{
			alert("Please select progress");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/update_monthly_bill_progress.php",
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
	            	getMonthlyStatus();
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

	// Add Project Button Discount
	$(document).on("click","#ce-add-project-discount-btn",function(){
		$("#add-project-discount-modal").modal({
		    // escapeClose: false,
		    // clickClose: false,
		});
	});

	//Add Project Discount Submit
	$(document).on("submit","form#ce-add-cloud-project-discount-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Saving...";
		var btndefault = "Save";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("add-ri-discount") == "")
		{
			alert("Please enter RI discout");
		}
		else if(formData.get("add-payg-discount") == "")
		{
			alert("Please enter PAYG Discount");
		}
		else if(formData.get("add-credit-days") == "")
		{
			alert("Please enter credit days");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/add_cloud_project_discount.php",
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


	// Update Project Button Discount
	$(document).on("click","#ce-change-project-discount-btn",function(){
		var pid = $("#project-id-common").val();
		$("#update-project-discount-cont").html("");    
		dataArray = {projectid : pid};
	    $("#update-project-discount-cont").load("updateprojectdiscountmodalsection.php",dataArray,function(){    	
	    	$("#update-project-discount-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			});
		});	

	});


	// Get Change Discount Month
	$(document).on("change","#discount-year",function(){
		
		var yearval = $(this).val();
		var piid = $(this).attr("data-project-item-id");
		var pid = $(this).attr("data-project-id");
		dataArray = {year:yearval,projectid:pid};

		$("#discount-month").load("clouddiscountmonthsection.php",dataArray,function(){	
		});

	});

	//Change Project Discount Submit
	$(document).on("submit","form#ce-change-cloud-project-discount-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Updating...";
		var btndefault = "Update";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("change-payg-discount") == "")
		{
			alert("Please enter PAYG Discount");
		}
		else if(formData.get("change-ri-discount") == "")
		{
			alert("Please enter RI discount");
		}
		else if(formData.get("change-credit-days") == "")
		{
			alert("Please enter credit days");
		}
		else if(formData.get("discount-year") == "0")
		{
			alert("Please select year");
		}
		else if(formData.get("discount-month") == "0")
		{
			alert("Please select month");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/update_cloud_project_discount.php",
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
	            	getProjectDiscount();
	            	getMonthlyStatus();
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


	// Show Cloud Inward Modal Button
	$(document).on("click",".show-create-cloud-inward-form-modal-btn",function(e){
		e.preventDefault();

		var bid = $(this).attr("data-bill-id");
		var pid = $("#project-id-common").val();
		var dataArray = {projectid : pid, billid : bid};

      	$("#create-cloud-inward-form-cont").load("createcloudinwardmodal.php",dataArray,function(){
		  	$("#create-cloud-inward-form-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			});
		});
	});


	//Add Cloud Inward Submit
	$(document).on("submit","form#ce-create-cloud-inward-form",function(e){
		e.preventDefault();

		if(confirm("Are sure you want to create Cloud Inward for this month ?"))
		{
			var formid = $(this).attr("id");
			var submitbtnid = "#"+formid+"-submit";
			var spinnerid = formid+"-spinner";
			var btnprocessing = "Creating...";
			var btndefault = "Create CI";
			formid = "#"+formid;


			var formData = new FormData(this);

			$(submitbtnid).attr('disabled','disabled');
	      	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
	      	
	      	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/create_cloud_inward.php",
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
	              	$(submitbtnid).removeAttr('disabled');
	            	$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            	$(formid).trigger("reset");
	            	getMonthlyStatus();
	            	getProjectCloudInward();
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

	// Show Cancel Cloud Inward Modal Button
	$(document).on("click",".show-cancel-cloud-inward-modal-btn",function(e){
		e.preventDefault();

		var ciid = $(this).attr("data-cloud-inward-id");
		var pid = $("#project-id-common").val();
		var dataArray = {projectid : pid,cloudinwardid : ciid};

      	$("#cancel-cloud-inward-form-cont").load("cancelcloudinwardmodal.php",dataArray,function(){
		  	$("#cancel-cloud-inward-form-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			});
		});
	});


	// Cancel Cloud Inward Form Submit
	$(document).on("submit","#ce-cancel-cloud-inward-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Cancelling...";
		var btndefault = "Cancel CI";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("cancel-ci-reason") == "")
		{
			alert("Please enter reason for cancelling this CI");
		}
		else
		{
			if(confirm("Are you sure you want to cancel this CI ?"))
			{

				$(submitbtnid).attr('disabled','disabled');
		      	$(submitbtnid).val(btnprocessing);  	
				$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
		      	
		      	$.ajax(
		        {
		        	type: "POST",
		            url: "php_action/cancel_cloud_inward.php",
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
		              	$(submitbtnid).removeAttr('disabled');
		            	$(submitbtnid).val(btndefault);
		            	$("#"+spinnerid).remove();
		            	$(formid).trigger("reset");
		            	getMonthlyStatus();
		            	getProjectCloudInward();
		            	getProjectDebitNote();
		            	getProjectCreditNote();
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
		}
	});



	// Show Cloud Outward Modal Button
	$(document).on("click",".show-create-cloud-outward-form-modal-btn",function(e){
		e.preventDefault();

		var bid = $(this).attr("data-bill-id");
		var pid = $("#project-id-common").val();
		var dataArray = {projectid : pid, billid : bid};

      	$("#create-cloud-outward-form-cont").load("createcloudoutwardmodal.php",dataArray,function(){
		  	$("#create-cloud-outward-form-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			});
		});
	});


	//Add Cloud Outward Submit
	$(document).on("submit","form#ce-create-cloud-outward-form",function(e){
		e.preventDefault();

		if(confirm("Are sure you want to create Cloud Outward for this month ?"))
		{
			var formid = $(this).attr("id");
			var submitbtnid = "#"+formid+"-submit";
			var spinnerid = formid+"-spinner";
			var btnprocessing = "Creating...";
			var btndefault = "Create CO";
			formid = "#"+formid;


			var formData = new FormData(this);

			$(submitbtnid).attr('disabled','disabled');
	      	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
	      	
	      	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/create_cloud_outward.php",
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
	              	$(submitbtnid).removeAttr('disabled');
	            	$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            	$(formid).trigger("reset");
	            	getMonthlyStatus();
	            	getProjectCloudOutward();
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

	// Show Cancel Cloud Outward Modal Button
	$(document).on("click",".show-cancel-cloud-outward-modal-btn",function(e){
		e.preventDefault();

		var coid = $(this).attr("data-cloud-outward-id");
		var pid = $("#project-id-common").val();
		var dataArray = {projectid : pid,cloudoutwardid : coid};

      	$("#cancel-cloud-outward-form-cont").load("cancelcloudoutwardmodal.php",dataArray,function(){
		  	$("#cancel-cloud-outward-form-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			});
		});
	});


	// Cancel Cloud Outward Form Submit
	$(document).on("submit","#ce-cancel-cloud-outward-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Cancelling...";
		var btndefault = "Cancel CO";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("cancel-co-reason") == "")
		{
			alert("Please enter reason for cancelling this CO");
		}
		else
		{
			if(confirm("Are you sure you want to cancel this CO ?"))
			{

				$(submitbtnid).attr('disabled','disabled');
		      	$(submitbtnid).val(btnprocessing);  	
				$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
		      	
		      	$.ajax(
		        {
		        	type: "POST",
		            url: "php_action/cancel_cloud_outward.php",
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
		              	$(submitbtnid).removeAttr('disabled');
		            	$(submitbtnid).val(btndefault);
		            	$("#"+spinnerid).remove();
		            	$(formid).trigger("reset");
		            	getMonthlyStatus();
		            	getProjectCloudOutward();
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
		}
	});

	//----------- Project Invoices Section ---------------------

	//Show Add Invoice Modal
	$(document).on("click",".show-add-invoice-form-modal-btn",function(e){
		e.preventDefault();

		var pid = $("#project-id-common").val();  
		var ls = $(this).attr("data-load-select");  
		dataArray = {projectid : pid,loadselect : ls};

      	$("#add-invoice-form-cont").load("addinvoiceformmodal.php",dataArray,function(){    	
		  	$("#add-invoice-form-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			    closeExisting: false
			});
		});
	});

	//Add Invoice Submit
	$(document).on("submit","form#ce-add-invoice-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Adding...";
		var btndefault = "Add Invoice";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("add-invoice-project-id") == "" || formData.get("add-invoice-project-id") == "0")
		{
			alert("Please select project");
		}
		else if(formData.get("add-invoice-reference-no") == "")
		{
			alert("Please enter invoice reference number");
		}
		else if(formData.get("add-invoice-amount") == "")
		{
			alert("Please enter invoice total amount");
		}
		else if(!formData.get("add-invoice-amount").toString().match(/^\-?\d+((\.)\d+)?$/))
		{
			alert("Please enter a valid unit price");
		}
		else if(formData.get("add-invoice-gst-slab") == "0")
		{
			alert("Please select GST Slab");
		}
		else if($("input:radio[name='add-invoice-type']:checked").length == 0)
		{
			alert("Please select invoice type");
		}
		// else if(formData.get("add-invoice-date") == "")
		// {
		// 	alert("Please select invoice date");
		// }
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/add_invoice.php",
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
	            	getProjectInvoices();
	            	var loadselect = formData.get("add-invoice-load-select");
	            	if(loadselect == 1)
	            	{
	            		getUnassignedPurchaseInvoice();
	            	}
	              	$(submitbtnid).removeAttr('disabled');
	            	$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            	$(formid).trigger("reset");
	            	// $.modal.close();
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

	// Delete Invoice Button
	$(document).on("click",".delete-project-invoice-btn",function(){

		if(confirm("All debit & credit notes related to this invoice will also be deleted. Are you sure you want to delete this invoice ?"))
		{
			var eleid = $(this).attr("id");
			var bid = $(this).attr("data-bill-id");
			var inid = $(this).attr("data-invoice-id");
			var pid = $("#project-id-common").val();
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {billid : bid,invoiceid : inid,projectid : pid};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deactivating...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_project_invoice.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getProjectInvoices();
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

	$(document).on("click",".copy-hash-code-btn",function(){
		var val = $(this).attr("data-hash-value");
		$(this).children(".text-copied-msg-box").show();
		navigator.clipboard.writeText(val);
		$(this).children(".text-copied-msg-box").fadeOut(2000);
	});


	// ----------------- Debit Note ---------------------------


	//Show Create Debit Note Modal
	$(document).on("click",".show-create-debit-note-modal-btn",function(e){
		e.preventDefault();

		var dntype = $(this).attr("data-is-general-debit-note");
		var pid = $("#project-id-common").val();  
		if(dntype == 1)
		{
			dataArray = {projectid : pid};
		}
		else
		{
			var bid = $(this).attr("data-bill-id");
			var inid = $(this).attr("data-invoice-id");
			var diff = $("#calculated-actual-difference").val();  
			var hasndiff = $("#calculated-actual-difference").attr("data-has-negative-amount");  
			dataArray = {projectid : pid, billid : bid, invoiceid : inid,difference : diff,hasnegativediff : hasndiff};
		}
      	$("#create-debit-note-form-cont").load("createdebitnoteformmodal.php",dataArray,function(){    	
		  	$("#create-debit-note-form-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			    closeExisting: false
			});
		});
	});

	$(document).on("change","#create-debit-note-type",function(){

		var typeid = $(this).val();
		var projectid = $(this).attr("data-project-id");

		var htmlVal = "";

		if(typeid == 1)
		{
			htmlVal = "<input type='hidden' id='create-debit-note-invoice-id' name='create-debit-note-invoice-id' value='0'>";
			$("#create-debit-note-invoice-id-wrapper").hide();
			$("#create-debit-note-invoice-id-input-wrapper").html(htmlVal);
		}
		else if(typeid == 2)
		{
			htmlVal = "<select id='create-debit-note-invoice-id' name='create-debit-note-invoice-id'></select>";
			$("#create-debit-note-invoice-id-input-wrapper").html(htmlVal);
			$("#create-debit-note-invoice-id-wrapper").show();
			if(projectid != 0)
			{
				getUnassignedProjectPurchaseInvoice(projectid);
			}
		}
		else
		{
			$("#create-debit-note-invoice-id-wrapper").hide();
			$("#create-debit-note-invoice-id-input-wrapper").html(htmlVal);
		}

	});

	//Create Debit Note Submit
	$(document).on("submit","form#ce-create-debit-note-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Creating...";
		var btndefault = "Create Debit Note";
		formid = "#"+formid;


		var formData = new FormData(this);
		var billid = formData.get("create-debit-note-bill-id");

		dntype = formData.get("create-debit-note-type");

	 	if(formData.get("create-debit-note-type") == "" || formData.get("create-debit-note-type") == "0")
		{
			alert("Please select DN relation");
		}
		else if(formData.get("create-debit-note-amount") == "")
		{
			alert("Please enter DN amount");
		}
		else if(!formData.get("create-debit-note-amount").toString().match(/^\-?\d+((\.)\d+)?$/))
		{
			alert("Please enter a valid DN amount");
		}
		else if(formData.get("create-debit-note-project-id") == "" || formData.get("create-debit-note-project-id") == "0")
		{
			alert("Please select project");
		}
		else if(formData.get("create-debit-note-credit-type") == "0")
		{
			alert("Please select DN type");
		}
		else if(formData.get("create-debit-note-type") == "2" && formData.get("create-debit-note-invoice-id") == "0")
		{
			alert("Please please select invoice");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/create_debit_note.php",
	            data: formData,
	            dataType: 'JSON',
	            contentType: false,
	            processData: false
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getMonthlyStatus();
	            	getProjectDebitNote();
	            	getMonthlySummaryInvoices(billid);
	            	showMonthlyBillItemsModal(billid);

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

	// Delete Credit Note Button
	$(document).on("click",".delete-debit-note-btn",function(){

		if(confirm("All the credit notes related to this debit note will also be deleted. Are you sure you want to continue ?"))
		{
			var eleid = $(this).attr("id");
			var bid = $(this).attr("data-bill-id");
			var inid = $(this).attr("data-invoice-id");
			var dnid = $(this).attr("data-debit-note-id");
			var pid = $("#project-id-common").val();
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {billid : bid,invoiceid : inid,projectid : pid,debitnoteid : dnid};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deactivating...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_debit_note.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getProjectDebitNote();
	            	getProjectCreditNote();
	            	getMonthlyStatus();
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

	// ----------------- Credit Note ---------------------------


	//Show Create Credit Note Modal
	$(document).on("click",".show-create-credit-note-modal-btn",function(e){
		e.preventDefault();

		var bid = $(this).attr("data-bill-id");
		var inid = $(this).attr("data-invoice-id");
		var dnid = $(this).attr("data-debit-note-id");
		var pid = $("#project-id-common").val();  
		dataArray = {projectid : pid, billid : bid, invoiceid : inid, debitnoteid : dnid};

      	$("#create-credit-note-form-cont").load("createcreditnoteformmodal.php",dataArray,function(){    	
		  	$("#create-credit-note-form-modal").modal({
			    escapeClose: false,
			    clickClose: false
			});
		});
	});

	//Create Credit Note Submit
	$(document).on("submit","form#ce-create-credit-note-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Creating...";
		var btndefault = "Create Credit Note";
		formid = "#"+formid;


		var formData = new FormData(this);

	 	if(formData.get("create-credit-note-reference-no") == "")
		{
			alert("Please enter reference number");
		}
		else if(formData.get("create-credit-note-amount") == "")
		{
			alert("Please enter credit amount");
		}
		else if(!formData.get("create-credit-note-amount").toString().match(/^\-?\d+((\.)\d+)?$/))
		{
			alert("Please enter a valid credit amount");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/create_credit_note.php",
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
	            	getProjectCreditNote();
	            	getMonthlyStatus();
	            	getProjectDebitNote();
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

	// Delete Credit Note Button
	$(document).on("click",".delete-credit-note-btn",function(){

		if(confirm("Are you sure you want to delete this credit note ?"))
		{
			var eleid = $(this).attr("id");
			var bid = $(this).attr("data-bill-id");
			var inid = $(this).attr("data-invoice-id");
			var cnid = $(this).attr("data-credit-note-id");
			var pid = $("#project-id-common").val();
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {billid : bid,invoiceid : inid,projectid : pid,creditnoteid : cnid};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deactivating...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_credit_note.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getProjectCreditNote();
	            	getMonthlyStatus();
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

	$(document).on("click",".update-portal-bill-item-btn",function(){

		if(confirm("Purchase prices will be deleted. Are you sure you want to continue ?"))
		{
			var eleid = $(this).attr("id");
			var bid = $(this).attr("data-bill-id");
			var month = $(this).attr("data-month");
			var year = $(this).attr("data-year");
			var pid = $("#project-id-common").val();
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {billid : bid,projectid : pid,billmonth : month, billyear : year};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deactivating...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/update_bill_item.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getMonthlyStatus();
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


	// Show Invoice Cloud Inward Form Modal Button
	$(document).on("click",".show-create-invoice-cloud-inward-modal-btn",function(e){
		e.preventDefault();

		var bid = $(this).attr("data-bill-id");
		var inid = $(this).attr("data-invoice-id");
		var pid = $("#project-id-common").val();
		var dataArray = {projectid : pid, billid : bid,invoiceid : inid};

      	$("#create-invoice-cloud-inward-form-cont").load("createinvoicecloudinwardmodal.php",dataArray,function(){
		  	$("#create-invoice-cloud-inward-form-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			    closeExisting: false
			});
		});
	});


	//Add Invoice Cloud Inward Submit
	$(document).on("submit","form#ce-create-invoice-cloud-inward-form",function(e){
		e.preventDefault();

		if(confirm("Are sure you want to create Cloud Inward for this Invoice ?"))
		{
			var formid = $(this).attr("id");
			var submitbtnid = "#"+formid+"-submit";
			var spinnerid = formid+"-spinner";
			var btnprocessing = "Creating...";
			var btndefault = "Create CI";
			formid = "#"+formid;


			var formData = new FormData(this);

			var billid = formData.get("create-invoice-ci-bill-id");

			$(submitbtnid).attr('disabled','disabled');
	      	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
	      	
	      	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/create_invoice_cloud_inward.php",
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
	              	$(submitbtnid).removeAttr('disabled');
	            	$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            	$(formid).trigger("reset");
	            	getMonthlyStatus();
	            	getProjectCloudInward();
	            	getProjectDebitNote();
	            	getMonthlySummaryInvoices(billid);
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

	// Show Cancel Invoice Cloud Inward Modal Button
	$(document).on("click",".show-cancel-invoice-cloud-inward-modal-btn",function(e){
		e.preventDefault();

		var ciid = $(this).attr("data-cloud-inward-id");
		var pid = $("#project-id-common").val();
		var dataArray = {projectid : pid,cloudinwardid : ciid};

      	$("#cancel-invoice-cloud-inward-form-cont").load("cancelinvoicecloudinwardmodal.php",dataArray,function(){
		  	$("#cancel-invoice-cloud-inward-form-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			});
		});
	});


	// Cancel Invoice Cloud Inward Form Submit
	$(document).on("submit","#ce-cancel-invoice-cloud-inward-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Cancelling...";
		var btndefault = "Cancel CI";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("cancel-invoice-ci-reason") == "")
		{
			alert("Please enter reason for cancelling this CI");
		}
		else
		{
			if(confirm("Are you sure you want to cancel this CI ?"))
			{

				$(submitbtnid).attr('disabled','disabled');
		      	$(submitbtnid).val(btnprocessing);  	
				$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
		      	
		      	$.ajax(
		        {
		        	type: "POST",
		            url: "php_action/cancel_invoice_cloud_inward.php",
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
		              	$(submitbtnid).removeAttr('disabled');
		            	$(submitbtnid).val(btndefault);
		            	$("#"+spinnerid).remove();
		            	$(formid).trigger("reset");
		            	getMonthlyStatus();
		            	getProjectCloudInward();
		            	getProjectDebitNote();
		            	getProjectCreditNote();
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
		}
	});


	// Upload File Attachment
	$(document).on("submit","#ce-file-attachment-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Uploading...";
		var btndefault = "Upload";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("attachment-project-id") == "")
		{
			alert("Error : Missing Parameters");
		}
		else if(formData.get("file-title") == "")
		{
			alert("Please enter attachment title");
		}
		else if($("#attachment-file").val() == "")
		{
			alert("Please select a file(.pdf,.xlsx,.xls,.doc,.docx,.png,.jpg,.jpeg)");
		}
		else
		{
			

			$(submitbtnid).attr('disabled','disabled');
	      	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
	      	
	      	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/add_attachment_file.php",
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
	              	$(submitbtnid).removeAttr('disabled');
	            	$(submitbtnid).val(btndefault);
	            	$("#"+spinnerid).remove();
	            	$(formid).trigger("reset");
	            	getProjectAttachment();
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

	// Delete Attachment Button
	$(document).on("click",".delete-attachment-file-btn",function(){

		if(confirm("Are you sure you want to delete this attachment ?"))
		{
			var eleid = $(this).attr("id");
			var aid = $(this).attr("data-attachment-id");
			var pid = $("#project-id-common").val();
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {attachmentid : aid,projectid : pid};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deactivating...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_attachment_file.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getProjectAttachment();
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