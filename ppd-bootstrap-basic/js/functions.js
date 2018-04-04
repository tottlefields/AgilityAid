function getCompNumbers(show_id) {

	var data = {
		'action' : 'comp_nos',
		'show_id' : show_id
	};

	jQuery.ajax({
		type : "post",
		url : ajaxObject.ajax_url,
		dataType : "json",
		data : data,
		success : function(results) {
			jsonToCSV(results.class_data, "Class Counts " + results.show.post_name);
			return;
		}
	});

}

function getEntryDetails(show_id) {

	var data = {
		'action' : 'entry_details',
		'show_id' : show_id
	};

	jQuery.ajax({
		type : "post",
		url : ajaxObject.ajax_url,
		dataType : "json",
		data : data,
		success : function(results) {
			//console.log(results.entries);
			jsonToCSV(results.entries, "Entry Details " + results.show.post_name);
			return;
		}
	});

}

function getPairsDetails(show_id){

	var data = {
                'action' : 'pairs_details',
                'show_id' : show_id
        };

        jQuery.ajax({
                type : "post",
                url : ajaxObject.ajax_url,
                dataType : "json",
                data : data,
                success : function(results) {
                        //console.log(results.pairs_info);
                        jsonToCSV(results.pairs_info, "Pairs Details " + results.show.post_name);
                        return;
                }
        });

}

function viewShowEntries(show_id) {

	var data = {
		'action' : 'entry_data',
		'show_id' : show_id
	};

	jQuery.ajax({
		type : "post",
		url : ajaxObject.ajax_url,
		dataType : "json",
		data : data,
		success : function(results) {
			var showData = results.show;
			var entryData = results.form_data;
//			console.log(entryData);
			pdf_online_entries(entryData, showData.post_title, showData.showDates, showData.showMeta.venue, showData.post_name + "_ShowEntries.pdf");
			return;
		}
	});

}

function jsonToCSV(data, title) {
	var arrData = typeof data != 'object' ? JSON.parse(data) : data;
	var CSV = '';

	for (var i = 0; i < arrData.length; i++) {
		var row = "";
		for ( var j in arrData[i]) {
			row += '"' + arrData[i][j] + '",';
		}
		row.slice(0, row.length - 1);
		CSV += row + "\n";
	}

	if (CSV == '') {
		console.log("Invalid data");
		return;
	}

	var fileName = 'AgilityAid_';
	fileName += title.replace(/ /g, "_");

	var uri = 'data:text/csv;charset=utf-8,' + escape(CSV);

	var link = document.createElement('a');
	link.setAttribute('href', uri);
	link.setAttribute('download', fileName + ".csv");
	document.body.appendChild(link);
	link.click();
	document.body.removeChild(link);
}

// ////////////////////////////////////////////////////////////////////////////
// PDF for online show entries //
// ////////////////////////////////////////////////////////////////////////////
function pdf_online_entries(entryDetails, showName, showDates, showVenue, fileName) {

	page = 0;
	ddShowEntries.content = [];

	ddShowEntries.content.push(header(showName, showDates, showVenue));
	ddShowEntries.content.push(mainContent(entryDetails));

	pdfMake.createPdf(ddShowEntries).download(fileName);
	//pdfMake.createPdf(ddShowEntries).open();

}

function header(showName, showDates, showVenue) {

	return {
		text : [ {
			text : 'AgilityAid Online Entries for ' + showName + '\n',
			style : 'header'
		}, {
			text : showDates + ' at ' + showVenue,
			style : 'subheader'
		} ]
	}

}

