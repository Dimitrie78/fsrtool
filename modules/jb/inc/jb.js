$(document).ready(function(){ 
	$('#addchar').submit(function() {
		var keyid = $('#keyid').val();
		var vcode = $('#vcode').val();
		$("#error").html('');
		$('#fetch').attr('disabled', true);
		$.post('dowork.php', {module:'jb',action:'addchar',keyid:keyid,vcode:vcode}, function(data) {	
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
			if(!(data.result.key.accessMask & 16777218)) {
				$("#error").html('You need Api Key Access Mask:16777218 (Private Information: Locations, AssetList)');
				$('#fetch').attr('disabled', false);
				return false;
			}
			if(data.result.key.accessMask & 16777218) {
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
		$.post('dowork.php', {module:'jb',action:'insert',obj:obj}, function(data) {	
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
