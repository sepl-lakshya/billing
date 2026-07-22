function initializeDataTable(tableid)
{
	let table = new DataTable('#'+tableid, {
    	order: false,
    	autoWidth: false,
    	"initComplete": function (settings, json) {  
		    $("#"+tableid).wrap("<div class='table-element-post-load-wrapper' style=''></div>");            
		  }
	});
}
