$(document).ready(function(){ 
	$(":button#update").css({background:"green", border:"2px black solid", cursor:"pointer"});
	$(":button#delall").css({background:"red", color:"black", border:"2px black solid", cursor:"pointer"});
	$("#result").css({color:"red"});
	$("#update").click(function(){
		$("#result").html('Wait while loading Data...');
		$.post('dowork.php', {module:'Pos', action:'update'}, function(data) {
			$("#result").html(data);
		});
	});
	
	$(":button#delall").click(function(){
		//$("#result").html('Wait while deleting account and all Data...');
		var charid = $('#charid').val();
		var corpid = $('#corpid').val();
		$.msgBox({
			title: "Are You Sure",
			content: "Would you delete all Stuff?",
			type: "confirm",
			buttons: [{ value: "Yes" }, { value: "No" }, { value: "Cancel"}],
			success: function (result) {
				if (result == "Yes") {
					$.post('dowork.php', {module:'Pos', action:'delall', charid:charid, corpid:corpid}, function(data) {
						$("#result").html(data);
					});
				}
			}
		});
		
	});
	
	$('#addchar').submit(function() {
		var keyid = $('#keyid').val();
		var vcode = $('#vcode').val();
		$("#error").html('');
		$('#fetch').attr('disabled', true);
		$.post('dowork.php', {module:'Pos',action:'addchar',keyid:keyid,vcode:vcode}, function(data) {	
			if(data.error) {
				$("#error").html(data.error);
				$('#fetch').attr('disabled', false);
				return false;
			}
			if(data.result.key.type != 'Corporation') {
				$("#error").html('You need Api Key Type:Corporation');
				$('#fetch').attr('disabled', false);
				return false;
			}
			if(data.result.key.expires != '') {
				$("#error").html('You need Api Key Expires = no Expires');
				$('#fetch').attr('disabled', false);
				return false;
			}
			if(!(data.result.key.accessMask & 52035594)) {
				$("#error").html('You need Api Key Access Mask:52035594 <br />'
					+ 'Account and Market: WalletJournal<br />'
					+ 'Corporation Members: MemberTrackingExtended<br />'
					+ 'Outposts and Starbases: StarbaseList, StarbaseDetail<br />'
					+ 'Private Information: Locations, CorporationSheet, AssetList');
				$('#fetch').attr('disabled', false);
				return false;
			}
			if(data.result.key.accessMask & 52035594) {
				data['keyid'] = keyid;
				data['vcode'] = vcode;
				$('#addchar').selectChar(data);
			} else {
				$("#error").html('something went wrong!');
				$('#fetch').attr('disabled', false);
			}
		}, "json");
		
		return false;
	});
	
	$('#savechar').submit(function() {
		var keyid = $('#keyid').val();
		var vcode = $('#vcode').val();
		var charid = $('#charid').val();
		$("#error").html('');
		$('#fetch').attr('disabled', true);
		$.post('dowork.php', {module:'Pos',action:'addchar',keyid:keyid,vcode:vcode}, function(data) {	
			if(data.error) {
				$("#error").html(data.error);
				$('#fetch').attr('disabled', false);
				return false;
			}
			if(data.result.key.type != 'Corporation') {
				$("#error").html('You need Api Key Type:Corporation');
				$('#fetch').attr('disabled', false);
				return false;
			}
			if(data.result.key.expires != '') {
				$("#error").html('You need Api Key Expires = no Expires');
				$('#fetch').attr('disabled', false);
				return false;
			}
			if(!(data.result.key.accessMask & 52035594)) {
				$("#error").html('You need Api Key Access Mask:52035594 <br />'
					+ 'Account and Market: WalletJournal<br />'
					+ 'Corporation Members: MemberTrackingExtended<br />'
					+ 'Outposts and Starbases: StarbaseList, StarbaseDetail<br />'
					+ 'Private Information: Locations, CorporationSheet, AssetList');
				$('#fetch').attr('disabled', false);
				return false;
			}
			if(data.result.key.accessMask & 52035594) {
				data['keyid'] = keyid;
				data['vcode'] = vcode;
				data['charid'] = charid;
				if(data.result.key.characters[charid]) {
					// $("#error").html('freischalten einbauen');
					$.post('dowork.php', {module:'Pos',action:'insert',obj:data}, function(data) {	
						if(data.error) { 
							$("#error").html(data.error);
							return false;
						}
						$('#fetch').attr('disabled', false);
						console.log('success');
						location.reload();
					}, "json");
				} else {
					$("#error").html('Wrong Char. löschen einbauen');
					$('#fetch').attr('disabled', false);
				}
				// $('#addchar').selectChar(data);
			} else {
				$("#error").html('something went wrong!');
				$('#fetch').attr('disabled', false);
			}
		}, "json");
		
		return false;
	});
	
	$('input#time').change(function () {
		$("#error").html('').show();
		var val = $(this).val();
		var charid = $("#charid").val();
		if (val !== "" && $.isNumeric(val) && val >= 24) {
			$.post('dowork.php', {module:'Pos',action:'fueltime',time:val, charid:charid}, function(data) {	
				if(data.error) { 
					$("#error").html(data.error);
					return false;
				}
				console.log('success');
				location.reload();
			}, "json");
			$("#error").html('Saved').fadeOut(4000);
		} else {
			$("#error").html('Min 24h and Numbers');
		}
	});
	
	
	$('#addmail').css( 'cursor', 'pointer' );
	$('#addmail').attr('disabled', true);
	
	$("#email").keyup(function(){
		var email = $("#email").val();
		
		if(email != 0){
			if(isValidEmailAddress(email)){
				$('#addmail').attr('disabled', false);
			} else {
				$('#addmail').attr('disabled', true);
			}
		} else {
			$('#addmail').attr('disabled', true);
		}
	});
	
	$('#addmail').click(function() {
		$("#error").html('').show();
		var email = $('#email').val();
		var corpid = $('#corpid').val();
		$.post('dowork.php', {module:'Pos',action:'addmail',email:email,corpid:corpid}, function(data) {	
			if(data.error) { 
				$("#error").html(data.error);
				return false;
			}
			console.log('success');
			location.reload();
		}, "json");
	});
	
	$('.del').each(function() {
		$(this).click(function() {
			var email = $(this).closest('td').prev('td').text();
			var corpid = $('#corpid').val();
			$.post('dowork.php', {module:'Pos',action:'delmail',email:email,corpid:corpid}, function(data) {	
				if(data.error) { 
					$("#error").html(data.error);
					return false;
				}
				console.log(email);
				location.reload();
			}, "json");
		});
	});
});

$.fn.selectChar = function(obj){
	$('#selchar').remove();
    $(this).after('<table class="selchar" id="selchar"></table>');
	for( var c in obj.result.key.characters) {
		$('#selchar').append('<tr id="'+obj.result.key.characters[c].characterID+'"></tr>');
		$('#'+c).append('<td><img src="https://image.eveonline.com/Character/'+obj.result.key.characters[c].characterID+'_64.jpg" /></td>');
		$('#'+c).append('<td>'+obj.result.key.characters[c].characterName+'<br/>'+obj.result.key.characters[c].corporationName+'</td>');
	}
	
	$('#selchar tr').click(function(){
		obj['charid'] = $(this).attr('id');
		$.post('dowork.php', {module:'Pos',action:'insert',obj:obj}, function(data) {	
			if(data.error) { 
				$("#error").html(data.error);
				return false;
			}
			$('#selchar').remove();
			$('#fetch').attr('disabled', false);
			console.log('success');
			location.reload();
		}, "json");
	}).css('cursor','pointer');
	
	return false;
};

function isValidEmailAddress(emailAddress) {
	var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
	return pattern.test(emailAddress);
}
