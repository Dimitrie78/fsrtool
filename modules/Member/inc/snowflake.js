// Komplexere Variante mit korrekter Sortierung

// Zunächst Komma Filter für Floats definieren
$.tablesorter.addParser({ 
  id: 'g_float',
  is: function(s) {
	return /^\d+\.\d+,\d+ \u20AC$/.test(s);
  },

  format: function(s) {
	return jQuery.tablesorter.formatInt( s.replace(/[\., \u20AC]/g,"") );
  },

  type: "numeric"
});

// Dokument ist fertig geladen - Wir können die Funktion nun sicher zuordnen
$(document).ready(function(){ 
    // Aufruf mit Plugin und korrekter Sortierung
	if ($(".snow").attr("id")) {
	    var id = $('.snow').attr('id').match(/[0-9]+/)[0]; }
	//alert(id); 
	if (id == 1) $('#player_'+id).tablesorter( { widgets: ['zebra'],	headers: { 1: { sorter: "g_float", } } } );
	if (id == 2) $('#player_'+id).tablesorter( { widgets: ['zebra'],	headers: { 2: { sorter: "g_float", } } } );
	if (id == 3) $('#player_'+id).tablesorter( { widgets: ['zebra'],	headers: { 3: { sorter: "g_float", } } } );
	if (id == 9) $('#player_'+id).tablesorter( { widgets: ['zebra'],	headers: { 9: { sorter: "g_float", } } } );
	if (id == 23) $('#player_'+id).tablesorter( { widgets: ['zebra'],	headers: { 2: { sorter: "g_float", } , 3: { sorter: "g_float", } } } );
	
	$('table tbody tr').hover(
			function(){
				$(this).css('background-color','#467c15');
			},
			function(){
				$(this).css('background-color','');
			}
	);
}); 


function hideDiv(d) {
	getE(d).style.display = "none";
}

function showDiv(d) {
	getE(d).style.display = "block";
}

function toggleDiv(d) {
	if(getE(d).style.display == "none") { getE(d).style.display = "block"; }
	else { getE(d).style.display = "none"; }
}

function centerWin(d) {
	x = getE(d);
	vpWidth=self.innerWidth;
	vpHeight=self.innerHeight;
	dialogWidth=x.offsetWidth;
	dialogHeight=x.offsetHeight;
	dialogTop = (vpHeight/2) - (dialogHeight/2);
	dialogLeft = (vpWidth/2) - (dialogWidth/2);
	x.style.top =dialogTop+"px";
	x.style.left =dialogLeft+"px";
}

// AltWIN
function showIsAltWin(charName, charID, redirect, action, corpID) {
	fillIsAltWin(charName, charID, redirect, action);
	showDiv('isAltWin');
	centerWin('isAltWin');
	getE('isAltSearch').focus();
	$('#isAltSearch').autocomplete({
		minLength: 3,
		source: "dowork.php?module=Member&action=isAltSearch&corpID="+ corpID
	});
}

function fillIsAltWin(charName, charID, redirect, action) {
	getE('isAltWin').innerHTML = '<table class="cleanTable"><tr>'
		+ '<td width="100%"><form action="'+ redirect +'" method="POST">'
		+ charName + ' is an alt of <input type="text" id="isAltSearch" name="altOf">'
		+ '<input type="hidden" name="module" value="Member">'
		+ '<input type="hidden" name="action" value="'+ action +'">'
		+ '<input type="hidden" name="name" value="'+ charName +'">'
		+ '<input type="hidden" name="charID" value="'+ charID +'"></form></td>'

		+ '<td width="0%"><a href="javascript:hideDiv(\'isAltWin\')">'
		+ '<img src="modules/Member/images/greenx.jpg"></a></td>'
		+ '</tr></table>';
}

function hideIsAltWin() {
	showDiv('isAltWin');
}

function recenterIsAltWin() {
	centerWin('isAltWin');
}

//########################

function showEditEvalWin(charID, evaluation, comment, date, redirect, division, action) {
	fillEditEvalWin(charID, evaluation, comment, date, redirect, division, action);
	showDiv('editEvalWin');
	centerWin('editEvalWin');
}

