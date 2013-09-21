$(document).ready(function(){
  $(".user").tablesorter();
  $('img.del').click(function(){
	var id = $(this).parent('td:last').parent().attr('id');
	
	$.post('dowork.php', {module:'userManager', action:'delChar', charID:id}, function(data) {
		if (data == 1) $('tr#'+id).remove();
		else alert('You can not delete itself...');
	});
  });
  
  $('img.edit').click(function(){
	var id = $(this).parent('td:last').parent().attr('id');
	// Getting the variable's value from a link 
	var editBox = $('div#edit');
	
	$.post('dowork.php', {module:'userManager', action:'editChar', charID:id}, function(data) {
		$(editBox).table(data);
		$(editBox).fadeIn(300);
			
			//Set the center alignment padding + border
			var popMargTop = ($(editBox).height() + 24) / 2; 
			var popMargLeft = ($(editBox).width() + 24) / 2; 
			
			$(editBox).css({ 
				'margin-top' : -popMargTop,
				'margin-left' : -popMargLeft
			});
			
			// Add the mask to body
			$('body').append('<div id="mask"></div>');
			$('#mask').fadeIn(300);
			$("#charedit").submit(function(){
			$.post($("#charedit").attr("action"), $("#charedit").serialize(), function(msg) {
				if (msg == 1) {
					$(editBox).fadeOut(300 , function() {
						$('#mask').remove();
					});
					location.reload();
				} else {
					$(editBox).fadeOut(300 , function() {
						$('#mask').remove();  
					});
				}
			});
			return false;
		});
	}, "json");
  });
	
	
	// When clicking on the button close or the mask layer the popup closed
	$('a.close, #mask').live('click', function() { 
	  $('#mask , .mail-popup').fadeOut(300 , function() {
		$('#mask').remove();  
	  }); 
	  return false;
	});
  
  
  $('img.role').click(function(){
	var charID = $(this).parent('td:last').parent().attr('id');
	var roleID = $(this).attr('id');
	var x = $(this);
	if ( $(this).attr('src') == 'icons/cross.png' ) {
		$.post('dowork.php', {module:'userManager', action:'editUser', charID:charID, roleID:roleID, edit:1}, function(data) {
			if (data == 1) $(x).attr('src', 'icons/tick.png');
			else if (data == 2) alert('Sorry, you must be an Admin');
			else alert('noooo...');
		});
	}
	else {
		$.post('dowork.php', {module:'userManager', action:'editUser', charID:charID, roleID:roleID, edit:0}, function(data) {
			if (data == 1) $(x).attr('src', 'icons/cross.png');
			else if (data == 2) alert('Sorry, you must be an Admin');
			else alert('noooo...');
		});
	}
	//alert(charID + roleID);
  });
  
  $('img.altrole').click(function(){
	var charID = $(this).parent('td:last').parent().attr('id');
	var role = $(this).attr('id');
	var x = $(this);
	if ( $(this).attr('src') == 'icons/cross.png' ) {
		$.post('dowork.php', {module:'userManager', action:'editAltUser', charID:charID, role:role, edit:1}, function(data) {
			if (data == 1) $(x).attr('src', 'icons/tick.png');
			else alert('Noob...');
		});
	}
	else {
		$.post('dowork.php', {module:'userManager', action:'editAltUser', charID:charID, role:role, edit:0}, function(data) {
			if (data == 1) $(x).attr('src', 'icons/cross.png');
			else alert('Noob...');
		});
	}
	//alert(charID + role);
  });
  
  $('input#id_search').quicksearch('table.user tbody tr');
});

$.fn.table = function (data) {
	var table = '<form id="charedit" action="dowork.php?module=userManager&action=editChar">'
		+ '<table width="100%">'
		+ '<tr><td colspan="2" style="text-align: right; vertical-align: top;">'
		+ '<a href="#" class="close">'
		+ '<img class="btn_close" src="icons/cross.png" title="Close Window" alt="Close"></a></td></tr>'
		+ '<tr><td width="100">Name:</td><td>'+data.username+'</td></tr>'
		+ '<tr><td>Email:</td><td><input type="text" name="edit[email]" value="'+data.email+'" /></td></tr>'
		+ '<tr><td>Description:</td><td><input type="text" name="edit[des]" value="'+data.description+'" /></td></tr>'
		+ '<tr><td>Password:</td><td><input type="password" name="edit[pwd]" /></td></tr>'
		+ '<tr><td align="center" colspan="2"><input type="submit" value="-> EDIT <-" /></td></tr>'
		+ '</table><input type="hidden" name="edit[charID]" value="'+data.charID+'" /></form>';
	return $(this).html(table);
}

function hideDiv(id) {
	$('div#'+id).hide();
}

$.fn.extend({
  center: function (options) {
	   var options =  $.extend({ // Default values
			inside:window, // element, center into window
			transition: 0, // millisecond, transition time
			minX:0, // pixel, minimum left element value
			minY:0, // pixel, minimum top element value
			vertical:true, // booleen, center vertical
			withScrolling:true, // booleen, take care of element inside scrollTop when minX < 0 and window is small or when window is big
			horizontal:true // booleen, center horizontal
	   }, options);
	   return this.each(function() {
			var props = {position:'absolute'};
			if (options.vertical) {
				 var top = ($(options.inside).height() - $(this).outerHeight()) / 2;
				 if (options.withScrolling) top += $(options.inside).scrollTop() || 0;
				 top = (top > options.minY ? top : options.minY);
				 $.extend(props, {top: top+'px'});
			}
			if (options.horizontal) {
				  var left = ($(options.inside).width() - $(this).outerWidth()) / 2;
				  if (options.withScrolling) left += $(options.inside).scrollLeft() || 0;
				  left = (left > options.minX ? left : options.minX);
				  $.extend(props, {left: left+'px'});
			}
			if (options.transition > 0) $(this).animate(props, options.transition);
			else $(this).css(props);
			return $(this);
	   });
  }
});
