
function initializeDataTable(tableid)
{
	let table = new DataTable('#'+tableid, {
    	order: false,
    	autoWidth: false

	});
}

function getDebitNotes()
{
	$("#debit-note-section").html("");  
	$("#debit-note-section").hide();  
	$("#debit-note-section-loading").show();
    $("#debit-note-section").load("managedebitnotesection.php",function(){    	
	  	$("#debit-note-section-loading").fadeOut(50,function(){
	   		$("#debit-note-section").fadeIn(100);  
		});
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

$(document).ready(function(){


	getDebitNotes();

	//Show Create Debit Note Modal
	$(document).on("click","#show-create-debit-note-modal-btn",function(e){
		e.preventDefault();

      	$("#create-debit-note-form-cont").load("createdebitnoteformmodal.php",function(){    	
		  	$("#create-debit-note-form-modal").modal({
			    escapeClose: false,
			    clickClose: false
			});
		});
	});

	$(document).on("change","#create-debit-note-project-id",function(){

		var projectid = $(this).val();
		getUnassignedProjectPurchaseInvoice(projectid);
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
		$("#create-debit-note-project-id").val("0");

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

	 	if(formData.get("create-debit-note-type") == "" || formData.get("create-debit-note-type") == "0")
		{
			alert("Please select DN type");
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
	            processData: false,
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getDebitNotes();

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

	// Create Credit Note

	//Show Create Credit Note Modal
	$(document).on("click",".show-create-credit-note-modal-btn",function(e){
		e.preventDefault();

		var bid = $(this).attr("data-bill-id");
		var inid = $(this).attr("data-invoice-id");
		var dnid = $(this).attr("data-debit-note-id");
		var pid = $(this).attr("data-project-id"); 
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
	            	getDebitNotes();
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


});