$(document).ready(function(){
	$('table.evemail tbody tr').hover(
		function(){
			$(this).css('background-color','#467c15');
		},
		function(){
			$(this).css('background-color','');
		}
	);
	
	$('span.timer').each(function(){
		$(this).mailtimer();
	});
	
	$('table.evemail tbody tr').click(function() {
		
		// Getting the variable's value from a link 
		var loginBox = $('div#mail-box');
		
		$.post('dowork.php', {module:'ooe', action:'eveMail', messageID:$(this).attr('id')}, function(data) {
			$(loginBox).addmail(data);
			//Fade in the Popup and add close button
			$(loginBox).fadeIn(300);
			
			//Set the center alignment padding + border
			var popMargTop = ($(loginBox).height() + 24) / 2; 
			var popMargLeft = ($(loginBox).width() + 24) / 2; 
			
			$(loginBox).css({ 
				'margin-top' : -popMargTop,
				'margin-left' : -popMargLeft
			});
			
			// Add the mask to body
			$('body').append('<div id="mask"></div>');
			$('#mask').fadeIn(300);
		});
		return false;
	});
	
	// When clicking on the button close or the mask layer the popup closed
	$('a.close, #mask').live('click', function() { 
	  $('#mask , .mail-popup').fadeOut(300 , function() {
		$('#mask').remove();  
	  }); 
	  return false;
	});
});

$.fn.addmail = function (data) {
	var table = '<table width="100%">'
		+ '<tr><td style="text-align: right; vertical-align: top;">'
		+ '<a href="#" class="close">'
		+ '<img class="btn_close" src="icons/close_pop.png" title="Close Window" alt="Close"></a></td></tr>'
		+ '<tr><td>'+data+'</td></tr>'
		+ '</table>';
	return $(this).html(table);
}

function hideDiv(id) {
	$('div#'+id).hide();
}


$.fn.mailtimer = function(){

	if(arguments.length == 0){
		var timer = $(this).text();
		$(this).css('display','inline');
	}else{
		var timer = arguments[0];
	} 
	
	var entity = new Object();
	entity['d'] = Math.floor(timer/60/60/24);
	entity['h'] = Math.floor(timer/60/60)-entity['d']*24;
	entity['m'] = Math.floor(timer/60)-entity['h']*60-entity['d']*24*60;
	entity['s'] = timer-entity['m']*60-entity['h']*60*60-entity['d']*24*60*60;
	
	var output = '';
	
	for(var i in entity){
		if(entity[i]>0){
			output+=entity[i]+i+' ';
		}
	}
	if(timer>2){
		$(this).text(output).doTimeout(1000, function(){
			$(this).mailtimer(parseInt(timer)-1);
		});
	}else{
		$(this).text('NOW!');
	}
};