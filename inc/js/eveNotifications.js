$(document).ready(function(){ 

	$('img#addPush').click(function(){
		$.msgBox({ type: "prompt",
			title: "Pushover",
			opacity: 0.8,
			inputs: [
			{ header: "Pushover-user", type: "text", name: "pUser" },
			{ header: "Pushover-token", type: "text", name: "pToken" }],
			buttons: [
			{ value: "Save" }, { value: "Cancel" }],
			success: function (result, values) {
				if(result=='Save') {
					if(values[0].value != '' && values[1].value != '') {
						var v = result + " has been clicked\n";
						$.post('dowork.php', {action:'setPushMail',values:values,type:'addPush'}, function(data) {	
							console.log(data);
							if(data) {
								$('span#push').html('<br />User:&nbsp;' + values[0].value + '<br />Token:&nbsp;' + values[1].value + '&nbsp;<img id="delPush" src="icons/delete.png" title="delete" alt="delete" />');
							}
						}, "json");
					} else {
						$.msgBox({
							title: "Ooops",
							content: "Ohh dear! You broke it!!!",
							type: "error",
							buttons: [{ value: "Ok" }]
						});
					}
				}
				return false;
			}
		});
	});
	
	$('img#addMail').click(function(){
		$.msgBox({ type: "prompt",
			title: "E-Mail",
			opacity: 0.8,
			inputs: [
			{ header: "E-Mail", type: "text", name: "eMail" }],
			buttons: [
			{ value: "Save" }, { value: "Cancel" }],
			success: function (result, values) {
				if(result=='Save') {
					if(isValidEmailAddress(values[0].value)) {
						$.post('dowork.php', {action:'setPushMail',values:values,type:'addMail'}, function(data) {
							console.log(data);
							if(data) {
								$('span#mail').html('<br />' + values[0].value + '&nbsp;<img id="delMail" src="icons/delete.png" title="delete" alt="delete" />');
							}
						}, "json");
					} else {
						$.msgBox({
							title: "Ooops",
							content: "Ohh dear! You broke it!!!",
							type: "error",
							buttons: [{ value: "Ok" }]
						});
					}
				}
				return true;
			}
		});
	});
	
	$('img#delPush').click(function(){
		$.msgBox({
			title: "Are You Sure",
			content: "Would you delete it?",
			type: "confirm",
			buttons: [{ value: "Yes" }, { value: "No" }],
			success: function (result) {
				if (result == "Yes") {
					$.post('dowork.php', {action:'delPushMail', type:'delPush'}, function(data) {
						console.log(data);
						if(data) {
							$('span#push').html('');
						}
					}, "json");
				}
			}
		});
	});
	
	$('img#delMail').click(function(){
		$.msgBox({
			title: "Are You Sure",
			content: "Would you delete it?",
			type: "confirm",
			buttons: [{ value: "Yes" }, { value: "No" }],
			success: function (result) {
				if (result == "Yes") {
					$.post('dowork.php', {action:'delPushMail', type:'delMail'}, function(data) {
						console.log(data);
						if(data) {
							$('span#mail').html('');
						}
					}, "json");
				}
			}
		});
	});
	
	$('input:checkbox').click(function(){
		var id = $(this).attr('value');
		var status = $(this).prop('checked');
		console.log(id,status);
		$.post('dowork.php', {action:'setPushMail',id:id,status:status,type:'noti'}, function(data) {	
			console.log(data);
		}, "json");	
	});
});

function isValidEmailAddress(emailAddress) {
	var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
	return pattern.test(emailAddress);
}