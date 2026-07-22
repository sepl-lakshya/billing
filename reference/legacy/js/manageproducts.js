

function initializeDataTable(tableid)
{
	let table = new DataTable('#'+tableid, {
    	order: false,
    	autoWidth: false

	});
}


function getProducts()
{
	$("#product-list-section").html("");  
	$("#product-list-section").hide();  
	$("#product-list-section-loading").show();
    $("#product-list-section").load("manageproductsection.php",function(){    	
	  	$("#product-list-section-loading").fadeOut(50,function(){
	   		$("#product-list-section").fadeIn(100);  
		});
	});	
}

$(document).ready(function(){

	getProducts();

	//Add Product Submit
	$("form#ce-add-product-form").on("submit",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Adding...";
		var btndefault = "Add Product";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("product-oem") == "0")
		{
			alert("Please select product OEM");
		}
		else if(formData.get("product-category") == "0")
		{
			alert("Please select product category");
		}
		else if(formData.get("product-category") == "2" && formData.get("licence-category") == "0")
		{
			alert("Please select licence category");
		}
		else if(formData.get("product-category") == "1" && formData.get("cloud-category") == "0")
		{
			alert("Please select product type");
		}
		else if(formData.get("product-name") == "")
		{
			alert("Please enter product name");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/add_product.php",
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
	            	// window.location.href = getDomain()+"/managefirm"+getPageExt();
	            	getProducts();
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


	// Product Delete Button
	$(document).on("click",".delete-product-btn",function(){

		if(confirm("Are you sure you want to delete this product ?"))
		{
			var eleid = $(this).attr("id");
			var pid = $(this).attr("data-product-id");
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {productid : pid};

			$(eleid).attr('disabled','disabled');
          	$(eleid).html("Deleting...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_product.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getProducts();
	            }
	            else
	            {
	              	alert(response.msg);
	              	$(eleid).removeAttr('disabled');
	              	$(eleid).html("Delete");
					$("#"+spinnerid).remove();
	            }               
	        })
	        //Error
	        .fail(function(response) 
	        {         
	            alert("Server Error : Invalid response from Server.");
	            $(eleid).removeAttr('disabled');
	            $(eleid).html("Delete");
				$("#"+spinnerid).remove();
	        });

		}

	});

	// Product Category On Change To Licence

	$("#product-category").on("change",function(){

		var catval = $(this).val();

		if(catval == 1)
		{
			$(".product-sub-category-wrapper").hide();
			$("#cloud-category-wrapper").show();
		}
		else if(catval == 2)
		{
			$(".product-sub-category-wrapper").hide();
			$("#licence-category-wrapper").show();
		}	
		else
		{
			$(".product-sub-category-wrapper").hide();
			$("#sub-category-wrapper-blank").show();
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