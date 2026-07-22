
function initializeDataTable(tableid)
{
	let table = new DataTable('#'+tableid, {
    	order: false,
    	autoWidth: false

	});
}

function getPurchaseHeaders()
{
	$("#purchase-header-list-section").html("");  
	$("#purchase-header-list-section").hide();  
	$("#purchase-header-list-section-loading").show();
    $("#purchase-header-list-section").load("managepurchaseheadersection.php",function(){    	
	  	$("#purchase-header-list-section-loading").fadeOut(50,function(){
	   		$("#purchase-header-list-section").fadeIn(100);  
		});
	});	
}

$(document).ready(function(){

	getPurchaseHeaders();

	//Add Purchase Header Submit
	$("form#ce-add-purchase-header-form").on("submit",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Adding...";
		var btndefault = "Add Purchase Header";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("purchase-header-name") == "")
		{
			alert("Please enter purchase header name");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/add_purchase_header.php",
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
	            	getPurchaseHeaders();
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


	// OEM Delete Button
	$(document).on("click",".delete-purchase-header-btn",function(){

		if(confirm("Are you sure you want to delete this purchase header ?"))
		{
			var eleid = $(this).attr("id");
			var phid = $(this).attr("data-purchase-header-id");
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {purchaseheaderid : phid};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deleting...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_purchase_header.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getPurchaseHeaders();
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


	// Edit Product Button
	// $(document).on("click",".delete-product-btn",function(){

	// 	if(confirm("Are you sure you want to delete this product ?"))
	// 	{
	// 		var eleid = $(this).attr("id");
	// 		var pid = $(this).attr("data-product-id");
	// 		var spinnerid = eleid+"-spinner";
	// 		eleid = "#"+eleid;			
	// 		var dataArray = {productid : pid};

	// 		$(eleid).attr('disabled','disabled');
 //          	$(eleid).html("Deleting...");  	
	// 		$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

	// 		$.ajax(
	//         {
	//         	type: "POST",
	//             url: "php_action/delete_product.php",
	//             data: dataArray,
	//             dataType: 'JSON',
	//         })
	//           //Success
	//         .done(function(response) 
	//         {
	//             if(response.status == 1)
	//             {  
	//             	getProducts();
	//             }
	//             else
	//             {
	//               	alert(response.msg);
	//               	$(eleid).removeAttr('disabled');
	//               	$(eleid).html("Delete");
	// 				$("#"+spinnerid).remove();
	//             }               
	//         })
	//         //Error
	//         .fail(function(response) 
	//         {         
	//             alert("Server Error : Invalid response from Server.");
	//             $(eleid).removeAttr('disabled');
	//             $(eleid).html("Delete");
	// 			$("#"+spinnerid).remove();
	//         });

	// 	}

	// });

});