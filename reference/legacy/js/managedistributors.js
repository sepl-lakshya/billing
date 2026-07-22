
function initializeDataTable(tableid)
{
	let table = new DataTable('#'+tableid, {
    	order: false,
    	autoWidth: false

	});
}

function getDistributors()
{
	$("#distributor-list-section").html("");  
	$("#distributor-list-section").hide();  
	$("#distributor-list-section-loading").show();
    $("#distributor-list-section").load("managedistributorssection.php",function(){    	
	  	$("#distributor-list-section-loading").fadeOut(50,function(){
	   		$("#distributor-list-section").fadeIn(100);  
		});
	});	
}

$(document).ready(function(){

	getDistributors();

	//Add Distributor Submit
	$("form#ce-add-distributor-form").on("submit",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Adding...";
		var btndefault = "Add Distributor";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("distributor-name") == "")
		{
			alert("Please enter distributor name");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/add_distributor.php",
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
	            	getDistributors();
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


	// Distributor Delete Button
	$(document).on("click",".delete-distributor-btn",function(){

		if(confirm("Are you sure you want to delete this distributor ?"))
		{
			var eleid = $(this).attr("id");
			var did = $(this).attr("data-distributor-id");
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {distributorid : did};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deleting...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_distributor.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getDistributors();
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