function fillEditEvalWin(charID, evaluation, comment, date, redirect, division, action) {
	
	if (evaluation == 0) evaluation0 = 'checked="checked"'; else evaluation0 = '';
	if (evaluation == 1) evaluation1 = 'checked="checked"'; else evaluation1 = '';
	if (evaluation == 2) evaluation2 = 'checked="checked"'; else evaluation2 = '';
	if (evaluation == 3) evaluation3 = 'checked="checked"'; else evaluation3 = '';
	if (evaluation == 4) evaluation4 = 'checked="checked"'; else evaluation4 = '';
	if (evaluation == 5) evaluation5 = 'checked="checked"'; else evaluation5 = '';
	
	getE('editEvalWin').innerHTML = '<form action="'+ redirect +'" method="POST"><table class="cleanTable"><tr>'
		+ '<input type="hidden" name="module" value="Member">'
		+ '<input type="hidden" name="action" value="'+ action +'">'
		+ '<input type="hidden" name="charID" value="'+ charID +'">'
		+ '<input type="hidden" name="date" value="'+ date +'">'
		+ '<input type="hidden" name="division" value="'+ division +'">'
		+ '<input type="hidden" name="redirect" value="'+ redirect +'">'
		+ '<td width="100%"><h4>editEval</h4></td>'
		+ '<td style="text-align: right; vertical-align: top"><a href="javascript:hideDiv(\'editEvalWin\')">'
		+ '<img src="modules/Member/images/greenx.jpg"></a></td></tr></table>'
		+ '<table class="cleanTable"><tr>'
		+ '<tr><td rowspan="2" width="50%">Evaluation: <font color="000000">' + date + '</font></td>'
		+ '<td width="60" style="font-size:0.8em; text-align:center">n.e.</td>'
		+ '<td width="60" style="font-size:0.8em; text-align:center">--</td>'
		+ '<td width="60" style="font-size:0.8em; text-align:center">-</td>'
		+ '<td width="60" style="font-size:0.8em; text-align:center">0</td>'
		+ '<td width="60" style="font-size:0.8em; text-align:center">+</td>'
		+ '<td width="60" style="font-size:0.8em; text-align:center">++</td></tr><tr>'
		+ '<td width="60" style="text-align:center"><input type="radio" name="evaluation" value="0" ' + evaluation0 + '></td>'
		+ '<td width="60" style="text-align:center"><input type="radio" name="evaluation" value="1" ' + evaluation1 + '></td>'
		+ '<td width="60" style="text-align:center"><input type="radio" name="evaluation" value="2" ' + evaluation2 + '></td>'
		+ '<td width="60" style="text-align:center"><input type="radio" name="evaluation" value="3" ' + evaluation3 + '></td>'
		+ '<td width="60" style="text-align:center"><input type="radio" name="evaluation" value="4" ' + evaluation4 + '></td>'
		+ '<td width="60" style="text-align:center"><input type="radio" name="evaluation" value="5" ' + evaluation5 + '></td>'
		+ '</tr></table><table class="cleanTable"><tr>'
		+ '<td>Notes:<br><textarea name="comment" rows="7" cols="38" wrap="soft">' + comment + '</textarea></td>'
		+ '<td width="30%" style="vertical-align: bottom; text-align:right"><input type="submit" name="editEval" value="Go"></td></tr></table></form>';
}

function hideEditEvalWin() {
	showDiv('editEvalWin');
}

function recenterEditEvalWin() {
	centerWin('editEvalWin');
}

//##

function showAddEvalWin(charID, redirect, division, action) {
	fillAddEvalWin(charID, redirect, division, action);
	showDiv('addEvalWin');
	centerWin('addEvalWin');
}

