
function initializeDataTable(tableid)
{
	let table = new DataTable('#'+tableid, {
    	order: false,
    	autoWidth: false

	});
}

function getExpenses()
{
	$("#expense-list-section").html("");  
	$("#expense-list-section").hide();  
	$("#expense-list-section-loading").show();
    $("#expense-list-section").load("manageexpensesection.php",function(){    	
	  	$("#expense-list-section-loading").fadeOut(50,function(){
	   		$("#expense-list-section").fadeIn(100);  
		});
	});	
}

$(document).ready(function(){

	getExpenses();

	//Add Project Expense Submit
	$("form#ce-add-expense-form").on("submit",function(e){
		e.preventDefault();

		var formid = $(this).attr("id");
		var submitbtnid = "#"+formid+"-submit";
		var spinnerid = formid+"-spinner";
		var btnprocessing = "Adding...";
		var btndefault = "Add Expense";
		formid = "#"+formid;


		var formData = new FormData(this);

		if(formData.get("add-expense-type") == "0")
		{
			alert("Please select expense type");
		}
		else if(formData.get("add-expense-name") == "")
		{
			alert("Please enter expense name");
		}
		else if(formData.get("add-expense-amount") == "")
		{
			alert("Please enter expense amount");
		}
		else if(!(formData.get("add-expense-amount").toString().trim().match(/^\d+(\.\d+)?$/)))
		{
			alert("Please enter a valid expense amount ");
		}
		else
		{
			$(submitbtnid).attr('disabled','disabled');
          	$(submitbtnid).val(btnprocessing);  	
			$(submitbtnid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='30' width='30'>"); 	
          	
          	$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/add_cloud_expense.php",
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
	            	getExpenses();
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

	// Delete Project Expense Button
	$(document).on("click",".delete-expense-btn",function(){

		if(confirm("Are you sure you want to delete this expense ?"))
		{
			var eleid = $(this).attr("id");
			var exid = $(this).attr("data-expense-id");
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {expenseid : exid};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deleting...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_expense.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getExpenses();
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