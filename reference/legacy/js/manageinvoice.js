
function initializeDataTable(tableid)
{
	let table = new DataTable('#'+tableid, {
    	order: false,
    	autoWidth: false
	});
	// .on('search.dt', function() {

	// 	var totalamount = 0;
	// 	$('div.invoice-amount-excl-gst').each(function(index) { 
	// 		var amount = $(this).html();
	// 		amount = parseFloat(amount);
	// 		totalamount += amount;
	// 	});
	// 	alert(totalamount);

	// });

}

function getInvoices()
{
	$("#purchase-invoice-section").html("");  
	$("#purchase-invoice-section").hide();  
	$("#purchase-invoice-section-loading").show();
    $("#purchase-invoice-section").load("manageinvoicesection.php",function(){    	
	  	$("#purchase-invoice-section-loading").fadeOut(50,function(){
	   		$("#purchase-invoice-section").fadeIn(100);  
		});
	});	
}

$(document).ready(function(){


	getInvoices();

	//Show Add Invoice Modal
	$(document).on("click","#show-add-invoice-form-modal-btn",function(e){
		e.preventDefault();

		var dataArray = {};

      	$("#add-invoice-form-cont").load("addinvoiceformmodal.php",dataArray,function(){    	
		  	$("#add-invoice-form-modal").modal({
			    escapeClose: false,
			    clickClose: false,
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
	            	getInvoices();
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


	// Delete Invoice Button
	$(document).on("click",".delete-invoice-btn",function(){

		if(confirm("All debit & credit notes related to this invoice will also be deleted. Are you sure you want to delete this invoice ?"))
		{
			var eleid = $(this).attr("id");
			var inid = $(this).attr("data-invoice-id");
			var pid = $(this).attr("data-project-id");
			var spinnerid = eleid+"-spinner";
			eleid = "#"+eleid;			
			var dataArray = {invoiceid : inid,projectid : pid};

			$(eleid).attr('disabled','disabled');
          	// $(eleid).html("Deactivating...");  	
			$(eleid).after("<img class='ae-ajax-loading-spinner' id='"+spinnerid+"' alt='Loading' src='images/gif/spinner.gif' height='25' width='25'>"); 	

			$.ajax(
	        {
	        	type: "POST",
	            url: "php_action/delete_invoice.php",
	            data: dataArray,
	            dataType: 'JSON',
	        })
	          //Success
	        .done(function(response) 
	        {
	            if(response.status == 1)
	            {  
	            	getInvoices();
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