function fillAddEvalWin(charID, redirect, division, action) {
	
	getE('addEvalWin').innerHTML = '<form action="'+ redirect +'" method="POST"><table class="cleanTable"><tr>'
		+ '<input type="hidden" name="module" value="Member">'
		+ '<input type="hidden" name="action" value="'+ action +'">'
		+ '<input type="hidden" name="charID" value="'+ charID +'">'
		+ '<input type="hidden" name="division" value="'+ division +'">'
		+ '<input type="hidden" name="redirect" value="'+ redirect +'">'
		+ '<td width="100%"><h4>addEval</h4></td>'
		+ '<td style="text-align: right; vertical-align: top"><a href="javascript:hideDiv(\'addEvalWin\')">'
		+ '<img src="modules/Member/images/greenx.jpg"></a></td></tr></table>'
		+ '<table class="cleanTable"><tr>'
		+ '<td rowspan="2" width="50%">Evaluation:</td>'
		+ '<td width="60" style="font-size:0.8em; text-align:center">n.e.</td>'
		+ '<td width="60" style="font-size:0.8em; text-align:center">--</td>'
		+ '<td width="60" style="font-size:0.8em; text-align:center">-</td>'
		+ '<td width="60" style="font-size:0.8em; text-align:center">0</td>'
		+ '<td width="60" style="font-size:0.8em; text-align:center">+</td>'
		+ '<td width="60" style="font-size:0.8em; text-align:center">++</td></tr><tr>'
		+ '<td width="60" style="text-align:center"><input type="radio" name="evaluation" value="0"></td>'
		+ '<td width="60" style="text-align:center"><input type="radio" name="evaluation" value="1"></td>'
		+ '<td width="60" style="text-align:center"><input type="radio" name="evaluation" value="2"></td>'
		+ '<td width="60" style="text-align:center"><input type="radio" name="evaluation" value="3"></td>'
		+ '<td width="60" style="text-align:center"><input type="radio" name="evaluation" value="4"></td>'
		+ '<td width="60" style="text-align:center"><input type="radio" name="evaluation" value="5"></td>'
		+ '</tr></table><table class="cleanTable"><tr>'
		+ '<td>Notes:<br><textarea name="comment" rows="7" cols="38" wrap="soft"></textarea></td>'
		+ '<td width="30%" style="vertical-align: bottom; text-align:right"><input type="submit" name="addEval" value="Go"></td></tr></table></form>';
}

function hideAddEvalWin() {
	showDiv('addEvalWin');
}

function recenterAddEvalWin() {
	centerWin('addEvalWin');
}

// Flag_Win

function showFlagWin(charName, main, charID, division, afk, afkText, tz, carrier, dread, investigate, posgunner, notes, redirect, posd, exempt, legacy, probation, stuff, pvpDiv, acti) {
	fillFlagWin(charName, main, charID, division, afk, afkText, tz, carrier, dread, investigate, posgunner, notes, redirect, posd, exempt, legacy, probation, stuff, pvpDiv, acti);
	showDiv('flagWin');
	centerWin('flagWin');
	$('form').submit(function() {
	  var data = $(this).serializeArray();
	  var url = 'dowork.php';
	  $.ajax({
		   type: 'POST',
		   url: url,
		   data: data,
		   beforeSend: function(){
			 $('#flagWin').html('<img src="icons/loading_animation.gif" alt="loading">'); 
		   },
		   success: function(msg){
			 $('#flagWin').hide();
			 location.reload(); 
		   }
	  });
	  
	  return false;
	});
}

