function hideDiv(d) {
	getE(d).style.display = "none";
}

function showDiv(d) {
	getE(d).style.display = "block";
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

function getE(x) {
	return document.getElementById(x);
}

function showSaveFilterWin() {
	fillSaveFilterWin();
	showDiv('saveFilterWin');
	centerWin('saveFilterWin');
}

function fillSaveFilterWin() {
	getE('saveFilterWin').innerHTML = '<table><tr>'
		+ '<td width="100%">'
		+ 'Set a name for the Filter:&nbsp;<input type="text" name="saveF">'
		+ '&nbsp;<input type="submit" value="save">'
		+ '</td>'
		+ '<td width="0%"><a href="javascript:hideDiv(\'saveFilterWin\')">'
		+ '<img src="modules/Member/images/greenx.jpg"></a></td>'
		+ '</tr></table>';
}