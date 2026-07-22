
function initializeDataTable(tableid)
{
	let table = new DataTable('#'+tableid, {
    	order: false,
    	autoWidth: false

	});
}

function getOEMs()
{
	$("#oem-list-section").html("");  
	$("#oem-list-section").hide();  
	$("#oem-list-section-loading").show();
    $("#oem-list-section").load("manageoemsection.php",function(){    	
	  	$("#oem-list-section-loading").fadeOut(50,function(){
	   		$("#oem-list-section").fadeIn(100);  
		});
	});	
}

$(document).ready(function(){

	getOEMs();

	//Add OEM Submit
	$("form#ce-add-oem-form").on("submit",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Adding...";
		var btndefault = "Add OEM";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("oem-name") == "")
		{
			alert("Please enter OEM name");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/add_oem.php",
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
	            	getOEMs();
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
	$(document).on("click",".delete-oem-btn",function(){

		if(confirm("Are you sure you want to delete this OEM ?"))
		{
			var eleid = $(this).attr("id");
			var oid = $(this).attr("data-oem-id");
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {oemid : oid};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deleting...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_oem.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getOEMs();
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