function toggleUser(user){
	$(document).ready(function(){
		if($('div.'+user)) {
		  $('div.user').css('display','none');
			$('div.'+user).slideToggle('fast');
		}
	});
}
$(document).ready(function(){
	$('table.miningDiv tbody tr').hover(
			function(){
				$(this).css('background-color','#467c15');
			},
			function(){
				$(this).css('background-color','');
			}
	);
});