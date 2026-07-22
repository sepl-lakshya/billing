
function initializeDataTable(tableid)
{
	let table = new DataTable('#'+tableid, {
    	order: false,
    	autoWidth: false

	});
}

function getProjects()
{
	$("#project-list-section").html("");  
	$("#project-list-section").hide();  
	$("#project-list-section-loading").show();
    $("#project-list-section").load("cloudprojectssection.php",function(){    	
	  	$("#project-list-section-loading").fadeOut(50,function(){
	   		$("#project-list-section").fadeIn(100);  
		});
	});	
}

function showAssignmentModal(pid)
{
	if(pid != "")
	{
		var dataArray = {projectid : pid};

		$("#assign-project-cont").load("cloudprojectassignsection.php",dataArray,function(){    		
		  	$("#assign-project-modal").modal({
			    escapeClose: false,
			    clickClose: false,
			});
		});
	}
}

$(document).ready(function(){

	getProjects();

	//Add Project Submit
	$("form#ce-create-project-form").on("submit",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Creating...";
		var btndefault = "Create Project";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("product-category") != "1")
		{
			alert("Error : Invalid Parameters");
		}
		else if(formData.get("project-name") == "")
		{
			alert("Please enter project name");
		}
		else if(formData.get("project-city") == "")
		{
			alert("Please enter project city");
		}
		else if(formData.get("project-state") == "0")
		{
			alert("Please select project state");
		}
		else if(formData.get("tender-ref-no") == "")
		{
			alert("Please enter tender number");
		}
		else if(formData.get("project-start-date") == "")
		{
			alert("Please select project start date");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/create_project.php",
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
	            	getProjects();
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


	// Project Delete Button
	$(document).on("click",".delete-project-btn",function(){

		if(confirm("All the data related to this project will be DELETED PERMANENTLY! Are you sure you want to continue ?"))
		{
			var eleid = $(this).attr("id");
			var pid = $(this).attr("data-project-id");
			var phash = $(this).attr("data-project-hash");
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {projectid : pid,projecthash : phash};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deleting...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_cloud_project.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getProjects();
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



	// Show Assign Project Model
	$(document).on("click",".manage-assignment-btn",function(){
		var pid = $(this).attr("data-project-id");
		showAssignmentModal(pid);
	});

	//Assign Project to User Submit
	$(document).on("submit","form#ce-assign-project-form",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Assigning...";
		var btndefault = "Assign";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("assign-project-id") == "")
		{
			alert("Error : Missing Parameters");
		}
		else if(formData.get("assign-user-id") == "0")
		{
			alert("Please select user");
		}
		else
		{
			var pid = formData.get("assign-project-id");
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/assign_cloud_project.php",
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
	            	showAssignmentModal(pid);
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

	// Unassign Project Button
	$(document).on("click",".unassign-project-btn",function(){

		if(confirm("Are you sure you want to unassign this user ?"))
		{
			var eleid = $(this).attr("id");
			var pid = $(this).attr("data-project-id");
			var uid = $(this).attr("data-user-id");
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {projectid : pid,userid : uid};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deleting...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/unassign_cloud_project.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	showAssignmentModal(pid);
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

});


	