function fillFlagWin(charName, main, charID, division, afk, afkText, tz, carrier, dread, investigate, posgunner, notes, redirect, posd, exempt, legacy, probation, stuff, pvpDiv, acti) {
	
	if (division == 0) division0 = 'SELECTED'; else division0 = '';
	if (division == 1) division1 = 'SELECTED'; else division1 = '';
	if (division == 2) division2 = 'SELECTED'; else division2 = '';
	if (division == 3) division3 = 'SELECTED'; else division3 = '';
	if (division == 4) division4 = 'SELECTED'; else division4 = '';
	if (division == 5) division5 = 'SELECTED'; else division5 = '';
	if (division == 6) division6 = 'SELECTED'; else division6 = '';
	if (division == 7) division7 = 'SELECTED'; else division7 = '';
	
	if (tz == 1) tzEuro = 'SELECTED'; else tzEuro = '';
	if (tz == 2) tzAmer = 'SELECTED'; else tzAmer = '';
	if (tz == 3) tzOceanic = 'SELECTED'; else tzOceanic = '';
	
	if (carrier == 1) carrier1 = 'SELECTED'; else carrier1 = '';
	if (carrier == 2) carrier2 = 'SELECTED'; else carrier2 = '';
	if (carrier == 3) carrier3 = 'SELECTED'; else carrier3 = '';
	if (carrier == 4) carrier4 = 'SELECTED'; else carrier4 = '';
	
	if (dread == 1) dread1 = 'SELECTED'; else dread1 = '';
	if (dread == 2) dread2 = 'SELECTED'; else dread2 = '';
	if (dread == 3) dread3 = 'SELECTED'; else dread3 = '';
	if (dread == 4) dread4 = 'SELECTED'; else dread4 = '';
	//inserting the quotes in the correct places
	notes = notes.replace(/c39c/g,'\'');
	notes = notes.replace(/c34c/g,'\"');
	charName = charName.replace(/c39c/,'\'');
	charName = charName.replace(/c34c/,'\"');
	
	getE('flagWin').innerHTML = '<form><table class="cleanTable"><tr>'
		+ '<td width="100%">' + charName + ' is...<br></td>'
		+ '<td width="0%" style="text-align: right;"><a href="javascript:hideDiv(\'flagWin\')">'
		+ '<img src="modules/Member/images/greenx.jpg"></a></td>'
		
		+ '<tr><td style="padding-left: 10px;">'
		+ '<input type="hidden" name="module" value="Member">'
		+ '<input type="hidden" name="action" value="addFlag">'
		+ '<input type="hidden" name="main2" value="'+ main +'">'
		+ '<input type="hidden" name="main" value="'+ charName +'">'
		+ '<input type="hidden" name="charID" value="'+ charID +'">'
		+ '<input type="hidden" name="redirect" value="'+ redirect +'">'
		+ 'Division: <select name="division"><option value="0" '+ division0 +'>None</option><option value="1" '+ division1 +'>'+ pvpDiv +'</option><option value="2" '+ division2 +'>Mining Division</option><option value="3" '+ division3 +'>POS Division</option><option value="4" '+ division4 +'>Support Division</option><option value="5" '+ division5 +'>High Command</option><option value="6" '+ division6 +'>Leader</option><option value="7" '+ division7 +'>'+ stuff +'</option></select><br><br>'
		+ '<input type="checkbox" id="afk" name="afk" value="true" '+ afk +'>AFK because <textarea name="afkText" rows="3" cols="50">' + afkText + '</textarea><br>'
		+ '<input type="checkbox" name="investigate" value="true" '+ investigate +'>Investigate<br><br>'
		+ '<table class="cleanTable">'
		+ '<tr><td colspan=2><input type="checkbox" name="posd" value="true" '+ posd +'>POS Duties</td>'
		+ '<td colspan=2><input type="checkbox" name="exempt" value="true" '+ exempt +'>Exempt/Officer</td></tr>'
		+ '<tr><td colspan=2><input type="checkbox" name="legacy" value="true" '+ legacy +'>Legacy Member</td></tr>'
		+ '<tr><td colspan=3><input type="checkbox" name="probation" value="true" '+ probation +'>Member on Probation</td></tr>'
		+ '<tr><td colspan=2><input type="checkbox" name="posgunner" value="true" '+ posgunner +'>POS Gunner</td><td>Timezone:</td><td><select name="tz"><option value="0">Unknown</option><option value="1" '+ tzEuro +'>European</option><option value="2" '+ tzAmer +'>American</option><option value="3" '+ tzOceanic +'>Oceanic</option></select></td></tr>'
		+ '<tr><td>Carrier:</td><td><select name="carrier"><option value="0">None</option><option value="1" '+ carrier1 +'>Archon</option><option value="2" '+ carrier2 +'>Chimera</option><option value="3" '+ carrier3 +'>Thanatos</option><option value="4" '+ carrier4 +'>Nidhoggur</option></select></td>'
		+ '<td>Dread:</td><td><select name="dread"><option value="0">None</option><option value="1" '+ dread1 +'>Revelation</option><option value="2" '+ dread2 +'>Phoenix</option><option value="3" '+ dread3 +'>Moros</option><option value="4" '+ dread4 +'>Naglfar</option></select></td></tr>'
		+ '</table><br>'
		+ 'Notes:<br><textarea name="notes" rows="10" cols="50">' + notes + '</textarea>'
		+ '</td><td style="vertical-align: bottom;"><input type="submit" name="addFlag" value="Go"></td></tr></table></form>';
}

function hideFlagWin() {
	showDiv('flagWin');
}

function recenterFlagWin() {
	centerWin('flagWin');
}

function getE(x) {
	return document.getElementById(x);
}
