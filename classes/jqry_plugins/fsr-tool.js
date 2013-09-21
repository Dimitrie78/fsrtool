//jQuery.noConflict();
$(document).ready(function(){
    $('a[title]').qtip({ style: { name: 'fsr', tip: true } });
	
	$('input.amount').each(function(){
		$(this).dg(0);
	});
	
	$('table.data tbody tr:not(.merge)').hover(
			function(){
				$(this).css('background-color','#467c15');
			},
			function(){
				$(this).css('background-color','');
			}
	);
	$('div#menu ul.items li').not('#disabled').hover(
		function(){
			$(this).css('background-color','#000');
		},
		function(){
			$(this).css('background-color','');
		}
	);
	// Confirm
	$('a.delete').confirm({
		msg:'',
		timeout:10000,
		dialogShow:'fadeIn',
		dialogSpeed:'slow',
		buttons: {
			ok:'<img src="icons/accept.png" />',
			cancel:'<img src="icons/cancel.png" />',
			separator:' '
		}  
	});
	$('#loader').ajaxStart(function() {
		$(this).show();
		$('#mask').addClass('progress');
	}).ajaxComplete(function() {
		$(this).hide();
		$('#mask').removeClass('progress');
	});
 
});
