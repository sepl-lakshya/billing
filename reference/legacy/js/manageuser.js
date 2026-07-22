
function initializeDataTable(tableid)
{
	let table = new DataTable('#'+tableid, {
    	order: false,
    	autoWidth: false

	});
}

function getUsers()
{
	$("#user-list-section").html("");  
	$("#user-list-section").hide();  
	$("#user-list-section-loading").show();
    $("#user-list-section").load("manageusersection.php",function(){    	
	  	$("#user-list-section-loading").fadeOut(50,function(){
	   		$("#user-list-section").fadeIn(100);  
		});
	});	
}

$(document).ready(function(){

	getUsers();

	//Add User Submit
	$("form#ce-add-user-form").on("submit",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Adding...";
		var btndefault = "Add User";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("full-name") == "")
		{
			alert("Please enter full name");
		}
		else if(formData.get("user-email") == "")
		{
			alert("Please enter E-mail");
		}
		else if(formData.get("azure-object-id") == "")
		{
			alert("Please enter Azure Object ID of user");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/add_user.php",
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
	            	getUsers();
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


	// User Delete Button
	$(document).on("click",".delete-user-btn",function(){

		if(confirm("Are you sure you want to delete this User ?"))
		{
			var eleid = $(this).attr("id");
			var uid = $(this).attr("data-user-id");
			var aoid = $(this).attr("data-azure-object-id");
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {userid : uid,azureobjectid : aoid};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deleting...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_user.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getUsers();
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