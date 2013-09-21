function toggleOrders(user){
	$(document).ready(function(){
		$('tr.'+user).each(function(){
			if($(this).css('display')=='none'){
				$(this).css('display','');
			}else{
				$(this).css('display','none');
			}
		});	
	});
}

function checkOrders(user){
	$('input#check_'+user).attr('checked', true);
}

$(document).ready(function(){
	
	$('tr.merge_group').hover(
			function(){
				$('+ tr.merge', this).css('background-color','#800000');
				$(this).css('background-color','#800000');
			},
			function(){
				$('+ tr.merge', this).css('background-color','');
				$(this).css('background-color','');
			}
	).css('cursor','pointer');
	$('input#open_all').click(function(){
		if($('input#open_all:checked').length == 0){
			$('tr.merge').css('display','none');
		}else{
			$('tr.merge').css('display','');
		}
	});
	$('form#settings input:radio').change(function(){
		$('form#settings').submit();
	});
});