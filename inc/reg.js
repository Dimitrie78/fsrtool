function reg(userName) {
	var charID = $('input[name="'+userName+'"]').val();
	$('#reg').append('<input type="hidden" name="charID" value="'+charID+'" />');
	return false;
}