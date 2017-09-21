

function getCompNumbers(show_id){
	
	var data = {
			'action' : 'comp_nos',
			'show_id' : show_id
	};
	
	jQuery.ajax({
		type: "post",
		url: ajaxObject.ajax_url,
		dataType: "json",
		data : data,
		success: function(results){
			jsonToCSV(results.class_data, "Class Counts "+results.show.post_name);
			return;
		}
	});
	
}

function getEntryDetails(show_id){
	
	var data = {
			'action' : 'entry_details',
			'show_id' : show_id
	};
	
	jQuery.ajax({
		type: "post",
		url: ajaxObject.ajax_url,
		dataType: "json",
		data : data,
		success: function(results){
			jsonToCSV(results.entries, "Entry Details "+results.show.post_name);
			return;
		}
	});
	
}


function jsonToCSV(data, title){
	var arrData = typeof data != 'object' ? JSON.parse(data) : data;
	var CSV = '';
	
	
	for (var i=0; i<arrData.length; i++){
		var row = "";
		for (var j in arrData[i]){
			row += '"'+arrData[i][j]+'",';
		}
		row.slice(0, row.length-1);
		CSV += row + "\n";
	}
	
	if (CSV == ''){
		console.log("Invalid data");
		return;
	}
	
	var fileName = 'AgilityAid_';
	fileName += title.replace(/ /g, "_");
	
	var uri = 'data:text/csv;charset=utf-8,' + escape(CSV);
	
	var link = document.createElement('a');
	link.setAttribute('href', uri);
	link.setAttribute('download', fileName+".csv");
	document.body.appendChild(link);
	link.click();
	document.body.removeChild(link);
}













