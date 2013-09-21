// install.js

function get_remote() {
  $.ajax({
	url: "remote-bar.txt",
	dataType: "text",
	ifModified: false,
	success: function(response) {
	  $("#progressbar").progressbar({value:parseInt(response)});
	  $("#progressbar").html(response+'%');
	  if(parseInt(response) <= 100)
		setTimeout("get_remote()", 1000);
	}
  });
}

function process(id, e, t) {
	if(t != '') {
		$('#progress').append('<br><a href="install.php?step='+t+'">Next Step</a>')
	} else { 
		if(id != '') {
			$("#progressbar").progressbar({value:0});
			$('#progress').html(e);
		} else {
			$("#progressbar").progressbar({value:parseInt(e)});
			$("#progress").html('Status: '+e+'%');
		}
	}
}

function ajax_CheckDB(){   
	var juname  = $('#dbuname').val();
	var jhost   = $('#dbhost').val();
	var jname	= $('#dbname').val();
	var jpass	= $('#dbpass').val();
	var jprefix = $('#dbprefix').val();
	var querytotal = $('#querytotal').val();
	
	$('#loader').show();
	
	$.post('install.php', {step:1, do:'Ajax_CheckDB',dbuname:juname, dbhost:jhost, dbname:jname, dbpass:jpass, dbprefix:jprefix}, function(data) {
		$('#dbinfo').html(data);
		if (data.substr(0, 11) == 'DATABASE OK') {
			$('#btnWrite').attr('disabled', false);
			$('#dbuname').attr('disabled', true);
			$('#dbhost').attr('disabled',true);
			$('#dbname').attr('disabled', true);
			$('#dbpass').attr('disabled', true);
			$('#dbprefix').attr('disabled', true);
			
		}
		$('#loader').hide();
		
	});
}

function ajax_WriteConfig(){
	var juname  = $('#dbuname').val();
	var jhost   = $('#dbhost').val();
	var jname	= $('#dbname').val();
	var jpass	= $('#dbpass').val();
	var jprefix = $('#dbprefix').val();
	
	$('#loader').show();
	
	$.post('install.php', {step:1, do:'Ajax_WriteConfig',dbuname:juname, dbhost:jhost, dbname:jname, dbpass:jpass, dbprefix:jprefix}, function(data) {
		$('#dbinfo').html(data);
		if (data == 'OK') {
		//	$('#btnTest').attr('disabled', true);
			$('#btnTest').hide();
		//	$('#btnWrite').attr('disabled', true);
			$('#btnWrite').hide();
			$('#btnNext').attr('disabled',false);
		}
		$('#loader').hide();
	});
}
	
function ajax_InstallTables(){
    $("#progressbar").progressbar({value:0});
	$('#loader').show();
	$('#dbinfo').html('Creating/Updating tables');
	$('#btnNext').attr('disabled', true);
    $.post('install.php', {step:2, do:'Ajax_WriteTables'}, function(data) {
		if (data == 'done') {
			location.href = 'install.php?step=2';
		}
		$('#dbinfo').html(data);
		$('#loader').hide();
		$("#progressbar").progressbar({value:100});
	});

}

function reg(userName) {
	var charID = $('input[name="'+userName+'"]').val();
	$('#reg').append('<input type="hidden" name="charID" value="'+charID+'" />');
	return false;
}

$(document).ready(function(){
	//global vars
	var form = $("#userform");
	var email = $("#email");
	var emailInfo = $("#emailInfo");
	var pass1 = $("#pass1");
	var pass1Info = $("#pass1Info");
	var pass2 = $("#pass2");
	var pass2Info = $("#pass2Info");

	
	//On blur
	email.blur(validateEmail);
	pass1.blur(validatePass1);
	pass2.blur(validatePass2);
	//On key press
	pass1.keyup(validatePass1);
	pass2.keyup(validatePass2);
	//On Submitting
	form.submit(function(){
		if(validateEmail() & validatePass1() & validatePass2()){
			$('#apikeyin').show();
		//	email.attr('disabled', true);
		//	pass1.attr('disabled', true);
		//	pass2.attr('disabled', true);
			$('#userBtn').hide();
			if($("#keyID").val() != '' & $("#vCode").val() != '')
				return true;
			return false;
		}
		else
			return false;
	});
	
	//validation functions
	function validateEmail(){
		//testing regular expression
		var a = $("#email").val();
		var filter = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
		//if it's valid email
		if(filter.test(a)){
			email.removeClass("error");
			emailInfo.text("Valid E-mail please");
			emailInfo.removeClass("error");
			return true;
		}
		//if it's NOT valid
		else{
			email.addClass("error");
			emailInfo.text("Stop cowboy! Type a valid e-mail please :P");
			emailInfo.addClass("error");
			return false;
		}
	}
	
	function validatePass1(){
		//it's NOT valid
		if(pass1.val().length <5){
			pass1.addClass("error");
			pass1Info.text("Ey! Remember: At least 5 characters: letters, numbers and '_'");
			pass1Info.addClass("error");
			return false;
		}
		//it's valid
		else{			
			pass1.removeClass("error");
			pass1Info.text("At least 5 characters: letters, numbers and '_'");
			pass1Info.removeClass("error");
			validatePass2();
			return true;
		}
	}
	function validatePass2(){
		//are NOT valid
		if( pass1.val() != pass2.val() ){
			pass2.addClass("error");
			pass2Info.text("Passwords doesn't match!");
			pass2Info.addClass("error");
			return false;
		}
		//are valid
		else{
			pass2.removeClass("error");
			pass2Info.text("Confirm password");
			pass2Info.removeClass("error");
			return true;
		}
	}
});