function mainContent(entryDetails) {
	var body = [];
	headerRow = [];
	headerRow.push("", {
		text : "KC REGISTERED NAME OF DOG",
		style : 'tableHeader',
		noWrap : true,
	}, {
		text : "KC NUMBER",
		style : 'tableHeader',
		noWrap : true,
	}, {
		text : "BREED",
		style : 'tableHeader'
	}, {
		text : "SEX",
		style : 'tableHeader',
		alignment : 'center'
	}, {
		text : "DOB",
		style : 'tableHeader',
		alignment : 'center'
	}, {
		text : "GRADE",
		style : 'tableHeader',
		alignment : 'center'
	}, {
		text : "HANDLER",
		style : 'tableHeader'
	}, {
		text : "CLASSES",
		style : 'tableHeader'
	}, {
		text : "RING NO",
		style : 'tableHeader',
		noWrap : true,
		alignment : 'center'
	}, {
		text : "LHO",
		style : 'tableHeader',
		alignment : 'center'
	});

	body.push(headerRow);

	for ( var form_no in entryDetails) {
		var details = entryDetails[form_no]['details'];

		var detailsString = details.name;
		if (details.address !== '') {
			detailsString += ", " + details.address + "\t";
		}
		if (details.tel !== '') {
			detailsString += "Tel:" + details.tel;
		}
		if (details.email !== '') {
			detailsString += "    Email:" + details.email;
		}
		var dataRow = [];
		dataRow.push(form_no, {
			text : detailsString,
			colSpan : 10
		});
		body.push(dataRow);

		var dogs = entryDetails[form_no]['dogs'];

		for (i in dogs) {
			dog = dogs[i];
			cells = [];
			cells.push("");
			cells.push(dog.kc_name);
			cells.push(dog.kc_no);
			cells.push(dog.breed);
			cells.push({text: dog.sex.substr(0, 1), alignment: 'center'});
			cells.push({text: dog.dob, alignment: 'center'});
			cells.push({text: dog.grade, alignment: 'center'});
			cells.push(dog.handler);
			cells.push(dog.classes);
			cells.push({text: dog.ring_no, alignment: 'center'});
			cells.push({text: dog.lho, alignment: 'center'});
			body.push(cells);
		}
		
		if (Object.keys(entryDetails[form_no]['camping']).length > 0){
			camping = entryDetails[form_no]['camping'];
			campString = "CAMPING: "+camping['pitches']+" UNIT(S) for "+camping['duration'];
			if (camping['camp_group'] !== undefined){ campString += " with "+camping['camp_group']; }
			// TODO: added extra camping details when needed - electrics/nights/car reg etc.
			infoRows = [];
			infoRows.push("", {
				text : campString,
				colSpan : 10,
				margin: [25,0,0,0]
			});
			body.push(infoRows);
		}
		
		if (Object.keys(entryDetails[form_no]['helpers']).length > 0){
			console.log();
			helpers = entryDetails[form_no]['helpers'];
			helperString = "HELPING: "
			for (var helper in helpers){
				helpDetails = helpers[helper];
				helperString += helper.toUpperCase()+" - "+helpDetails.group+" - "+helpDetails.job+" / ";
			}
			infoRows = [];
			infoRows.push("", {
				text : helperString,
				colSpan : 10,
				margin: [25,0,0,0]
			});
			body.push(infoRows);
		}
		
		if (Object.keys(entryDetails[form_no]['fees']).length > 0){
			totalClassFee = entryDetails[form_no]['fees']['classes']['count'] * entryDetails[form_no]['fees']['classes']['amount'];
			feeString = "FEES:    Classes - "+entryDetails[form_no]['fees']['classes']['count']+" @ "+entryDetails[form_no]['fees']['classes']['amount'].toFixed(2)+" = "+totalClassFee.toFixed(2);
			if(entryDetails[form_no]['fees']['camping'] !== undefined ){
				feeString += "; Camping - "+entryDetails[form_no]['fees']['camping']['count']+" @ "+entryDetails[form_no]['fees']['camping']['amount'].toFixed(2)+" = "+entryDetails[form_no]['fees']['camping']['amount'].toFixed(2);
			}
			//TODO: add champ/pair/trios etc 
			infoRows = [];
			infoRows.push("", {
				text : feeString,
				colSpan : 10,
				margin: [25,0,0,0]
			});
			body.push(infoRows);
		}


		var emptyRow = [];
		emptyRow.push({
			text : " ",
			colSpan : 11
		});
		body.push(emptyRow);
	}

	return {
		table : {
			// widths : [ 20, 20, '*', 50, 50, 50, 30, 20 ],
			headerRows : 1,
			body : body
		},
		layout : 'noBorders',
		margin : [ 0, 10, 0, 0]
	};
}

var ddShowEntries = {
	pageSize : 'A4',
	pageOrientation : 'landscape',
	pageMargins : [ 20, 20, 20, 20 ],
	content : [],
	defaultStyle : {
		font : 'Courier',
		fontSize : 8,
	},
	styles : {
		header : {
			fontSize : 12,
			bold : true,
			alignment : 'center'
		},
		subheader : {
			fontSize : 10,
			alignment : 'center'
		},
		tableHeader : {
			bold : true,
			fontSize : 10,
			color : 'black'
		},
		strong : {
			bold : true
		},
		small : {
			margin : [ 0, 0, 0, 0 ],
			fontSize : 8
		}
	},
	images : {
		aaLogo : 'data:image/png;base64,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'
	}
};

pdfMake.fonts = {
	Courier : {
		normal : 'Courier.ttf',
		bold : 'Courier-Bold.ttf',
		italics : 'Courier-Italic.ttf',
		bolditalics : 'Courier-Bold-Italic.ttf'
	},
	Tahoma : {
		normal : 'Tahoma.ttf',
		bold : 'Tahoma Bold.ttf',
		italics : 'Tahoma.ttf',
		bolditalics : 'Tahoma Bold.ttf',
	}
}
