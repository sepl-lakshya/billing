
function initializeDataTable(tableid)
{
	let table = new DataTable('#'+tableid, {
    	order: false,
    	autoWidth: false

	});
}

function getCreditNotes()
{
	$("#credit-note-section").html("");  
	$("#credit-note-section").hide();  
	$("#credit-note-section-loading").show();
    $("#credit-note-section").load("managecreditnotesection.php",function(){    	
	  	$("#credit-note-section-loading").fadeOut(50,function(){
	   		$("#credit-note-section").fadeIn(100);  
		});
	});	
}


$(document).ready(function(){


	getCreditNotes();

	// Delete Credit Note Button
	$(document).on("click",".delete-credit-note-btn",function(){

		if(confirm("Are you sure you want to delete this credit note ?"))
		{
			var eleid = $(this).attr("id");
			var bid = $(this).attr("data-bill-id");
			var inid = $(this).attr("data-invoice-id");
			var cnid = $(this).attr("data-credit-note-id");
			var pid = $(this).attr("data-project-id");
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
	            	getCreditNotes();
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